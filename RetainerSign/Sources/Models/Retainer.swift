import Foundation

enum FeeType: String, Codable, CaseIterable, Identifiable {
    case flat = "Flat Fee"
    case hourly = "Hourly"
    case hybrid = "Flat Fee + Hourly"
    var id: String { rawValue }
}

enum RetainerStatus: String, Codable {
    case draft = "Draft"
    case sentForSignature = "Sent for signature"
    case signedInPerson = "Signed in person"
    case emailed = "Emailed"
    case completed = "Completed"
}

enum DocumentSource: String, Codable { case generated, scanned }

enum FieldKind: String, Codable, CaseIterable, Identifiable {
    case clientSignature = "Client signature"
    case clientDate = "Client date"
    case clientInitials = "Client initials"
    case attorneySignature = "Attorney signature"
    var id: String { rawValue }
    var isClient: Bool { self != .attorneySignature }
    /// Default size in PDF points.
    var size: CGSize {
        switch self {
        case .clientSignature, .attorneySignature: return CGSize(width: 160, height: 36)
        case .clientDate: return CGSize(width: 90, height: 22)
        case .clientInitials: return CGSize(width: 50, height: 22)
        }
    }
}

/// A field placed on a scanned document. Position is normalized (0…1) to the page,
/// measured from the top-left corner, so it survives any display scale.
struct SignatureField: Identifiable, Codable, Equatable {
    var id = UUID()
    var kind: FieldKind
    var pageIndex: Int
    var x: Double
    var y: Double
}

struct Retainer: Identifiable, Codable, Equatable {
    var id = UUID()
    var createdAt = Date()

    // Client
    var clientName = ""
    var clientEmail = ""
    var clientPhone = ""
    var clientAddress = ""

    // Engagement
    var matterDescription = ""
    var scopeOfWork = ""
    var feeType: FeeType = .flat
    var flatFee: Decimal = 0
    var hourlyRate: Decimal = 0
    var retainerDeposit: Decimal = 0
    var paymentTerms = "Due upon signing."
    var agreementDate = Date()

    // Scanned paper retainer (instead of the generated template)
    var source: DocumentSource = .generated
    var scannedPDFFileName: String?   // relative to Documents/Scans
    var fields: [SignatureField] = []

    // Tracking
    var status: RetainerStatus = .draft
    var docusignEnvelopeId: String?
    var signedAt: Date?
    var signedPDFFileName: String?   // relative to Documents/Signed
    var auditNote: String?

    var displayName: String { clientName.isEmpty ? "New retainer" : clientName }
}

extension Decimal {
    var currencyString: String {
        let f = NumberFormatter()
        f.numberStyle = .currency
        f.locale = Locale(identifier: "en_US")
        return f.string(from: self as NSDecimalNumber) ?? "$0.00"
    }
}
