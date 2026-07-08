# SW JProjects 2.7.0 Package Build - 2026-07-05

## Scope

Build the installable SW JProjects `2.7.0` ZIP from the clean release worktree.

## Inputs

- Build config: `E:\dev\SWJProjects\.agents\build\package.config.json`
- Build root: `E:\dev\SWJProjects-release-2.7.0`
- Output directory: `E:\dev\SWJProjects-release-2.7.0\.packages`
- Version override: `2.7.0`

## Command

```powershell
php E:\.agents\tools\phing-packager\bin\packager.php package --config=E:\dev\SWJProjects\.agents\build\package.config.json --root=E:\dev\SWJProjects-release-2.7.0 --output-dir=E:\dev\SWJProjects-release-2.7.0\.packages --version=2.7.0
```

## Output

- Package: `E:\dev\SWJProjects-release-2.7.0\.packages\SW JProjects_2.7.0.zip`
- Size: `443762` bytes
- SHA256: `8CC4749448EA05503E4D62C6F988D5E6715C88512274A9D6E5FB3AC4A10D1E79`
- Last write time: `2026-07-05 13:05:03 +04:00`

## Verification

Archive manifest check:

- `pkg_swjprojects.xml` contains `<version>2.7.0</version>`
- `pkg_swjprojects.xml` contains `<packagename>swjprojects</packagename>`
- `pkg_swjprojects.xml` contains `<scriptfile>script.php</scriptfile>`
- `plg_system_swjprojects/script.php` no longer contains plugin-local Joomla/PHP compatibility checks; compatibility remains package-installer responsibility.
- `plg_system_swjprojects/script.php` still contains `enablePlugin()` and calls it from `install()` and `update()`.
- `com_swjprojects/swjprojects.xml` contains a single top-level `<menu>COM_SWJPROJECTS</menu>`.
- `com_swjprojects/swjprojects.xml` does not contain `<submenu>` entries.
- `plg_system_swjprojects/swjprojects.xml` contains `administrator_menu_placement` with default `components`.
- `plg_system_swjprojects/src/Extension/Swjprojects.php` loads submenu children through `MenusHelper::loadPreset('swjprojects', false)`.
- `plg_system_swjprojects/src/Extension/Swjprojects.php` uses `PreprocessMenuItemsEvent`, `getContext()`, `getItems()`, and `updateItems()` instead of positional `getArgument(0/1)` access.
- The top-level administrator menu remains available only through the plugin parameter value `top`.

Required files found in the ZIP:

- `pkg_swjprojects.xml`
- `script.php`
- `com_swjprojects/swjprojects.xml`
- `com_swjprojects/admin/forms/maintainer.xml`
- `com_swjprojects/admin/forms/filter_maintainers.xml`
- `com_swjprojects/admin/presets/swjprojects.xml`
- `com_swjprojects/admin/sql/updates/mysql/2.6.2.sql`
- `plg_system_swjprojects/swjprojects.xml`

Excluded technical/project files were not found:

- `.agents/`
- `.git/`
- `.serena/`
- `.playwright-mcp/`
- `phing.xml`
- `ruleset.xml`
- `eslint.config.mjs`
- `.php-cs-fixer.dist.php`
- `.stylelintrc.json`
- `.editorconfig`

## Result

Package build and archive-surface verification passed.

## Rebuild Note

The package was rebuilt at `2026-07-05T10:23:44.8758097+04:00` after removing the system plugin installer script compatibility checks from both:

- `E:\dev\SWJProjects\plg_system_swjprojects\script.php`
- `E:\dev\SWJProjects-release-2.7.0\plg_system_swjprojects\script.php`

The package was rebuilt again at `2026-07-05T10:54:39.6266515+04:00` after restoring the component manifest fallback menu without nested submenu entries in both:

- `E:\dev\SWJProjects\com_swjprojects\swjprojects.xml`
- `E:\dev\SWJProjects-release-2.7.0\com_swjprojects\swjprojects.xml`

The package was rebuilt again at `2026-07-05T12:20:32.5023030+04:00` after changing the system plugin menu placement behavior:

- default placement: standard Joomla `Components` menu item
- optional placement: separate top-level administrator menu group

The package was rebuilt again at `2026-07-05T13:04:23.4564137+04:00` after replacing the system plugin's positional menu event argument handling with Joomla's typed `PreprocessMenuItemsEvent` API.

The package was rebuilt again at `2026-07-05T13:56:13+04:00` after adding the SW JProjects administrator dashboard:

- `com_swjprojects/swjprojects.xml` now declares dashboard id `swjprojects`.
- `com_swjprojects/script.php` conditionally creates a `mod_submenu` dashboard module at `cpanel-swjprojects` from preset `swjprojects`.
- `com_swjprojects/admin/language/en-GB/com_swjprojects.sys.ini` and `ru-RU` contain `COM_SWJPROJECTS_DASHBOARD_SWJPROJECTS_TITLE`.

