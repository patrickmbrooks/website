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
