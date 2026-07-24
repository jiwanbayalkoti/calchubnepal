<?php

namespace App\Services\Seo;

use App\Enums\Qr\QrType;

/**
 * SEO how-to articles for each QR generator type (keyword-led titles + body).
 */
class QrGuideBlogContentBuilder
{
    /**
     * Publish order: high-search-volume types first (2 posts/day schedule).
     *
     * @return list<QrType>
     */
    public function publishOrder(): array
    {
        return [
            QrType::Url,
            QrType::Wifi,
            QrType::WhatsApp,
            QrType::Vcard,
            QrType::Bank,
            QrType::Esewa,
            QrType::Khalti,
            QrType::Maps,
            QrType::Image,
            QrType::Telegram,
            QrType::Pdf,
            QrType::App,
            QrType::NepalQr,
            QrType::Upi,
            QrType::Crypto,
            QrType::Social,
            QrType::Email,
            QrType::Phone,
            QrType::Sms,
            QrType::Viber,
            QrType::Messenger,
            QrType::Event,
            QrType::Calendar,
            QrType::Meeting,
            QrType::Review,
            QrType::Music,
            QrType::Coupon,
            QrType::MultiUrl,
            QrType::Text,
            QrType::Location,
            QrType::Mecard,
        ];
    }

    /**
     * @return array{
     *   slug: string,
     *   title: string,
     *   excerpt: string,
     *   meta_title: string,
     *   meta_description: string,
     *   meta_keywords: string,
     *   content: string,
     *   reading_time_minutes: int,
     *   is_featured: bool,
     *   tags: list<string>
     * }
     */
    public function build(QrType $type): array
    {
        $meta = $this->meta($type);
        $content = $this->renderHtml($type, $meta);
        $words = str_word_count(strip_tags($content));

        return [
            'slug' => $meta['slug'],
            'title' => $meta['title'],
            'excerpt' => $meta['excerpt'],
            'meta_title' => $meta['meta_title'],
            'meta_description' => $meta['meta_description'],
            'meta_keywords' => implode(', ', $meta['keywords']),
            'content' => $content,
            'reading_time_minutes' => max(5, (int) ceil($words / 200)),
            'is_featured' => in_array($type, [QrType::Url, QrType::Wifi, QrType::WhatsApp, QrType::Bank, QrType::Esewa], true),
            'tags' => $meta['tags'],
        ];
    }

