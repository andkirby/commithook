You may try to use this script with shortcuts for your local `~/.bashrc`.

Please update paths if needed.

```shell
# php
alias php='/c/xampp/php/php.exe'

# commithook
alias commithook='~/vendor/bin/commithook'
alias task='commithook config task'

# Composer
export COMPOSER_HOME=~/.composer # not required for non-Windows systems
alias composer='php ~/composer.phar'
alias c='composer'
alias cg='composer global'
alias cu='c update'
alias ci='c install'

if [ -z "$ORIGINAL_PATH" ]; then
    # this variable prevents adding the same path on reload file
    ORIGINAL_PATH=$PATH
    readonly ORIGINAL_PATH
fi

# Make global composer binary files available for running from everywhere
PATH=${ORIGINAL_PATH}':~/.composer/vendor/bin'
```
Now you may reload file:
```shell
$ . ~/.bashrc
```