Updated package data:

- Size: `444097` bytes
- SHA256: `F6FF0DCB250E2508F8B6CFC8B8792878533A03FF4AD39852046FD937F46CF839`
- Install result: Joomla CLI returned `[OK] Extension installed successfully.`
- DB result: `mod_submenu` id `113`, position `cpanel-swjprojects`, preset `swjprojects`, published `1`.
- Browser result: authenticated Playwright snapshot `swjprojects-dashboard-2.7.0-auth-depth8.md` shows the SW JProjects dashboard links from preset.

## Residual Risk

The latest rebuild was installed into `swjprojects.local` and runtime/browser assurance passed. A local stand language-cache permission warning and HTTP COOP console warnings remain environment noise, not package blockers.

## Rebuild Note - Dashboard Components Menu Icon

The package was rebuilt again at `2026-07-05T15:05:12+04:00` after fixing the missing dashboard icon next to `SW JProjects` in the Joomla Components menu.

Changes:

- `com_swjprojects/swjprojects.xml` includes `<menu><params><dashboard>swjprojects</dashboard></params>COM_SWJPROJECTS</menu>`.
- `com_swjprojects/script.php` updates the installed administrator `#__menu` item params to include `dashboard=swjprojects` on install/update.
- `plg_system_swjprojects/src/Extension/Swjprojects.php` sets `$componentMenu->dashboard = 'swjprojects'` when adding preset children under the standard Components menu.
- `com_swjprojects/admin/presets/swjprojects.xml` root has `dashboard="swjprojects"` for optional top-level mode.

Updated package data:

- Size: `444353` bytes
- SHA256: `046C2D14E2F660C0EA2B4A4EC881EBD69C95101FA53597A07B04324A1884DE37`
- Joomla 5.4.6 update result: Joomla CLI returned `[OK] Extension installed successfully.`
- Joomla 5 DB result: `#__menu.params` for `index.php?option=com_swjprojects` is `{"dashboard":"swjprojects"}`.
- Joomla 5 browser result: `joomla5-components-expanded-dashboard-icon.md` shows `SW JProjects Dashboard` in the expanded Components menu.

## Rebuild Note - Installer Script Resync

The package was rebuilt again at `2026-07-07T15:02:00+04:00` after correcting a release-worktree drift in `com_swjprojects/script.php`.

Changes:

- The current repo `E:\dev\SWJProjects\com_swjprojects\script.php` already used guarded migration inside `update()` based on installed component version from `#__extensions.manifest_cache`.
- The release worktree `E:\dev\SWJProjects-release-2.7.0\com_swjprojects\script.php` still had the older unconditional `postflight()` calls for `checkProjectLinkTypes()` and `migrateProjectLinks()`.
- `E:\dev\SWJProjects-release-2.7.0\com_swjprojects\script.php` was resynchronized from the current repo before rebuilding.

Updated package data:

- Size: `448015` bytes
- SHA256: `1195A413318BA597874BBD729C40CB49718CB4047102D8C3A04C9C435042414C`
- ZIP verification: `com_swjprojects/script.php` contains `getInstalledComponentVersion()` and `version_compare($installedVersion, '2.7.0', '<')`.
- ZIP verification: `postflight()` no longer carries the direct project-link migration markers.
- Local copied-production reinstall result on `web-tolk.local`: Joomla CLI returned `[OK] Extension installed successfully.`
- Local installed-file verification: `E:\OSPanel\home\web-tolk.local\public\administrator\components\com_swjprojects\script.php` now matches the guarded installer variant.

## Rebuild Note - Update Gate Timing Fix

The package was rebuilt again at `2026-07-07T16:33:00+04:00` after fixing the installer version-gate timing bug in `com_swjprojects/script.php`.

Changes:

- A clean rerun from the archived local `2.6.2` stand showed that reading the old component version from `#__extensions.manifest_cache` inside installer `update()` is too late on the real Joomla update path.
- The component installer now captures the installed version during `preflight('update', ...)` from `administrator/components/com_swjprojects/swjprojects.xml` and reuses that captured value in `update()`.
- `E:\dev\SWJProjects-release-2.7.0\com_swjprojects\script.php` was resynchronized from the current repo before rebuilding.

Updated package data:

- Size: `448129` bytes
- SHA256: `EFC5CB075F0789A41196D2932B06315B79187BE2EAC46599E2A8C06075861372`
- Local clean rerun result on restored `web-tolk.local`: Joomla CLI returned `[OK] Extension installed successfully.`
- Local clean rerun verification: `project_link_types` seeded, `typed_rows=101`, `legacy_rows=0`, and project `id=5` migrated to typed rows.

