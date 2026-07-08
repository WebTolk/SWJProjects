# Change Summary

## Flow

`10-upgrade-262-270-maintainer-backfill`

## Summary

Implemented typed project links for the `2.6.2 -> 2.7.0` upgrade path without creating or assigning maintainers, then completed a real browser-driven update on the `web-tolk.local` production-copy stand.

## Behavior

- Existing fixed-key `projects.urls` JSON is normalized to typed project-link list JSON.
- Admin project edit form now stores project links as typed rows.
- Frontend project buttons still render legacy and typed link data.
- Documentation URL override still suppresses generated documentation route when a project documentation URL is present.
- Component params now have separate `project_link_types`.
- Installer `update()` now reads the installed version from `#__extensions.manifest_cache` and, only when it is below `2.7.0` (or missing), seeds `project_link_types` and migrates stored project URL JSON idempotently.
- Real browser upload/update through Joomla administrator on `https://web-tolk.local/administrator/` succeeds for the rebuilt `2.7.0` package.
- A real regression was found during browser assurance: saving a migrated project without edits converted typed links into broken rows like `type=urls0`, `value=Array`.
- That regression was fixed in `ProjectLinksHelper::normalizeLinks()` by recognizing associative posted subform row arrays as typed rows before falling back to legacy `type => value` map normalization.
- After rebuilding and reinstalling the package, admin save keeps migrated link rows intact and frontend rendering remains correct.

## Compatibility

- Legacy rows remain readable through `ProjectLinksHelper`.
- Re-running migration on typed data keeps the same list and does not create duplicate links.
- Main worktree API model returns typed project links; release worktree has no API folder and was not expanded.
- Browser verification on real copied production data confirmed project `id=5` keeps all six links after update, after save, and on the public project page.

## Runtime Assurance Result

- `web-tolk.local` browser update path is verified on the copied production site.
- `com_swjprojects` and `pkg_swjprojects` are now `2.7.0` on that stand after browser install.
- `project_link_types` were seeded in component params with the six default types: `demo`, `support`, `github`, `jed`, `donate`, `documentation`.
- Migrated data on the copied production DB is fully typed (`typed_rows=101`, `legacy_rows=0`), and the helper recheck is idempotent (`idempotent_remaining_changes=0`).
- CLI installer on this machine is still not a reliable proxy because standalone PHP cannot create temp folders in the current execution context, but the real browser-side Joomla installer flow is proven to work.

## Package Evidence

- Rebuilt package: `E:\dev\SWJProjects-release-2.7.0\.packages\SW JProjects_2.7.0.zip`
- The package was rebuilt again after the browser-discovered save-path fix in `ProjectLinksHelper.php`.
- Browser installer success on `web-tolk.local` confirmed the rebuilt package installs through the real Joomla administrator update flow.

## 2026-07-07 Local Installer Sync

- A local-only drift check showed the current repo already had the guarded installer logic in `com_swjprojects/script.php`, while `E:\dev\SWJProjects-release-2.7.0\com_swjprojects\script.php` and the installed `web-tolk.local` copy still carried the older unconditional `postflight()` migration path.
- `E:\dev\SWJProjects-release-2.7.0\com_swjprojects\script.php` was resynchronized from the current repo, `SW JProjects_2.7.0.zip` was rebuilt, and the copied-production stand `web-tolk.local` was updated locally from that rebuilt ZIP.
- Rebuilt package metadata: size `448015` bytes, SHA256 `1195A413318BA597874BBD729C40CB49718CB4047102D8C3A04C9C435042414C`.
- Local Joomla CLI reinstall on `web-tolk.local` returned `[OK] Extension installed successfully.` and replaced the installed component script with the guarded `update()` version.

## 2026-07-07 Update-Gate Fix

- A clean rerun from the archived `web-tolk.local` `2.6.2` snapshot exposed a real defect in the guarded installer logic: reading the old version from `#__extensions.manifest_cache` inside `update()` does not work on the real Joomla update path because `storeExtension()` rewrites `manifest_cache` before the installer `update()` callback runs.
- The component installer script now captures the currently installed version during `preflight('update', ...)` from the live administrator manifest file `administrator/components/com_swjprojects/swjprojects.xml`, stores it on the installer instance, and then uses that captured version inside `update()`.
- The fixed installer script was mirrored into `E:\dev\SWJProjects-release-2.7.0\com_swjprojects\script.php`, the package was rebuilt, and the rebuilt local test ZIP is `E:\dev\SWJProjects-release-2.7.0\.packages\SW JProjects_2.7.0.zip` with SHA256 `EFC5CB075F0789A41196D2932B06315B79187BE2EAC46599E2A8C06075861372`.
- A full local rerun from the archived `2.6.2` files+DB on `web-tolk.local` now passes: update to `2.7.0` seeds `project_link_types`, migrates project `id=5` to typed rows, yields `typed_rows=101`, `legacy_rows=0`, and installs the corrected script variant on the stand.

## 2026-07-08 One-List Hotfix

- Component params no longer model project and maintainer link types as two independent config lists.
- The canonical component param is now link_types, shared by both maintainer and project link subforms.
- Legacy params maintainer_link_types and project_link_types are still readable as migration/fallback inputs, but installer checkLinkTypes() now collapses them into link_types and removes both legacy keys on install/update.
- ProjectLinksHelper now delegates link-type sourcing to MaintainerLinksHelper, so both surfaces read the same normalized type registry.
- Rebuilt release package: E:\dev\SWJProjects-release-2.7.0\.packages\SW JProjects_2.7.0.zip
- Rebuilt package SHA256: 705DF9CB03386E532CCF11E3EDEB28A5D4E39C73BCBFAE47D4499545C1223175

## Pending Runtime Check

- Install the rebuilt package on the target stand and confirm the component config shows one shared link_types fieldset.
- Confirm existing stored values survive migration into link_types and both project/maintainer subforms still render their options.


## 2026-07-08 Language-Key Titles Hotfix

- Built-in shared link-type rows now store language keys directly in the shared `link_types` param (`COM_SWJPROJECTS_URLS_DEMO`, `..._SUPPORT`, `..._GITHUB`, `..._JED`, `..._DONATE`, `..._DOCUMENTATION`).
- Maintainer and project link-type fields now translate those stored keys at render time, so the config rows keep stable locale-neutral values while the edit forms still show localized labels.
- Installer `checkLinkTypes()` now rewrites already-saved built-in titles like `Demo`, `Support`, `JED`, `Donate`, and `Documentation` into the corresponding language keys during install/update, while custom user-defined titles remain unchanged.
- Rebuilt release package: E:\dev\SWJProjects-release-2.7.0\.packages\SW JProjects_2.7.0.zip
- Rebuilt package SHA256: 47363106F942E3F1DBF29FDF741CD75C77453DDDBD7EA92F88D9F2CE57583F3D
- Rebuilt package size: 450773 bytes
- Runtime install/config smoke for this title-normalization slice is still pending.
