[Back to top](../README.md)

# Enable JSHint validation for Javascript files
CommitHooks app has a validator to connect JSHint Javascript files validator.
You may install it.
## Install
Let's admit you already have `NodeJS` and `npm`. If not, please take a look the references below.
### JSHint
```shell
$ npm install -g jshint
```
### Integration
Installed `jshint` package has a binary file. So as an interpreter we may use `bash` (or use `node` instead).
```
$ commithook config --global --xpath validators/JsHint/execution/interpreter_type bash
```

Note: you may check your interpreter path, e.g. `node`:
```
$ commithook config --xpath code/interpreter/node
c:/xampp/node/node.exe
$ commithook config --xpath code/interpreter/bash
bash
```

If `jshint` binary file is not allowed globally define a path to it then:
```
commithook config --global --xpath validators/JsHint/execution/linter /path/to/jshint
```

And now, let's enable the validation:
```
commithook config validator:disable JsHint --enable
```
There is a created file `.commithook/JsHint.xml`.

### Configure JSHint validation
You may define your JSHint configuration in file `.jshintrc` in project root directory.
Also your may define it in `packages.json` in the project root ([see how](http://jshint.com/blog/better-npm-integration/)). 

And you may define your own custom path within project, for instance (it can be `packages.json` or `.jshintrc` file):
```
$ commithook config --project --xpath validators/JsHint/config/file/custom/any_name path/to/file/packages.json
```

## References
- [Quick install NodeJS and NPM](https://gist.github.com/andkirby/3f65c5a6499739c842e25fb7f6d5e682)
(Windows / GitBash only)
```
$ curl -Ls https://gist.github.com/andkirby/3f65c5a6499739c842e25fb7f6d5e682/raw/node-npm-git-bash-win.sh | bash
```
- [Node Windows binary (v6.5.0)](https://nodejs.org/download/release/v6.5.0/win-x64/node.exe)
- [Install Node without admin rights](http://abdelraoof.com/blog/2014/11/11/install-nodejs-without-admin-rights/)
- [NPM releases](https://nodejs.org/download/release/npm/)
- [NodeJS: download package manager](https://nodejs.org/en/download/package-manager/)

[Back to top](../README.md)
