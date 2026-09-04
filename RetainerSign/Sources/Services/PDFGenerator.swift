import UIKit
import CoreText

/// Builds the retainer agreement PDF (US Letter). Signature lines carry hidden anchor
/// strings so DocuSign can auto-place signature and date tabs.
struct PDFGenerator {
    static let clientSigAnchor = "/client_sig/"
    static let clientDateAnchor = "/client_date/"
    static let attorneySigAnchor = "/atty_sig/"
    static let attorneyDateAnchor = "/atty_date/"

    let firm: FirmProfile
    let retainer: Retainer
    /// Optional in-person signature captured on device, drawn onto the client signature line.
    var clientSignature: UIImage? = nil
    var attorneySignature: UIImage? = nil
    var auditFooter: String? = nil

    private let pageRect = CGRect(x: 0, y: 0, width: 612, height: 792)
    private let margin: CGFloat = 60
    private var contentRect: CGRect { pageRect.insetBy(dx: margin, dy: margin) }

    // MARK: Public

    func makePDF() -> Data {
        let format = UIGraphicsPDFRendererFormat()
        format.documentInfo = [
            kCGPDFContextTitle as String: "Retainer Agreement - \(retainer.clientName)",
            kCGPDFContextAuthor as String: firm.firmName,
            kCGPDFContextCreator as String: "RetainerSign"
        ]
        let renderer = UIGraphicsPDFRenderer(bounds: pageRect, format: format)
        let body = attributedBody()

        return renderer.pdfData { ctx in
            var pageNumber = 0
            let framesetter = CTFramesetterCreateWithAttributedString(body)
            var location = 0
            let length = body.length
            var lastFrameBottom: CGFloat = contentRect.minY

            while location < length {
                pageNumber += 1
                ctx.beginPage()
                drawHeaderFooter(page: pageNumber)

                let cg = ctx.cgContext
                let path = CGPath(rect: contentRect, transform: nil)
                let frame = CTFramesetterCreateFrame(framesetter, CFRange(location: location, length: 0), path, nil)

                cg.saveGState()
                cg.textMatrix = .identity
                cg.translateBy(x: 0, y: pageRect.height)
                cg.scaleBy(x: 1, y: -1)
                CTFrameDraw(frame, cg)
                cg.restoreGState()

                let visible = CTFrameGetVisibleStringRange(frame)
                lastFrameBottom = usedHeight(of: frame) + contentRect.minY
                if visible.length == 0 { break }   // safety: nothing fit
                location += visible.length
            }

            // Signature block: needs ~230pt; start a new page if it won't fit.
            let needed: CGFloat = 230
            var y = lastFrameBottom + 30
            if y + needed > contentRect.maxY {
                pageNumber += 1
                ctx.beginPage()
                drawHeaderFooter(page: pageNumber)
                y = contentRect.minY
            }
            drawSignatureBlock(startY: y)

            if let audit = auditFooter {
                pageNumber += 1
                ctx.beginPage()
                drawHeaderFooter(page: pageNumber)
                drawAuditPage(audit)
            }
        }
    }

    // MARK: Template filling

