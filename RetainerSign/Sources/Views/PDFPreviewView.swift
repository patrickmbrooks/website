import SwiftUI
import PDFKit

struct PDFKitView: UIViewRepresentable {
    let data: Data
    func makeUIView(context: Context) -> PDFView {
        let v = PDFView()
        v.autoScales = true
        v.displayMode = .singlePageContinuous
        v.document = PDFDocument(data: data)
        return v
    }
    func updateUIView(_ v: PDFView, context: Context) {
        v.document = PDFDocument(data: data)
    }
}

struct PDFPreviewView: View {
    @EnvironmentObject var settings: SettingsStore
    @Binding var retainer: Retainer
    @State private var pdf = Data()
    @State private var pdfURL = URL(fileURLWithPath: NSTemporaryDirectory()).appendingPathComponent("Retainer Agreement.pdf")
    @State private var showSend = false

    var body: some View {
        PDFKitView(data: pdf)
            .ignoresSafeArea(edges: .bottom)
            .navigationTitle("Preview")
            .navigationBarTitleDisplayMode(.inline)
            .onAppear { regenerate() }
            .toolbar {
                ToolbarItem(placement: .topBarTrailing) {
                    ShareLink(item: pdfURL) { Image(systemName: "square.and.arrow.up") }
                }
                ToolbarItem(placement: .bottomBar) {
                    Button { showSend = true } label: {
                        Label("Send / Sign", systemImage: "signature")
                    }
                    .buttonStyle(.borderedProminent)
                }
            }
            .sheet(isPresented: $showSend) {
                NavigationStack { SendView(retainer: $retainer, pdf: pdf) }
            }
    }

    private func regenerate() {
        pdf = PDFGenerator(firm: settings.firm, retainer: retainer).makePDF()
        let name = "Retainer Agreement - \(retainer.clientName).pdf"
        pdfURL = URL(fileURLWithPath: NSTemporaryDirectory()).appendingPathComponent(name)
        try? pdf.write(to: pdfURL, options: .atomic)
    }
}
