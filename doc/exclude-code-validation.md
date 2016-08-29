[Back](../README.md)

## Ignoring code validation


### Ignore for a commit
This command may make next as a blind commit.
```
$ commithook blind-commit
```
More info by command `commithook blind-commit --help`.

### Ignore code block
```
// @codingStandardsIgnoreStart
your_bad_code_here();
// @codingStandardsIgnoreEnd
```

### Ignore code in whole file
```
// @codingStandardsIgnoreFile

your_bad_code_here();
```

#### Ignore code validation by path
To skip validation for a file/directory please use

`commithook files:skip path/to`

For unset:
`commithook files:skip path/to --unset`

To skip validation for an extension please use

`commithook files:skip --extension jpg`

This extensions list already defined:
`jpg png gif bmp ico svg zip rar gz tar ttf fon eot woff`

For unset:
`commithook files:skip --extension jpg --unset`

[Back](../README.md)


