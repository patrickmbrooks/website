import Foundation
import AuthenticationServices
import CryptoKit
import UIKit
import PDFKit

/// Minimal DocuSign eSignature client: OAuth Authorization Code + PKCE (no backend or
/// client secret needed) and envelope creation with anchor-placed signature tabs.
@MainActor
final class DocuSignClient: NSObject, ObservableObject {
    enum DSError: LocalizedError {
        case notConfigured, notSignedIn, badResponse(String), cancelled
        var errorDescription: String? {
            switch self {
            case .notConfigured: return "Enter your DocuSign Integration Key in Settings first."
            case .notSignedIn: return "Sign in to DocuSign in Settings first."
            case .badResponse(let s): return s
            case .cancelled: return "Sign-in was cancelled."
            }
        }
    }

    struct Session: Codable {
        var accessToken: String
        var refreshToken: String?
        var expiresAt: Date
        var accountId: String
        var baseURI: String
        var userEmail: String
    }

    static let redirectURI = "retainersign://oauth"
    private static let sessionKey = "docusign.session"

    @Published private(set) var session: Session? = DocuSignClient.loadSession()
    var isSignedIn: Bool { session != nil }

    private let config: DocuSignConfig
    private var pkceVerifier = ""
    private var authSession: ASWebAuthenticationSession?

    init(config: DocuSignConfig) {
        self.config = config
        super.init()
    }

    private var authHost: String { config.useDemoEnvironment ? "account-d.docusign.com" : "account.docusign.com" }

    // MARK: Sign in / out

    func signIn() async throws {
        guard !config.integrationKey.isEmpty else { throw DSError.notConfigured }
        pkceVerifier = Self.randomString(64)
        let challenge = Self.base64url(SHA256.hash(data: Data(pkceVerifier.utf8)))

        var comps = URLComponents(string: "https://\(authHost)/oauth/auth")!
        comps.queryItems = [
            .init(name: "response_type", value: "code"),
            .init(name: "scope", value: "signature"),
            .init(name: "client_id", value: config.integrationKey),
            .init(name: "redirect_uri", value: Self.redirectURI),
            .init(name: "code_challenge", value: challenge),
            .init(name: "code_challenge_method", value: "S256"),
            .init(name: "prompt", value: "login"),
        ]

        let callback: URL = try await withCheckedThrowingContinuation { cont in
            let s = ASWebAuthenticationSession(url: comps.url!, callbackURLScheme: "retainersign") { url, error in
                if let url { cont.resume(returning: url) }
                else if let e = error as? ASWebAuthenticationSessionError, e.code == .canceledLogin { cont.resume(throwing: DSError.cancelled) }
                else { cont.resume(throwing: error ?? DSError.cancelled) }
            }
            s.presentationContextProvider = self
            s.prefersEphemeralWebBrowserSession = false
            self.authSession = s
            s.start()
        }

        guard let code = URLComponents(url: callback, resolvingAgainstBaseURL: false)?
                .queryItems?.first(where: { $0.name == "code" })?.value else {
            throw DSError.badResponse("DocuSign did not return an authorization code.")
        }
        try await exchange(form: [
            "grant_type": "authorization_code", "code": code,
            "client_id": config.integrationKey, "code_verifier": pkceVerifier,
            "redirect_uri": Self.redirectURI])
    }

    func signOut() {
        session = nil
        Keychain.delete(Self.sessionKey)
    }

