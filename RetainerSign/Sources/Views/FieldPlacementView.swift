import SwiftUI
import PDFKit

/// Shows each page of a scanned PDF; tap to drop a signature/date/initials field where the
/// client should sign, drag to nudge it, tap a field to remove it.
struct FieldPlacementView: View {
    let pdf: Data
    @Binding var fields: [SignatureField]
    var onDone: () -> Void

    @State private var pages: [UIImage] = []
    @State private var pageSizes: [CGSize] = []
    @State private var kind: FieldKind = .clientSignature

    var body: some View {
        VStack(spacing: 0) {
            Picker("Field", selection: $kind) {
                ForEach(FieldKind.allCases) { Text($0.rawValue).tag($0) }
            }
            .pickerStyle(.menu)
            .padding(.vertical, 6)

            Text("Tap where the \(kind.rawValue.lowercased()) goes. Drag to move. Tap a field to remove it.")
                .font(.caption).foregroundStyle(.secondary).padding(.bottom, 6)

            ScrollView {
                VStack(spacing: 16) {
                    ForEach(pages.indices, id: \.self) { i in
                        pageView(i)
                    }
                }
                .padding(.vertical)
            }
            .background(Color(.secondarySystemBackground))
        }
        .navigationTitle("Place signature fields")
        .navigationBarTitleDisplayMode(.inline)
        .toolbar {
            ToolbarItem(placement: .confirmationAction) {
                Button("Next") { onDone() }
                    .disabled(!fields.contains { $0.kind == .clientSignature })
            }
        }
        .onAppear(perform: renderPages)
    }

    private func pageView(_ i: Int) -> some View {
        GeometryReader { geo in
            let size = pageSizes[i]
            let scale = geo.size.width / size.width
            ZStack(alignment: .topLeading) {
                Image(uiImage: pages[i])
                    .resizable()
                    .frame(width: geo.size.width, height: size.height * scale)
                    .contentShape(Rectangle())
                    .onTapGesture { loc in
                        let w = kind.size.width * scale, h = kind.size.height * scale
                        let x = min(max(0, loc.x - w / 2), geo.size.width - w) / geo.size.width
                        let y = min(max(0, loc.y - h / 2), size.height * scale - h) / (size.height * scale)
                        fields.append(SignatureField(kind: kind, pageIndex: i, x: x, y: y))
                    }
                ForEach($fields) { $f in
                    if f.pageIndex == i {
                        FieldChip(field: $f, scale: scale, pageWidth: geo.size.width, pageHeight: size.height * scale) {
                            fields.removeAll { $0.id == f.id }
                        }
                    }
                }
            }
        }
        .aspectRatio(pageSizes[i].width / pageSizes[i].height, contentMode: .fit)
        .padding(.horizontal, 12)
        .shadow(radius: 2)
    }

    private func renderPages() {
        guard pages.isEmpty, let doc = PDFDocument(data: pdf) else { return }
        var imgs: [UIImage] = [], sizes: [CGSize] = []
        for i in 0..<doc.pageCount {
            guard let p = doc.page(at: i) else { continue }
            let b = p.bounds(for: .mediaBox)
            sizes.append(b.size)
            imgs.append(p.thumbnail(of: CGSize(width: b.width * 2, height: b.height * 2), for: .mediaBox))
        }
        pages = imgs; pageSizes = sizes
    }
}

private struct FieldChip: View {
    @Binding var field: SignatureField
    let scale: CGFloat
    let pageWidth: CGFloat
    let pageHeight: CGFloat
    let onRemove: () -> Void
    @State private var dragStart: CGPoint?

    var body: some View {
        let w = field.kind.size.width * scale, h = field.kind.size.height * scale
        ZStack {
            RoundedRectangle(cornerRadius: 4).fill(color.opacity(0.25))
            RoundedRectangle(cornerRadius: 4).stroke(color, lineWidth: 1.5)
            Text(label).font(.system(size: 9, weight: .semibold)).foregroundStyle(color).lineLimit(1).minimumScaleFactor(0.6)
        }
        .frame(width: w, height: h)
        .offset(x: field.x * pageWidth, y: field.y * pageHeight)
        .onTapGesture(perform: onRemove)
        .gesture(
            DragGesture()
                .onChanged { v in
                    if dragStart == nil { dragStart = CGPoint(x: field.x, y: field.y) }
                    let s = dragStart!
                    field.x = min(max(0, s.x + v.translation.width / pageWidth), 1 - w / pageWidth)
                    field.y = min(max(0, s.y + v.translation.height / pageHeight), 1 - h / pageHeight)
                }
                .onEnded { _ in dragStart = nil }
        )
    }

    private var color: Color { field.kind == .attorneySignature ? .purple : .orange }
    private var label: String {
        switch field.kind {
        case .clientSignature: return "Client signs"
        case .clientDate: return "Date"
        case .clientInitials: return "Initials"
        case .attorneySignature: return "Attorney signs"
        }
    }
}
