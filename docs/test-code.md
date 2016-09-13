[Back to top](../README.md)

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
```
And take a look at errors list.

[Back to top](../README.md)
