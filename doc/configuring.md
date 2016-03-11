[Back](../README.md)
## Configuring

### How to use configuration layers

The system loads src/config.xml file at first.

#### Config caching
In then it will try to load cached file with full merged configuration by path:

- `HOME/.commithook/.cache/md5(version + directory of hookfile).xml`

Cache will be invalidated if version was updated.

*NOTE:* In case you changed your local config files your need to clean up cache files.

#### Config layers
Basically, first file will be read be path `commithook/src/config/root.xml`.
After that, the system will try to get user option of this file by path `~/.commithook/user-root.xml`.
After that, it will merge all files in the XML node "additional_config".
There are several default config XML files which will be loaded by default.
So default files ordering is presented as this list below:
- `commithook/src/config/commithook.xml` (base configuration)
- `commithook/src/config/pre-commit.xml` (contains pre-commit hook configuration)
- `commithook/src/config/commit-msg.xml` (contains main part of configuration)
- `commithook/src/config/pre-commit-magento.xml` (contains configuration for magento projects)
- `commithook/commithook-local.xml` (it may contain your specific local configuration)
- `HOME/.commithook/commithook.xml` (the same but in user profile directory, the same `~/.commithook.xml`)
- `HOME/.commithook/pre-commit.xml` (like previous one but to split up specific configuration for `pre-commit` hook)
- `HOME/.commithook/commit-msg.xml` (like previous one but to split up specific configuration for `commit-msg` hook)
- `PROJECT_DIR/commithook.xml` (it may contain a project specific configuration which can be shared among your team)
- `PROJECT_DIR/.commithook/` (the same like previous, but will load all `*.xml` files)
- `PROJECT_DIR/commithook-self.xml` (it may contain a local project specific configuration which shouldn't shared to your team)

The file `PROJECT_DIR/commithook.xml` can be added into a project and might be used by all developers.

`PROJECT_DIR` - is your root project directory where CommitHook will be used.

[Back](../README.md)
