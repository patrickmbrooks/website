import SwiftUI

struct RetainerListView: View {
    @EnvironmentObject var store: RetainerStore
    @EnvironmentObject var settings: SettingsStore
    @State private var editing: Retainer?
    @State private var showSettings = false

    var body: some View {
        NavigationStack {
            Group {
                if store.retainers.isEmpty {
                    ContentUnavailableView("No retainers yet", systemImage: "doc.text",
                                           description: Text("Tap + to draft a retainer agreement for a client."))
                } else {
                    List {
                        ForEach(store.retainers) { r in
                            Button { editing = r } label: { row(r) }
                                .buttonStyle(.plain)
                        }
                        .onDelete(perform: store.delete)
                    }
                }
            }
            .navigationTitle("Retainers")
            .toolbar {
                ToolbarItem(placement: .topBarLeading) {
                    Button { showSettings = true } label: { Image(systemName: "gearshape") }
                }
                ToolbarItem(placement: .topBarTrailing) {
                    Button { editing = Retainer() } label: { Image(systemName: "plus") }
                }
            }
            .sheet(item: $editing) { r in
                NavigationStack { RetainerEditorView(retainer: r) }
            }
            .sheet(isPresented: $showSettings) {
                NavigationStack { SettingsView() }
            }
        }
    }

    private func row(_ r: Retainer) -> some View {
        HStack {
            VStack(alignment: .leading, spacing: 3) {
                Text(r.displayName).font(.headline)
                Text(r.matterDescription.isEmpty ? "No matter description" : r.matterDescription)
                    .font(.subheadline).foregroundStyle(.secondary).lineLimit(1)
            }
            Spacer()
            VStack(alignment: .trailing, spacing: 3) {
                Text(r.status.rawValue).font(.caption).foregroundStyle(statusColor(r.status))
                Text(r.agreementDate, style: .date).font(.caption2).foregroundStyle(.secondary)
            }
        }
        .contentShape(Rectangle())
    }

    private func statusColor(_ s: RetainerStatus) -> Color {
        switch s {
        case .draft: return .secondary
        case .sentForSignature, .emailed: return .orange
        case .signedInPerson, .completed: return .green
        }
    }
}
