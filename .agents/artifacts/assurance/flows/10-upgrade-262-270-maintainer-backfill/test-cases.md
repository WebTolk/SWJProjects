# Test Cases

## Executed Cases

- `php -l` passed for main-worktree changed PHP files:
  - `ProjectLinksHelper.php`
  - `ProjectlinktypesField.php`
  - admin `ProjectModel.php`
  - component `script.php`
  - touched site models
  - touched layouts
  - API `ProjectsModel.php`
- XML parse passed for main `admin/config.xml` and `admin/forms/project.xml`.
- Helper idempotency check passed:
  - old map JSON converted to typed list JSON;
  - second conversion returned the same typed list JSON.
- Mirrored files into `E:\dev\SWJProjects-release-2.7.0`.
- `php -l` passed for release-worktree changed PHP files.
- XML parse passed for release `admin/config.xml` and `admin/forms/project.xml`.
- Rebuilt `E:\dev\SWJProjects-release-2.7.0\.packages\SW JProjects_2.7.0.zip`.
- Archive listing confirmed:
  - `com_swjprojects/admin/src/Helper/ProjectLinksHelper.php`;
  - `com_swjprojects/admin/src/Field/ProjectlinktypesField.php`;
  - `com_swjprojects/admin/config.xml`;
  - `com_swjprojects/admin/forms/project.xml`;
  - `com_swjprojects/script.php`.
- Archive content checks confirmed:
  - `script.php` imports `ProjectLinksHelper`;
  - `script.php` contains `checkProjectLinkTypes()` and `migrateProjectLinks()`;
  - `admin/config.xml` contains `project_link_types`;
  - `admin/forms/project.xml` contains `projectlinktypes`;
  - `ProjectLinksHelper.php` contains `normalizeLinks()` and `toJson()`.
- `com_swjprojects/script.php` `update()` now reads installed component version from `#__extensions.manifest_cache`:
  - if version is missing or `< 2.7.0`, `checkProjectLinkTypes()` and `migrateProjectLinks()` are executed there.
  - `postflight()` no longer performs `checkProjectLinkTypes()`/`migrateProjectLinks()` directly.
- Runtime DB migration checks on `web-tolk.local` production-copy DB (user pre-filled project `id=5` links) were executed first via direct DB script using `ProjectLinksHelper::toJson()`:
  - before: `com_swjprojects` and `pkg_swjprojects` versions were `2.6.2`;
  - `project_link_types` missing before update check;
  - `yumwf_swjprojects_projects.urls` for `id=5` was legacy map:
    - `{"demo":"https://demo.ru","support":"https://support.ru","github":"https://github.com/sergeytolkachov/JoomShopping-Russian-Post-Shipping-method-via-API","jed":"https://extensions.joomla.org/webtolk/","donate":"https://donate.ru","documentation":"https://docs.ru"}`
  - migration pass applied:
    - `project_link_types` written into component params with six default types;
    - `urls` converted to typed array JSON for 110 projects with non-empty links;
    - `id=5` became:
      - `[{"type":"demo","title":"","value":"https://demo.ru"},{"type":"support","title":"","value":"https://support.ru"},{"type":"github","title":"","value":"https://github.com/sergeytolkachov/JoomShopping-Russian-Post-Shipping-method-via-API"},{"type":"jed","title":"","value":"https://extensions.joomla.org/webtolk/"},{"type":"donate","title":"","value":"https://donate.ru"},{"type":"documentation","title":"","value":"https://docs.ru"}]`
      - second pass returned idempotent result (`IDEMPOTENT_RECHECK=0`).
  - current data shape after migration:
    - `projects` total: `110`
    - `urls` typed JSON rows: `101`
    - `urls` legacy map rows: `0`.
- CLI installer probe on `web-tolk.local`:
  - `php cli/joomla.php extension:install --path="...SW JProjects_2.7.0.zip"` returns `[ERROR] Unable to install extension`.
  - `Joomla\\CMS\\Installer\\InstallerHelper::unpack(..., true)` returns `extractdir=NULL` and `type=false`.
  - standalone PHP in this environment cannot create temp directories even outside Joomla (`mkdir(): Permission denied`), so CLI install is not a valid runtime proxy here.
- Real browser update on `web-tolk.local`:
  - logged into `https://web-tolk.local/administrator/` as `webtolkai`;
  - elevated `webtolkai` to `Super Users` because installer access was initially `403` under plain `Administrator`;
  - opened `index.php?option=com_installer&view=install`;
  - uploaded rebuilt `E:\dev\SWJProjects-release-2.7.0\.packages\SW JProjects_2.7.0.zip`;
  - Joomla reported successful extension update and showed `v.2.7.0`.
