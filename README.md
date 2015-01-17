# PHP CommitHOOKs

[![Gitter](https://badges.gitter.im/Join%20Chat.svg)](https://gitter.im/andkirby/commithook?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)
The main purpose of this project is checking coding standards at first for PHP files.

## Set up GIT hooks

To set up GIT hooks you have to set up your commit-msg and pre-commit files.
If you placed commithook project into the same projects root directory and you 
have just to copy such from commithook directory into yourproject/.git/hooks.
In other cases please set up them manually.

## Configuration

### How to use configuration layers

The system loads LibHooks/config.xml file at first.

#### Config caching
In then it will try to load cached file with full merged configuration by path:

- commithook/.cache/md5(version + directory of hookfile).xml

Cache will be invalidated if version was updated.

*NOTE:* In case you changed your local config files your need to clean up cache files. 

#### Config layers
In such case it will merge all files in the XML node "additional_config". There are several default config XML files which will be loaded by default. So default files ordering is presented as this list below:
- commithook/LibHooks/config.xml (base configuration)
- commithook/LibHooks/commithook.xml (contains main part of configuration)
- commithook/LibHooks/commithook-magento.xml (contains configuration for magento projects)
- commithook/commithook-local.xml (it may contain your specific local configuration)
- PROJECT_DIR/commithook.xml (it may contain a project specific configuration which can be shared among your team)
The last one can be added into a project and might be used by all developers. PROJECT_DIR - is your project directory where from CommitHOOK has been run.

# Release notes
- v1.5.0 Implemented layered configuration loading.
- v1.4.3 Minor fix in the check trailing spaces.
- v1.4.2 [CRITICAL] Fixed running validators from pre-commit processor.
- v1.4.1 Added tests for trailing spaces and trailing line validator. Improved trailing spaces validation.
- v1.4.0 Added checking GIT conflict lines to prevent commits conflicted files.
- v1.3.0 Added checking empty type in PHPDoc tags @param @var
- v1.2.4 Added close symbol on split content. Added checking slashed slash (\\) on split content.
- v1.2.3 Fixed case when operator name used in variable.
- v1.2.2 Bugfix for case:
<?php if ($i != $recommendationsCount-1) echo ","?>