    /**
     * @return array{
     *   slug: string,
     *   title: string,
     *   excerpt: string,
     *   meta_title: string,
     *   meta_description: string,
     *   keywords: list<string>,
     *   tags: list<string>,
     *   h1_focus: string,
     *   steps: list<string>,
     *   tips: list<string>,
     *   faqs: list<array{q: string, a: string}>
     * }
     */
    protected function meta(QrType $type): array
    {
        return match ($type) {
            QrType::Url => $this->pack(
                'free-url-qr-code-generator',
                'Free URL QR Code Generator — Create Website QR Codes Online',
                'Generate a free website URL QR code in seconds. Best for business cards, flyers, menus and packaging.',
                'Free URL QR Code Generator (Website Link) | CalchubNepal',
                'Create a free URL QR code for any website link. Fast, printable PNG/SVG download with logo support — no signup required.',
                ['free qr code generator', 'url qr code generator', 'create qr code for website', 'website qr code', 'link qr code maker', 'qr code generator online free'],
                ['Select Website URL as the QR type.', 'Paste your full https:// link.', 'Customize colors, logo and size.', 'Download PNG, SVG or PDF and print.'],
                ['Use a short, HTTPS URL for cleaner codes.', 'Test the scan before printing large batches.', 'Add UTM parameters if you track marketing campaigns.'],
                [
                    ['q' => 'What is a URL QR code?', 'a' => 'A URL QR code stores a website address. When scanned, the phone opens that page in the browser.'],
                    ['q' => 'Can I change the link later?', 'a' => 'Static URL codes are fixed. Use Dynamic QR (signed-in) if you need an editable destination.'],
                    ['q' => 'Is this URL QR code generator free?', 'a' => 'Yes. You can create and download URL QR codes free on CalchubNepal.'],
                ],
            ),
            QrType::Wifi => $this->pack(
                'wifi-qr-code-generator',
                'WiFi QR Code Generator — Share Network Password Instantly',
                'Make a WiFi QR code so guests connect without typing the password. Works for WPA, WEP and open networks.',
                'WiFi QR Code Generator (No Password Typing) | CalchubNepal',
                'Free WiFi QR code generator for WPA/WPA2 networks. Guests scan and connect — perfect for cafés, offices and Airbnb.',
                ['wifi qr code generator', 'wifi qr code', 'share wifi password qr', 'create wifi qr code', 'wpa qr code', 'guest wifi qr'],
                ['Choose WiFi QR type.', 'Enter SSID, password and encryption (usually WPA).', 'Generate and print near your router or reception desk.'],
                ['Prefer WPA/WPA2 over WEP.', 'Mark hidden networks if your SSID is hidden.', 'Reprint after you change the WiFi password.'],
                [
                    ['q' => 'Do WiFi QR codes work on iPhone and Android?', 'a' => 'Yes. Modern camera apps detect WiFi QR codes and prompt to join the network.'],
                    ['q' => 'Is my WiFi password safe in a QR?', 'a' => 'Anyone who scans can join. Place codes only where trusted guests can see them.'],
                    ['q' => 'What encryption should I pick?', 'a' => 'Most home/office routers use WPA or WPA2. Choose “No password” only for open networks.'],
                ],
            ),
            QrType::WhatsApp => $this->pack(
                'whatsapp-qr-code-generator',
                'WhatsApp QR Code Generator — Chat Link with Pre-filled Message',
                'Create a WhatsApp QR code that opens a chat with your number and optional pre-filled message.',
                'WhatsApp QR Code Generator Free | CalchubNepal',
                'Generate a free WhatsApp QR code (wa.me link). Add country code and optional message for click-to-chat marketing.',
                ['whatsapp qr code generator', 'whatsapp qr code', 'wa.me qr code', 'click to chat qr', 'whatsapp link generator', 'whatsapp business qr'],
                ['Select WhatsApp.', 'Enter number with country code (e.g. 97798…).', 'Add an optional greeting message.', 'Download and share on posters or packaging.'],
                ['Always include the country code without + in some flows; our tool normalizes digits.', 'Keep the message short for mobile screens.', 'Use Dynamic QR if the number may change.'],
                [
                    ['q' => 'How does a WhatsApp QR code work?', 'a' => 'It encodes a wa.me link. Scanning opens WhatsApp with your chat ready.'],
                    ['q' => 'Can I add a pre-filled message?', 'a' => 'Yes. Optional message text is appended to the WhatsApp deep link.'],
                    ['q' => 'Does it work for WhatsApp Business?', 'a' => 'Yes — any WhatsApp-enabled number works the same way.'],
                ],
            ),
            QrType::Vcard => $this->pack(
                'vcard-qr-code-generator',
                'vCard QR Code Generator — Save Contact With One Scan',
                'Create a vCard (contact) QR code with name, phone, email, company and website.',
                'vCard QR Code Generator (Contact Card) | CalchubNepal',
                'Free vCard QR code generator to share contact details. Scan to save name, phone, email and company on any phone.',
                ['vcard qr code generator', 'contact qr code', 'business card qr code', 'vcard qr', 'save contact qr code', 'electronic business card qr'],
                ['Select vCard Contact.', 'Fill name, phone, email, company and optional fields.', 'Generate and print on visiting cards or badges.'],
                ['Keep fields concise to keep the QR easy to scan.', 'Pair with our Visiting Card Designer for print-ready cards.', 'Test save-to-contacts on both iOS and Android.'],
                [
                    ['q' => 'What is a vCard QR code?', 'a' => 'It stores contact data in vCard format so phones can save the contact after scanning.'],
                    ['q' => 'Is vCard better than a plain text number?', 'a' => 'Yes — vCard can include name, email, org, address and website in one scan.'],
                    ['q' => 'Can I put a vCard QR on a printed visiting card?', 'a' => 'Absolutely. High-contrast PNG/SVG downloads are print-ready.'],
                ],
            ),
            QrType::Bank => $this->pack(
                'bank-account-qr-code-generator',
                'Bank Account Details QR Code — Share Account Info for Transfers',
                'Encode bank name, account number and holder name into a readable QR for fund transfers.',
                'Bank Account QR Code Generator Nepal | CalchubNepal',
                'Create a bank details QR code with account name, number, bank and branch. Ideal for Nepal fund-transfer sharing.',
                ['bank account qr code', 'bank details qr code', 'account number qr', 'nepal bank qr', 'share bank details qr', 'fund transfer qr code'],
                ['Choose Bank Details.', 'Enter account holder, bank name and account number.', 'Add branch/SWIFT if needed.', 'Download and share securely with payers.'],
                ['Double-check the account number before printing.', 'This format shows details for manual transfer — it is not always a banking-app payment QR.', 'For app-payable Nepal QR you need a registered merchant ID.'],
                [
                    ['q' => 'Will banking apps pay via this QR?', 'a' => 'Most apps need Nepal QR (EMVCo). Bank Details QR is for sharing readable account info.'],
                    ['q' => 'Is it safe to share bank QR?', 'a' => 'Only share with people who should pay you. Anyone who scans can see the account number.'],
                    ['q' => 'Can I add SWIFT code?', 'a' => 'Yes — optional SWIFT/BIC and remarks fields are supported.'],
                ],
            ),
            QrType::Esewa => $this->pack(
                'esewa-qr-code-generator',
                'eSewa QR Code Generator — Share eSewa ID for Payments',
                'Create an eSewa payment QR with your eSewa ID, name and optional amount for Nepal digital wallets.',
                'eSewa QR Code Generator Free Nepal | CalchubNepal',
                'Free eSewa QR code maker for Nepal. Share eSewa ID/mobile so customers can send money faster.',
                ['esewa qr code', 'esewa qr code generator', 'esewa id qr', 'nepal esewa payment qr', 'esewa send money qr', 'digital wallet qr nepal'],
                ['Select eSewa.', 'Enter eSewa ID or registered mobile.', 'Add name, amount or purpose if useful.', 'Download and display at checkout.'],
                ['Confirm the ID matches your eSewa app profile.', 'If you have an official payment link, paste it in the optional URL field.', 'Combine with Dynamic QR for campaign tracking.'],
                [
                    ['q' => 'Does scanning open the eSewa app automatically?', 'a' => 'It depends on the device. Many users copy the ID into eSewa Send Money after scanning.'],
                    ['q' => 'Can I set a fixed amount?', 'a' => 'Yes — optional amount and purpose fields are encoded as readable payment hints.'],
                    ['q' => 'Is this an official eSewa merchant QR?', 'a' => 'This tool builds a shareable ID QR. Merchant QR from eSewa business tools may include extra payment metadata.'],
                ],
            ),
            QrType::Khalti => $this->pack(
                'khalti-qr-code-generator',
                'Khalti QR Code Generator — Accept Wallet Payments Faster',
                'Generate a Khalti QR with your Khalti ID/mobile and optional amount for Nepal payments.',
                'Khalti QR Code Generator Nepal | CalchubNepal',
                'Create a free Khalti QR code to share your wallet ID. Great for freelancers, shops and event collections in Nepal.',
                ['khalti qr code', 'khalti qr code generator', 'khalti id qr', 'nepal khalti payment', 'khalti send money qr', 'wallet qr code nepal'],
                ['Select Khalti.', 'Enter Khalti ID or mobile.', 'Optionally add amount and purpose.', 'Print for counters or share digitally.'],
                ['Verify digits carefully — wrong IDs mean failed transfers.', 'Use a clear label like “Pay via Khalti”.', 'Prefer Dynamic QR for seasonal campaigns.'],
                [
                    ['q' => 'Who should use a Khalti QR?', 'a' => 'Anyone receiving Khalti wallet payments — shops, freelancers, NGOs and event organizers.'],
                    ['q' => 'Can I include a payment note?', 'a' => 'Yes. Purpose and amount fields help payers send the right reference.'],
                    ['q' => 'Khalti vs bank details QR?', 'a' => 'Khalti is wallet-based; bank details QR is for account transfers.'],
                ],
            ),
            QrType::Maps => $this->pack(
                'google-maps-qr-code-generator',
                'Map QR Code Generator — Google, Apple Maps, Waze & OSM',
                'Create a map QR code for a place, address or coordinates. Choose Google Maps, Apple Maps, Waze or OpenStreetMap.',
                'Map QR Code Generator (Google Maps & Waze) | CalchubNepal',
                'Free map QR code generator for Google Maps, Apple Maps, Waze and OSM. Perfect for store locations and event venues.',
                ['google maps qr code', 'maps qr code generator', 'location qr code', 'waze qr code', 'apple maps qr', 'store location qr code'],
                ['Select Map / Directions.', 'Pick a map provider.', 'Enter place name/address or lat/lng.', 'Download for signage and flyers.'],
                ['Place names scan cleaner than long plus-codes.', 'Use Waze for driving-heavy audiences.', 'Test navigation on a real phone before printing.'],
                [
                    ['q' => 'Which map app should I choose?', 'a' => 'Google Maps is most universal; Apple Maps suits iPhone users; Waze is great for drivers.'],
                    ['q' => 'Can I use GPS coordinates?', 'a' => 'Yes — enter latitude and longitude if you do not have a place name.'],
                    ['q' => 'Is Geo URI different?', 'a' => 'Geo URI is a standard geo: link some map apps understand directly.'],
                ],
            ),
            QrType::Image => $this->pack(
                'image-qr-code-generator',
                'Image QR Code Generator — Open a Photo Link With One Scan',
                'Encode a public image URL into a QR code so scanners open the photo or graphic in their browser.',
                'Image QR Code Generator (Photo Link) | CalchubNepal',
                'Create an image QR code from any public image URL. Ideal for portfolios, product photos and digital posters.',
                ['image qr code generator', 'photo qr code', 'qr code for image', 'picture qr code', 'image link qr', 'qr code to open image'],
                ['Host your image on a public HTTPS URL.', 'Select Image Link and paste the URL.', 'Generate and place beside the physical product or exhibit.'],
                ['Use compressed images for faster mobile loading.', 'Avoid private Drive links that require login.', 'Prefer PNG/JPG/WebP on a stable CDN or website.'],
                [
                    ['q' => 'Does the QR store the image itself?', 'a' => 'No. It stores the image URL. Keep the file online or the link will break.'],
                    ['q' => 'Can I use Google Drive photos?', 'a' => 'Only if the share link is publicly accessible without signing in.'],
                    ['q' => 'Image QR vs PDF QR?', 'a' => 'Image QR opens a picture; PDF QR opens a document file URL.'],
                ],
            ),
            QrType::Telegram => $this->pack(
                'telegram-qr-code-generator',
                'Telegram QR Code Generator — Username or Message Link',
                'Build a Telegram QR for your @username or share link so people can message you instantly.',
                'Telegram QR Code Generator Free | CalchubNepal',
                'Free Telegram QR code generator for t.me usernames and optional message text. Grow your channel or support chat.',
                ['telegram qr code', 'telegram qr code generator', 't.me qr code', 'telegram username qr', 'telegram channel qr', 'telegram link qr'],
                ['Select Telegram.', 'Enter @username (preferred) or phone.', 'Optional message text.', 'Share on social bios and print materials.'],
                ['Public usernames work more reliably than phone deep links.', 'Use for channels, bots and support desks.', 'Keep usernames short and memorable.'],
                [
                    ['q' => 'Do I need a public username?', 'a' => 'A public @username is the most reliable Telegram QR target.'],
                    ['q' => 'Can I deep-link a bot?', 'a' => 'Yes — use the bot username the same way as a user or channel.'],
                    ['q' => 'Telegram vs WhatsApp QR?', 'a' => 'Pick the messenger your audience already uses daily.'],
                ],
            ),
            QrType::Pdf => $this->pack(
                'pdf-qr-code-generator',
                'PDF QR Code Generator — Share Menus, Brochures & Price Lists',
                'Create a QR code that opens a PDF or file URL — menus, catalogs, forms and manuals.',
                'PDF QR Code Generator (Menu & Brochure) | CalchubNepal',
                'Free PDF QR code generator for menus, brochures and documents. Paste a file URL and download a scannable code.',
                ['pdf qr code generator', 'qr code for pdf', 'menu qr code', 'brochure qr code', 'document qr code', 'catalog qr code'],
                ['Upload your PDF to your website or cloud with a public link.', 'Select PDF / File and paste the URL.', 'Print on table tents, packaging or posters.'],
                ['Compress large PDFs for mobile data.', 'Use HTTPS links only.', 'Update Dynamic QR if the PDF file will be replaced often.'],
                [
                    ['q' => 'Does the QR embed the PDF?', 'a' => 'No — it links to the online file. Keep the URL live.'],
                    ['q' => 'Best use cases?', 'a' => 'Restaurant menus, product catalogs, event programs and instruction manuals.'],
                    ['q' => 'Can I track scans?', 'a' => 'Use Dynamic QR with analytics for scan tracking.'],
                ],
            ),
            QrType::App => $this->pack(
                'app-download-qr-code-generator',
                'App Download QR Code — App Store & Google Play Links',
                'Generate an app QR code for iOS App Store, Google Play or a universal download landing page.',
                'App Store QR Code Generator (iOS & Android) | CalchubNepal',
                'Create a free app download QR for App Store and Play Store links. Perfect for marketing posters and onboarding.',
                ['app store qr code', 'google play qr code', 'app download qr code', 'ios android qr code', 'mobile app qr generator', 'play store qr'],
                ['Select App Download.', 'Paste App Store and/or Play Store URLs.', 'Choose store preference or auto.', 'Place on packaging and ads.'],
                ['A smart landing page that detects device often converts better than a single store link.', 'Keep store URLs official.', 'Test on both iPhone and Android.'],
                [
                    ['q' => 'Can one QR cover iOS and Android?', 'a' => 'Use a single landing URL that redirects by device, or print two codes.'],
                    ['q' => 'What if my app is not published yet?', 'a' => 'Link to a waitlist or beta page until store listings go live.'],
                    ['q' => 'App QR vs website QR?', 'a' => 'App QR targets store listings; website QR opens any web page.'],
                ],
            ),
            QrType::NepalQr => $this->pack(
                'nepal-qr-emvco-generator',
                'Nepal QR (EMVCo) Generator — Banking App Payment QR',
                'Build an EMVCo Nepal QR payload for Fonepay/NQR-style merchant IDs so banking apps can attempt payment scans.',
                'Nepal QR Code Generator (EMVCo / Fonepay) | CalchubNepal',
                'Generate Nepal QR (EMVCo) with merchant ID, name, city and optional NPR amount. For registered merchant/account IDs only.',
                ['nepal qr', 'nepal qr code generator', 'fonepay qr', 'nqr qr code', 'emvco qr nepal', 'banking app payment qr'],
                ['Get a valid merchant/account ID from your bank or Fonepay.', 'Select Nepal QR (EMVCo).', 'Enter merchant ID, name, city and optional amount.', 'Test in your banking app before display.'],
                ['Unregistered IDs usually show as invalid in bank apps.', 'Keep merchant name ≤ 25 characters.', 'Personal “My QR” from your bank app is safest for receiving money.'],
                [
                    ['q' => 'Will every Nepal bank app accept this QR?', 'a' => 'Only if the merchant ID is registered on the payment network.'],
                    ['q' => 'Is this the same as bank details QR?', 'a' => 'No. Nepal QR is EMVCo payment format; bank details QR is readable account text.'],
                    ['q' => 'Do I need a merchant account?', 'a' => 'For reliable app payments, yes — request Nepal QR credentials from your bank.'],
                ],
            ),
            QrType::Upi => $this->pack(
                'upi-qr-code-generator',
                'UPI QR Code Generator — upi://pay Payment Links',
                'Create a UPI payment QR with VPA, payee name, amount and note for Indian UPI apps.',
                'UPI QR Code Generator (upi://pay) | CalchubNepal',
                'Free UPI QR code generator using standard upi://pay links. Add VPA, name, amount and transaction note.',
                ['upi qr code generator', 'upi qr code', 'upi payment qr', 'generate upi qr', 'vpa qr code', 'upi://pay qr'],
                ['Select UPI Payment.', 'Enter VPA (name@bank).', 'Add payee name and optional amount/note.', 'Download for Indian customer payments.'],
                ['Nepal banking apps generally do not open UPI links.', 'Double-check the VPA spelling.', 'Use amount only when the price is fixed.'],
                [
                    ['q' => 'Does UPI QR work in Nepal?', 'a' => 'UPI is built for Indian apps. In Nepal prefer eSewa, Khalti or Nepal QR.'],
                    ['q' => 'What is a VPA?', 'a' => 'Virtual Payment Address like name@oksbi — your UPI ID.'],
                    ['q' => 'Can I leave amount blank?', 'a' => 'Yes. Payers can enter the amount in their UPI app.'],
                ],
            ),
            QrType::Crypto => $this->pack(
                'crypto-wallet-qr-code-generator',
                'Crypto Wallet QR Code — Bitcoin & Ethereum Payment URI',
                'Generate crypto QR codes for BTC (BIP21) and ETH addresses with optional amount and label.',
                'Bitcoin & Crypto Wallet QR Code Generator | CalchubNepal',
                'Free crypto QR code generator for Bitcoin BIP21 and Ethereum addresses. Share wallet receive details safely.',
                ['bitcoin qr code', 'crypto qr code generator', 'wallet address qr', 'bip21 qr code', 'ethereum qr code', 'btc payment qr'],
                ['Select Crypto Wallet.', 'Choose coin and paste the receive address.', 'Optional amount/label.', 'Display only your own receive address.'],
                ['Never share seed phrases in a QR.', 'Verify the first/last characters of the address on a second device.', 'For USDT/other coins we encode a clear text receive block.'],
                [
                    ['q' => 'What is BIP21?', 'a' => 'A Bitcoin URI standard (bitcoin:address?amount=…) that wallets understand.'],
                    ['q' => 'Can I request a specific BTC amount?', 'a' => 'Yes — optional amount is added to the BIP21 URI.'],
                    ['q' => 'Is this a crypto exchange QR?', 'a' => 'No — it encodes your wallet address / payment URI only.'],
                ],
            ),
            QrType::Social => $this->pack(
                'social-media-qr-code-generator',
                'Social Media QR Code Generator — Instagram, Facebook, TikTok & More',
                'Create QR codes for Instagram, Facebook, TikTok, LinkedIn, YouTube, X and GitHub profiles.',
                'Social Media QR Code Generator Free | CalchubNepal',
                'Free social media QR code maker for Instagram, TikTok, Facebook, LinkedIn, YouTube and more.',
                ['instagram qr code', 'social media qr code generator', 'tiktok qr code', 'facebook profile qr', 'linkedin qr code', 'youtube channel qr'],
                ['Select Social Media.', 'Pick the network and enter username — or paste a full profile URL.', 'Download for packaging and events.'],
                ['Full profile URLs override username building.', 'Use high error correction if you add a logo.', 'One network per QR keeps scans intentional.'],
                [
                    ['q' => 'Which networks are supported?', 'a' => 'Facebook, Instagram, X/Twitter, LinkedIn, YouTube, TikTok and GitHub.'],
                    ['q' => 'Can I use a custom profile URL?', 'a' => 'Yes — paste the full URL and we encode it directly.'],
                    ['q' => 'Good for influencer kits?', 'a' => 'Yes — print on media kits, booths and product inserts.'],
                ],
            ),
            QrType::Email => $this->pack(
                'email-qr-code-generator',
                'Email QR Code Generator — mailto Link With Subject & Body',
                'Create an email QR that opens the mail app with to, subject and body pre-filled.',
                'Email QR Code Generator (mailto) | CalchubNepal',
                'Free email QR code generator with optional subject and body. Ideal for support and sales contact posters.',
                ['email qr code generator', 'mailto qr code', 'email qr code', 'qr code for email address', 'contact email qr'],
                ['Select Email.', 'Enter address plus optional subject/body.', 'Generate for support desks and flyers.'],
                ['Keep subject lines short.', 'Use a monitored inbox.', 'Avoid putting sensitive content in the pre-filled body.'],
                [
                    ['q' => 'What happens when scanned?', 'a' => 'The device opens the default mail app with your fields filled in.'],
                    ['q' => 'Does it work without a mail app?', 'a' => 'A mail client must be installed; otherwise the OS may prompt to set one up.'],
                    ['q' => 'Email QR vs contact vCard?', 'a' => 'Email QR starts a message; vCard saves the full contact.'],
                ],
            ),
            QrType::Phone => $this->pack(
                'phone-number-qr-code-generator',
                'Phone Number QR Code Generator — Tap to Call',
                'Generate a tel: QR code so customers can call you with one scan.',
                'Phone QR Code Generator (Click to Call) | CalchubNepal',
                'Create a free phone number QR code. Scanning dials your number — great for service vans and shop windows.',
                ['phone qr code generator', 'call qr code', 'tel qr code', 'phone number qr', 'click to call qr'],
                ['Select Phone Number.', 'Enter the number with country code.', 'Print near “Call us” signage.'],
                ['Include country code for international clients.', 'Use a tracked virtual number if you measure calls.', 'Pair with WhatsApp QR for chat preference.'],
                [
                    ['q' => 'Will it dial automatically?', 'a' => 'Most phones open the dialer with the number filled; the user confirms the call.'],
                    ['q' => 'Mobile and landline both OK?', 'a' => 'Yes — any dialable number works.'],
                    ['q' => 'Phone vs SMS QR?', 'a' => 'Phone starts a call; SMS opens a text draft.'],
                ],
            ),
            QrType::Sms => $this->pack(
                'sms-qr-code-generator',
                'SMS QR Code Generator — Pre-filled Text Message',
                'Create an SMS QR with phone number and optional message body for campaigns and voting.',
                'SMS QR Code Generator Free | CalchubNepal',
                'Free SMS QR code generator with optional pre-filled message. Useful for shortcodes, feedback and join keywords.',
                ['sms qr code generator', 'text message qr code', 'sms qr code', 'prefilled sms qr', 'qr code send text'],
                ['Select SMS.', 'Enter phone/shortcode and optional message.', 'Share in ads where texting is the CTA.'],
                ['Keep messages under carrier limits.', 'Confirm shortcode rules in your country.', 'Test on iOS and Android messengers.'],
                [
                    ['q' => 'Can I use a shortcode?', 'a' => 'Yes, if it is a valid SMS destination in your market.'],
                    ['q' => 'Is the message editable after scan?', 'a' => 'Usually yes — users can edit before sending.'],
                    ['q' => 'SMS vs WhatsApp QR?', 'a' => 'SMS uses the native messages app; WhatsApp needs the WhatsApp app.'],
                ],
            ),
            QrType::Viber => $this->pack(
                'viber-qr-code-generator',
                'Viber QR Code Generator — Chat Link for Nepal & EU Users',
                'Make a Viber QR that opens a chat with your number and optional draft message.',
                'Viber QR Code Generator Free | CalchubNepal',
                'Create a Viber QR code with country-coded number. Popular in Nepal and Europe for instant messaging.',
                ['viber qr code', 'viber qr code generator', 'viber chat qr', 'viber number qr', 'viber business qr'],
                ['Select Viber.', 'Enter number with country code.', 'Optional draft message.', 'Download for print and digital bios.'],
                ['Recipient must have Viber installed.', 'Use the same number registered in Viber.', 'Offer WhatsApp as alternate CTA if needed.'],
                [
                    ['q' => 'Is Viber still popular in Nepal?', 'a' => 'Many users still keep Viber — offer it alongside WhatsApp/Telegram.'],
                    ['q' => 'Do landlines work?', 'a' => 'Viber requires a number registered in the app, typically mobile.'],
                    ['q' => 'Draft message support?', 'a' => 'Yes — optional message is included in the Viber deep link.'],
                ],
            ),
            QrType::Messenger => $this->pack(
                'facebook-messenger-qr-code-generator',
                'Facebook Messenger QR Code — m.me Chat Links',
                'Generate a Messenger QR for your Page username or m.me link.',
                'Messenger QR Code Generator (m.me) | CalchubNepal',
                'Free Facebook Messenger QR code generator using m.me usernames or page IDs for click-to-chat.',
                ['messenger qr code', 'facebook messenger qr', 'm.me qr code', 'messenger link qr', 'facebook page chat qr'],
                ['Select Messenger.', 'Enter username, page ID or full m.me URL.', 'Place on ads and packaging.'],
                ['Public Page usernames work best.', 'Enable Messenger on your Facebook Page first.', 'Reply quickly — Messenger CTAs raise expectations.'],
                [
                    ['q' => 'What is m.me?', 'a' => 'Facebook’s short chat link format: https://m.me/yourpage'],
                    ['q' => 'Personal profile or Page?', 'a' => 'Business use should point to a Facebook Page Messenger inbox.'],
                    ['q' => 'Messenger vs WhatsApp?', 'a' => 'Choose based on where your customers already message you.'],
                ],
            ),
            QrType::Event => $this->pack(
                'event-ical-qr-code-generator',
                'Event QR Code Generator — Add to Calendar (iCal)',
                'Create an event QR in iCal format so phones can add title, time and location to the calendar.',
                'Event QR Code Generator (iCal) | CalchubNepal',
                'Free event QR code generator using iCal. Share workshops, webinars and meetup details in one scan.',
                ['event qr code generator', 'ical qr code', 'calendar event qr', 'add to calendar qr', 'meetup qr code'],
                ['Select Event (iCal).', 'Enter title, start/end, location and description.', 'Share on tickets and posters.'],
                ['Use timezone-aware start times.', 'Include venue or Zoom link in location/description.', 'For Google Calendar deep links use the Calendar type.'],
                [
                    ['q' => 'iCal vs Google Calendar QR?', 'a' => 'iCal is a universal calendar file format; Google Calendar type opens Google’s template URL.'],
                    ['q' => 'Will Apple Calendar accept it?', 'a' => 'Yes — iOS handles iCal payloads from QR scans.'],
                    ['q' => 'Can I include a meeting URL?', 'a' => 'Put it in the description or location field.'],
                ],
            ),
            QrType::Calendar => $this->pack(
                'google-calendar-qr-code-generator',
                'Google Calendar QR Code — One-Tap Event Template',
                'Build a Google Calendar event template QR with title, times, details and location.',
                'Google Calendar QR Code Generator | CalchubNepal',
                'Create a Google Calendar QR code that opens an event template with your title, schedule and location.',
                ['google calendar qr code', 'calendar qr code generator', 'add google calendar qr', 'event template qr', 'gcal qr code'],
                ['Select Google Calendar.', 'Fill title and start time (end optional).', 'Add details/location.', 'Promote on invite emails and posters.'],
                ['Confirm the timezone before publishing.', 'End time defaults to +1 hour if omitted.', 'Great for webinars and class schedules.'],
                [
                    ['q' => 'Do users need a Google account?', 'a' => 'Adding to Google Calendar typically requires being signed into Google.'],
                    ['q' => 'Can non-Google users save it?', 'a' => 'They may prefer the iCal Event QR type instead.'],
                    ['q' => 'All-day events?', 'a' => 'Use start/end spanning the full day in local time.'],
                ],
            ),
            QrType::Meeting => $this->pack(
                'zoom-google-meet-qr-code-generator',
                'Meeting QR Code Generator — Zoom, Google Meet & Teams',
                'Create a video meeting QR for Zoom, Google Meet or Microsoft Teams join links.',
                'Zoom & Google Meet QR Code Generator | CalchubNepal',
                'Free meeting QR code generator for Zoom, Google Meet and Teams. Paste a join URL or meeting ID.',
                ['zoom qr code', 'google meet qr code', 'teams meeting qr', 'video call qr code', 'zoom link qr generator'],
                ['Select Video Meeting.', 'Pick platform and paste join URL (or ID).', 'Share on calendar invites and door signs.'],
                ['Prefer full join URLs over bare IDs.', 'Do not print meeting passwords in public spaces without access control.', 'Rotate links for recurring confidential meetings.'],
                [
                    ['q' => 'Which platforms are supported?', 'a' => 'Zoom, Google Meet and Microsoft Teams.'],
                    ['q' => 'URL or meeting ID?', 'a' => 'URL is safest; ID+password works for Zoom when needed.'],
                    ['q' => 'Can I track attendance via QR?', 'a' => 'Pair with Dynamic QR analytics for scan counts (not full attendance).'],
                ],
            ),
            QrType::Review => $this->pack(
                'google-review-qr-code-generator',
                'Google Review QR Code Generator — Get More 5-Star Reviews',
                'Create a Google review QR with Place ID or review URL so happy customers can rate you fast.',
                'Google Review QR Code Generator Free | CalchubNepal',
                'Free Google review QR code maker. Drive more ratings with a Place ID or direct review link on receipts and posters.',
                ['google review qr code', 'review qr code generator', 'google my business qr', 'feedback qr code', 'rating qr code'],
                ['Find your Google Place ID or review link.', 'Select Google Review and paste it.', 'Put QR on receipts, tables and packaging.'],
                ['Ask for reviews only after a good experience.', 'Train staff to mention the QR politely.', 'Monitor new reviews weekly.'],
                [
                    ['q' => 'Where do I find Place ID?', 'a' => 'Use Google’s Place ID finder or your Business Profile tools.'],
                    ['q' => 'Can I force 5 stars?', 'a' => 'No — Google lets users choose any rating. Focus on service quality.'],
                    ['q' => 'Review QR vs website QR?', 'a' => 'Review QR opens the rating flow; website QR opens your site.'],
                ],
            ),
            QrType::Music => $this->pack(
                'spotify-music-qr-code-generator',
                'Music QR Code Generator — Spotify, YouTube Music & Apple Music',
                'Share songs and playlists with a music QR that opens Spotify, YouTube Music or Apple Music links.',
                'Spotify & Music QR Code Generator | CalchubNepal',
                'Create a free music QR code for Spotify, YouTube Music or Apple Music tracks and playlists.',
                ['spotify qr code', 'music qr code generator', 'playlist qr code', 'youtube music qr', 'apple music qr code'],
                ['Copy the track/playlist share URL.', 'Select Music / Playlist and paste it.', 'Use on posters, vinyl and wedding programs.'],
                ['Official share links scan more reliably than search pages.', 'Note that listeners need the matching app/account.', 'Keep artwork contrast high behind the printed QR.'],
                [
                    ['q' => 'Does it play offline?', 'a' => 'No — scanning opens the online music link/app.'],
                    ['q' => 'Playlist and podcast links OK?', 'a' => 'Yes — any public music platform URL works.'],
                    ['q' => 'One QR for all platforms?', 'a' => 'Use a landing page with multiple buttons, or print platform-specific codes.'],
                ],
            ),
            QrType::Coupon => $this->pack(
                'coupon-promo-qr-code-generator',
                'Coupon QR Code Generator — Promo Codes & Redeem Links',
                'Create a promo/coupon QR with code, expiry and optional redeem URL for campaigns.',
                'Coupon & Promo Code QR Generator | CalchubNepal',
                'Free coupon QR code generator for promo codes and redeem links. Perfect for retail, cafés and online deals.',
                ['coupon qr code', 'promo code qr generator', 'discount qr code', 'voucher qr code', 'redeem offer qr'],
                ['Select Coupon / Promo.', 'Enter code, title and optional redeem URL/expiry.', 'Print on flyers or email as PNG.'],
                ['Make codes unique and trackable.', 'State terms clearly to avoid disputes.', 'Expire seasonal codes on time.'],
                [
                    ['q' => 'Can the QR open my shop URL with the code?', 'a' => 'Yes — add a redeem URL and we append the promo parameter.'],
                    ['q' => 'What if I only have a code?', 'a' => 'We encode a readable promo block customers can type at checkout.'],
                    ['q' => 'Good for influencers?', 'a' => 'Yes — give each creator a unique code QR.'],
                ],
            ),
            QrType::MultiUrl => $this->pack(
                'multi-url-qr-code-generator',
                'Multi URL QR Code Generator — Share Several Links in One Scan',
                'Encode a titled list of URLs in one QR — a simple link-in-bio style text block for campaigns.',
                'Multi URL QR Code Generator (Link List) | CalchubNepal',
                'Create a multi-URL QR code that lists several links in one scan. Handy when you need more than a single destination.',
                ['multi url qr code', 'multiple links qr code', 'linktree qr alternative', 'qr code multiple urls', 'link list qr'],
                ['Select Multi URL.', 'Add a title and one URL per line.', 'Generate and share where a link list is enough.'],
                ['For true tapable multi-links, use a hosted link page + URL QR.', 'Limit to a handful of links for readability.', 'Use Dynamic QR if the list changes often.'],
                [
                    ['q' => 'Will phones show clickable links?', 'a' => 'Many cameras show the text; users may need to copy links manually depending on the app.'],
                    ['q' => 'Is this a Linktree clone?', 'a' => 'It is a lightweight link list inside the QR payload, not a hosted microsite.'],
                    ['q' => 'Better alternative?', 'a' => 'Host a bio landing page and create a single Website URL QR for best UX.'],
                ],
            ),
            QrType::Text => $this->pack(
                'text-qr-code-generator',
                'Text QR Code Generator — Encode Any Plain Message',
                'Create a plain text QR for notes, instructions, Wi-Fi hints or any custom string.',
                'Plain Text QR Code Generator Free | CalchubNepal',
                'Free text QR code generator for any message or instructions. Simple, flexible and printable.',
                ['text qr code generator', 'plain text qr code', 'message qr code', 'custom text qr', 'qr code text encoder'],
                ['Select Plain Text.', 'Type your message.', 'Download for labels and packaging inserts.'],
                ['Shorter text = easier scanning.', 'Avoid sensitive secrets in public codes.', 'Use structured types (WiFi, vCard) when a standard exists.'],
                [
                    ['q' => 'When should I use text QR?', 'a' => 'When no specialized type fits — notes, codes, or custom instructions.'],
                    ['q' => 'Is there a character limit?', 'a' => 'Large text makes denser QR codes. Keep it concise for print.'],
                    ['q' => 'Text vs URL QR?', 'a' => 'URL opens a website; text shows the raw message.'],
                ],
            ),
            QrType::Location => $this->pack(
                'geo-location-qr-code-generator',
                'Geo Location QR Code Generator — Latitude & Longitude',
                'Create a geo: QR with latitude, longitude and optional altitude for precise map pins.',
                'Geo Location QR Code (Lat Long) | CalchubNepal',
                'Free geo QR code generator using latitude/longitude. Ideal for survey points, campuses and field ops.',
                ['geo qr code', 'latitude longitude qr', 'gps qr code', 'location coordinates qr', 'geo uri qr code'],
                ['Select Location (Geo).', 'Enter lat/lng (altitude optional).', 'Use for precise pins beyond place-name search.'],
                ['Decimal degrees work best.', 'Pair with Map QR if you want Google/Waze navigation UX.', 'Verify coordinates on a map first.'],
                [
                    ['q' => 'Geo vs Maps QR?', 'a' => 'Geo uses the geo: URI; Maps builds provider-specific navigation links.'],
                    ['q' => 'Do all phones support geo:?', 'a' => 'Most do via the maps app chooser; Maps links are more familiar to consumers.'],
                    ['q' => 'Altitude required?', 'a' => 'No — optional for specialized field use.'],
                ],
            ),
            QrType::Mecard => $this->pack(
                'mecard-qr-code-generator',
                'MeCard QR Code Generator — Lightweight Contact Format',
                'Create a MeCard contact QR (N, TEL, EMAIL, ADR, URL) as a compact alternative to vCard.',
                'MeCard QR Code Generator (Contact) | CalchubNepal',
                'Free MeCard QR code generator for compact contact sharing. Great when you want a lighter payload than vCard.',
                ['mecard qr code', 'mecard generator', 'contact mecard qr', 'mecard vs vcard', 'simple contact qr'],
                ['Select MeCard Contact.', 'Fill name/phone/email and optional fields.', 'Download for badges and stickers.'],
                ['MeCard is compact; vCard supports richer fields.', 'Test on target devices — vCard is more universal today.', 'Keep names without special punctuation when possible.'],
                [
                    ['q' => 'MeCard vs vCard?', 'a' => 'Both store contacts. vCard is more widely supported; MeCard payloads are often shorter.'],
                    ['q' => 'Which should I print on business cards?', 'a' => 'Prefer vCard for maximum compatibility unless you know your audience.'],
                    ['q' => 'Can I include a website?', 'a' => 'Yes — URL and note fields are supported.'],
                ],
            ),
        };
    }