    func filledText() -> String {
        var fee: String
        switch retainer.feeType {
        case .flat:
            fee = "Client agrees to pay Attorney a flat fee of \(retainer.flatFee.currencyString) for the services described above. This fee is earned as services are performed and is not contingent on the outcome of the matter."
        case .hourly:
            fee = "Client agrees to pay Attorney for services at the rate of \(retainer.hourlyRate.currencyString) per hour, billed in increments of one-tenth (0.1) of an hour."
        case .hybrid:
            fee = "Client agrees to pay Attorney a flat fee of \(retainer.flatFee.currencyString) for the services described above. Services outside that scope will be billed at \(retainer.hourlyRate.currencyString) per hour in increments of one-tenth (0.1) of an hour."
        }
        if retainer.retainerDeposit > 0 {
            fee += " Client agrees to pay an initial retainer deposit of \(retainer.retainerDeposit.currencyString), to be held in trust and applied as described below."
        }

        let df = DateFormatter()
        df.dateStyle = .long
        let map: [String: String] = [
            "date": df.string(from: retainer.agreementDate),
            "firm_name": firm.firmName,
            "attorney_name": firm.attorneyName,
            "firm_address": firm.address.isEmpty ? "________________" : firm.address,
            "client_name": retainer.clientName,
            "client_address": retainer.clientAddress.isEmpty ? "________________" : retainer.clientAddress,
            "client_email": retainer.clientEmail,
            "matter": retainer.matterDescription,
            "scope": retainer.scopeOfWork.isEmpty ? "the legal services reasonably necessary to handle the matter described above" : retainer.scopeOfWork,
            "fee_terms": fee,
            "payment_terms": retainer.paymentTerms,
            "governing_state": firm.governingState.isEmpty ? "________" : firm.governingState,
        ]
        var text = firm.template
        for (k, v) in map { text = text.replacingOccurrences(of: "{{\(k)}}", with: v) }
        return text
    }

    private func attributedBody() -> NSAttributedString {
        let text = filledText()
        let result = NSMutableAttributedString()
        let bodyFont = UIFont(name: "TimesNewRomanPSMT", size: 11.5) ?? .systemFont(ofSize: 11.5)
        let headFont = UIFont(name: "TimesNewRomanPS-BoldMT", size: 11.5) ?? .boldSystemFont(ofSize: 11.5)
        let titleFont = UIFont(name: "TimesNewRomanPS-BoldMT", size: 15) ?? .boldSystemFont(ofSize: 15)

        let paragraphs = text.components(separatedBy: "\n")
        for (i, raw) in paragraphs.enumerated() {
            let line = raw.trimmingCharacters(in: .whitespaces)
            let style = NSMutableParagraphStyle()
            style.paragraphSpacing = 6
            style.lineSpacing = 2
            var font = bodyFont
            if i == 0 {
                font = titleFont
                style.alignment = .center
                style.paragraphSpacing = 14
            } else if line.range(of: #"^\d+\.\s+[A-Z][A-Z\s;,&'-]+$"#, options: .regularExpression) != nil {
                font = headFont
                style.paragraphSpacingBefore = 6
            } else {
                style.alignment = .justified
            }
            let attrs: [NSAttributedString.Key: Any] = [.font: font, .paragraphStyle: style, .foregroundColor: UIColor.black]
            result.append(NSAttributedString(string: line + (i < paragraphs.count - 1 ? "\n" : ""), attributes: attrs))
        }
        return result
    }

    // MARK: Drawing helpers

    private func usedHeight(of frame: CTFrame) -> CGFloat {
        let lines = CTFrameGetLines(frame) as! [CTLine]
        guard !lines.isEmpty else { return 0 }
        var origins = [CGPoint](repeating: .zero, count: lines.count)
        CTFrameGetLineOrigins(frame, CFRange(location: 0, length: 0), &origins)
        var descent: CGFloat = 0
        CTLineGetTypographicBounds(lines.last!, nil, &descent, nil)
        // origins are relative to the frame path's flipped coordinate space (bottom-left origin)
        let lastOriginY = origins.last!.y
        return contentRect.height - lastOriginY + descent
    }

    private func drawHeaderFooter(page: Int) {
        let small = UIFont.systemFont(ofSize: 8)
        let gray = UIColor.darkGray
        let header = "\(firm.firmName)  ·  Retainer Agreement  ·  \(retainer.clientName)"
        header.draw(at: CGPoint(x: margin, y: 30), withAttributes: [.font: small, .foregroundColor: gray])
        let footer = "Page \(page)"
        let size = footer.size(withAttributes: [.font: small])
        footer.draw(at: CGPoint(x: pageRect.midX - size.width / 2, y: pageRect.height - 40), withAttributes: [.font: small, .foregroundColor: gray])
        // initials line for the client on every page
        "Client initials: ______".draw(at: CGPoint(x: pageRect.maxX - margin - 100, y: pageRect.height - 40), withAttributes: [.font: small, .foregroundColor: gray])
    }

