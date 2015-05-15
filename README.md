# PHP CommitHOOKs

[![Gitter](https://badges.gitter.im/Join%20Chat.svg)](https://gitter.im/andkirby/commithook?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)
The main purpose of this project is checking coding standards at first for PHP files.

[![Travis CI](https://travis-ci.org/andkirby/commithook.svg?branch=develop)](https://travis-ci.org/andkirby/commithook)
Travis Continuous Integration status.

## Installation

### Composer

To install Composer you may follow the link https://getcomposer.org/download/. Just go to your PHP directory and follow the instructions therefrom. ([Composer Intro](https://getcomposer.org/doc/00-intro.md))

Install composer package `andkirby/commithook` by command (you may put it into your global vendor directory):
```shell
$ composer global require andkirby/commithook
```
[More info about installation.](doc/composer-installation.md)

## [Configuring](doc/configuring.md)

## [Set up GIT hooks manually](doc/manual-setup.md)

## Features
### [Commit Message Validation](doc/commit-msg.md)

### [Ignoring Code Validators](doc/ignore-validator.md)

## Release notes
- v1.7.0b JIRA integration. (Removed blocking by the code validation for multiline 'if' conditions in PHTML)
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
