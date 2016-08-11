[Back](README.md)
## GIT hook files installation

Before you start please be sure that command `commithook` works for your. If not, try to use [`.bashrc` file configuration](example-bashrc.md).

#### `commithook install`: Generate files in `.git/hooks/` directory

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
and restart your shell or referesh by reloading `.bashrc`.
```shell
. ~/.bashrc
```

[Back](README.md)
