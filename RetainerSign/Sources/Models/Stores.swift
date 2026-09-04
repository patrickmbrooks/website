import Foundation
import Combine

/// Persists retainers as JSON in the app's Documents directory.
@MainActor
final class RetainerStore: ObservableObject {
    @Published var retainers: [Retainer] = [] { didSet { save() } }

    private let fileURL: URL
    static let signedFolder: URL = {
        let docs = FileManager.default.urls(for: .documentDirectory, in: .userDomainMask)[0]
        let url = docs.appendingPathComponent("Signed", isDirectory: true)
        try? FileManager.default.createDirectory(at: url, withIntermediateDirectories: true)
        return url
    }()

    init() {
        let docs = FileManager.default.urls(for: .documentDirectory, in: .userDomainMask)[0]
        fileURL = docs.appendingPathComponent("retainers.json")
        load()
    }

    func upsert(_ r: Retainer) {
        if let i = retainers.firstIndex(where: { $0.id == r.id }) {
            retainers[i] = r
        } else {
            retainers.insert(r, at: 0)
        }
    }

    func delete(at offsets: IndexSet) {
        for i in offsets {
            if let name = retainers[i].signedPDFFileName {
                try? FileManager.default.removeItem(at: Self.signedFolder.appendingPathComponent(name))
            }
        }
        retainers.remove(atOffsets: offsets)
    }

    private func load() {
        guard let data = try? Data(contentsOf: fileURL) else { return }
        let decoder = JSONDecoder()
        decoder.dateDecodingStrategy = .iso8601
        retainers = (try? decoder.decode([Retainer].self, from: data)) ?? []
    }

    private func save() {
        let encoder = JSONEncoder()
        encoder.dateEncodingStrategy = .iso8601
        encoder.outputFormatting = [.prettyPrinted, .sortedKeys]
        if let data = try? encoder.encode(retainers) {
            try? data.write(to: fileURL, options: [.atomic, .completeFileProtection])
        }
    }
}

/// Firm profile + DocuSign settings, stored in UserDefaults (tokens live in the Keychain).
@MainActor
final class SettingsStore: ObservableObject {
    @Published var firm: FirmProfile { didSet { persist(firm, key: "firm") } }
    @Published var docusign: DocuSignConfig { didSet { persist(docusign, key: "docusign") } }

    init() {
        firm = Self.loadValue(FirmProfile.self, key: "firm") ?? FirmProfile()
        docusign = Self.loadValue(DocuSignConfig.self, key: "docusign") ?? DocuSignConfig()
    }

    private static func loadValue<T: Decodable>(_ type: T.Type, key: String) -> T? {
        guard let data = UserDefaults.standard.data(forKey: key) else { return nil }
        return try? JSONDecoder().decode(T.self, from: data)
    }

    private func persist<T: Encodable>(_ value: T, key: String) {
        if let data = try? JSONEncoder().encode(value) {
            UserDefaults.standard.set(data, forKey: key)
        }
    }
}
