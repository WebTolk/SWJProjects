# Release Notes

- timestamp: 2026-07-07T12:40:49.8772689+04:00
- flow: `10-upgrade-262-270-maintainer-backfill`
- stage: release

## Release Scope

- Close the `2.6.2 -> 2.7.0` upgrade-safety flow for project links after real browser-driven assurance on the copied production stand.

## User-Visible Changes

- Existing project links stored in legacy fixed-key JSON are migrated to typed link rows during update to `2.7.0`.
- Project edit forms keep migrated links intact on save after the `normalizeLinks()` regression fix.
- Public project pages continue to render migrated `documentation`, `demo`, `support`, `github`, `jed`, and `donate` links.

## Internal Changes

- `ProjectLinksHelper` now distinguishes associative posted subform rows from the legacy `type => value` JSON map before fallback normalization.
- Component params now seed `project_link_types` during update when upgrading from below `2.7.0`.
- The release package was rebuilt after the browser-discovered save-path regression fix and reinstalled through the Joomla administrator update flow.

## Verification Status

- Passed on `web-tolk.local` copied production stand.
- Real browser upload/update, admin project save, DB persistence, and frontend rendering were verified on project `id=5`.
- CLI install remains non-authoritative on this machine because standalone PHP temp-directory writes fail, but that did not block the real browser-based Joomla update proof.

## Risks And Caveats

- Delivery confidence is based on one copied production stand with real data.
- Repeat on a second production-like copy only if rollout policy requires two-stand evidence.

## Rollback Notes

- Roll back by reinstalling the previous `2.6.2` package and restoring the pre-upgrade database snapshot if a deployment stand shows unexpected link-shape regressions.
- Do not use the local CLI installer on this host as rollback evidence until the temp-directory permission issue is resolved.

## Toolchain Contract References

- `phing` via configured toolchain

## 2026-07-08 Hotfix Addendum

- Fixed a config-level regression in the typed-links slice: component params now use one shared link_types list instead of separate maintainer/project link-type registries.
- Installer/update path now merges legacy maintainer_link_types and project_link_types into link_types.
- Rebuilt package: SW JProjects_2.7.0.zip SHA256 705DF9CB03386E532CCF11E3EDEB28A5D4E39C73BCBFAE47D4499545C1223175.


## 2026-07-08 Language-Key Titles Addendum

- Built-in rows in the shared component-config `link_types` registry now persist as language keys instead of human-readable text.
- Maintainer and project edit forms still display localized labels because their link-type field options translate the stored keys at render time.
- Rebuilt package: SW JProjects_2.7.0.zip SHA256 47363106F942E3F1DBF29FDF741CD75C77453DDDBD7EA92F88D9F2CE57583F3D.

## 2026-07-08 Package Whats-New Addendum

- Updated the package after-update whats-new text to the current `v.2.7.0` summary and added the maintainer entity there for both English and Russian locales.
- Rebuilt package: SW JProjects_2.7.0.zip SHA256 5132C65DA4E5926CB212D21728ADC92E04925958E20BC418B439AA9C2B5D976B.