    private func exchange(form: [String: String]) async throws {
        var req = URLRequest(url: URL(string: "https://\(authHost)/oauth/token")!)
        req.httpMethod = "POST"
        req.setValue("application/x-www-form-urlencoded", forHTTPHeaderField: "Content-Type")
        req.httpBody = form.map { "\($0.key)=\($0.value.addingPercentEncoding(withAllowedCharacters: .alphanumerics) ?? "")" }
            .joined(separator: "&").data(using: .utf8)
        let (data, resp) = try await URLSession.shared.data(for: req)
        guard (resp as? HTTPURLResponse)?.statusCode == 200,
              let json = try JSONSerialization.jsonObject(with: data) as? [String: Any],
              let access = json["access_token"] as? String else {
            throw DSError.badResponse("Token request failed: \(String(data: data, encoding: .utf8) ?? "")")
        }
        let expires = Date().addingTimeInterval(TimeInterval(json["expires_in"] as? Int ?? 3600) - 60)

        // Look up account id + base URI
        var info = URLRequest(url: URL(string: "https://\(authHost)/oauth/userinfo")!)
        info.setValue("Bearer \(access)", forHTTPHeaderField: "Authorization")
        let (idata, _) = try await URLSession.shared.data(for: info)
        guard let ijson = try JSONSerialization.jsonObject(with: idata) as? [String: Any],
              let accounts = ijson["accounts"] as? [[String: Any]],
              let acct = accounts.first(where: { ($0["is_default"] as? Bool) == true }) ?? accounts.first,
              let accountId = acct["account_id"] as? String,
              let base = acct["base_uri"] as? String else {
            throw DSError.badResponse("Could not read DocuSign account info.")
        }
        let s = Session(accessToken: access, refreshToken: json["refresh_token"] as? String,
                        expiresAt: expires, accountId: accountId, baseURI: base,
                        userEmail: ijson["email"] as? String ?? "")
        session = s
        if let d = try? JSONEncoder().encode(s), let str = String(data: d, encoding: .utf8) {
            Keychain.set(str, for: Self.sessionKey)
        }
    }

    private func validSession() async throws -> Session {
        guard let s = session else { throw DSError.notSignedIn }
        if s.expiresAt > Date() { return s }
        guard let refresh = s.refreshToken else { signOut(); throw DSError.notSignedIn }
        try await exchange(form: ["grant_type": "refresh_token", "refresh_token": refresh, "client_id": config.integrationKey])
        guard let fresh = session else { throw DSError.notSignedIn }
        return fresh
    }

    // MARK: Envelopes

    /// Sends the PDF for signature and returns the envelope id.
    /// - fields: for scanned documents, tab positions placed by the attorney. When empty,
    ///   the generated template's hidden anchor strings are used instead.
    func sendForSignature(pdf: Data, retainer: Retainer, firm: FirmProfile, fields: [SignatureField] = []) async throws -> String {
        let s = try await validSession()

        func anchorTabs(sig: String, date: String) -> [String: Any] {
            ["signHereTabs": [["anchorString": sig, "anchorUnits": "pixels", "anchorXOffset": "0", "anchorYOffset": "-10"]],
             "dateSignedTabs": [["anchorString": date, "anchorUnits": "pixels", "anchorXOffset": "0", "anchorYOffset": "0"]]]
        }
        /// DocuSign positions are in pixels at 72 dpi from the page's top-left, i.e. PDF points.
        func positioned(_ fs: [SignatureField]) -> [String: Any] {
            let pageSize = PDFPageSizes.sizes(of: pdf)
            func tab(_ f: SignatureField) -> [String: Any] {
                let page = pageSize.indices.contains(f.pageIndex) ? pageSize[f.pageIndex] : CGSize(width: 612, height: 792)
                let r = f.rect(in: CGRect(origin: .zero, size: page))
                return ["documentId": "1", "pageNumber": "\(f.pageIndex + 1)",
                        "xPosition": "\(Int(r.minX))", "yPosition": "\(Int(r.minY))"]
            }
            var t: [String: Any] = [:]
            let sig = fs.filter { $0.kind == .clientSignature || $0.kind == .attorneySignature }.map(tab)
            let dates = fs.filter { $0.kind == .clientDate }.map(tab)
            let initials = fs.filter { $0.kind == .clientInitials }.map(tab)
            if !sig.isEmpty { t["signHereTabs"] = sig }
            if !dates.isEmpty { t["dateSignedTabs"] = dates }
            if !initials.isEmpty { t["initialHereTabs"] = initials }
            return t
        }

        let clientTabs = fields.isEmpty
            ? anchorTabs(sig: PDFGenerator.clientSigAnchor, date: PDFGenerator.clientDateAnchor)
            : positioned(fields.filter { $0.kind.isClient })
        var signers: [[String: Any]] = [[
            "email": retainer.clientEmail, "name": retainer.clientName,
            "recipientId": "1", "routingOrder": "1",
            "tabs": clientTabs
        ]]
        let attorneyFields = fields.filter { $0.kind == .attorneySignature }
        if config.attorneySignsToo, !firm.email.isEmpty, fields.isEmpty || !attorneyFields.isEmpty {
            signers.append([
                "email": firm.email, "name": firm.attorneyName,
                "recipientId": "2", "routingOrder": "2",
                "tabs": fields.isEmpty
                    ? anchorTabs(sig: PDFGenerator.attorneySigAnchor, date: PDFGenerator.attorneyDateAnchor)
                    : positioned(attorneyFields)
            ])
        }
        let body: [String: Any] = [
            "emailSubject": config.emailSubject,
            "emailBlurb": config.emailBlurb,
            "documents": [[
                "documentBase64": pdf.base64EncodedString(),
                "name": "Retainer Agreement - \(retainer.clientName)",
                "fileExtension": "pdf", "documentId": "1"
            ]],
            "recipients": ["signers": signers],
            "status": "sent"
        ]

        var req = URLRequest(url: URL(string: "\(s.baseURI)/restapi/v2.1/accounts/\(s.accountId)/envelopes")!)
        req.httpMethod = "POST"
        req.setValue("Bearer \(s.accessToken)", forHTTPHeaderField: "Authorization")
        req.setValue("application/json", forHTTPHeaderField: "Content-Type")
        req.httpBody = try JSONSerialization.data(withJSONObject: body)
        let (data, resp) = try await URLSession.shared.data(for: req)
        let status = (resp as? HTTPURLResponse)?.statusCode ?? 0
        guard (200..<300).contains(status),
              let json = try JSONSerialization.jsonObject(with: data) as? [String: Any],
              let id = json["envelopeId"] as? String else {
            throw DSError.badResponse("DocuSign error (\(status)): \(String(data: data, encoding: .utf8) ?? "")")
        }
        return id
    }

