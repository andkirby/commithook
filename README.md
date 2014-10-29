The main purpose of this project is making an application which can check coding standards at first for PHP files.

h3. How to use configuration layers
The system load LibHooks/config.xml file at first. In then it will try to load cached file with full merged configuration by path:
- commithook/.cache/md5(version + directory of hookfile).xml

Cache will be invalidated if version was updated. In such case it will merge all files in the XML node "additional_config". There are several default config XML files which will be loaded by default. So default files ordering is presented as this list below:
- commithook/LibHooks/config.xml
- commithook/LibHooks/commithook.xml
- commithook/LibHooks/commithook-magento.xml
- commithook/commithook-local.xml
- PROJECT_DIR/commithook.xml

The last one can be committed into a project and might be use by all developers. 