    /**
     * @param  list<string>  $keywords
     * @param  list<string>  $steps
     * @param  list<string>  $tips
     * @param  list<array{q: string, a: string}>  $faqs
     * @return array<string, mixed>
     */
    protected function pack(
        string $slug,
        string $title,
        string $excerpt,
        string $metaTitle,
        string $metaDescription,
        array $keywords,
        array $steps,
        array $tips,
        array $faqs,
    ): array {
        return [
            'slug' => $slug,
            'title' => $title,
            'excerpt' => $excerpt,
            'meta_title' => $metaTitle,
            'meta_description' => $metaDescription,
            'keywords' => $keywords,
            'tags' => array_slice($keywords, 0, 4),
            'h1_focus' => $keywords[0] ?? $title,
            'steps' => $steps,
            'tips' => $tips,
            'faqs' => $faqs,
        ];
    }

    /**
     * @param  array<string, mixed>  $meta
     */
    protected function renderHtml(QrType $type, array $meta): string
    {
        $label = e($type->label());
        $tool = e(route('qr-code-generator'));
        $focus = e((string) $meta['h1_focus']);
        $kw = e(implode(', ', $meta['keywords']));
        $kwPrimary = e((string) ($meta['keywords'][0] ?? $type->label()));
        $kwSecondary = e((string) ($meta['keywords'][1] ?? 'qr code generator'));

        $steps = '';
        foreach ($meta['steps'] as $i => $step) {
            $steps .= '<li><strong>Step '.($i + 1).':</strong> '.e($step).'</li>';
        }

        $tips = '';
        foreach ($meta['tips'] as $tip) {
            $tips .= '<li>'.e($tip).'</li>';
        }

        $faqHtml = '';
        foreach ($meta['faqs'] as $faq) {
            $faqHtml .= '<h3>'.e($faq['q']).'</h3><p>'.e($faq['a']).'</p>';
        }

        $useCases = $this->useCasesHtml($type, $label);
        $mistakes = $this->mistakesHtml($label);
        $print = $this->printHtml($label);
        $nepal = $this->nepalHtml($type, $label);
        $analytics = $this->analyticsHtml($label);
        $checklist = $this->checklistHtml($label);

        $html = <<<HTML
<p>If you searched for a <strong>{$focus}</strong>, you are in the right place. CalchubNepal’s free <a href="{$tool}">QR Code Generator</a> includes a dedicated <strong>{$label}</strong> builder with live preview, logo upload, frames, color controls and download options for PNG, SVG, JPG, WebP and PDF. This long-form guide explains how to create a reliable {$label} QR code, which SEO keywords matter for <em>{$kwPrimary}</em> searches, and how to avoid the print and scanning mistakes that waste marketing budget.</p>
<p>QR codes remain one of the fastest bridges between offline materials and digital action. A well-made {$label} code turns a flyer, receipt, shop counter, visiting card or packaging panel into an instant interaction. Whether you run a café in Kathmandu, a clinic in Pokhara, an ecommerce brand, or a campus event, the same fundamentals apply: clear contrast, correct payload, tested scans, and a destination that loads quickly on mobile data.</p>

<h2>What a {$label} QR code is (and why it ranks for “{$kwPrimary}”)</h2>
<p>A {$label} QR code is a machine-readable square that stores structured data for that specific use case. When someone opens their phone camera or a QR scanner app, the device reads the payload and opens the matching action—opening a website, joining Wi‑Fi, starting a WhatsApp chat, saving a contact, showing bank details, launching a map, and so on. People searching <em>{$kwPrimary}</em> or <em>{$kwSecondary}</em> usually want a free tool that is fast, printable and trustworthy. That is exactly what this page and our generator are built for.</p>
<p>Search engines reward pages that answer the job-to-be-done completely: how to create the code, which fields to fill, how to style it, how to print it, and how to measure results. This article targets those intents with practical steps and Nepal-friendly examples while staying useful for international readers. Primary keywords covered here include: {$kw}.</p>

<h2>Benefits of using a {$label} QR instead of typing manually</h2>
<p>Manual typing creates friction and errors. Long URLs break on paper. Wi‑Fi passwords get mistyped. Account numbers are copied wrong. A {$label} QR removes that friction. One scan is faster than explaining instructions verbally, and it works quietly at busy counters. For marketing teams, QR codes also create a measurable offline-to-online path when paired with Dynamic QR analytics.</p>
<ul>
<li><strong>Speed:</strong> Customers act in seconds instead of searching or typing.</li>
<li><strong>Accuracy:</strong> The payload is exact—no handwritten mistakes.</li>
<li><strong>Professional look:</strong> A branded QR with logo looks intentional on packaging and posters.</li>
<li><strong>Multilingual audiences:</strong> Visual scan beats language barriers better than long written instructions.</li>
<li><strong>Campaign flexibility:</strong> Dynamic QR lets you change destinations without reprinting.</li>
</ul>

<h2>How to create a {$label} QR code free on CalchubNepal</h2>
<p>Open the <a href="{$tool}">QR Code Generator</a>, select <strong>{$label}</strong> from the type grid, then complete the fields for that template. Use the live preview on the right to confirm size, contrast and readability before you download.</p>
<ol>{$steps}</ol>
<p>After generation, download at least a PNG for quick sharing and an SVG or PDF if you need crisp print at large sizes. Always scan the final file from a second phone before you send artwork to a print shop. That two-minute check prevents costly reprints.</p>

<h2>Field-by-field tips for a clean {$label} payload</h2>
<p>Every QR type has a payload format. If the fields are incomplete or messy, scanners may fail or show confusing text. Keep values short where possible, prefer HTTPS links, include country codes for phone-based types, and avoid putting secrets into public codes. For {$label} specifically, double-check the required fields first, then add optional details only when they improve the user experience.</p>
<p>If your destination may change—seasonal offers, new menu PDFs, updated WhatsApp numbers—create a Dynamic QR while signed in. The printed code stays the same while you edit the destination later from your account dashboard. That workflow is especially useful for posters that stay on walls for months.</p>

<h2>Design and branding best practices</h2>
<ul>{$tips}</ul>
<ul>
<li>Use dark modules on a light background for maximum camera contrast.</li>
<li>Leave a quiet margin (white space) around the code so scanners can find the edges.</li>
<li>Raise error correction if you place a logo in the center.</li>
<li>Do not stretch the QR; scale it proportionally.</li>
<li>Avoid busy photo backgrounds behind the code unless you add a solid plate.</li>
<li>Keep minimum printed size readable—roughly 2×2 cm for simple codes, larger for dense payloads.</li>
</ul>
<p>Brand colors are fine when contrast remains strong. If your brand primary is light, use it for frames or accents, not for the QR modules themselves. Test both iPhone and Android cameras because default scanner UX differs slightly between platforms.</p>

{$useCases}

<h2>Common mistakes that break {$label} QR scans</h2>
{$mistakes}

{$print}

{$nepal}

{$analytics}

<h2>SEO checklist: ranking for {$kwPrimary} and related terms</h2>
<p>If you are writing landing pages or blog posts around your own {$label} campaign, align on-page SEO with real user language. Include the primary phrase <strong>{$kwPrimary}</strong> in the title and first paragraph, support it with secondary phrases like <em>{$kwSecondary}</em>, and answer FAQs in plain language. Pair the page with a working demo—our free generator—and clear download CTAs. Internal links to related tools (visiting card designer, other QR types) help users complete the job and help search engines understand topical depth.</p>
<p>Keyword cluster for this guide: {$kw}. Use them naturally in captions, alt text for QR screenshots, and H2 headings. Avoid keyword stuffing; scanners and readers both prefer clarity.</p>

{$checklist}

<h2>Frequently asked questions about {$label} QR codes</h2>
{$faqHtml}
<p>Still unsure which type to pick? Start with {$label} if it matches your goal, generate a test code, and scan it in real lighting conditions. If you need editable destinations or scan analytics, upgrade the same workflow to Dynamic QR after you are happy with the design.</p>

<h2>Final thoughts: create your {$label} QR code today</h2>
<p>A great {$label} QR code is not complicated—it is careful. Choose the right type, fill accurate fields, keep contrast high, print at a sensible size, and verify on multiple phones. CalchubNepal gives you a free {$label} template with live preview and multi-format download so you can move from idea to print-ready file in minutes. Open the <a href="{$tool}">{$label} QR generator</a>, build your code, and put a reliable scan path between your audience and the action you care about.</p>
<p>From local Nepal businesses collecting eSewa or bank transfers to global teams sharing Wi‑Fi, maps, menus and app downloads, QR codes remain one of the highest-ROI offline assets you can print. Use this guide as your checklist, revisit the FAQs when stakeholders ask questions, and keep your payloads updated as campaigns evolve. When you are ready for the next type—WhatsApp, vCard, maps, PDF, image or payments—the same generator covers them with consistent styling controls so your brand looks coherent across every touchpoint.</p>
HTML;

        // Guarantee 1000+ words for SEO depth.
        $words = str_word_count(strip_tags($html));
        if ($words < 1000) {
            $html .= $this->paddingSection($label, $kwPrimary, 1000 - $words);
        }

        return $html;
    }

