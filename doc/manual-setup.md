[Back to top](../README.md)

## Set up GIT hooks manually

To set up GIT hooks you have to set up your commit-msg and pre-commit files.
If you placed commithook project into the same projects root directory and you
have just to copy such files from commithook directory into yourproject/.git/hooks.
In other cases please set up them manually.

Also you may use following template to create commit-msg and pre-commit files:
```php
#!/usr/bin/env /your/php
<?php
$hookName = __FILE__;
require_once '/path/to/commithook/bin/runner.php';
```

[Back to top](../README.md)
