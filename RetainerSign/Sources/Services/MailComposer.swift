import SwiftUI
import MessageUI

/// Wraps the system mail sheet so the PDF can be emailed directly (no e-signature).
struct MailComposer: UIViewControllerRepresentable {
    let to: String
    let subject: String
    let body: String
    let attachment: Data
    let fileName: String
    let onFinish: (MFMailComposeResult) -> Void

    static var canSend: Bool { MFMailComposeViewController.canSendMail() }

    func makeUIViewController(context: Context) -> MFMailComposeViewController {
        let vc = MFMailComposeViewController()
        vc.mailComposeDelegate = context.coordinator
        vc.setToRecipients([to])
        vc.setSubject(subject)
        vc.setMessageBody(body, isHTML: false)
        vc.addAttachmentData(attachment, mimeType: "application/pdf", fileName: fileName)
        return vc
    }

    func updateUIViewController(_ uiViewController: MFMailComposeViewController, context: Context) {}
    func makeCoordinator() -> Coordinator { Coordinator(onFinish: onFinish) }

    final class Coordinator: NSObject, MFMailComposeViewControllerDelegate {
        let onFinish: (MFMailComposeResult) -> Void
        init(onFinish: @escaping (MFMailComposeResult) -> Void) { self.onFinish = onFinish }
        func mailComposeController(_ controller: MFMailComposeViewController, didFinishWith result: MFMailComposeResult, error: Error?) {
            controller.dismiss(animated: true)
            onFinish(result)
        }
    }
}
