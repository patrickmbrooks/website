import SwiftUI
import VisionKit
import PDFKit

/// Wraps the system document camera (auto edge detection, perspective correction).
struct DocumentScannerView: UIViewControllerRepresentable {
    let onScan: ([UIImage]) -> Void
    let onCancel: () -> Void

    static var isSupported: Bool { VNDocumentCameraViewController.isSupported }

    func makeUIViewController(context: Context) -> VNDocumentCameraViewController {
        let vc = VNDocumentCameraViewController()
        vc.delegate = context.coordinator
        return vc
    }
    func updateUIViewController(_ vc: VNDocumentCameraViewController, context: Context) {}
    func makeCoordinator() -> Coordinator { Coordinator(self) }

    final class Coordinator: NSObject, VNDocumentCameraViewControllerDelegate {
        let parent: DocumentScannerView
        init(_ p: DocumentScannerView) { parent = p }
        func documentCameraViewController(_ controller: VNDocumentCameraViewController, didFinishWith scan: VNDocumentCameraScan) {
            parent.onScan((0..<scan.pageCount).map { scan.imageOfPage(at: $0) })
        }
        func documentCameraViewControllerDidCancel(_ controller: VNDocumentCameraViewController) { parent.onCancel() }
        func documentCameraViewController(_ controller: VNDocumentCameraViewController, didFailWithError error: Error) { parent.onCancel() }
    }
}

enum ScanPDF {
    /// Converts scanned page images into a US Letter PDF (each image fit to the page).
    static func make(from images: [UIImage], title: String) -> Data {
        let page = CGRect(x: 0, y: 0, width: 612, height: 792)
        let format = UIGraphicsPDFRendererFormat()
        format.documentInfo = [kCGPDFContextTitle as String: title, kCGPDFContextCreator as String: "RetainerSign"]
        return UIGraphicsPDFRenderer(bounds: page, format: format).pdfData { ctx in
            for img in images {
                ctx.beginPage()
                let scale = min(page.width / img.size.width, page.height / img.size.height)
                let w = img.size.width * scale, h = img.size.height * scale
                img.draw(in: CGRect(x: (page.width - w) / 2, y: (page.height - h) / 2, width: w, height: h))
            }
        }
    }
}

/// Draws signatures/dates/initials onto an existing PDF at the placed fields, and
/// optionally appends a certificate page.
enum PDFStamper {
    static func stamp(pdf: Data, fields: [SignatureField], signature: UIImage,
                      initials: UIImage?, dateText: String, certificate: String?) -> Data {
        guard let doc = PDFDocument(data: pdf) else { return pdf }
        let first = doc.page(at: 0)?.bounds(for: .mediaBox) ?? CGRect(x: 0, y: 0, width: 612, height: 792)
        return UIGraphicsPDFRenderer(bounds: first).pdfData { ctx in
            for i in 0..<doc.pageCount {
                guard let page = doc.page(at: i) else { continue }
                let bounds = page.bounds(for: .mediaBox)
                ctx.beginPage(withBounds: bounds, pageInfo: [:])
                let cg = ctx.cgContext
                cg.saveGState()
                cg.translateBy(x: 0, y: bounds.height)
                cg.scaleBy(x: 1, y: -1)
                page.draw(with: .mediaBox, to: cg)
                cg.restoreGState()

                for f in fields where f.pageIndex == i && f.isClientField {
                    let rect = f.rect(in: bounds)
                    switch f.kind {
                    case .clientSignature:
                        draw(signature, fitting: rect)
                    case .clientInitials:
                        if let initials { draw(initials, fitting: rect) } else { draw(signature, fitting: rect) }
                    case .clientDate:
                        (dateText as NSString).draw(in: rect, withAttributes: [.font: UIFont.systemFont(ofSize: 12), .foregroundColor: UIColor(red: 0.05, green: 0.1, blue: 0.4, alpha: 1)])
                    case .attorneySignature: break
                    }
                }
            }
            if let certificate {
                ctx.beginPage()
                "SIGNATURE CERTIFICATE".draw(at: CGPoint(x: 60, y: 60), withAttributes: [.font: UIFont.boldSystemFont(ofSize: 14)])
                (certificate as NSString).draw(in: CGRect(x: 60, y: 90, width: 492, height: 640),
                                               withAttributes: [.font: UIFont(name: "Menlo", size: 9) ?? .systemFont(ofSize: 9)])
            }
        }
    }

    private static func draw(_ img: UIImage, fitting rect: CGRect) {
        let scale = min(rect.width / img.size.width, rect.height / img.size.height)
        let w = img.size.width * scale, h = img.size.height * scale
        img.draw(in: CGRect(x: rect.minX, y: rect.maxY - h, width: w, height: h))
    }
}

extension SignatureField {
    var isClientField: Bool { kind.isClient }
    /// Field rectangle in page points (top-left origin), given the page bounds.
    func rect(in page: CGRect) -> CGRect {
        CGRect(x: x * page.width, y: y * page.height, width: kind.size.width, height: kind.size.height)
    }
}
