# Next Session Handoff

## Start Here

This session context is dirty. Restart from these artifacts:

1. `.agents/artifacts/intake/flows/10-upgrade-262-270-maintainer-backfill/task.json`
2. `.agents/artifacts/intake/flows/10-upgrade-262-270-maintainer-backfill/brief.md`
3. `.agents/artifacts/intake/flows/10-upgrade-262-270-maintainer-backfill/scope.md`
4. `.agents/artifacts/investigation/flows/10-upgrade-262-270-maintainer-backfill/upgrade-report.md`

## Key Decisions

- Do not create a default maintainer.
- Do not auto-assign existing projects to any maintainer.
- Do not migrate project URLs into maintainer links.
- Introduce typed project links separately from maintainer typed links.
- Default project link types should match the old project URL form:
  - `demo`
  - `support`
  - `github`
  - `jed`
  - `donate`
  - `documentation`
- Migration should convert old fixed-key `projects.urls` JSON to typed project-link list JSON.
- Keep compatibility for reading both old and new JSON shapes during the migration window.
- Implementation must stay PHP 8.1 compatible.

## Evidence Already Collected

- Built clean `SW JProjects_2.6.2.zip` from git tag `v.2.6.2`.
- Installed `2.6.2` on `joomla5.local`.
- Seeded a control project with old `urls` JSON.
- Updated to built `SW JProjects_2.7.0.zip`.
- Confirmed structure update works, and migration was applied on `joomla5.local` via the same conversion logic (`ProjectLinksHelper::toJson`) because CLI install is blocked by `public/tmp` write permissions.
- On `web-tolk.local` production-copy DB (project `id=5` pre-filled by user), confirmed:
  - before upgrade: `com_swjprojects=2.6.2`, `pkg_swjprojects=2.6.2`;
  - `id=5` legacy links converted to typed list JSON using `ProjectLinksHelper::toJson`;
  - `project_link_types` seeded in component params and persisted;
  - typed conversion is idempotent on repeat (`IDEMPOTENT_RECHECK=0`).
- On `web-tolk.local` browser-installed stand, confirmed:
  - Joomla administrator upload/update from `E:\dev\SWJProjects-release-2.7.0\.packages\SW JProjects_2.7.0.zip` succeeds;
  - `com_swjprojects` and `pkg_swjprojects` are `2.7.0` after update;
  - project `id=5` renders six typed link rows in the administrator form after migration;
  - public frontend project page renders `Документация`, `Демо`, `Поддержка`, `GitHub`, `JED`, and `Поддержать` links correctly.
- During browser assurance, a real regression was found and fixed:
  - saving the project without edits corrupted typed links into `type=urlsN`, `value=Array`;
  - root cause was associative posted subform rows being treated as legacy map input in `ProjectLinksHelper::normalizeLinks()`;
  - after fixing the helper, rebuilding, reinstalling, and repeating the browser save, the links remained intact in DB and UI.

## Current State

Flow 10 migration objective is met on the copied production stand.

- Legacy project link JSON is migrated to typed rows during the `2.6.2 -> 2.7.0` update path.
- Global `project_link_types` defaults are seeded during update only when upgrading from below `2.7.0`.
- No default maintainer is created and no project is auto-assigned to a maintainer.
- Real browser update, administrator save, and frontend rendering are verified on `web-tolk.local`.
- Release and evolve closeout artifacts were completed on 2026-07-07; this flow should not be treated as the current project stop point anymore.

## If This Flow Is Reopened

- Repeat the same browser update/save/frontend scenario on one more production-like copy if rollout policy requires two-stand evidence instead of one.
- Do not treat CLI `extension:install` failure on this machine as product evidence unless the PHP temp-directory permission issue is resolved first.


## 2026-07-07 Refactor Stop Point

