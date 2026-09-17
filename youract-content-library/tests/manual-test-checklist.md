# Manual Test Checklist

## Activation and Deactivation

- Activate plugin with WP_DEBUG enabled and confirm no warnings/notices/fatals.
- Activate with ACF active and confirm Resource Details appears exactly once.
- Deactivate ACF and confirm the plugin remains active without a fatal error.
- With ACF unavailable, confirm an administrator who can activate plugins sees the missing-ACF notice.
- Reactivate ACF and confirm the missing-ACF notice disappears.
- Confirm activation creates the Recommended Reading term with slug `recommended-reading`.
- Repeat activation and confirm no duplicate Recommended Reading term is created.
- Confirm existing Resource Type terms remain unchanged.
- Confirm Resources and Events menu items appear.
- Deactivate plugin and confirm no content is deleted.

## Rewrites

- Confirm /resources/ archive resolves.
- Confirm /events/ archive resolves.
- Confirm taxonomy rewrites for /topics/, /geography/, /resource-types/, /event-types/.

## Resource Authoring

- Create a Resource with every ACF field and confirm save/reload persistence.
- Confirm date fields store `Ymd` and display using the WordPress site date format.
- Confirm Why It Matters strips unsafe HTML while preserving permitted markup.
- Confirm registered REST metadata requires appropriate post-specific edit authorization.
- Confirm the native WordPress excerpt is the ACT Summary and no Resource Summary field exists.

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

## Resource Visibility

- Published + Active Resource appears in the public archive and `[youract_resources]`.
- Published + Needs Review Resource does not appear in the archive or generic public listing.
- Published + Archived Resource does not appear in the archive or generic public listing.
- Draft + Active Resource does not appear publicly.
- Add another archive meta query constraint and confirm Resource status filtering preserves it.

## Recommended Publications Eligibility

- Published + Active or unset status + Recommended Reading + valid external URL is included.
- Confirm exclusions for Draft, Needs Review, Archived, another Resource Type, and missing external URL.

## Recommended Publication Output

- Add `[youract_publications]` and confirm the headline links directly to the external source.
- Confirm no forced new tab, internal Resource permalink, Read More link, or second external link appears.
- Confirm source and date appear immediately below the headline.
- Confirm the separator appears only when both source and date exist.
- Confirm missing source/date produces no broken metadata markup.
- Confirm ACT Summary uses the native excerpt.
- Confirm Why It Matters uses canonical metadata and unsafe HTML is removed.
- Confirm multiple entries have unique heading IDs.
- Confirm visible keyboard focus on publication headline links.
- Confirm pagination uses `youract_publications_page` and preserves unrelated query parameters.
- Confirm shortcode attributes clamp `per_page` to 1-100 and normalize invalid `orderby`/`order` values.
- Confirm multiple publication shortcodes do not corrupt post context.

## Generic Resource Regression

- Verify `[youract_resources]` still works with keyword and taxonomy filters.
- Verify `[youract_featured_resources]` still works.
- Verify generic Resource cards and singular Resource details still work.
- Verify Resource template overrides still work.

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
- Confirm Resource structured data uses canonical metadata, conservative source fields, and valid ISO dates.

## Event Regression

- Event editor still works and Event metadata saves unchanged.
- Event archive and Event shortcodes still work.
- Event Details block still renders and avoids duplicate details.
- Event structured data still validates.
- Confirm no Event metadata key was renamed.

## Theme and Portability

- Switch themes and confirm CPTs, taxonomies, and metadata remain functional.
- Confirm shortcode output remains functional without theme-specific JS.
