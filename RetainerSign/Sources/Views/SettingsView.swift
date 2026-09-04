import SwiftUI

struct SettingsView: View {
    @EnvironmentObject var settings: SettingsStore
    @Environment(\.dismiss) private var dismiss
    @State private var dsMessage: String?
    @State private var dsSignedIn = false
    @State private var dsEmail = ""

    var body: some View {
        Form {
            Section("Firm") {
                TextField("Firm name", text: $settings.firm.firmName)
                TextField("Attorney name", text: $settings.firm.attorneyName)
                TextField("Bar number", text: $settings.firm.barNumber)
                TextField("Address", text: $settings.firm.address, axis: .vertical)
                TextField("Phone", text: $settings.firm.phone).keyboardType(.phonePad)
                TextField("Email (attorney signs from this address)", text: $settings.firm.email)
                    .keyboardType(.emailAddress).textInputAutocapitalization(.never)
                TextField("Governing state (e.g. Texas)", text: $settings.firm.governingState)
            }

            Section {
                TextField("Integration key (Client ID)", text: $settings.docusign.integrationKey)
                    .textInputAutocapitalization(.never).autocorrectionDisabled()
                Toggle("Use developer sandbox (demo)", isOn: $settings.docusign.useDemoEnvironment)
                Toggle("Attorney also signs", isOn: $settings.docusign.attorneySignsToo)
                TextField("Email subject", text: $settings.docusign.emailSubject)
                TextField("Email message", text: $settings.docusign.emailBlurb, axis: .vertical)
                if dsSignedIn {
                    LabeledContent("Signed in as", value: dsEmail)
                    Button("Sign out of DocuSign", role: .destructive) {
                        DocuSignClient(config: settings.docusign).signOut()
                        refreshDS()
                    }
                } else {
                    Button("Sign in to DocuSign") { Task { await signIn() } }
                        .disabled(settings.docusign.integrationKey.isEmpty)
                }
                if let dsMessage { Text(dsMessage).font(.footnote).foregroundStyle(.secondary) }
            } header: {
                Text("DocuSign")
            } footer: {
                Text("Create a free developer account at developers.docusign.com, add an app under Apps & Keys, enable Mobile app (PKCE) and add the redirect URI \(DocuSignClient.redirectURI). Turn off the sandbox toggle once your production key is approved.")
            }

            Section {
                NavigationLink("Edit agreement template") { TemplateEditor(text: $settings.firm.template) }
                Button("Reset template to default", role: .destructive) {
                    settings.firm.template = FirmProfile.defaultTemplate
                }
            } header: { Text("Agreement text") } footer: {
                Text("Placeholders: {{date}} {{firm_name}} {{attorney_name}} {{firm_address}} {{client_name}} {{client_address}} {{client_email}} {{matter}} {{scope}} {{fee_terms}} {{payment_terms}} {{governing_state}}")
            }
        }
        .navigationTitle("Settings")
        .toolbar { ToolbarItem(placement: .confirmationAction) { Button("Done") { dismiss() } } }
        .onAppear(perform: refreshDS)
    }

    private func refreshDS() {
        let c = DocuSignClient(config: settings.docusign)
        dsSignedIn = c.isSignedIn
        dsEmail = c.session?.userEmail ?? ""
    }

    private func signIn() async {
        do {
            try await DocuSignClient(config: settings.docusign).signIn()
            dsMessage = nil
        } catch {
            dsMessage = error.localizedDescription
        }
        refreshDS()
    }
}

private struct TemplateEditor: View {
    @Binding var text: String
    var body: some View {
        TextEditor(text: $text)
            .font(.system(.body, design: .serif))
            .padding(8)
            .navigationTitle("Template")
            .navigationBarTitleDisplayMode(.inline)
    }
}
