[Back to top](../README.md)

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
Also you may set path of your project/VCS root.
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
Please take a look them via getting help information `commithook install --help`.

#### `commithook remove`: Remove GIT hook Files
To remove CommitHook files from your project you may use command:
```shell
$ commithook remove
```

[Back to top](../README.md)
