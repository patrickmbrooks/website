import SwiftUI

/// Scan a paper retainer → enter client details → place fields → send/sign.
struct ScanFlowView: View {
    @EnvironmentObject var store: RetainerStore
    @EnvironmentObject var settings: SettingsStore
    @Environment(\.dismiss) private var dismiss

    @State private var retainer: Retainer = {
        var r = Retainer(); r.source = .scanned; return r
    }()
    @State private var pdf: Data?
    @State private var showScanner = true
    @State private var step = 0   // 0 details, 1 placement, 2 send

    var body: some View {
        Group {
            if pdf == nil {
                ContentUnavailableView("Scan the retainer", systemImage: "doc.viewfinder",
                                       description: Text("Use the camera to capture each page of the paper agreement."))
                    .overlay(alignment: .bottom) {
                        Button("Open camera") { showScanner = true }
                            .buttonStyle(.borderedProminent).padding()
                    }
            } else {
                detailsForm
            }
        }
        .navigationTitle("Scanned retainer")
        .navigationBarTitleDisplayMode(.inline)
        .toolbar { ToolbarItem(placement: .cancellationAction) { Button("Cancel") { dismiss() } } }
        .fullScreenCover(isPresented: $showScanner) {
            if DocumentScannerView.isSupported {
                DocumentScannerView(onScan: { images in
                    showScanner = false
                    savePDF(ScanPDF.make(from: images, title: "Retainer Agreement"))
                }, onCancel: { showScanner = false })
                .ignoresSafeArea()
            } else {
                ContentUnavailableView("Camera scanning isn't available on this device", systemImage: "camera.fill")
                    .overlay(alignment: .bottom) { Button("Close") { showScanner = false }.padding() }
            }
        }
        .navigationDestination(isPresented: Binding(get: { step == 1 }, set: { if !$0 { step = 0 } })) {
            if let pdf {
                FieldPlacementView(pdf: pdf, fields: $retainer.fields) {
                    store.upsert(retainer)
                    step = 2
                }
                .navigationDestination(isPresented: Binding(get: { step == 2 }, set: { if !$0 { step = 1 } })) {
                    SendView(retainer: $retainer, pdf: pdf)
                }
            }
        }
    }

    private var detailsForm: some View {
        Form {
            Section("Client") {
                TextField("Full name", text: $retainer.clientName).textContentType(.name)
                TextField("Email", text: $retainer.clientEmail)
                    .textContentType(.emailAddress).keyboardType(.emailAddress).textInputAutocapitalization(.never)
                TextField("Matter (for your records)", text: $retainer.matterDescription)
            }
            Section {
                if let pdf, let url = tempURL(pdf) {
                    ShareLink(item: url) { Label("View / share scan", systemImage: "doc.text.magnifyingglass") }
                }
                Button { showScanner = true } label: { Label("Rescan", systemImage: "camera") }
            }
            Section {
                Button {
                    store.upsert(retainer)
                    step = 1
                } label: { Label("Place signature fields", systemImage: "signature") }
                .disabled(retainer.clientName.isEmpty)
            }
        }
    }

    private func savePDF(_ data: Data) {
        let name = "Scan-\(retainer.id.uuidString).pdf"
        try? data.write(to: RetainerStore.scansFolder.appendingPathComponent(name), options: [.atomic, .completeFileProtection])
        retainer.scannedPDFFileName = name
        retainer.fields = []
        pdf = data
    }

    private func tempURL(_ data: Data) -> URL? {
        let url = URL(fileURLWithPath: NSTemporaryDirectory()).appendingPathComponent("Scanned Retainer.pdf")
        return (try? data.write(to: url, options: .atomic)) != nil ? url : nil
    }
}
