# RetainerSign

An iPhone app (also runs on iPad and Apple Silicon Macs) for drafting client retainer
agreements as PDFs and getting them signed. Built for a solo/small law practice.

## What it does

1. **Draft** – fill in client, matter and fee details. The agreement text comes from an
   editable template with `{{placeholders}}` (Settings → Agreement text).
2. **Preview** – a US Letter PDF is generated on device (PDFKit/CoreText), with page
   headers, page numbers, client-initials lines and a two-column signature block.
3. **Send or sign**, three ways:
   * **DocuSign** – emails the client a signing link. Uses DocuSign's eSignature REST API
     with OAuth + PKCE directly from the phone, so there is no server to run and no client
     secret in the app. Signature/date tabs are auto-placed via hidden anchor text in the PDF.
     Optionally routes to you as a second signer.
   * **In person** – the client signs on your screen with a finger or Apple Pencil. The
     signature is stamped on the PDF along with a certificate page (signer, time, device,
     SHA-256 of the unsigned document). The signed PDF is saved on device and shareable.
   * **Email the PDF** – attaches the unsigned PDF to a Mail message.
4. **Scan a paper retainer instead** – tap + → *Scan paper retainer*. The system document
   camera captures each page (edge detection, de-skew). You then tap where the client
   signs, dates, or initials (and optionally where you sign), and send it the same three
   ways. DocuSign places its fields exactly at your taps; in-person signing stamps the
   signature there.
5. **Track** – each retainer shows Draft / Sent / Signed status and the DocuSign envelope id.
   For DocuSign envelopes, *Check status / download signed copy* pulls the completed PDF
   with DocuSign's certificate of completion. DocuSign also emails the completed copy to
   you and the client automatically.

Data stays on the device (JSON in the app's Documents folder, file-protected). DocuSign
tokens are stored in the Keychain.

## Project layout

```
RetainerSign/
  project.yml                 XcodeGen spec → RetainerSign.xcodeproj
  Sources/
    RetainerSignApp.swift     entry point
    Models/                   Retainer, FirmProfile, DocuSignConfig, stores
    Services/PDFGenerator     PDF layout + template filling
    Services/DocuSignClient   OAuth PKCE, create envelope, status, download
    Services/MailComposer     Mail sheet wrapper
    Services/DocumentScanner  camera scan → PDF, and stamping signatures onto a scan
    Services/Keychain         token storage
    Views/                    list, editor, preview, send, signature pad, settings,
                              scan flow, signature-field placement
    Resources/DefaultTemplate.txt   default agreement text
```

## Build (needs a Mac with Xcode 15+)

```bash
brew install xcodegen
cd RetainerSign
xcodegen generate
open RetainerSign.xcodeproj
```

In Xcode: select the RetainerSign target → Signing & Capabilities → pick your Apple
Developer team. Change the bundle identifier if you like (`com.patrickbrookslaw.retainersign`).
Pick your iPhone (or "My Mac (Designed for iPad)") as the run destination and press Run.

If you would rather not install XcodeGen: File → New → Project → iOS App (SwiftUI),
delete the generated `ContentView.swift`/`…App.swift`, drag the `Sources` folder in, and
add the `retainersign` URL scheme under Info → URL Types.

The code has not been compiled in this repo (no Swift toolchain here), so expect to fix
the odd compiler nit on first build.

## DocuSign setup (one time)

1. Create a free developer account at https://developers.docusign.com.
2. Admin → Apps and Keys → Add App and Integration Key. Copy the **Integration Key**.
3. Under Authentication choose **Mobile App (PKCE)**; no secret is needed.
4. Add redirect URI `retainersign://oauth`.
5. In the app: Settings → DocuSign → paste the key, keep "developer sandbox" on, tap
   **Sign in to DocuSign**.
6. Send yourself a test retainer. Sandbox envelopes are watermarked "demo".
7. For real clients, request **Go-Live** in the DocuSign developer console (it reviews ~20
   successful sandbox API calls), then create the key in your production account and turn
   the sandbox toggle off. Sending requires a paid DocuSign plan with API access
   (Personal/Standard plans with "API" add-on or the Developer/Real-time pricing).

## Legal notes

* Both the DocuSign and in-person flows produce electronic signatures valid under the
  federal ESIGN Act and state UETA equivalents. DocuSign adds its own certificate of
  completion with IP address and email verification; the in-person flow records what the
  device can observe (time, device, document hash, attorney as witness).
* Review the default template against your state bar's rules on fee agreements (e.g.
  trust-account and non-refundable-fee language) before using it with clients.

## Shipping to the App Store (or not)

You do not need the App Store to use this yourself:

* **Personal use, simplest** – run it from Xcode onto your phone. With a paid Apple
  Developer account ($99/yr) the install lasts a year; without one, 7 days.
* **TestFlight** – archive (Product → Archive), upload to App Store Connect, add
  yourself as an internal tester. Installs last 90 days and update over the air. This is
  the sweet spot for a one-person app.
* **App Store** – same upload plus screenshots, privacy policy URL, and review. Apple
  can be strict about single-user "utility" apps, so TestFlight is recommended unless you
  want to distribute it to other lawyers.

The same target runs on a Mac via Mac Catalyst / "Designed for iPad" — no separate Mac
project is needed.
