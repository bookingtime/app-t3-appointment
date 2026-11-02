# CLAUDE.md

@../bokonetlocaldev/docs/projekte/app-t3-appointment.md

Project context (maintainers, release accounts, status, open points) lives in the centrally imported project
profile; this file contains only the technical part of the extension. **This repository is public** — keep
customer data, internal hostnames, ticket contents and personal names out of code, docs and commits.

## What this is

TYPO3 extension **`bt_appointment`** ("bookingtime appointments", Composer `bookingtime/app-t3-appointment`,
PSR-4 `Bookingtime\Appointment\` → `Classes/`). It embeds bookingtime's online booking module into a TYPO3
site: an Extbase backend module (steps 1–3) registers the site owner with bookingtime or connects an existing
booking page via the bookingtime API (`bookingtime/lib-phpsdk-app`), manages booking URLs
(`tx_appointment_domain_model_bookingtimepageurl`), and a frontend content element (CType) renders the
booking iframe for the URL chosen in its FlexForm.

One code base supports **TYPO3 11.5 through 14** in Composer mode and classic mode. Sibling plugins with the
same concept: `app-wp-appointment` (WordPress) and `app-joomla-appointment` (Joomla).

## Layout

- `Classes/Controller/AppointmentController.php` — backend module actions (`step1..3`, `list`, `add`,
  `create`, `edit`, `update`, `delete`, `preview`) and frontend plugin; version-agnostic helpers
  `moduleResponse()` (`renderResponse` exists only from v12) and `severity()` (enum from v12, constants in v11).
- `Classes/Service/BookingtimeService.php` — SDK access; built lazily via `getSdk()`, API calls only where
  needed (step 2), never in the constructor.
- `Classes/Domain/{Model,Repository}/Bookingtimepageurl*.php` — the booking URL record.
- `Classes/Updates/ListTypeToCTypeUpdate.php` — upgrade wizard migrating old `list_type` plugins to the
  CType; keeps `getIdentifier()` because v11 requires it.
- `Configuration/Backend/Modules.php` — module registration for v12+; `ext_tables.php` registers the module
  classically for v11 only (guarded by version check).
- `Configuration/TCA/Overrides/tt_content.php` — CType registration via `ExtensionUtility::registerPlugin`
  (with a v11 fallback for the plugin signature), `Configuration/FlexForms/FlexformAppointment.xml`,
  `Configuration/TypoScript/` (static template "Appointment"), `Configuration/Services.yaml`.
- `Resources/Private/{Templates,Partials,Layouts,Language}`, `Resources/Public/Icons`.
- `ext_emconf.php` — version, constraints (`typo3 11.5.0-14.99.99`, `php 8.1.0-8.5.99`) and the classic-mode
  `classmap`.
- `Documentation/` — admin documentation with screenshots, FAQ, changelog.

## Commands

```bash
composer install                   # vendor/ for classic mode (gitignored); needs access to the SDK repo
composer update bookingtime/lib-phpsdk-app
# in a TYPO3 instance (classic mode, per version):
TYPO3_PATH_ROOT=/path/to/site php typo3/sysext/core/bin/typo3 dumpautoload
vendor/bin/typo3 extension:setup && vendor/bin/typo3 upgrade:run     # v12+ (v14: upgrade:run replaces database:updateschema)
```

There is no test suite. Verify in local TYPO3 11.5, 12.4, 13.4 and 14 instances: extension setup, TCA load,
backend module steps 1–3, frontend plugin, upgrade wizard.

## Conventions

- **Classic-mode `classmap` in `ext_emconf.php` lists individual vendor directories on purpose**
  (`Classes`, `vendor/bookingtime`, `vendor/kriswallsmith`, `vendor/nyholm`, `vendor/php-http`, `vendor/psr`).
  Never add `vendor/symfony`: its `options-resolver` would shadow the core's version and break the TYPO3 13
  backend login.
- Version scheme: major = highest supported TYPO3 major (`14.x.y`); bump `ext_emconf.php` and the changelog
  in `Documentation/Changelog/Readme.md`, tag `v<version>`. A classic-mode release ZIP is built with
  `vendor/` included (do not commit the ZIP).
- Keep v11 compatibility shims (`ext_tables.php` module registration, `getIdentifier()`, TypoScript
  `module.tx_appointment.view` paths) as long as the constraint includes 11.5.
- Use only APIs available in 12.4, 13.4 and 14 (`ModuleTemplate::renderResponse`,
  `ContextualFeedbackSeverity`, `LocalizationUtility`, CType plugins — no `list_type`, no `addPlugin`).
- Commit messages: `typ: beschreibung` (`fix`, `feat`, `docs`, `release`, `chore`).
