# Scheduled Events for Zen Cart

Current version: v2.0.0 — requires Zen Cart v2.0.0 or later (developed and tested against
v2.2.2/v2.2.3).

Displays upcoming scheduled events (shows, fairs, expos, appearances, etc.) on their own storefront
page, with a link added into the current template's Information sidebox. Events are managed from a
dedicated admin screen (Extras > Scheduled Events), with display options, labels, and the display
time window configurable under Configuration > Scheduled Events.

Highlights:
- Installs as a fully self-contained (encapsulated) plugin — no core Zen Cart files are ever modified.
  Install/uninstall only touches this plugin's own database table, its own configuration entries, and
  its own admin menu registration.
- Storefront events page (`main_page=events`) listing every currently-qualifying event, separated by
  a horizontal rule between entries, with a configurable "no events scheduled" message (showing the
  current date window) when none qualify.
- Each event has a name, place, and start/stop dates (required), plus optional comments, booth
  location, event information, and driving directions.
- Name, Booth Location, and Event Information display best when kept as brief as possible while still
  conveying the required information — Name doubles as the carousel sidebox's only event label (no
  room for long text there), and Booth Location/Event Information become clickable link text on the
  events page, so a short, clear phrase reads better than a long sentence.
- Booth Location and Event Information are each a plain-text description with a separate, optional
  URL — the description becomes a clickable link only when a valid URL is given, so the store owner
  never has to write HTML or worry about a bad link breaking the page. The URL may be a full external
  address or a root-relative path to an asset already on the store, so a locally-hosted booth-location
  map image doesn't require an absolute URL — use `/images/mapName.jpg` if it was uploaded via the
  admin Image Manager, or `/includes/templates/your_template/images/mapName.jpg` if it was placed
  directly in the current template's own images folder. Any common image format works (JPG, PNG, GIF,
  WebP, SVG, etc.) — this is just a link to an existing file, not a processed product image, so the
  format doesn't matter to the plugin. If uploading via Zen Cart's Image Manager on a store running
  Image Handler 5, note that WebP files are known to convert slowly there; JPG/PNG avoid that
  entirely. Also size/optimize the image yourself before uploading — unlike product images, this file
  is displayed exactly as uploaded, with no resizing by Zen Cart or Image Handler 5. Test any such path
  by opening it in a browser first, since a bad path is silently ignored rather than shown as an error.
  Some hosts also block direct access to non-image files like PDFs even when the path is correct (a
  "403 Forbidden"), regardless of what this plugin does — if that happens, try a folder your host does
  allow direct access to rather than the images folder.
- Driving Directions accepts either a plain address/URL (opens Google Maps in a new tab) or a Google
  Maps "Embed a map" `<iframe>` code (opens as a self-contained on-page pop-up map instead — no page
  navigation, full-screen on phones/tablets, closed via an X, clicking outside, or Escape). The map is
  lazy-loaded and never fetched unless the pop-up is actually opened.
- A master "Enable Scheduled Events Display" switch fully hides the storefront page and sidebox link
  without uninstalling.
- Each event has an Active checkbox (checked by default) so a store owner can pause/hide an event —
  e.g. a scheduling conflict — without deleting it or changing its dates. Inactive events never appear
  on the storefront page or in any sidebox. The admin events list shows active events only by
  default, with a "Show All" link to reveal paused events (shown dimmed with a Status column) for
  editing or reactivation.
- A link to the events page is always added into the current template's Information sidebox, using the
  exact same classes `responsive_classic` and ZCA Bootstrap already use for their own sidebox links and
  cards — so if you customize either template's own colors/fonts (e.g. via the ZCA Bootstrap color
  theme editor), the sidebox link/box follows along automatically, with no separate update needed.
  Using any other template may require adjusting its styling to match that template's own conventions.
  The main events page itself, by contrast, uses this plugin's own dedicated stylesheet rather than the
  template's classes — it's designed to read cleanly on both templates by default, but won't pick up
  template color/theme customizations the way the sidebox does.
- "Enable Additional Bootstrap Sidebox" (Configuration > Scheduled Events, off by default) adds a
  second, separate promotional listing alongside the Information sidebox link above: the plugin's own
  auto-scrolling box, placed via Design > Layout Boxes Controller — requires a Bootstrap-based
  template, and is meant to be left off on any other template. That box's placement doesn't survive an
  uninstall, so it needs to be re-placed via Layout Boxes Controller after any uninstall/reinstall.
  Bootstrap-based templates typically hide the entire sidebar column on phones/tablets; this sidebox
  automatically relocates itself into the main content area as a full-width block below that breakpoint
  instead of disappearing, then moves back to the sidebar on larger screens.
- Reinstalling after an uninstall resets every Configuration > Scheduled Events setting back to its
  default value — that's separate from the event data itself, which a SQL dump/restore of the
  `eventz` table preserves independently.

Author: dbltoe

See [readme_scheduled_events.html](readme_scheduled_events.html) for installation/upgrade/uninstall
instructions and change history.
