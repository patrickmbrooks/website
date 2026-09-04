import SwiftUI

@main
struct RetainerSignApp: App {
    @StateObject private var store = RetainerStore()
    @StateObject private var settings = SettingsStore()

    var body: some Scene {
        WindowGroup {
            RetainerListView()
                .environmentObject(store)
                .environmentObject(settings)
        }
    }
}
