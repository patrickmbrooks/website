import Foundation

struct FirmProfile: Codable, Equatable {
    var firmName = "Patrick Brooks Law"
    var attorneyName = "Patrick Brooks"
    var barNumber = ""
    var address = ""
    var phone = ""
    var email = ""
    var governingState = ""
    /// Retainer agreement body. Supports {{placeholders}}; see DefaultTemplate.txt.
    var template: String = FirmProfile.defaultTemplate

    static var defaultTemplate: String {
        if let url = Bundle.main.url(forResource: "DefaultTemplate", withExtension: "txt"),
           let s = try? String(contentsOf: url, encoding: .utf8) {
            return s
        }
        return "RETAINER AGREEMENT\n\nThis agreement is between {{firm_name}} and {{client_name}}."
    }
}

struct DocuSignConfig: Codable, Equatable {
    var integrationKey = ""          // "Client ID" from DocuSign Apps & Keys
    var useDemoEnvironment = true    // account-d.docusign.com sandbox vs production
    var attorneySignsToo = true      // add the attorney as a second signer
    var emailSubject = "Please sign your retainer agreement"
    var emailBlurb = "Please review and sign the attached retainer agreement. Reply to this email with any questions."
}
