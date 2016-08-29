[Back to top](../README.md)

# Active task in commit message
You may declare "active task" by similar command and don't care about numbers in commit messages:
```shell
$ commithook tracker:task 256
```
### Using example
Now you are able to make simple commit messages:
```
$ git add myfile.php
$ git commit -m 'I have done something good!'
```
Expected commit message:
```
Implemented #256: Do something good
 - I have done something good!
```
More information about short commit messages [here](commit-msg.md).

### Available options:

Set active task `123`:
```
commithook tracker:task 123
```
Show value of active task:
```
commithook tracker:task
```
Read info about active task:
```
commithook tracker:task --info
```
Read info about task `321`:
```
commithook tracker:task 321 --info
```

[Back to top](../README.md)