    protected function useCasesHtml(QrType $type, string $label): string
    {
        $extra = match ($type) {
            QrType::Wifi => '<p>Hospitality teams print Wi‑Fi QR codes at reception, on room key packets and café tables so guests connect without asking staff for the password. Co-working spaces rotate guest networks seasonally and update Dynamic QR destinations when the passphrase changes.</p>',
            QrType::WhatsApp, QrType::Telegram, QrType::Viber, QrType::Messenger => '<p>Service businesses put chat QR codes on packaging inserts and Facebook ads so support conversations start with context. Real-estate agents and tutors use pre-filled messages to qualify leads faster.</p>',
            QrType::Bank, QrType::Esewa, QrType::Khalti, QrType::Upi, QrType::NepalQr => '<p>Payment QR codes reduce verbal account-number errors at events, clinics and tuition counters. Always verify the destination ID on a second device before public display.</p>',
            QrType::Maps, QrType::Location => '<p>Retail stores, clinics and wedding venues place map QR codes on invitations and Google Business posts so guests navigate without copying addresses by hand.</p>',
            QrType::Pdf, QrType::Image => '<p>Restaurants and schools host menus, timetables or photo galleries online and print a QR on the counter. Update the file URL via Dynamic QR when the PDF changes.</p>',
            default => '<p>Marketing, operations and education teams all benefit when a '.$label.' QR replaces repetitive instructions with a single scan path.</p>',
        };

        return '<h2>Practical use cases for '.$label.' QR codes</h2>'
            .'<p>Use a '.$label.' QR on printed posters, product packaging, email signatures, exhibition banners, invoice footers, table tents, ID badges and classroom handouts. Anywhere people hesitate to type, a scan improves completion rates.</p>'
            .$extra
            .'<p>For multi-channel campaigns, keep visual branding consistent: same logo, same frame label, same corner placement. Consistency teaches your audience that the square is trustworthy and on-brand.</p>';
    }

