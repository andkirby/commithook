[Back](../README.md)

# Project configuration quick example

[Expanded version](example-wizard.md).

## Install GIT hook files in project
To enable GIT hooks ([detailed info](hooks-installation.md)):
```
$ commithook install
```

## Set up issue tracker integration
```
$ commithook tracker:wizard
```

## PHP CodeSniffer integration

Fetch PHPCS package (for CommitHook global installation):
```
$ composer global require squizlabs/php_codesniffer:~2.0@stable
```

#### Fast install (Magento 1.x ECG standards)
Go to your project git root directory and run commands:
```
$ curl https://codeload.github.com/andkirby/commithook-standard/tar.gz/magento-ecg | tar zxf -
$ mv -f commithook-standards-magento-ecg/.co* .
$ rm -rf commithook-standards-magento-ecg/
```

Install magento-ecg standards
```
$ composer --working-dir=.coding-standards install -o
```

### Test integration
You may make quick test for the integration [here](test-code.md).

### Share commithook files with your team
```
$ git add .commithook .coding-standards .commithook.xml
$ git commit -m '@@through Added commithook files.' && git push
```

[Back](../README.md)