## Rebuild Note - Installer Helper Decoupling (Pending local rerun)

The package was rebuilt again at `2026-07-07T21:24:43+04:00` after removing the installer-script dependency on the separate `ProjectLinksHelper` class for the project-link migration/default-type path.

Changes:

- `com_swjprojects/script.php` no longer imports `Joomla\Component\SWJProjects\Administrator\Helper\ProjectLinksHelper`.
- The installer now owns its local project-link default/migration helpers (`getDefaultProjectLinkTypes()`, `normalizeProjectLinksToJson()`, `normalizeProjectLinks()`, `normalizeProjectLink()`, `projectLinksToArray()`, `normalizeProjectLinkCode()`).
- `E:\dev\SWJProjects-release-2.7.0\com_swjprojects\script.php` was resynchronized from the current repo before rebuilding.

Updated package data:

- SHA256: `E08ECC324E6CA7FFBC77E16D27F7FDA0F41BD273F4019ABE29CB30FF0EC81F2C`
- Verification status: package rebuild completed, but the fresh local rerun is still pending because `web-tolk.local` restore is paused with the restored site nested under `public\web-tolk.ru`.
- Exact resume point: flatten the restored folder back into `public`, keep the local `configuration.php`, then rerun Joomla CLI update from this rebuilt ZIP.

## Rebuild Note - Shared Link Types Hotfix

The package was rebuilt again at 2026-07-08T20:04:53.2626670+04:00 after collapsing the duplicate component-config link-type lists into one shared link_types registry.

Changes:

- com_swjprojects/admin/config.xml now exposes one shared link_types subform instead of separate maintainer_link_types and project_link_types fieldsets.
- MaintainerLinksHelper is now the canonical source for shared link-type descriptors, with legacy fallback from the older param names.
- ProjectLinksHelper now reads its type descriptors from the same shared registry.
- com_swjprojects/script.php now merges legacy param values into link_types during install/update and removes the old keys.

Updated package data:

- Size: 449604 bytes
- SHA256: 705DF9CB03386E532CCF11E3EDEB28A5D4E39C73BCBFAE47D4499545C1223175
- Package: E:\dev\SWJProjects-release-2.7.0\.packages\SW JProjects_2.7.0.zip
- Runtime verification: pending stand install/config smoke for this hotfix slice.


## Rebuild Note - Shared Link-Type Title Language Keys

The package was rebuilt again at 2026-07-08T20:38:48.3233048+04:00 after normalizing built-in shared link-type titles to language constants.

Changes:

- `com_swjprojects/admin/src/Helper/MaintainerLinksHelper.php` now defines the built-in shared titles as `COM_SWJPROJECTS_URLS_*` keys and normalizes legacy built-in labels to those constants.
- `com_swjprojects/admin/src/Field/MaintainerlinktypesField.php` and `ProjectlinktypesField.php` now translate stored title keys with `Text::_()` when rendering selector options.
- `com_swjprojects/script.php` now rewrites already-saved built-in shared `link_types` titles to language-key form during install/update while keeping custom titles unchanged.
- `E:\dev\SWJProjects-release-2.7.0\com_swjprojects\...` was resynchronized from the current repo before rebuilding.

Updated package data:

- Size: `450773` bytes
- SHA256: `47363106F942E3F1DBF29FDF741CD75C77453DDDBD7EA92F88D9F2CE57583F3D`
- Package: `E:\dev\SWJProjects-release-2.7.0\.packages\SW JProjects_2.7.0.zip`
- Runtime verification: pending stand install/config smoke for this title-normalization slice.

## Rebuild Note - Package Whats-New Maintainer Text

The package was rebuilt again at 2026-07-08T21:34:17.9731894+04:00 after updating the package whats-new language constants.

Changes:

- `language/en-GB/pkg_swjprojects.sys.ini` now carries the current `v.2.7.0` whats-new text and explicitly mentions the maintainer entity.
- `language/ru-RU/pkg_swjprojects.sys.ini` now carries the matching Russian `v.2.7.0` whats-new text with the maintainer entity.
- `E:\dev\SWJProjects-release-2.7.0\language\...` was resynchronized from the current repo before rebuilding.

Updated package data:

- Size: `450937` bytes
- SHA256: `5132C65DA4E5926CB212D21728ADC92E04925958E20BC418B439AA9C2B5D976B`
- Package: `E:\dev\SWJProjects-release-2.7.0\.packages\SW JProjects_2.7.0.zip`
- ZIP verification: packaged English and Russian `PKG_SWJPROJECTS_WHATS_NEW` constants contain the maintainer-entity addendum.
