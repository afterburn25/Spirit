# RC8 post-upgrade content alignment

RC8 is installed. The remaining public mismatch is saved database content/settings overriding RC8 fallbacks.

Do not create RC9 for this. Use the existing administration UI.

## Why the live site still looks old

Published Page records override RC8 fallback text whenever their subtitle/body fields are non-empty.

Store navigation is controlled by the persisted store setting:
- checked: `Open the published merchandise catalog` -> Merch/Your bag remain visible;
- unchecked: RC8 hides Merch from the main navigation and Your bag from the footer.

## Store setting

Open:

`Admin -> Merchandise -> Store settings`

Uncheck:

`Open the published merchandise catalog`

Then click:

`Save store settings`

Leave products, variants, stock, customers and payment settings untouched.

## Page alignment workflow

Open:

`Admin -> Content -> Pages`

Use the **Your designed pages** directory.

For each section below:
1. Open the existing page record.
2. Keep its URL name unchanged.
3. Save a private draft.
4. Preview the saved draft.
5. When correct, click **Publish approved content**.
6. If anything is wrong, use **Version history & restore** instead of recreating the page.

### Home

URL name: `home`

Subtitle:
`Real people. Shared songs. A reason to come together.`

Page text:
`Music can turn an ordinary moment into something shared. Take a little time to listen, explore the performances, and discover the people behind Spiritual Gorillaz.

Music. People. Purpose. Start with a song and stay for the connection.`

Search / share title:
`Spiritual Gorillaz | Music, People, Purpose`

Search / share description:
`Discover Spiritual Gorillaz through music, performances, band stories and event inquiries.`

### Music

URL name: `music`

Subtitle:
`Find a song. Stay for the feeling.`

Page text:
`Explore the recordings selected for the Spiritual Gorillaz website. Choose a track, discover a performance, and follow the band's approved listening links.`

Search / share title:
`Spiritual Gorillaz Music | Recordings & Performances`

Search / share description:
`Explore Spiritual Gorillaz recordings, performances and approved listening links.`

### Band

URL name: `band`

Subtitle:
`The people behind the sound.`

Page text:
`Spiritual Gorillaz brings together Chad Lindsey on guitar, keyboards and vocals, Ron Green on harmonica, and Charlie Savell on drums.`

Search / share title:
`Meet Spiritual Gorillaz | The Band`

Search / share description:
`Meet Chad Lindsey, Ron Green and Charlie Savell, the musicians behind Spiritual Gorillaz.`

Do not create fake portraits or biographies.

If no member CMS profiles are published, RC8 itself will provide factual non-clickable fallback cards for:
- Chad Lindsey — Guitar, keyboards and vocals
- Ron Green — Harmonica
- Charlie Savell — Drums

### Booking

URL name: `booking`

Subtitle:
`Let's talk about your event.`

Page text:
`Tell us where, when, and what you have in mind. Include the venue, preferred date, event type, expected audience and any sound or production arrangements.

An inquiry starts the conversation. It does not reserve a date or take a payment.`

Search / share title:
`Book Spiritual Gorillaz | Event Inquiries`

Search / share description:
`Send a Spiritual Gorillaz booking inquiry with your preferred date, venue and event details. An inquiry is not a confirmed booking.`

### Press

URL name: `press`

Subtitle:
`Band information and booking inquiries.`

Page text:
`Spiritual Gorillaz brings together Chad Lindsey on guitar, keyboards and vocals, Ron Green on harmonica, and Charlie Savell on drums.

For event inquiries, use the booking page to provide your date, venue and performance requirements.`

Search / share title:
`Spiritual Gorillaz Press Kit | Band & Booking Information`

Search / share description:
`Official Spiritual Gorillaz band information, confirmed lineup and booking details.`

The prepared PDF must still be uploaded through the Media library and intentionally published as a Press download before it appears publicly.

## Optional sections

Shows, Media and Merch do not need invented records.

If their current saved Page introductions are older than the approved copy, align them through the same draft -> preview -> publish flow.

### Shows

Subtitle:
`The next connection starts here.`

Page text:
`Find confirmed performance dates, venue details and admission information in one place. When tickets are available, the ticket button takes you to the venue's existing seller.`

### Media

Subtitle:
`Watch. Listen. Get closer to the music.`

Page text:
`Spend a little time with Spiritual Gorillaz through selected performances and photographs. Choose a video to watch, or open a photograph for a closer look.`

### Merchandise

Only publish/change this Page if the store will later open.

Subtitle:
`Take a little of the music with you.`

Page text:
`Explore the Spiritual Gorillaz collection and choose the pieces that feel right for you. Each published product includes its available options, price and care information, so you can review the details before adding it to your bag.`

## After publishing

Public acceptance should show:

- Home no longer says “Music with a higher purpose.”
- Music no longer says “Real stories. Real sound.”
- Band shows the confirmed lineup even without member portraits.
- Booking says “Let's talk about your event.”
- Press says “Band information and booking inquiries.”
- If the store is disabled, Merch is absent from the main navigation and Your bag is absent from the footer.

Then run the read-only acceptance probe on this ops branch:

`python3 tools/live_acceptance.py --base https://www.spiritualgorillaz.com/`