- Runtime version/params checks after browser update on `web-tolk.local`:
  - `com_swjprojects` manifest version is `2.7.0`;
  - `pkg_swjprojects` manifest version is `2.7.0`;
  - `project_link_types` is present in component params with six default types (`demo`, `support`, `github`, `jed`, `donate`, `documentation`).
- Runtime project-link data checks after browser update on `web-tolk.local`:
  - project `id=5` `urls` is typed JSON with all six user-prepared links;
  - `typed_rows=101`;
  - `legacy_rows=0`;
  - helper idempotency recheck reports `idempotent_remaining_changes=0`.
- Browser admin-form verification before fix:
  - opened `index.php?option=com_swjprojects&view=project&layout=edit&id=5`;
  - confirmed the `Ссылки` tab showed all six expected links with correct typed rows;
  - clicked `Save` with no edits;
  - DB `urls` for `id=5` became corrupted typed rows like `[{"type":"urls0","title":"","value":"Array"}, ...]`.
- Root-cause fix:
  - `ProjectLinksHelper::normalizeLinks()` now treats associative posted subform row arrays as typed rows before treating associative input as legacy `type => value` map.
  - `php -l` passed for `com_swjprojects/admin/src/Helper/ProjectLinksHelper.php` in both main and release worktrees.
  - release package was rebuilt and reinstalled through browser.
- Browser admin-form verification after fix:
  - restored correct typed JSON for project `id=5`;
  - reopened the project edit form and confirmed all six links before save;
  - clicked `Save` with no edits;
  - DB `urls` for `id=5` remained correct typed JSON after save;
  - reopening/reading the form again showed all six link rows intact.
- Frontend rendering verification after fix:
  - followed the built-in site preview from the admin project page;
  - opened the real frontend project URL for `id=5`;
  - confirmed rendered public buttons/links for `Документация`, `Демо`, `Поддержка`, `GitHub`, `JED`, and `Поддержать` with the expected migrated URLs.

## Pending Cases

- Optional repeat on a second production-like stand if extra delivery confidence is required beyond `web-tolk.local`.

## Failures

- Copying `com_swjprojects/api/src/Model/ProjectsModel.php` to the release worktree failed because `E:\dev\SWJProjects-release-2.7.0\com_swjprojects\api` does not exist. This is recorded as an intentional non-mirror after structure verification.

## 2026-07-07 Drift Recovery

- `git diff --no-index` confirmed the current repo `com_swjprojects/script.php` already had gated `update()` migration logic, while `E:\dev\SWJProjects-release-2.7.0\com_swjprojects\script.php` still had unconditional `postflight()` calls for `checkProjectLinkTypes()` and `migrateProjectLinks()`.
- The release worktree component installer script was resynchronized from the current repo.
- `php E:\.agents\tools\phing-packager\bin\packager.php package --config=E:\dev\SWJProjects\.agents\build\package.config.json --root=E:\dev\SWJProjects-release-2.7.0 --output-dir=E:\dev\SWJProjects-release-2.7.0\.packages --version=2.7.0` rebuilt the release ZIP successfully.
- Rebuilt ZIP metadata: size `448015` bytes, SHA256 `1195A413318BA597874BBD729C40CB49718CB4047102D8C3A04C9C435042414C`.
- Direct ZIP entry inspection confirmed `com_swjprojects/script.php` contains `getInstalledComponentVersion()` and `version_compare($installedVersion, '2.7.0', '<')`, while the rebuilt `postflight()` no longer carries direct project-link migration markers.
- `php cli/joomla.php extension:install --path E:\dev\SWJProjects-release-2.7.0\.packages\SW JProjects_2.7.0.zip` on `E:\OSPanel\home\web-tolk.local\public` returned `[OK] Extension installed successfully.`.
- Installed `E:\OSPanel\home\web-tolk.local\public\administrator\components\com_swjprojects\script.php` now contains the guarded `version_compare($installedVersion, '2.7.0', '<')` branch.

## Pending Cases Addendum

- If a fresh proof of the `< 2.7.0 -> 2.7.0` gate is required after this resync, reset one local copy back to a real pre-`2.7.0` state and rerun the update path there; the current `web-tolk.local` manifest already reports `2.7.0`.

## 2026-07-07 Clean Rerun After Restore

