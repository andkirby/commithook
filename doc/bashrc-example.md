You may try to use this script for your local `~/.bashrc`.

Please update paths if needed.

```shell
# PHP
alias php='/c/xampp/php/php.exe'

# commithook bin
alias commithook="~/vendor/bin/commithook"

# Composer
export COMPOSER_HOME=~/.composer # not required for non-Windows systems
alias composer='php ~/composer.phar'
alias c='composer'
alias cg='composer global'
alias cu='c update'
alias ci='c install'

if [ -z "$ORIG_PATH" ]; then
        ORIG_PATH=$PATH
        readonly ORIG_PATH
fi

PATH=${ORIG_PATH}':/c/xampp/php/:~/.composer/vendor/bin'
```
