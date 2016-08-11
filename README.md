# CommitHOOKs

[![Gitter](https://badges.gitter.im/Join%20Chat.svg)](https://gitter.im/andkirby/commithook?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)
The main purpose of this project is checking coding standards.

[![Travis CI](https://travis-ci.org/andkirby/commithook.svg?branch=develop)](https://travis-ci.org/andkirby/commithook)
Travis Continuous Integration status.

Please see more information on [Wiki pages](../../wiki).

[Installation Wizard](doc/example-wizard.md)

## Release notes
- Last beta: v2.0.0-beta.16
- v2.x-dev
    - ([#83](/../../issues/83)) Added command `validator:disable` for quick disable a validator.
    - ([#13](/../../issues/13)) Added an integration with GitHub (for short commit message).
    - Added an ability to test project files before commit via `commithook test` in GIT directory.
    - Refactored validation commit message.
    - Added an ability to customize the commit message format.
    - Added supporting automatic commit messages made by GitFlow process from SourceTree (it starts with word 'Finish').
    - Added an ability to validate issue status by white list.
- v1.8.0 Improved recognizing short commit message format.
- v1.7.0b JIRA integration. Integrated short commit message format. Removed blocking by the code validation for multi-line 'if' conditions in PHTML.
- v1.6.9 Fixed generating paths on install hook files into a project.
- v1.6.8 Improved skipping methods name validation. Added new tag `@skipCommitHookMethodNaming`.
- v1.6.7 Added supporting numbers in the issue project key in commit message.
- v1.6.6 Added PHP version of bin file (you may run all commands via `php commithook.php`). Added extra "complete" messages on "verbose" mode to the "remove" command.
- v1.6.5 Added new options command `--php-binary|-b` and `--project-dir|-d`. Improved PHP file validator.
- v1.6.4 Pushed tests to use PSR-4 autoload standard and to namespaces usage. Pushed code to use `bin/runner.php` file. `LibHooks/runner.php` is deprecated. Composer package require at least PHP 5.3.x version.
- v1.6.3 Improved installer. Added CommitHook files remover.
- v1.6.2 (alpha) Implemented application console usage.
- v1.6.1 Fixed dialog message.
- v1.6.0 Added CommitHook files installer.
- v1.5.1 Added composer supporting.
- v1.5.0 Implemented layered configuration loading.
- v1.4.3 Minor fix in the check trailing spaces.
- v1.4.2 [CRITICAL] Fixed running validators from pre-commit processor.
- v1.4.1 Added tests for trailing spaces and trailing line validator. Improved trailing spaces validation.
- v1.4.0 Added checking GIT conflict lines to prevent commits conflicted files.
- v1.3.0 Added checking empty type in PHPDoc tags @param @var
- v1.2.4 Added close symbol on split content. Added checking slashed slash (\\) on split content.
- v1.2.3 Fixed case when operator name used in variable.
- v1.2.2 Bugfix for case:
`<?php if ($i != $recommendationsCount-1) echo ","?>`