- Restored files from `E:\OSPanel\home\web-tolk.local\restore-work-2026-07-07-restore-1535\web-tolk.ru_2026-07-06_09-53.tar.gz` and DB from the matching SQL snapshot; local config was restored from `configuration.local.php`.
- Pre-update verification on the restored stand confirmed `com_swjprojects=2.6.2`, `pkg_swjprojects=2.6.2`, and project `id=5` back on legacy JSON (`{"github":"https://github.com/sergeytolkachyov/JoomShopping-Russian-Post-Shipping-method-via-API"}`).
- First clean rerun against the earlier guarded package exposed a real bug: update reached `2.7.0`, but `project_link_types` stayed `null`, `typed_rows=0`, `legacy_rows=101`, and `project id=5` stayed legacy.
- Joomla core inspection on `E:\.agents\docs\Joomla-core\6.x\6.1.0\libraries\src\Installer\InstallerAdapter.php` showed `storeExtension()` runs before installer `update()`, so reading the old version from `#__extensions.manifest_cache` inside `update()` is too late.
- Fixed installer strategy: capture the installed component version during `preflight('update', ...)` from the live manifest file and reuse that captured value in `update()`.
- Rebuilt package: `E:\dev\SWJProjects-release-2.7.0\.packages\SW JProjects_2.7.0.zip` SHA256 `EFC5CB075F0789A41196D2932B06315B79187BE2EAC46599E2A8C06075861372`.
- Re-restored the archived `2.6.2` stand and reran `php cli\joomla.php extension:install --path E:\dev\SWJProjects-release-2.7.0\.packages\SW JProjects_2.7.0.zip`.
- Final result on the clean rerun:
  - `com_swjprojects=2.7.0`;
  - `pkg_swjprojects=2.7.0`;
  - `project_link_types` seeded with six default typed link descriptors;
  - project `id=5` migrated to typed row JSON;
  - `typed_rows=101`;
  - `legacy_rows=0`;
  - installed local `administrator/components/com_swjprojects/script.php` contains the `installedComponentVersionBeforeUpdate` / `readInstalledComponentVersionFromFilesystem()` capture path.

## 2026-07-08 One-List Hotfix

### Executed Cases

- php -l passed for main-worktree changed PHP files:
  - com_swjprojects/admin/src/Helper/MaintainerLinksHelper.php
  - com_swjprojects/admin/src/Helper/ProjectLinksHelper.php
  - com_swjprojects/script.php
- php -l passed for the mirrored release-worktree copies of the same files.
- Mirrored the config, helper, installer, and language files into E:\dev\SWJProjects-release-2.7.0.
- Rebuilt SW JProjects_2.7.0.zip successfully.
- Rebuilt ZIP metadata:
  - size 449604 bytes
  - SHA256 705DF9CB03386E532CCF11E3EDEB28A5D4E39C73BCBFAE47D4499545C1223175

### Pending Cases

- Install the rebuilt package on a Joomla stand and verify the component config shows one shared link-types fieldset instead of two separate lists.
- Verify legacy stored params are collapsed into link_types after install/update and both maintainer/project subforms still resolve their options.


## 2026-07-08 Language-Key Titles Hotfix

### Executed Cases

- php -l passed for main-worktree files:
  - com_swjprojects/admin/src/Helper/MaintainerLinksHelper.php
  - com_swjprojects/admin/src/Helper/ProjectLinksHelper.php
  - com_swjprojects/admin/src/Field/MaintainerlinktypesField.php
  - com_swjprojects/admin/src/Field/ProjectlinktypesField.php
  - com_swjprojects/script.php
- Confirmed helper defaults now use `COM_SWJPROJECTS_URLS_*` values directly in shared link-type descriptors.
- Confirmed both link-type field classes render option labels through `Text::_($type['title'])`.
- Confirmed installer `checkLinkTypes()` rewrites existing shared `link_types` rows when normalization changes built-in titles.
- Mirrored the changed files into E:\dev\SWJProjects-release-2.7.0.
- php -l passed for the mirrored release-worktree copies of the same five files.
- Rebuilt `SW JProjects_2.7.0.zip` successfully.
- Rebuilt ZIP metadata:
  - size 450773 bytes
  - SHA256 47363106F942E3F1DBF29FDF741CD75C77453DDDBD7EA92F88D9F2CE57583F3D

### Pending Cases

- Install the rebuilt package on a Joomla stand and verify the component config `title` inputs now contain the `COM_SWJPROJECTS_URLS_*` constants for built-in rows.
- Verify maintainer and project edit forms still show localized option labels while reading the shared stored constants.

## 2026-07-08 Package Whats-New Maintainer Addendum

### Executed Cases

- Updated `PKG_SWJPROJECTS_WHATS_NEW` in both package locales to the `v.2.7.0` text.
- Mirrored both package language files into E:\dev\SWJProjects-release-2.7.0.
- Rebuilt `SW JProjects_2.7.0.zip` successfully.
- Extracted `language/en-GB/pkg_swjprojects.sys.ini` from the rebuilt ZIP and confirmed the maintainer-entity whats-new text is present.
- Extracted `language/ru-RU/pkg_swjprojects.sys.ini` from the rebuilt ZIP and confirmed the maintainer-entity whats-new text is present.
- Rebuilt ZIP metadata:
  - size 450937 bytes
  - SHA256 5132C65DA4E5926CB212D21728ADC92E04925958E20BC418B439AA9C2B5D976B
