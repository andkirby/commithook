Ignoring code validation

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

To skip validation for an extension please use

`commithook files:skip --extension path/to`

### Prohibit changes
#### Protect path
To prohibit committing changes for a file/directory please use

`commithook files:protect path/to`

#### Allow changes
To allow committing changes for a file/directory please use

`commithook files:allow path/to`

This command should be used to allow committing within protected path.
