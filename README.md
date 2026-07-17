# Scheduled Events for Zen Cart

Current version: v2.0.0 — requires Zen Cart v2.0.0 or later (developed and tested against
v2.2.2/v2.2.3).

Displays upcoming scheduled events (shows, fairs, expos, appearances, etc.) on their own storefront
page, with a link added into the current template's Information sidebox. Events are managed from a
dedicated admin screen (Catalog > Scheduled Events), with display options, labels, and the display
time window configurable under Configuration > Scheduled Events.

Highlights:
- Storefront events page (`main_page=events`) listing every currently-qualifying event, separated by
  a horizontal rule between entries, with a configurable "no events scheduled" message (showing the
  current date window) when none qualify.
- Each event has a name, place, and start/stop dates (required), plus optional comments, booth
  location, event information, and driving directions.
- Booth Location and Event Information are each a plain-text description with a separate, optional
  URL — the description becomes a clickable link only when a valid URL is given, so the store owner
  never has to write HTML or worry about a bad link breaking the page.
- Driving Directions accepts either a plain address/URL (opens Google Maps in a new tab) or a Google
  Maps "Embed a map" `<iframe>` code (opens as a self-contained on-page pop-up map instead — no page
  navigation, full-screen on phones/tablets, closed via an X, clicking outside, or Escape). The map is
  lazy-loaded and never fetched unless the pop-up is actually opened.
- A master "Enable Scheduled Events Display" switch fully hides the storefront page and sidebox link
  without uninstalling.
- Each event has an Active checkbox (checked by default) so a store owner can pause/hide an event —
  e.g. a scheduling conflict — without deleting it or changing its dates. Inactive events never appear
  on the storefront page or in any sidebox. The admin Catalog list shows active events only by
  default, with a "Show All" link to reveal paused events (shown dimmed with a Status column) for
  editing or reactivation.
- The Information sidebox link is styled to match `responsive_classic` and ZCA Bootstrap out of the
  box. Using any other template may require adjusting its styling to match that template's own
  conventions.
- Sidebox Display Mode (Configuration > Scheduled Events) chooses between "Information Listing" (the
  link above) and "Bootstrap Sidebox" (the plugin's own scrolling box, placed via Design > Layout
  Boxes Controller — requires a Bootstrap-based template). That box's placement doesn't survive an
  uninstall, so it needs to be re-placed via Layout Boxes Controller after any uninstall/reinstall.
- Reinstalling after an uninstall resets every Configuration > Scheduled Events setting back to its
  default value — that's separate from the event data itself, which a SQL dump/restore of the
  `eventz` table preserves independently.

Author: dbltoe

See [readme_scheduled_events.html](readme_scheduled_events.html) for installation/upgrade/uninstall
instructions and change history.