    private func drawSignatureBlock(startY: CGFloat) {
        let label = UIFont.systemFont(ofSize: 10)
        let bold = UIFont.boldSystemFont(ofSize: 11)
        let lineWidth: CGFloat = 230
        let colX = [contentRect.minX, contentRect.minX + 260]
        var y = startY

        "AGREED AND ACCEPTED:".draw(at: CGPoint(x: colX[0], y: y), withAttributes: [.font: bold])
        y += 40

        func column(x: CGFloat, title: String, name: String, sigAnchor: String, dateAnchor: String, image: UIImage?) {
            var cy = y
            title.draw(at: CGPoint(x: x, y: cy), withAttributes: [.font: bold])
            cy += 40
            // signature line
            if let img = image {
                let h: CGFloat = 50
                let w = min(lineWidth, img.size.width * h / max(img.size.height, 1))
                img.draw(in: CGRect(x: x, y: cy - h + 4, width: w, height: h))
            }
            drawAnchor(sigAnchor, at: CGPoint(x: x + 2, y: cy - 28))
            hr(x: x, y: cy, width: lineWidth)
            "Signature".draw(at: CGPoint(x: x, y: cy + 3), withAttributes: [.font: label])
            cy += 32
            name.draw(at: CGPoint(x: x, y: cy - 14), withAttributes: [.font: label])
            hr(x: x, y: cy, width: lineWidth)
            "Printed name".draw(at: CGPoint(x: x, y: cy + 3), withAttributes: [.font: label])
            cy += 32
            drawAnchor(dateAnchor, at: CGPoint(x: x + 2, y: cy - 14))
            hr(x: x, y: cy, width: lineWidth)
            "Date".draw(at: CGPoint(x: x, y: cy + 3), withAttributes: [.font: label])
        }

        column(x: colX[0], title: "CLIENT", name: retainer.clientName,
               sigAnchor: Self.clientSigAnchor, dateAnchor: Self.clientDateAnchor, image: clientSignature)
        column(x: colX[1], title: "ATTORNEY", name: "\(firm.attorneyName), \(firm.firmName)",
               sigAnchor: Self.attorneySigAnchor, dateAnchor: Self.attorneyDateAnchor, image: attorneySignature)
    }

    /// Draws DocuSign anchor text in white so it is invisible but searchable.
    private func drawAnchor(_ text: String, at point: CGPoint) {
        text.draw(at: point, withAttributes: [.font: UIFont.systemFont(ofSize: 9), .foregroundColor: UIColor.white])
    }

    private func hr(x: CGFloat, y: CGFloat, width: CGFloat) {
        let p = UIBezierPath()
        p.move(to: CGPoint(x: x, y: y))
        p.addLine(to: CGPoint(x: x + width, y: y))
        p.lineWidth = 0.8
        UIColor.black.setStroke()
        p.stroke()
    }

    private func drawAuditPage(_ audit: String) {
        "SIGNATURE CERTIFICATE".draw(at: CGPoint(x: contentRect.minX, y: contentRect.minY), withAttributes: [.font: UIFont.boldSystemFont(ofSize: 14)])
        let style = NSMutableParagraphStyle()
        style.lineSpacing = 3
        let rect = CGRect(x: contentRect.minX, y: contentRect.minY + 30, width: contentRect.width, height: contentRect.height - 30)
        (audit as NSString).draw(in: rect, withAttributes: [.font: UIFont(name: "Menlo", size: 9) ?? .systemFont(ofSize: 9), .paragraphStyle: style])
    }
}
