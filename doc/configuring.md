## Configuring

### How to use configuration layers

The system loads LibHooks/config.xml file at first.

#### Config caching
In then it will try to load cached file with full merged configuration by path:

- `HOME/.commithook/.cache/md5(version + directory of hookfile).xml`

Cache will be invalidated if version was updated.

*NOTE:* In case you changed your local config files your need to clean up cache files.

#### Config layers
In such case it will merge all files in the XML node "additional_config". There are several default config XML files which will be loaded by default. So default files ordering is presented as this list below:
- `commithook/LibHooks/config.xml` (base configuration)
- `commithook/LibHooks/commithook.xml` (contains main part of configuration)
- `commithook/LibHooks/commithook-magento.xml` (contains configuration for magento projects)
- `commithook/commithook-local.xml` (it may contain your specific local configuration)
- `HOME/.commithook/commithook.xml` (the same but in user profile directory, the same `~/.commithook.xml`)
- `PROJECT_DIR/commithook.xml` (it may contain a project specific configuration which can be shared among your team)
- `PROJECT_DIR/commithook-self.xml` (it may contain a project specific configuration which shouldn't shared to your team)
The last one can be added into a project and might be used by all developers. PROJECT_DIR - is your project directory where from CommitHOOK has been run.

