# PHP CommitHOOKs
The main purpose of this project is checking coding standards at first for PHP files.

## Composer Installation

Install package by command (you may put it into your global vendor directory):

    $ composer require andkirby/commithook:*

### Set up via command line

This feature is available since v1.6.0.

Go to your project root directory and install commithook files:

    $ cd /d/home/my-project
    $ commithook-install
    PHP CommitHook files have been created in 'd:/home/my-project/.git/hooks'.

Actually you may skip step of getting project directory.
Run it from anywhere and put path to your project:

    cd d:/home/
    Please set your root project directory [d:/home]: d:/home/my-project
    PHP CommitHook files have been created in 'd:/home/my-project/.git/hooks'.

NOTE: Tested on Windows. Feel free to put your faced issues on the project issues page.

## Set up GIT hooks manually

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
