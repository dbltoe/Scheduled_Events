# Scheduled Events

Version 2.2.0 · Zen Cart 1.5.8 through 3.0.0 · PHP 7.4 through 8.5 · GPL v2 · by My Zen Cart Host (dbltoe)

Upcoming shows, fairs, expos and appearances on their own storefront page, managed from the admin.

## Overview

Scheduled Events installs as a fully self-contained (encapsulated) plugin — no core Zen Cart files are ever modified. Install/uninstall only touches this plugin's own database table, its own configuration entries, and its own admin menu registration.

Scheduled Events lets a store owner maintain a list of upcoming events (shows, fairs, expos, appearances, etc.) and display them on their own storefront page. Events are managed from Extras > Scheduled Events in the admin. On Zen Cart 2.0.0 and later, a link to the events page is always added into the current template's existing Information sidebox, using the exact same classes `responsive_classic` and ZCA Bootstrap already use for their own sidebox links and cards — so if you customize either template's own colors/fonts (e.g. via the ZCA Bootstrap color theme editor), the sidebox link/box follows along automatically, with no separate update needed. Using any other template may require adjusting its styling to match that template's own conventions. The main events page itself, by contrast, uses this plugin's own dedicated stylesheet rather than the template's classes — it's designed to read cleanly on any template by default, and its link and label colors are settings (see Look and Colors below).

"Enable Events Sidebox" (Configuration > Scheduled Events, off by default; called "Enable Additional Bootstrap Sidebox" before version 2.1.0) adds a separate promotional listing: the plugin's own Events sidebox, placed via Design > Layout Boxes Controller. On a Bootstrap-based template (e.g. ZCA Bootstrap or a clone, Bootstrap 4 or 5) it scrolls through the upcoming events; on any other template (e.g. `responsive_classic`) it lists them one under another. Its heading links to the events page.

**Zen Cart 1.5.8:** the Information sidebox there has no way for a plugin to add a link, so the Information sidebox link described above doesn't appear on 1.5.8. Turn on "Enable Events Sidebox" and place the box in a column; it is how customers find the events page on a 1.5.8 store. Everything else works the same on 1.5.8 as on later versions.

