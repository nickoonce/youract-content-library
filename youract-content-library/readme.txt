=== Your ACT Content Library ===
Contributors: youract
Tags: accessibility, events, resources, custom post type
Requires at least: 6.7
Tested up to: 6.8
Requires PHP: 8.1
Stable tag: 1.0.0
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Structured Resources and Events library plugin for YourACT.net using native WordPress APIs.

== Description ==

Your ACT Content Library adds two structured content types to WordPress:

- Resources
- Events

The plugin is built for Accessibility Coalition for Transformation (ACT) use cases with an emphasis on:

- Data portability
- Accessibility-oriented admin and front-end output
- Time-zone aware event handling
- Maintainable architecture and restrained scope

Out of scope by design:

- Ticket sales
- Registration workflows
- Payment processing
- Calendar grid UIs
- Community account systems

== Installation ==

1. Upload the youract-content-library folder to /wp-content/plugins/.
2. Activate "Your ACT Content Library" in Plugins.
3. Visit Settings > Permalinks and save once if your environment requires manual rewrite refresh.

== Initial Setup ==

1. Confirm Resources and Events appear in wp-admin.
2. Review seeded terms in Resource Types and Event Types.
3. Add Topics and Geographic Scope terms relevant to your community.
4. Add the provided shortcodes to pages as needed.

== Shortcode Documentation ==

= [youract_resources] =

Displays a searchable, filterable Resource library.

Optional attributes:

- per_page (default: 10)

GET filters:

- youract_resource_keyword
- youract_resource_topic
- youract_resource_geography
- youract_resource_type
- youract_include_archived (editor-level users)

Pagination query var:

- youract_resources_page

= [youract_events] =

Displays Events with time-based scope.

Optional attributes:

- scope: upcoming, past, all (default: upcoming)
- per_page (default: 10)

Pagination query var:

- youract_events_page

= [youract_featured_resources limit="3"] =

Displays active featured Resources.

= [youract_upcoming_events limit="3"] =

Displays next upcoming Events based on UTC timestamps.

== Field Documentation ==

= Resource Fields =

- External URL
- Source organization
- Resource summary
- Access notes
- Last reviewed (YYYY-MM-DD)
- Resource status: active, needs-review, archived
- Featured resource

= Event Fields =

- Event start UTC timestamp (stored)
- Event end UTC timestamp (stored)
- Event timezone (IANA)
- All-day flag
- Event format: in-person, online, hybrid
- Venue
- Address / connection details
- Organizer
- Event URL
- Registration URL
- Registration deadline UTC timestamp (stored)
- Event cost
- Accessibility information
- Accommodation contact
- Accommodation deadline UTC timestamp (stored)
- Event status: scheduled, postponed, cancelled, completed
- Last verified (YYYY-MM-DD)

Admin UI stores date/time input in selected event timezone and converts to UTC at save.

== Theme Integration Guidance ==

Templates can be overridden by placing files in your active theme:

- /youract-content-library/resource-card.php
- /youract-content-library/event-card.php
- /youract-content-library/resource-details.php
- /youract-content-library/event-details.php

Use hooks to customize output and behavior without editing plugin core.

== Accessibility Notes ==

The plugin uses:

- Semantic sections and heading structure
- Visible labels and descriptions for controls
- Keyboard-operable native form controls
- Focus-visible styling
- Non-color status text
- Non-JavaScript filter workflows

Final accessibility conformance depends on active theme styles and content quality.

== Data Retention Behavior ==

- Deactivation preserves all posts, terms, and metadata.
- Uninstall preserves all data by default.
- Data deletion on uninstall is opt-in only via:
- YOURACT_CONTENT_LIBRARY_DELETE_DATA constant set to true
- youract_delete_data_on_uninstall option set truthy

== Frequently Asked Questions ==

= Does this plugin require ACF or CPT UI? =

No. It uses only WordPress core APIs.

= Can editors include archived resources in the library filter? =

Yes. Users with edit capability can opt in using the "Include archived resources" filter.

= How are Event times stored? =

UTC Unix timestamps are stored. Admin entry/editing uses local time in the selected IANA timezone.

= Does the plugin output Event structured data? =

Yes, on singular Event pages when enough data is available for valid JSON-LD.

== Changelog ==

= 1.0.0 =

- Initial release.
- Added Resources and Events custom post types.
- Added Topics, Geographic Scope, Resource Types, and Event Types taxonomies.
- Added secure metadata registration and admin meta boxes.
- Added timezone-aware Event date/time validation and UTC conversion.
- Added accessible shortcodes and template override support.
- Added singular detail sections and Event JSON-LD output.
