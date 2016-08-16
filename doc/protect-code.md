[Back](../README.md)

## Prohibit changes

### Protect path
To prohibit committing changes for a path please use

`commithook files:protect path/to`

For unset:
`commithook files:protect path/to --unset`

### Allow changes
To allow committing changes for a file/directory please use

`commithook files:allow path/to`

For unset:
`commithook files:allow path/to --unset`

This command should be used to allow committing within protected path.

### Allowed be default
There is an option which allows to commit any file.

`commithook files:allow-default`

In this case "allowed" list will be ended rule and will have highest priority.

You switch it to OFF:

`commithook files:allow-default 0`

In this case "protected" list will be ended rule and will have highest priority.
It will pass a commit only if "allowed" list covers a path.

[Back](../README.md)