The two promotional listings link slightly differently, by design: the Information sidebox link and the Events sidebox heading open the events page at the top, while clicking an event in the Events sidebox jumps straight to that specific event further down the page. (If your template has a fixed/sticky navbar, the plugin offsets that jump so the event heading isn't hidden underneath it.)

Bootstrap-based templates typically hide the entire sidebar column on phones/tablets. Rather than disappear along with it, the Events sidebox (if enabled) automatically relocates itself into the main content area as a full-width block below that breakpoint, then moves back to its normal sidebar position on larger screens - no configuration needed.

The storefront page heading, all field labels, the "no events scheduled" message, the display time window (how many days before an event's start date it becomes visible), and the sidebox link text are all configurable under Configuration > Scheduled Events. A master "Enable Scheduled Events Display" switch there can fully hide the storefront page and all of its promotion at any time, without uninstalling the plugin.

## Look and Colors

The plugin keeps one look in its own areas, the events page and the Events sidebox, whatever the template or a site stylesheet adds. Every link in an event's lines (Booth Location, Event Information, Get Directions) keeps the same weight, size and underline at rest and on hover, so no link changes width or looks different from the others, and the labels stay bold. In the Events sidebox, the heading and the event names keep their bold on hover, and the event names get no hover shadow. Get Directions is a real link like the others, also when it opens the map pop-up, so a template's link styles reach it too.

Colors are settings instead, under Configuration > Scheduled Events:

| Setting | What it colors |
| --- | --- |
| Link Color | The links in each event and in the Events sidebox. |
| Link Hover Color | Those links under the pointer or with keyboard focus. |
| Label Color | Each event's labels (Place:, Start Date: and the rest). |

Each offers **Use Site Colors** (the template's own color; the default) or **Enter Code**: a hex color such as `#9a044f`, typed or picked with the color box beside it. A chosen color wins over any template or site rule.

## Event Fields

Each event has the following fields, set from Extras > Scheduled Events:

- **Name, Place, Start Date, Stop Date** — required for every event. Name doubles as the Events sidebox's only event label (there's no room for long text there), so a short, clear name reads best.
- **Comments** — optional free text.
- **Booth Location** (required) **and Event Information** (optional) — each is a plain-text description with a separate, optional URL field. If a valid URL is given, the description becomes a link (opened in a new tab); if the URL is missing or doesn't validate, it's silently ignored and the description just displays as plain text. The store owner never has to author HTML. Since the description becomes the visible link text, a brief phrase (e.g. "Space #16" or "Visit the official show website") reads better than a long sentence. The URL field accepts either a full external address or a root-relative path to an asset already on the store — handy for a Booth Location map image hosted locally rather than linked externally (Event Information is meant for an external event website, so this is unlikely to come up there in practice, though the field works the same way). Use `/images/mapName.jpg` if the image was uploaded via the admin Image Manager (the site root's `images` folder), or `/includes/templates/your_template/images/mapName.jpg` if it was placed directly in the current template's own images folder instead. Any common image format works (JPG, PNG, GIF, WebP, SVG, etc.) — this is just a link to an existing file, not a processed product image, so the format doesn't matter to the plugin. If uploading via Zen Cart's Image Manager on a store running Image Handler 5, note that WebP files are known to convert slowly there; JPG/PNG avoid that entirely. Also size/optimize the image yourself before uploading — unlike product images, this file is displayed exactly as uploaded, with no resizing by Zen Cart or Image Handler 5. Since a bad path is silently ignored rather than shown as an error, test it first by opening the path directly in a browser (e.g. `yourdomain.com/images/mapName.jpg`). Some hosts also block direct access to non-image files like PDFs even when the path is correct (a "403 Forbidden"), regardless of what this plugin does — if that happens, try a folder your host does allow direct access to rather than the images folder.
- **Driving Directions** — accepts either a plain address or URL (the storefront link opens Google Maps in a new tab), or a Google Maps "Embed a map" `<iframe>` code (the storefront link instead opens the map as a self-contained pop-up over the page — no page navigation, full-screen automatically on phones/tablets, closed via its X button, clicking outside the pop-up, or the Escape key). The embedded map is lazy-loaded: it is never fetched unless a visitor actually opens the pop-up.
- **Active** (checked by default) — uncheck to pause an event, hiding it from the storefront page and every sidebox without deleting it or changing its dates. Useful when an event is on hiatus (e.g. a scheduling conflict) but may run again later. The admin events list shows active events only by default; a "Show All" link reveals paused events (shown dimmed, with a Status column) so they can be edited or reactivated.

On the storefront, events are listed in order, each as its name (as an H2) followed by its details in a blockquote; a horizontal rule separates consecutive events (none appears with zero or one event listed). When no events currently qualify for display, the page instead shows a configurable message naming the current date window, followed by a "check back later" line.

## Screenshots

This guide shows no images. The screenshots ship with the plugin, in the `screenshots` folder beside this file:

| File | What it shows |
| --- | --- |
| `responsive_classicEventsPage.png` | The events page on `responsive_classic`, with the Information sidebox link. |
| `zcaBootstrapEventsPage.png` | The same events page on ZCA Bootstrap. |
| `zcaBootstrapMainPageSidebox.png` | The home page on ZCA Bootstrap with the Events sidebox turned on (then called "Enable Additional Bootstrap Sidebox"), scrolling alongside Information and Bestsellers. |
| `adminConfigurationPage.png` | Configuration > Scheduled Events. |
| `adminExtrasEventsListingPage.png` | Extras > Scheduled Events: the events list, with sorting and the "Show All" toggle. |
| `adminAddEventPage.png` | Adding a new event. |
| `adminEditEventPage.png` | Editing an existing event. |

## Installing, Upgrading, or Uninstalling

### Initial Installation or Upgrade

Upload the `zc_plugins/ScheduledEvents` folder from the download into your store's `zc_plugins` folder, so that it lands at `zc_plugins/ScheduledEvents/v2.2.0`. Nothing goes anywhere else. Then, in the admin, go to **Modules > Plugin Manager**, select **Scheduled Events**, and click **Install**.

To upgrade, upload the new version's folder beside the old one, select Scheduled Events in Plugin Manager, and click **Upgrade**. Your events and settings are kept. The old version folder can be deleted afterward.

This creates the `eventz` database table (respecting your store's table prefix, if any), adds the Configuration > Scheduled Events settings group, and adds the Extras > Scheduled Events admin menu entry.

If "Enable Events Sidebox" (Configuration > Scheduled Events) is turned on, the box will need to be placed in a column via Design > Layout Boxes Controller after installing - its placement does not survive an uninstall (see below), so this is also needed again after any uninstall/reinstall cycle.

Once it's installed, a superuser can open this guide from the **ReadMe** button in Plugin Manager.

### Uninstalling

In the admin, go to **Modules > Plugin Manager**, select **Scheduled Events**, and click **Un-Install**. This removes the `eventz` table and all of its data, the plugin's configuration settings, the admin menu entry, **and the Events sidebox's placement/position in Design > Layout Boxes Controller, if it had been placed in a column**. Removing the plugin's files from the server afterward is optional but recommended.

**Reinstalling after an uninstall resets every Configuration > Scheduled Events setting back to its default value** (page heading, field labels, no-events message, display window, the Events Sidebox toggle/title/max items, etc.) - any customization made there will need to be re-entered. This is separate from the event data itself: exporting/restoring the `eventz` table (e.g. via a SQL dump) preserves your events, but not your Configuration choices.

## For Developers: Notifiers

From version 2.1.0 the plugin fires these notifiers, so an add-on can extend it without editing its files. Each works on every supported Zen Cart version. HTML an observer adds is output as is, so the observer escapes its own content.

| Notifier | Where | Parameters |
| --- | --- | --- |
| `NOTIFY_EVENTZ_QUALIFYING_EVENTS_QUERY` | Storefront, before the events are read (page and sidebox) | `['window_days', 'limit']`, then by reference the WHERE and ORDER BY clauses (table alias `e`) |
| `NOTIFY_EVENTZ_QUALIFYING_EVENTS_RESULT` | Storefront, after the events are read, before the sidebox's limit | the same array, then by reference the list of event rows (every column of the table) |
| `NOTIFY_EVENTZ_PAGE_LIST_TOP` | Events page, above the listing or the no-events message | `['events']`, then an HTML string by reference |
| `NOTIFY_EVENTZ_EVENT_DETAILS_END` | Events page, at the end of each event's details | `['event']`, then an HTML string by reference |
| `NOTIFY_EVENTZ_SIDEBOX_ITEM_END` | Events sidebox, inside each event's link, after its dates | `['event']`, then an HTML string by reference |
| `NOTIFY_EVENTZ_ADMIN_LIST_TOP` | Admin events list, beside Add Event | `['show_all', 'sort']`, then an HTML string by reference |
| `NOTIFY_EVENTZ_ADMIN_LIST_ROW_ACTIONS` | Admin events list, after each event's Edit and Delete | `['event']`, then an HTML string by reference |
| `NOTIFY_EVENTZ_ADMIN_FORM_EXTRA_ROWS` | Admin Add/Edit Event form, above Active (the form accepts file uploads) | `['action', 'event']`, then an HTML string by reference |
| `NOTIFY_EVENTZ_ADMIN_VALIDATE` | Admin, when the form is submitted | `['action', 'id']`, then by reference the submitted values and the error list |
| `NOTIFY_EVENTZ_ADMIN_EVENT_SAVED` | Admin, after an event is added or changed | `['action', 'id', 'data']` |
| `NOTIFY_EVENTZ_ADMIN_EVENT_DELETED` | Admin, after an event is deleted | `['id']` |

The page's own `NOTIFY_HEADER_START_EVENTS` and `NOTIFY_HEADER_END_EVENTS` are unchanged.

## Support

Source, issues and releases are on GitHub (https://github.com/dbltoe/Scheduled_Events). Questions and problem reports go to the Zen Cart forum support thread (https://www.zen-cart.com/threads/107224), which is also linked from the plugin's Plugin Manager panel.

## Changelog

See `changelog.txt`. Version 2.2.0 keeps one look in the plugin's own areas on any template and adds the Link Color, Link Hover Color and Label Color settings. Version 2.1.1 stops the admin page loading its stylesheet twice, where the second copy could be a stale one from the browser's cache. Version 2.1.0 adds Zen Cart 1.5.8 and PHP 7.4 support, makes the Events sidebox work on every template, and keeps text such as "Smith & Sons" as typed.

## License

Scheduled Events is free software under the GNU General Public License, version 2 (https://www.gnu.org/licenses/old-licenses/gpl-2.0.html); the full text is in `LICENSE`. Copyright © 2026 My Zen Cart Host (dbltoe).
