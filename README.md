# CmHook

CmHook is GIT commit hook the code validator for PHP (Code Sniffer), CSS (base only), JS (JsHint) code and commit message formatter/filler with the issue tracker integration (JIRA, GitHub).

[![Gitter](https://badges.gitter.im/Join%20Chat.svg)](https://gitter.im/andkirby/commithook?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)

The main purpose of this project is
 - checking coding standards before commit (with using PHP CodeSniffer, JSHint, or any lint tool)
 - filling up commit messages automatically to proper format ([details](docs/commit-msg.md))

Supprted file types: php, phtml, js, coffee, css, scss, xml, sh, scss, css, json, less, html, htm.

## The simplest commit message you could ever make
Target commit message:
```
Implemented #33: Make smth good now
 - Added my file.
```
This commit message will be generated automatically, we need to type only commit description:
```
$ git add myfile.php
$ git commit -m 'Added my file.'
```
See more details about [short commit messages](docs/commit-msg.md).

## Installation
### Latest release is `v2.0.0-beta.42`
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

### Enable hooks in a project (with predefined configuration)
If a project already have base configuration files all what you need to:
- define interpreters (PHP for PHPCS, Ruby for Rubocop, NodeJs for JSHint)
  - PHP example:
    ```shell
    $ commithook config --xpath code/interpreter/php d:/s/php-7.0.14/php.exe --project-self
    ```
    (You may try to use [PHP Windows Binaries Downloader (gist)](https://gist.github.com/andkirby/67e87e319c376b8676d559edb759e3fe))
- Install extra code validators:
  - PHPCS
    ```shell
    $ composer global require squizlabs/php_codesniffer:~2.0@stable
    ```
  - [JSHint](docs/jshint-setup.md) ([original](http://jshint.com/install/)).
  - [Rubocop installation](https://github.com/bbatsov/rubocop/blob/master/manual/installation.md).
- (optional) Perhaps, you have to define binaries for extra code validators in case they are not allow to use globally. (e.g. [JSHint](docs/jshint-setup.md), any see how to install any "linter" in [this example](src/config/examples/pre-commit/Linter-SimplyLint.xml).)
- Install GIT hook files
  ```
  $ commithook install
  ```
- Enable your tracker integration:
  ```
  $ commithook tracker:wizard
  ```

#### Sample `.bashrc`
Here is [an example of `.bashrc` file](docs/example-bashrc.md) for global using.

### Separate installation
If global way doesn't work you may use [installation via `create-project`](docs/install-create-project.md).

## Documentation references
### Installation
[Initialize configuration wizard](docs/example-quick-wizard.md)<br>
[GIT integration: hook files installation](docs/hooks-installation.md)<br>
### Password tracker update
[Password update](docs/example-wizard.md#password-reset)<br>
### Code validation
[Ignore validation](docs/exclude-code-validation.md)<br>
[Protect code](docs/protect-code.md)<br>
[Enable JsHint validation (Javascript)](docs/jshint-setup.md)<br>
### Commit message
[Commit message format](docs/commit-msg.md)<br>
[Ignore commit message validation](docs/commit-msg-ignore.md)<br>
[Active task in commit message](docs/active-task.md)<br>
[Auto-explode commit message into the list](docs/config-message.md)<br>

## Problems
### Bugs
[Issue bugs list](../../labels/bug).
#### Code validators doesn't work with GIT cache
The code validation works with GIT but it doesn't support git cache. It means if you added a file (`git add file.php`), changed it, and trying to commit (without adding it into GIT cache). In commit, of course, you will get code from cache but validators will validate your real file. ([#113](../../issues/113))
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

## [Release notes](docs/release-notes.md)
