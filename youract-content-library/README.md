# Your ACT Content Library

Production-oriented WordPress plugin for structured Resources and Events on YourACT.net.

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
- Structured data: includes/class-structured-data.php

## Hooks Reference

### Query filters

- youract_resource_query_args
- youract_featured_resource_query_args
- youract_event_query_args
- youract_upcoming_event_query_args

### Output filters

- youract_resource_card_data
- youract_resource_card_html
- youract_event_card_data
- youract_event_card_html
- youract_resource_details_data
- youract_resource_details_html
- youract_event_details_data
- youract_event_details_html
- youract_external_event_disclaimer

### Behavior toggles

- youract_resource_details_enabled
- youract_event_details_enabled
- youract_event_json_ld_enabled
- youract_event_json_ld_data
- youract_template_locations

## Template Overrides

Place override templates in your theme:

- youract-content-library/resource-card.php
- youract-content-library/event-card.php
- youract-content-library/resource-details.php
- youract-content-library/event-details.php

## Security Notes

- Nonce + capability checks for all metadata saves.
- Autosave/revision writes ignored.
- Input sanitized and validated by field type.
- Invalid date/time and URL values are rejected with admin notices.

## Performance Notes

- Uses bounded WP_Query calls with pagination.
- Uses no external network calls.
- Avoids rewrite flush except activation/deactivation.
