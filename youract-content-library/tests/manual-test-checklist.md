# Manual Test Checklist

## Activation and Deactivation

- Activate plugin with WP_DEBUG enabled and confirm no warnings/notices/fatals.
- Confirm Resources and Events menu items appear.
- Deactivate plugin and confirm no content is deleted.

## Rewrites

- Confirm /resources/ archive resolves.
- Confirm /events/ archive resolves.
- Confirm taxonomy rewrites for /topics/, /geography/, /resource-types/, /event-types/.

## Resource Authoring

- Create Resource with all fields and confirm save succeeds.
- Edit Resource and confirm metadata persists.
- Enter invalid Resource URL and verify admin notice appears and value is preserved for correction.
- Enter invalid Last Reviewed date and verify notice + no silent coercion.

## Event Authoring and Time Zones

- Create Event in site timezone and verify UTC storage plus local re-display.
- Create Event in a different IANA timezone and verify conversion in edit screen.
- Create all-day Event and verify date-only behavior.
- Create Event crossing midnight and verify end remains after start.
- Create Event with end before start and verify validation error.
- Create a registration deadline and verify conversion and re-display.
- Enter invalid deadline inputs and verify validation notice + preserved values.

## Status and Scope Logic

- Mark Event cancelled and confirm upcoming shortcode excludes it.
- Mark Event completed and confirm upcoming shortcode excludes it.
- Mark Event postponed and confirm status text is visible in card/details.
- Verify [youract_events scope="upcoming"] ordering is ascending by start.
- Verify [youract_events scope="past"] ordering is descending by start.
- Verify [youract_events scope="all"] returns full set.

## Resource Filters

- Verify keyword filter works.
- Verify Topic filter works.
- Verify Geographic Scope filter works.
- Verify Resource Type filter works.
- Verify selected filters remain selected after submit.
- Verify Apply filters button and Clear filters link behavior.
- Verify archived resources are excluded by default.
- Verify include archived is available only to authorized users.

## Pagination and Empty States

- Verify pagination appears for long lists.
- Verify page query vars are youract-prefixed.
- Verify empty result message displays when filters match nothing.

## Accessibility Checks

- Keyboard test all filter controls and submit actions.
- Confirm focus indication is visible on links and controls.
- Confirm headings and landmark regions are logical.
- Confirm statuses are visible as text (not color-only).
- Validate at 200% and 400% zoom.
- Check Windows forced-colors mode behavior.
- Verify screen reader announces result count updates and notices.

## REST API and Data

- Confirm CPTs and taxonomies appear in REST API index.
- Confirm registered meta appears in REST responses where expected.
- Verify nonce/capability protections prevent unauthorized metadata updates.
- Verify sanitization and escaping for all rendered fields.

## Structured Data

- Validate singular Event JSON-LD in Rich Results or Schema validator.
- Confirm incomplete Event data does not output invalid JSON-LD properties.
- Confirm status mapping uses correct schema.org EventStatus URLs.

## Theme and Portability

- Switch themes and confirm CPTs, taxonomies, and metadata remain functional.
- Confirm shortcode output remains functional without theme-specific JS.
