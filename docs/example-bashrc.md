[Back to top](../README.md)

## `~/.bashrc`

You may try to use this script with shortcuts for your local `~/.bashrc`.

Please update paths if needed.

```shell
# Composer
export COMPOSER_HOME='~/.composer' # not required for non-Windows systems
export COMPOSER_CACHE_DIR='~/.composer/cache' # not required for non-Windows systems

# alias for PHP
alias php='/d/s/php-7.1.0-nts-Win32-VC14-x64/php.exe'

if [ -z "$ORIGINAL_PATH" ]; then
    # this variable prevents adding the same path on reload file
    ORIGINAL_PATH=$PATH
    readonly ORIGINAL_PATH
fi

# Make global composer binary files available for running from everywhere
PATH=${ORIGINAL_PATH}':~/.composer/vendor/bin'
PATH=${PATH}':'$(php -i | grep 'php.exe' | head -1 | cut -d '>' -f2- | xargs -i dirname {} | sed -re 's|([A-z]):|/\1|g' )
```
Now you may reload file:
```shell
$ . ~/.bashrc
```

[Back to top](../README.md)
