[Back](README.md)
## Installation

### Set up via command line

This feature is available since v1.6.3. _(Simple install command `commithook-install` is available since v1.6.0 but it was removed in later versions.)_

#### `commithook install`: Generate files

**NOTE:** Since v1.6.6 you may run via **_alias_** command **`php commithook.php`**. It could be helpful if you have some global PHP version but would like to use another one to run PHP CommitHooks. (This problem faced on Windows with **XAMPP** and another PHP version) If you'll install PHP CommitHooks into your global PHP you shouldn't face this issue.

Before you start please be sure that shell can run files from `vendor/bin` directory ([read more](#set-up-composer-vendorbin-directory)). Or use absolute path `/path/to/vendor/bin/commithook`.

Go to your project root directory and install commithook files:
```shell
$ cd /d/home/my-project
$ commithook install
PHP CommitHook files have been created in 'd:/home/my-project/.git/hooks'.
```
Actually you may skip step of getting in a project directory.
Run `commithook install` anywhere and put path to your project:
```shell
$ cd /d/home
$ commithook install
Please set your root project directory [d:/home]: d:/home/my-project
PHP CommitHook files have been created in 'd:/home/my-project/.git/hooks'.
```
If system couldn't find path to your executable PHP file it will ask about it.

Since PHP 5.4 console should not ask your about PHP binary file. Anyway you may set up path to your PHP binary file.
Also you may set path your project/VCS root.
```shell
$ commithook install --php-binary=d:/s/php/php.exe --project-dir=d:/home/my-project
```
Or short version:
```shell
$ commithook install -pd:/s/php/php.exe -dd:/home/my-project
```

NOTE: Tested on Windows. Feel free [to put](../../issues/new "Add a new issue") your faced issues on [the project issues page](../../issues "Issues").

##### Extra Options
`commithook install` has options.
Please take a look them via command `commithook install -h`.
```shell
$ commithook install -h
[...]
Options:
 --project-dir (-d)    Set path to project (VCS) root directory.
 --hook                Set specific hook file to install.
 --commit-msg          Set 'commit-msg' hook file to install.
 --pre-commit          Set 'pre-commit' hook file to install.
 --overwrite (-w)      Overwrite exist hook files.
 --php-binary (-p)     Set path to PHP binary file.
[...]
```

#### `commithook remove`: Remove Hooks Files
To remove CommitHook files from your project you may use command:
```shell
$ commithook remove
```
Options list the same but without `--overwrite` and `--php-binary`.

#### Set up Composer vendor/bin directory
If you using GitBash or Unix system please be sure that your shell can find files in global vendor directory.
Or try to use absolute path `/path/to/vendor/bin/commithook`.
##### GitBash for Windows
You may don't have `vendor/bin` directory in global `PATH` environment variable.
For GitBash for Windows you can check this out:
```shell
$ echo $PATH | grep "vendor/bin"
```
If you got nothing try to add your `vendor/dir` path to your `~/.bashrc` file:
```shell
echo 'PATH=$PATH":/d/yourpath/to/php/vendor/bin"' >> ~/.bashrc
```
and restart your shell.
[Back](README.md)