- User requested that the installer stop depending on a separate helper for the project-link migration/default-type path.
- `com_swjprojects/script.php` was refactored locally so the installer now carries its own project-link migration helpers and no longer imports `Joomla\Component\SWJProjects\Administrator\Helper\ProjectLinksHelper`.
- The runtime helper was intentionally left in place for now because it is still consumed outside the installer by admin/site/API/layout surfaces; only the installer dependency was removed in this slice.
- The same `script.php` was mirrored into `E:\dev\SWJProjects-release-2.7.0\com_swjprojects\script.php` and a fresh local package was rebuilt:
  - `E:\dev\SWJProjects-release-2.7.0\.packages\SW JProjects_2.7.0.zip`
  - SHA256: `E08ECC324E6CA7FFBC77E16D27F7FDA0F41BD273F4019ABE29CB30FF0EC81F2C`
- Local rerestore for a fresh `2.6.2 -> 2.7.0` proof was started again on `web-tolk.local`:
  - DB is already back on `com_swjprojects=2.6.2`, `pkg_swjprojects=2.6.2`.
  - project `id=5` is back to the legacy `urls` JSON map.
  - current filesystem state is intermediate: `E:\OSPanel\home\web-tolk.local\public` contains the local `configuration.php` plus a nested restored site folder `public\web-tolk.ru\...`.
  - backup of the pre-rerestore site root is `E:\OSPanel\home\web-tolk.local\public.before-2026-07-07-refactor-205113`.
- The flatten step was interrupted by the user before CLI update verification resumed.

### Exact next step tomorrow

1. Move contents of `E:\OSPanel\home\web-tolk.local\public\web-tolk.ru\*` into `E:\OSPanel\home\web-tolk.local\public\`.
2. Remove the now-empty nested `web-tolk.ru` folder.
3. Re-copy local `configuration.php` from `public.before-2026-07-07-refactor-205113` if needed.
4. Run `php cli\joomla.php extension:install --path E:\dev\SWJProjects-release-2.7.0\.packages\SW JProjects_2.7.0.zip` from `E:\OSPanel\home\web-tolk.local\public`.
5. Verify versions and migration again (`2.7.0`, seeded `project_link_types`, typed rows, no legacy rows).

## 2026-07-08 Link Types Config Hotfix

- User checked the live site and found one remaining typed-links defect: the component config still exposed two global link-type lists (maintainer_link_types and project_link_types).
- The codebase hotfix is now applied in both main and release worktrees.
- Rebuilt package:
  - E:\dev\SWJProjects-release-2.7.0\.packages\SW JProjects_2.7.0.zip
  - SHA256: 705DF9CB03386E532CCF11E3EDEB28A5D4E39C73BCBFAE47D4499545C1223175

### Exact next step

1. Install the rebuilt 2.7.0 ZIP on the target Joomla stand.
2. Open component config and verify only one shared link-types fieldset is shown.
3. Confirm old stored values were collapsed into link_types.
4. Recheck maintainer and project edit forms to confirm both dropdowns still resolve the shared options.


## 2026-07-08 Language-Key Titles Hotfix

- Follow-up live defect addressed: built-in shared link-type titles now persist as `COM_SWJPROJECTS_URLS_*` constants directly in the component-config rows.
- Rebuilt package:
  - E:\dev\SWJProjects-release-2.7.0\.packages\SW JProjects_2.7.0.zip
  - SHA256: 47363106F942E3F1DBF29FDF741CD75C77453DDDBD7EA92F88D9F2CE57583F3D
  - Size: 450773 bytes

### Exact next step

1. Install the rebuilt ZIP on the target Joomla stand.
2. Open component config and verify the shared `link_types` title inputs for built-in rows contain the `COM_SWJPROJECTS_URLS_*` constants.
3. Open maintainer and project edit forms and confirm both selectors still show localized labels from the same shared registry.

## 2026-07-08 Package Whats-New Maintainer Addendum

- The package after-update whats-new text now matches `v.2.7.0` and explicitly mentions the maintainer entity in both locales.
- Rebuilt package:
  - E:\dev\SWJProjects-release-2.7.0\.packages\SW JProjects_2.7.0.zip
  - SHA256: 5132C65DA4E5926CB212D21728ADC92E04925958E20BC418B439AA9C2B5D976B
  - Size: 450937 bytes