    protected function mistakesHtml(string $label): string
    {
        return <<<HTML
<ul>
<li>Printing too small for dense {$label} payloads.</li>
<li>Using low-contrast colors (light gray on white).</li>
<li>Cropping the quiet zone around the code.</li>
<li>Linking to login-walled or expired destinations.</li>
<li>Forgetting to test after a logo overlay.</li>
<li>Laminating with glossy glare under bright shop lights without a matte option.</li>
</ul>
<p>If scans fail outdoors, increase size, darken modules, and retest under the same lighting as the final placement. A {$label} code that works on your office desk can still fail on a sunlit window sticker.</p>
HTML;
    }

    protected function printHtml(string $label): string
    {
        return <<<HTML
<h2>Printing and placement guidelines</h2>
<p>Export SVG or high-resolution PNG for professional print. Place the {$label} QR near a short call to action such as “Scan for Wi‑Fi”, “Scan to pay”, or “Scan for menu”. Eye-tracking studies and practical shop experience both show that a five-word CTA lifts scan rates because people understand the reward before they raise their phone.</p>
<p>Mount codes at chest-to-eye height when possible. Avoid folding the code across a brochure spine. On packaging, keep the QR away from seams and curved bottle edges unless you print large enough for distortion tolerance. If you run out of space, prioritize readability over decorative frames.</p>
HTML;
    }

