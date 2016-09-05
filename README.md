# CommitHOOKs

[![Gitter](https://badges.gitter.im/Join%20Chat.svg)](https://gitter.im/andkirby/commithook?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)

The main purpose of this project is
 - checking coding standards before commit (with using PHP CodeSniffer, JSHint, or any lint tool)
 - filling up commit messages automatically to proper format ([details](doc/commit-msg.md))

Supprted file types: php, phtml, js, coffee, css, scss, xml, sh, scss, css, json, less, html, htm.

## The simplest commit message you could ever make
```
$ git add myfile.php
$ git commit -m 'Added my file.'
```
Last command will generate commit message:
```
Implemented #33: Make smth good now
 - Added my file.
```
See more details [here](doc/commit-msg.md).

## Installation
### Latest release is `v2.0.0-beta.32`
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
- [Ignore validation](doc/exclude-code-validation.md)
- [Protect code](doc/protect-code.md)
- [Commit message format](doc/commit-msg.md)
- [Ignore commit message validation](doc/commit-msg-ignore.md)
- [Active task in commit message](doc/active-task.md)
- [Auto-explode commit message into the list.](doc/config-message.md)

## Problems
#### Code validators doesn't work with GIT cache
The code validation with GIT but it doesn't support git cache. It means if you added a file (`git add file.php`), changed it, and trying to commit (without adding it into GIT cache). In commit, of course, you will get code from cache but validators will validate your real file. ([#113](../../issues/113))
#### Minor bugs in short commit message
[#92](../../issues/92), [#91](../../issues/91), [#32](../../issues/32)

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