    /// Returns the envelope status string (sent, delivered, completed, declined, voided…).
    func envelopeStatus(_ envelopeId: String) async throws -> String {
        let s = try await validSession()
        var req = URLRequest(url: URL(string: "\(s.baseURI)/restapi/v2.1/accounts/\(s.accountId)/envelopes/\(envelopeId)")!)
        req.setValue("Bearer \(s.accessToken)", forHTTPHeaderField: "Authorization")
        let (data, _) = try await URLSession.shared.data(for: req)
        let json = try JSONSerialization.jsonObject(with: data) as? [String: Any]
        return json?["status"] as? String ?? "unknown"
    }

    /// Downloads the combined signed document (with DocuSign's certificate of completion).
    func downloadCompleted(_ envelopeId: String) async throws -> Data {
        let s = try await validSession()
        var req = URLRequest(url: URL(string: "\(s.baseURI)/restapi/v2.1/accounts/\(s.accountId)/envelopes/\(envelopeId)/documents/combined?certificate=true")!)
        req.setValue("Bearer \(s.accessToken)", forHTTPHeaderField: "Authorization")
        let (data, resp) = try await URLSession.shared.data(for: req)
        guard (resp as? HTTPURLResponse)?.statusCode == 200 else { throw DSError.badResponse("Download failed.") }
        return data
    }

    // MARK: Helpers

    private static func loadSession() -> Session? {
        guard let str = Keychain.get(sessionKey), let d = str.data(using: .utf8) else { return nil }
        return try? JSONDecoder().decode(Session.self, from: d)
    }

    private static func randomString(_ n: Int) -> String {
        let chars = Array("ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-._~")
        return String((0..<n).map { _ in chars.randomElement()! })
    }

    private static func base64url<D: Sequence>(_ bytes: D) -> String where D.Element == UInt8 {
        Data(bytes).base64EncodedString()
            .replacingOccurrences(of: "+", with: "-")
            .replacingOccurrences(of: "/", with: "_")
            .replacingOccurrences(of: "=", with: "")
    }
}

extension DocuSignClient: ASWebAuthenticationPresentationContextProviding {
    nonisolated func presentationAnchor(for session: ASWebAuthenticationSession) -> ASPresentationAnchor {
        MainActor.assumeIsolated {
            let scenes = UIApplication.shared.connectedScenes.compactMap { $0 as? UIWindowScene }
            return scenes.flatMap { $0.windows }.first { $0.isKeyWindow } ?? ASPresentationAnchor()
        }
    }
}

enum PDFPageSizes {
    static func sizes(of data: Data) -> [CGSize] {
        guard let doc = PDFDocument(data: data) else { return [] }
        return (0..<doc.pageCount).map { doc.page(at: $0)?.bounds(for: .mediaBox).size ?? CGSize(width: 612, height: 792) }
    }
}