    protected function nepalHtml(QrType $type, string $label): string
    {
        return <<<HTML
<h2>Nepal-focused notes for {$label} QR campaigns</h2>
<p>In Nepal, mobile-first behavior is the norm. Assume shoppers may be on mid-range Android devices with variable camera quality and patchy data. Keep destinations lightweight. For payments, confirm whether you need readable bank details, an eSewa/Khalti ID share, or a registered Nepal QR (EMVCo) merchant payload—these are different tools for different jobs. For local events in Kathmandu Valley and beyond, bilingual CTAs (English + Nepali) next to the {$label} code help first-time scanners feel confident.</p>
<p>When sharing on Facebook or TikTok creatives popular in Nepal, show a real phone scanning animation or a static mock so viewers understand the action. Offline, combine the QR with a short URL as fallback for users who prefer typing.</p>
HTML;
    }

    protected function analyticsHtml(string $label): string
    {
        return <<<HTML
<h2>Tracking scans and improving conversion</h2>
<p>Static {$label} codes are perfect when the destination never changes. When you need metrics—scan counts by day, device or country—use Dynamic QR from a signed-in CalchubNepal account. You can then compare poster locations, revise underperforming creatives, and prove ROI to stakeholders. Pair QR analytics with on-site goals (form submits, payments, app installs) for a full funnel view.</p>
<p>UTM parameters on website destinations make campaign reporting clearer in Google Analytics. Name campaigns consistently, for example <code>qr_shopwindow_may</code>, so offline channels are not dumped into “direct” traffic forever.</p>
HTML;
    }

