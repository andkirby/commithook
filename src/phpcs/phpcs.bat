@ECHO OFF
setlocal DISABLEDELAYEDEXPANSION
SET BIN_TARGET=%~dp0/../../vendor/squizlabs/php_codesniffer/scripts/phpcs
D:/s/php-5.5.38/php.exe "%BIN_TARGET%" %*
