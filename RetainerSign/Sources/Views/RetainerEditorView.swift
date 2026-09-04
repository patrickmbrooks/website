import SwiftUI

struct RetainerEditorView: View {
    @EnvironmentObject var store: RetainerStore
    @EnvironmentObject var settings: SettingsStore
    @Environment(\.dismiss) private var dismiss
    @State var retainer: Retainer
    @State private var showPreview = false

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
            if retainer.status != .draft {
                Section("Status") {
                    LabeledContent("Status", value: retainer.status.rawValue)
                    if let id = retainer.docusignEnvelopeId {
                        LabeledContent("DocuSign envelope", value: id).font(.caption)
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
                Button {
                    store.upsert(retainer)
                    showPreview = true
                } label: {
                    Label("Preview & Send", systemImage: "doc.richtext")
                }
                .buttonStyle(.borderedProminent)
                .disabled(retainer.clientName.isEmpty || retainer.matterDescription.isEmpty)
            }
        }
        .navigationDestination(isPresented: $showPreview) {
            PDFPreviewView(retainer: $retainer)
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