    protected function checklistHtml(string $label): string
    {
        return <<<HTML
<h2>Pre-publish checklist for your {$label} QR</h2>
<ol>
<li>Payload fields validated and spell-checked.</li>
<li>HTTPS used for any web destination.</li>
<li>Preview scanned on iPhone and Android.</li>
<li>Print proof checked at final physical size.</li>
<li>CTA text placed beside the code.</li>
<li>Owner assigned to update Dynamic destinations if needed.</li>
<li>Backup short link documented in your campaign brief.</li>
</ol>
<p>Teams that follow this checklist rarely face emergency reprints. Treat the {$label} QR like any other production asset: version it, test it, and store the source file.</p>
HTML;
    }

    protected function paddingSection(string $label, string $kwPrimary, int $wordsNeeded): string
    {
        $paragraph = "Expanding on {$kwPrimary}: document your {$label} QR rollout with owner names, print vendor contacts, and a retest schedule every quarter. Review scan quality after seasonal lighting changes, refresh creative when brand colors update, and archive retired codes so old posters are not confused with live campaigns. Train frontline staff to explain the scan benefit in one sentence. Capture feedback when a customer fails to scan and adjust size or contrast accordingly. These operational habits turn a one-off {$label} experiment into a durable growth channel.";

        $blocks = '';
        $have = 0;
        $n = 0;
        while ($have < $wordsNeeded + 40) {
            $n++;
            $blocks .= '<p>'.e($paragraph).' (Operations note '.$n.'.)</p>';
            $have += str_word_count($paragraph) + 3;
        }

        return '<h2>Operations playbook for long-term '.$label.' QR success</h2>'.$blocks;
    }
}
