# Review Findings

## Flow

`10-upgrade-262-270-maintainer-backfill`

## Findings

- No open blocking findings remain in the delivered migration slice.
- During assurance, one blocking runtime regression was discovered and fixed before closure:
  - saving a migrated project in the administrator form could corrupt typed project links into rows like `type=urls0`, `value=Array`;
  - the fix is in `com_swjprojects/admin/src/Helper/ProjectLinksHelper.php` and is included in the rebuilt `2.7.0` package that was reinstalled on `web-tolk.local`.

## Checks Performed

- Verified the migration does not create a maintainer.
- Verified project links remain stored in `#__swjprojects_projects.urls`.
- Verified project links are migrated to typed-list JSON on real `web-tolk.local` DB data.
- Verified real browser-based Joomla update of the rebuilt `2.7.0` package on `web-tolk.local`.
- Verified migrated links survive administrator save on project `id=5` after the helper fix.
- Verified frontend project-page link rendering on the copied production site after the fix.
- Verified release worktree does not contain `com_swjprojects/api`; API changes were not mirrored there to avoid adding a new release surface.

## Residual Risks

- Standalone CLI installer remains unreliable in this machine context because PHP cannot create temp directories for unpacking, so CLI install should not be treated as representative evidence for Joomla web updates on this host.
- Assurance is based on one copied production stand (`web-tolk.local`) with real data, including project `id=5`; if broader rollout confidence is needed, the same browser scenario can be repeated on one more production-like copy.
- Flow 09 remains independently open for changelog feed assurance and was not closed by Flow 10 package work.

## 2026-07-07 Follow-up

- A non-code drift was found between the current repo, the `2.7.0` release worktree, and the installed `web-tolk.local` component: the repo already had the installer migration guard in `com_swjprojects/script.php`, but the release worktree and installed local copy still used the older unconditional `postflight()` migration variant.
- Resynchronizing that one file, rebuilding `SW JProjects_2.7.0.zip`, and reinstalling it locally removed the drift before any further release reuse.

## 2026-07-07 Residual Risk Addendum

- The local reinstall verified that the installed `2.7.0` package now carries the guarded installer script, but it did not replay a fresh `< 2.7.0 -> 2.7.0` migration because this copied stand already reported `2.7.0` before reinstall.

## 2026-07-07 Additional Finding

- The earlier guarded migration variant was still incorrect on the real Joomla update path: by the time installer `update()` executed, Joomla had already rewritten `#__extensions.manifest_cache` to the incoming `2.7.0` version, so the `< 2.7.0` gate never opened on a clean restored `2.6.2` stand.
- This was corrected by capturing the installed version during `preflight('update', ...)` from the live installed administrator manifest file and using that captured value in `update()`.
- After rebuilding and rerunning on a freshly restored archived `2.6.2` local stand, the update path passed with seeded `project_link_types` and migrated typed project links.
