[Back to top](../README.md)

# Commit Message configuration
## Explode message
Explode commit message to the list (enabled by default):
```shell
$ commithook message explode 1
```

Now you are able to make explode sentences into the list:
(in examples used [short commit message format](commit-msg.md))
```
$ git add myfile.php
$ git commit -m ' - I have done this. And I have done that.'
```
Expected commit message:
```
Implemented #33: Make smth good now
 - I have done this.
 - And I have done that.
```

Default explode string is `. ` (dot and space).
```shell
$ commithook message explode-string
". "
```

### Custom explode string
You may define a custom delimiter:
```shell
$ commithook message explode-string '|'
```
Let's test:
```
$ git add myfile.php
$ git commit -m '- I have done this. Yeah, I'm sure. | And I have done that.'
```
Expected commit message:
```
Implemented #33: Make smth good now
 - I have done this. Yeah... I'm sure.
 - And I have done that.
```

[Back to top](../README.md)
