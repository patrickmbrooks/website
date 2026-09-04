import SwiftUI

struct RetainerEditorView: View {
    @EnvironmentObject var store: RetainerStore
    @EnvironmentObject var settings: SettingsStore
    @Environment(\.dismiss) private var dismiss
    @State var retainer: Retainer
    @State private var showPreview = false
    @State private var showFields = false
    @State private var showSend = false
    @State private var scanPDF: Data?
    @State private var dsMessage: String?
    @State private var checking = false

    var body: some View {
        Form {
            Section("Client") {
                TextField("Full name", text: $retainer.clientName)
                    .textContentType(.name)
                TextField("Email", text: $retainer.clientEmail)
                    .textContentType(.emailAddress).keyboardType(.emailAddress).textInputAutocapitalization(.never)
                TextField("Phone", text: $retainer.clientPhone)
                    .textContentType(.telephoneNumber).keyboardType(.phonePad)
                TextField("Address", text: $retainer.clientAddress, axis: .vertical)
                    .textContentType(.fullStreetAddress)
            }
            if retainer.source == .scanned {
                Section("Scanned document") {
                    TextField("Matter (for your records)", text: $retainer.matterDescription)
                    if let name = retainer.scannedPDFFileName {
                        ShareLink(item: RetainerStore.scansFolder.appendingPathComponent(name)) {
                            Label("View / share scan", systemImage: "doc.text.magnifyingglass")
                        }
                    }
                    Button { showFields = true } label: {
                        Label(retainer.fields.isEmpty ? "Place signature fields" : "Edit signature fields (\(retainer.fields.count))", systemImage: "signature")
                    }
                }
            } else {
            Section("Matter") {
                TextField("Matter (e.g. I-130 petition for spouse)", text: $retainer.matterDescription, axis: .vertical)
                TextField("Scope of work (optional)", text: $retainer.scopeOfWork, axis: .vertical)
                    .lineLimit(2...6)
                DatePicker("Agreement date", selection: $retainer.agreementDate, displayedComponents: .date)
            }
            Section("Fees") {
                Picker("Fee type", selection: $retainer.feeType) {
                    ForEach(FeeType.allCases) { Text($0.rawValue).tag($0) }
                }
                if retainer.feeType != .hourly {
                    money("Flat fee", value: $retainer.flatFee)
                }
                if retainer.feeType != .flat {
                    money("Hourly rate", value: $retainer.hourlyRate)
                }
                money("Retainer deposit", value: $retainer.retainerDeposit)
                TextField("Payment terms", text: $retainer.paymentTerms, axis: .vertical)
            }
            }
            if retainer.status != .draft {
                Section("Status") {
                    LabeledContent("Status", value: retainer.status.rawValue)
                    if let id = retainer.docusignEnvelopeId {
                        LabeledContent("DocuSign envelope", value: id).font(.caption)
                        Button {
                            Task { await checkDocuSign(id) }
                        } label: {
                            Label(checking ? "Checking…" : "Check status / download signed copy", systemImage: "arrow.down.doc")
                        }
                        .disabled(checking)
                        if let dsMessage { Text(dsMessage).font(.footnote).foregroundStyle(.secondary) }
                    }
                    if let d = retainer.signedAt {
                        LabeledContent("Signed", value: d.formatted(date: .abbreviated, time: .shortened))
                    }
                    if let name = retainer.signedPDFFileName {
                        ShareLink(item: RetainerStore.signedFolder.appendingPathComponent(name)) {
                            Label("Share signed PDF", systemImage: "square.and.arrow.up")
                        }
                    }
                }
            }
        }
        .navigationTitle(retainer.displayName)
        .navigationBarTitleDisplayMode(.inline)
        .toolbar {
            ToolbarItem(placement: .cancellationAction) { Button("Cancel") { dismiss() } }
            ToolbarItem(placement: .confirmationAction) {
                Button("Save") { store.upsert(retainer); dismiss() }
                    .disabled(retainer.clientName.isEmpty)
            }
            ToolbarItem(placement: .bottomBar) {
                if retainer.source == .scanned {
                    Button {
                        store.upsert(retainer)
                        scanPDF = store.sourcePDF(for: retainer, firm: settings.firm)
                        showSend = true
                    } label: { Label("Send / Sign", systemImage: "signature") }
                    .buttonStyle(.borderedProminent)
                    .disabled(retainer.clientName.isEmpty || !retainer.fields.contains { $0.kind == .clientSignature })
                } else {
                    Button {
                        store.upsert(retainer)
                        showPreview = true
                    } label: { Label("Preview & Send", systemImage: "doc.richtext") }
                    .buttonStyle(.borderedProminent)
                    .disabled(retainer.clientName.isEmpty || retainer.matterDescription.isEmpty)
                }
            }
        }
        .navigationDestination(isPresented: $showPreview) {
            PDFPreviewView(retainer: $retainer)
        }
        .navigationDestination(isPresented: $showFields) {
            FieldPlacementView(pdf: store.sourcePDF(for: retainer, firm: settings.firm), fields: $retainer.fields) {
                store.upsert(retainer)
                showFields = false
            }
        }
        .sheet(isPresented: $showSend) {
            if let scanPDF {
                NavigationStack { SendView(retainer: $retainer, pdf: scanPDF) }
            }
        }
    }

    /// Polls DocuSign; when the envelope is completed, downloads the signed PDF (with
    /// DocuSign's certificate of completion) into the Signed folder.
    private func checkDocuSign(_ envelopeId: String) async {
        checking = true; defer { checking = false }
        let client = DocuSignClient(config: settings.docusign)
        do {
            let status = try await client.envelopeStatus(envelopeId)
            if status == "completed" {
                let data = try await client.downloadCompleted(envelopeId)
                let name = "Retainer-\(retainer.clientName.replacingOccurrences(of: " ", with: "_"))-docusign-\(envelopeId.prefix(8)).pdf"
                try data.write(to: RetainerStore.signedFolder.appendingPathComponent(name), options: [.atomic, .completeFileProtection])
                retainer.signedPDFFileName = name
                retainer.signedAt = Date()
                retainer.status = .completed
                store.upsert(retainer)
                dsMessage = "Completed. Signed copy saved; DocuSign also emailed it to you and the client."
            } else {
                dsMessage = "DocuSign status: \(status)"
            }
        } catch {
            dsMessage = error.localizedDescription
        }
    }

    private func money(_ title: String, value: Binding<Decimal>) -> some View {
        HStack {
            Text(title)
            Spacer()
            TextField("$0.00", value: value, format: .currency(code: "USD"))
                .keyboardType(.decimalPad)
                .multilineTextAlignment(.trailing)
        }
    }
}
