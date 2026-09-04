import SwiftUI
import MessageUI
import CryptoKit

struct SendView: View {
    @EnvironmentObject var store: RetainerStore
    @EnvironmentObject var settings: SettingsStore
    @Environment(\.dismiss) private var dismiss
    @Binding var retainer: Retainer
    let pdf: Data

    @State private var busy = false
    @State private var message: String?
    @State private var showMail = false
    @State private var showSignaturePad = false

    var body: some View {
        List {
            Section {
                Button { Task { await sendViaDocuSign() } } label: {
                    optionRow("Send with DocuSign", "envelope.badge",
                              "Emails the client a signing link. Legally binding e-signature with audit certificate.")
                }
                .disabled(busy || retainer.clientEmail.isEmpty)
            } footer: {
                if retainer.clientEmail.isEmpty { Text("Add a client email to send for signature.") }
            }

            Section {
                Button { showSignaturePad = true } label: {
                    optionRow("Sign in person on this device", "hand.draw",
                              "Client signs on screen now. Signature, timestamp and document hash are stamped into the PDF.")
                }
                .disabled(busy)
            }

            Section {
                Button { showMail = true } label: {
                    optionRow("Email the PDF (no e-signature)", "paperplane",
                              "Attaches the unsigned PDF to a new email for the client to print and sign.")
                }
                .disabled(busy || !MailComposer.canSend)
            } footer: {
                if !MailComposer.canSend { Text("Set up an account in the Mail app to use this option.") }
            }

            if let message {
                Section { Text(message).font(.footnote) }
            }
        }
        .navigationTitle("Send or sign")
        .navigationBarTitleDisplayMode(.inline)
        .overlay { if busy { ProgressView("Sending…").padding().background(.regularMaterial, in: RoundedRectangle(cornerRadius: 12)) } }
        .toolbar { ToolbarItem(placement: .cancellationAction) { Button("Close") { dismiss() } } }
        .sheet(isPresented: $showMail) {
            MailComposer(to: retainer.clientEmail,
                         subject: "Retainer Agreement – \(settings.firm.firmName)",
                         body: "Hello \(retainer.clientName),\n\nPlease find your retainer agreement attached. Sign and return a copy at your convenience.\n\n\(settings.firm.attorneyName)\n\(settings.firm.firmName)",
                         attachment: pdf, fileName: "Retainer Agreement - \(retainer.clientName).pdf") { result in
                if result == .sent {
                    retainer.status = .emailed
                    store.upsert(retainer)
                    message = "Email sent."
                }
            }
        }
        .sheet(isPresented: $showSignaturePad) {
            NavigationStack {
                SignaturePadView(signerName: retainer.clientName) { image in
                    finishInPersonSignature(image)
                }
            }
        }
    }

    private func optionRow(_ title: String, _ icon: String, _ subtitle: String) -> some View {
        HStack(spacing: 14) {
            Image(systemName: icon).font(.title2).frame(width: 32)
            VStack(alignment: .leading, spacing: 3) {
                Text(title).font(.headline)
                Text(subtitle).font(.caption).foregroundStyle(.secondary)
            }
        }
        .padding(.vertical, 4)
    }

    // MARK: DocuSign

    private func sendViaDocuSign() async {
        busy = true; defer { busy = false }
        let client = DocuSignClient(config: settings.docusign)
        do {
            if !client.isSignedIn { try await client.signIn() }
            let fields = retainer.source == .scanned ? retainer.fields : []
            let id = try await client.sendForSignature(pdf: pdf, retainer: retainer, firm: settings.firm, fields: fields)
            retainer.docusignEnvelopeId = id
            retainer.status = .sentForSignature
            store.upsert(retainer)
            message = "Sent. DocuSign will email \(retainer.clientEmail) a signing link and email the completed agreement to both of you. Envelope \(id)."
        } catch {
            message = "DocuSign: \(error.localizedDescription)"
        }
    }

    // MARK: In-person

    private func finishInPersonSignature(_ image: UIImage) {
        let now = Date()
        let unsignedHash = SHA256.hash(data: pdf).map { String(format: "%02x", $0) }.joined()
        let audit = """
        Document: Retainer Agreement – \(retainer.clientName)
        Signer:   \(retainer.clientName) (\(retainer.clientEmail))
        Method:   Handwritten electronic signature captured in person on \(UIDevice.current.name) (\(UIDevice.current.systemName) \(UIDevice.current.systemVersion))
        Witness:  \(settings.firm.attorneyName), \(settings.firm.firmName)
        Signed:   \(ISO8601DateFormatter().string(from: now))
        Time zone: \(TimeZone.current.identifier)
        SHA-256 of unsigned agreement: \(unsignedHash)
        Retainer record id: \(retainer.id.uuidString)

        The signer viewed the complete agreement on screen and applied the above signature
        with intent to be bound. This certificate was generated by RetainerSign and appended
        to the agreement at the moment of signing.
        """
        let signed: Data
        if retainer.source == .scanned {
            let df = DateFormatter(); df.dateStyle = .medium
            signed = PDFStamper.stamp(pdf: pdf, fields: retainer.fields, signature: image, initials: nil,
                                      dateText: df.string(from: now), certificate: audit)
        } else {
            signed = PDFGenerator(firm: settings.firm, retainer: retainer, clientSignature: image, auditFooter: audit).makePDF()
        }
        let fileName = "Retainer-\(retainer.clientName.replacingOccurrences(of: " ", with: "_"))-\(Int(now.timeIntervalSince1970)).pdf"
        try? signed.write(to: RetainerStore.signedFolder.appendingPathComponent(fileName), options: [.atomic, .completeFileProtection])

        retainer.signedAt = now
        retainer.signedPDFFileName = fileName
        retainer.auditNote = audit
        retainer.status = .signedInPerson
        store.upsert(retainer)
        showSignaturePad = false
        message = "Signed. The signed PDF is saved on this device; open the retainer to share it or email a copy to the client."
    }
}
