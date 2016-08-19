# CommitHOOKs

[![Gitter](https://badges.gitter.im/Join%20Chat.svg)](https://gitter.im/andkirby/commithook?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)

The main purpose of this project is
 - checking coding standards before commit (with using PHP CodeSniffer)
 - filling up commit messages automatically to proper format ([details](doc/commit-msg.md))

## Installation
### Latest release is `v2.0.0-beta.25`
### Install globally
To get last beta version please define your `minimum-stability`.
```
{
  "minimum-stability": "beta",
  "prefer-stable": true
}
```

Due to reason the package requires one package which still in "dev", please fetch it first:
```shell
$ composer global require chobie/jira-api-restclient ^2.0@dev
```

Now fetch the package:
```shell
$ composer global require andkirby/commithook ~2.0@beta
```

#### Sample `.bashrc`
Here is [an example of `.bashrc` file](doc/example-bashrc.md) for global using.

### Separate installation
If global way doesn't work you may use [installation via `create-project`](doc/install-create-project.md).

## Main documentation
- [Configuration Wizard](doc/example-quick-wizard.md)
- [GIT integration: hook files installation](doc/hooks-installation.md)
- [Commit message format](doc/commit-msg.md)
- [Ignore validation](doc/exclude-code-validation.md)
- [Protect code](doc/protect-code.md)

## Tips & tricks
### Redundant gaps in code
You may quickly find gaps/trailing spaces in your code by the regular expression:
```
(\n\s*\n\s*\})|(\n\s*\n\s*\n)|(\{\n\s*\n)| +\n
```
Just use it in your IDE.

## OS environment
Tested on Windows in GIT Bash v2.9.

Feel free [to create](../../issues/new "Add a new issue") your faced issue.

## [Release notes](doc/release-notes.md)
