[Back](../README.md)

# Project configuration example

### Wizard
#### Wizard for JIRA integration
Let's init empty directory with GIT.
```shell
$ mkdir /d/home/prj1

$ cd /d/home/prj1

$ git init
Initialized empty Git repository in D:/home/prj1/.git/
```
Now we may start wizard.
```
$ commithook config wizard
Set up issue tracker connection.

 Tracker type:
  1 - jira
  2 - github
  3 - redmine
 []:
 > 1

 'jira' URL:
 > http://jira.example.com/

 'jira' username:
 > my.username

 'jira' password:
 > mypassword111

 Current 'jira' project key:
 > PRJ1

 Set config scope (tracker/type):
  1 - global (Default)
  2 - project
  3 - project-self
 [1]:
 > 1

Configuration updated.
Do not forget to share project commithook.xml file with your team.
Enjoy!
```
Let's check generated files.
```xml
$ cat commithook.xml
<?xml version="1.0" encoding="UTF-8"?>
<config>
    <tracker>
        <jira>
            <project>PRJ1</project>
        </jira>
    </tracker>
</config>

$ cat ~/.commithook/commithook.xml
<?xml version="1.0" encoding="UTF-8"?>
<config>
    <tracker>
        <type>jira</type>
        <jira>
            <url>http://jira.example.com/</url>
            <username>my.username</username>
            <password>eQcV0cX1uBhBTrANOIa7awjRcQFp4RH6ywoQqi7JSSc=</password>
        </jira>
    </tracker>
</config>
```
To reset your password you may use command:
```
$ commithook config --global --tracker jira password pass123
```

#### Define path to PHP interpreter
Default path: `c:/xampp/php/php.exe`
```
$ commithook config --xpath code/interpreter/php d:/s/php-5.5.38/php.exe --global
```
It will update file `~/.commithook/commithook.xml`.

_**NOTE:** You may set this up per project. Just use `--project-self` instead `--global`._

### Install GIT hook files
```
$ commithook install
```
[Here](hooks-installation.md) full documentation.

### PHPCodeSniffer integration

_**NOTE:** all files within directories `.commithook` and `.coding-standards` should be shared with your team. So just add those into VCS._

Fetch PHPCS package:
```
$ composer global require squizlabs/php_codesniffer:~2.0@stable
```

#### Fast install (Magento 1.x ECG standards)

```
$ cd my-project
$ curl https://codeload.github.com/andkirby/commithook-standard/tar.gz/magento-ecg | tar zxf -
$ mv -f commithook-standards-magento-ecg/.co* .
$ rm -rf commithook-standards-magento-ecg/
```

Fetch magento-ecg standards
```
$ composer --working-dir=.coding-standards require magento-ecg/coding-standard:~2.0 -o
```

#### Step by step

Now you need to set up PHPCS. Example with using `magento-ecg` standards.
```shell
$ mkdir -p .coding-standards/phpcs
```
Paste content to rule file `.coding-standards/phpcs/ruleset.xml`
```
$ vim .coding-standards/phpcs/ruleset.xml
```
Content:
```xml
<?xml version="1.0"?>
<ruleset name="CommitHooks">
    <description>Internal Magento coding standard. (extended from Magento ECG)</description>

    <!-- Include the whole magento-ecg standard -->
    <rule ref="../vendor/magento-ecg/coding-standard/Ecg">
        <!--Exclude TO-DO comments blocking-->
        <exclude name="Generic.Commenting.Todo" />
    </rule>
</ruleset>
```
Fetch magento-ecg standards
```
$ composer --working-dir=.coding-standards require magento-ecg/coding-standard:~2.0 -o
```
Ignore vendor dir within `.coding-standards/` directory.
```
$ echo vendor >> .coding-standards/.gitignore
```

Declare PHPCS rule file in file `.commithook/CodeSniffer.xml`.
```
$ mkdir .commithook
$ vim .commithook/CodeSniffer.xml
```

Content:
```xml
<?xml version="1.0"?>
<config>
    <!--Validators setup-->
    <validators>
        <CodeSniffer>
            <rule>
                <directory>.coding-standards/phpcs</directory>
            </rule>
        </CodeSniffer>
    </validators>
</config>
```

### Share commithook files with your team
```
$ git add .commithook .coding-standards commithook.xml
$ git commit -m '@@through Added commithook files.'
$ git push
```
### Checking
Let's test.
```
$ echo '<?php echo 111;' > test.php
$ git add test.php
$ commithook test
PHP CommitHooks v2.0.0-beta.XX
Please report all hook bugs to the GitHub project.
http://github.com/andkirby/commithook

Ooops! Something wrong in your files.
========================== test.php ==========================
Line: 1:7. (phpcs W) Use of echo language construct is discouraged.
```

[Back](../README.md)
