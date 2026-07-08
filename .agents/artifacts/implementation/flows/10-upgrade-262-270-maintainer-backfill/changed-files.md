# Changed Files

## Main Worktree

- `com_swjprojects/admin/src/Helper/ProjectLinksHelper.php`
- `com_swjprojects/admin/src/Field/ProjectlinktypesField.php`
- `com_swjprojects/admin/config.xml`
- `com_swjprojects/admin/forms/project.xml`
- `com_swjprojects/admin/src/Model/ProjectModel.php`
- `com_swjprojects/admin/language/en-GB/com_swjprojects.ini`
- `com_swjprojects/admin/language/ru-RU/com_swjprojects.ini`
- `com_swjprojects/script.php`
- `com_swjprojects/api/src/Model/ProjectsModel.php`
- `com_swjprojects/site/src/Model/DocumentationModel.php`
- `com_swjprojects/site/src/Model/DocumentModel.php`
- `com_swjprojects/site/src/Model/ProjectModel.php`
- `com_swjprojects/site/src/Model/ProjectsModel.php`
- `com_swjprojects/site/src/Model/VersionModel.php`
- `com_swjprojects/site/src/Model/VersionsModel.php`
- `com_swjprojects/layouts/project/urls.php`
- `com_swjprojects/layouts/project/urls/documentation.php`

## Release 2.7.0 Worktree

- Mirrored the same component files except `com_swjprojects/api/src/Model/ProjectsModel.php`.
- The release worktree does not contain `com_swjprojects/api`.
- During browser assurance on `web-tolk.local`, `com_swjprojects/admin/src/Helper/ProjectLinksHelper.php` was updated again in both worktrees to fix typed-link corruption on admin save for associative subform payloads (`urls0`, `urls1`, ...), then the release package was rebuilt.

## Flow Artifacts

- `.agents/artifacts/domain/flows/10-upgrade-262-270-maintainer-backfill/decision-log.md`
- `.agents/artifacts/architecture/flows/10-upgrade-262-270-maintainer-backfill/architecture.md`
- `.agents/artifacts/architecture/flows/10-upgrade-262-270-maintainer-backfill/implementation-plan.md`
- `.agents/artifacts/implementation/flows/10-upgrade-262-270-maintainer-backfill/changed-files.md`
- `.agents/artifacts/implementation/flows/10-upgrade-262-270-maintainer-backfill/change-summary.md`
- `.agents/artifacts/assurance/flows/10-upgrade-262-270-maintainer-backfill/review-findings.md`
- `.agents/artifacts/assurance/flows/10-upgrade-262-270-maintainer-backfill/test-plan.md`
- `.agents/artifacts/assurance/flows/10-upgrade-262-270-maintainer-backfill/test-cases.md`
- `.agents/artifacts/assurance/flows/10-upgrade-262-270-maintainer-backfill/browser-verification-report.md`

## 2026-07-08 Link Types Hotfix

### Main Worktree

- com_swjprojects/admin/config.xml
- com_swjprojects/admin/src/Helper/MaintainerLinksHelper.php
- com_swjprojects/admin/src/Helper/ProjectLinksHelper.php
- com_swjprojects/script.php
- com_swjprojects/admin/language/en-GB/com_swjprojects.ini
- com_swjprojects/admin/language/ru-RU/com_swjprojects.ini

### Release 2.7.0 Worktree

- Mirrored the same six component files into E:\dev\SWJProjects-release-2.7.0\com_swjprojects before rebuilding the package.

## 2026-07-08 Link-Type Title Language Keys Hotfix

### Main Worktree

- com_swjprojects/admin/src/Helper/MaintainerLinksHelper.php
- com_swjprojects/admin/src/Helper/ProjectLinksHelper.php
- com_swjprojects/admin/src/Field/MaintainerlinktypesField.php
- com_swjprojects/admin/src/Field/ProjectlinktypesField.php
- com_swjprojects/script.php

### Release 2.7.0 Worktree

- Mirrored the same five component files into E:\dev\SWJProjects-release-2.7.0\com_swjprojects before rebuilding the package.

## 2026-07-08 Package Whats-New Maintainer Addendum

### Main Worktree

- language/en-GB/pkg_swjprojects.sys.ini
- language/ru-RU/pkg_swjprojects.sys.ini

### Release 2.7.0 Worktree

- Mirrored the same two package language files into E:\dev\SWJProjects-release-2.7.0\language before rebuilding the package.
