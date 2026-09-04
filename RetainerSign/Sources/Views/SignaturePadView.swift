import SwiftUI

/// Finger/Pencil signature capture. Returns a PNG-backed UIImage of the strokes.
struct SignaturePadView: View {
    let signerName: String
    let onDone: (UIImage) -> Void
    @Environment(\.dismiss) private var dismiss
    @State private var strokes: [[CGPoint]] = []
    @State private var current: [CGPoint] = []
    @State private var agreed = false

    var body: some View {
        VStack(spacing: 16) {
            Text("\(signerName), please sign below").font(.headline)

            canvas
                .frame(height: 220)
                .background(Color.white)
                .clipShape(RoundedRectangle(cornerRadius: 12))
                .overlay(RoundedRectangle(cornerRadius: 12).stroke(.secondary.opacity(0.4)))
                .padding(.horizontal)

            Toggle(isOn: $agreed) {
                Text("I have read the retainer agreement and agree to sign it electronically.")
                    .font(.footnote)
            }
            .padding(.horizontal)

            HStack {
                Button("Clear", role: .destructive) { strokes = []; current = [] }
                Spacer()
                Button("Apply signature") {
                    if let img = render() { onDone(img) }
                }
                .buttonStyle(.borderedProminent)
                .disabled(strokes.isEmpty || !agreed)
            }
            .padding(.horizontal)
            Spacer()
        }
        .padding(.top)
        .navigationTitle("Sign")
        .navigationBarTitleDisplayMode(.inline)
        .toolbar { ToolbarItem(placement: .cancellationAction) { Button("Cancel") { dismiss() } } }
    }

    private var canvas: some View {
        SignatureCanvas(strokes: strokes, current: current)
            .gesture(
                DragGesture(minimumDistance: 0, coordinateSpace: .local)
                    .onChanged { v in current.append(v.location) }
                    .onEnded { _ in
                        if !current.isEmpty { strokes.append(current) }
                        current = []
                    }
            )
    }

    @MainActor
    private func render() -> UIImage? {
        // Crop to the ink bounds with a little padding, on a transparent background.
        let pts = strokes.flatMap { $0 }
        guard let minX = pts.map(\.x).min(), let maxX = pts.map(\.x).max(),
              let minY = pts.map(\.y).min(), let maxY = pts.map(\.y).max() else { return nil }
        let pad: CGFloat = 8
        let bounds = CGRect(x: minX - pad, y: minY - pad, width: maxX - minX + pad * 2, height: maxY - minY + pad * 2)
        let shifted = strokes.map { $0.map { CGPoint(x: $0.x - bounds.minX, y: $0.y - bounds.minY) } }
        let renderer = ImageRenderer(content: SignatureCanvas(strokes: shifted, current: [])
            .frame(width: bounds.width, height: bounds.height))
        renderer.scale = 3
        renderer.isOpaque = false
        return renderer.uiImage
    }
}

private struct SignatureCanvas: View {
    let strokes: [[CGPoint]]
    let current: [CGPoint]
    var body: some View {
        Canvas { ctx, _ in
            for s in strokes + [current] where s.count > 1 {
                var p = Path()
                p.move(to: s[0])
                for pt in s.dropFirst() { p.addLine(to: pt) }
                ctx.stroke(p, with: .color(Color(red: 0.05, green: 0.1, blue: 0.4)),
                           style: StrokeStyle(lineWidth: 2.5, lineCap: .round, lineJoin: .round))
            }
        }
    }
}
