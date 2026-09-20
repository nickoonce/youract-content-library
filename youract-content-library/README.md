# Your ACT Content Library

Production-oriented WordPress plugin for structured Events, Resources, and Opportunities on YourACT.net. Recommended Publications are Resources classified with the Recommended Reading Resource Type, not a separate post type.

## Dependencies

- Advanced Custom Fields provides the Resource editing interface.
- The plugin degrades gracefully when ACF is unavailable; public rendering and Event features remain available.
- Public rendering uses registered WordPress metadata and does not call ACF functions.

## Resource Model

- Post type: `act_resource`
- Archive: `/resources/`
- The native WordPress excerpt is the ACT Summary.

Resource taxonomies:

- `act_resource_type`
- `act_topic`
- `act_geography`

### Recommended Reading

- Term name: Recommended Reading
- Term slug: `recommended-reading`
- Publish only freely accessible original sources.
- Editors verify free availability and record Last Reviewed.
- No Access or Paywall field is used.

### Canonical Resource Metadata

- `youract_external_url`
- `youract_source_organization`
- `youract_original_author`
- `youract_original_publication_date`
- `youract_why_it_matters`
- `youract_accessibility_notes`
- `youract_last_reviewed`
- `youract_resource_status`
- `youract_featured_resource`

ACF dates are stored as `Ymd`. Displayed dates use the WordPress site date format. Resource Status values are `active`, `needs-review`, and `archived`.

## Opportunity Model

- Post type: `act_opportunity`
- Archive: `/opportunities/`
- Native post states control visibility: use Draft and Published.
- Stage values: `exploring`, `seeking-partners`, `in-progress`, `success-story`, and `inactive`.
- Inactive Opportunities are excluded from public archive collections.
- Featured Opportunities are ordered before non-featured Opportunities, then by publish date descending.

Opportunity metadata:

- `act_opportunity_stage`
- `act_opportunity_featured`
- `act_opportunity_subtitle`
- `act_opportunity_help_needed`
- `act_opportunity_cta_label`
- `act_opportunity_cta_url`

An Opportunity stays in the same post through its lifecycle. Moving it to Success story does not change its permalink, content, or revisions. A CTA is rendered only when both a label and a valid URL are present.

`act_opportunity_help_needed` is a plain-text summary of the contributions needed now. It is registered for REST access and can be displayed in templates and Query Loop patterns by binding a `core/post-meta` block to this key. Keep the complete “We’re looking for” list in the post content.

## Architecture Summary

- Bootstrap: youract-content-library.php
- Service coordinator: includes/class-plugin.php
- Content model: includes/class-post-types.php and includes/class-taxonomies.php
- Lifecycle: includes/class-activator.php and includes/class-deactivator.php
- Metadata registration: includes/class-meta-registration.php
- Admin editing workflows: includes/class-admin-resource.php and includes/class-admin-event.php
- Front-end querying and rendering:
- includes/class-resource-query.php
- includes/class-event-query.php
- includes/class-shortcodes.php
- includes/class-renderer.php
- Block integration: includes/class-blocks.php and blocks/event-details/block.json
- Structured data: includes/class-structured-data.php

## Hooks Reference

### Query filters

- youract_resource_query_args
- youract_featured_resource_query_args
- youract_publications_query_args
- youract_event_query_args
- youract_upcoming_event_query_args

### Output filters

- youract_resource_card_data
- youract_resource_card_html
- youract_event_card_data
- youract_event_card_html
- youract_resource_details_data
- youract_resource_details_html
- youract_publication_entry_data
- youract_publication_entry_html
- youract_event_details_data
- youract_event_details_html
- youract_external_event_disclaimer

### Behavior toggles

- youract_resource_details_enabled
- youract_event_details_enabled
- youract_event_json_ld_enabled
- youract_event_json_ld_data
- youract_template_locations
- youract_before_publications_loop
- youract_after_publications_loop

## Template Overrides

Place override templates in your theme:

- youract-content-library/resource-card.php
- youract-content-library/event-card.php
- youract-content-library/resource-details.php
- youract-content-library/event-details.php
- youract-content-library/publication-entry.php
- youract-content-library/opportunity-details.php

## Security Notes

- Nonce + capability checks for all metadata saves.
- Autosave/revision writes ignored.
- Input sanitized and validated by field type.
- Invalid date/time and URL values are rejected with admin notices.
- URLs are escaped, rich text is restricted with `wp_kses_post()`, and publication links do not force a new tab.

## Performance Notes

- Uses bounded WP_Query calls with pagination.
- Uses no external network calls.
- Avoids rewrite flush except activation/deactivation.

## Recommended Publications Shortcode

Use `[youract_publications]` to render eligible Recommended Reading Resources in a one-column editorial list.

Attributes:

- `per_page` (default: `50`, constrained to 1-100)
- `orderby` (`title` or `date`, default: `title`)
- `order` (`ASC` or `DESC`, default: `ASC`)

Examples:

`[youract_publications]`

`[youract_publications per_page="12" orderby="title" order="ASC"]`

Each entry displays, in order: a headline linked directly to the source, source and original publication date, ACT Summary, and Why It Matters. The publication loop does not include an internal Resource permalink or Read More link.

Pagination uses the `youract_publications_page` query variable.

## Import Contract

Replacement imports should use:

- Post type: `act_resource`
- Resource Type: Recommended Reading
- `post_title`: headline
- `post_excerpt`: ACT Summary
- `post_content`: optional or blank
- `post_status`: `draft` for initial imports

Metadata:

- `youract_external_url`
- `youract_source_organization`
- `youract_original_author`
- `youract_original_publication_date`
- `youract_why_it_matters`
- `youract_accessibility_notes`
- `youract_last_reviewed`
- `youract_resource_status`
- `youract_featured_resource`

Dates imported into ACF metadata must use `Ymd`.
