[Back](../README.md)

# Test code before commit
You may test your code before commit by the command:
```
$ commithook test
```

## Checking
Let's test.
```
$ echo '<?php echo 111; is_null(null);' > test.php
$ git add test.php
$ commithook test
PHP CommitHooks v2.0.0-beta.XX
Please report all hook bugs to the GitHub project.
http://github.com/andkirby/commithook

Ooops! Something wrong in your files.
========================== test.php ==========================
Line: 1:7. (phpcs W) Use of echo language construct is discouraged.
```

[Back](../README.md)
