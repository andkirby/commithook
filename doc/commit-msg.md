[Back](README.md)
### Commit Message Validation
Default commit message format is:
```
[Commit verb] [Issue Key]: [Issue Summary]
[Commit Message]
```
E.g. for the bug:
```
Fixed PRJNM-256: An email validation doesn't work
 - Added missed email validator.
```
Where PNM-25 is an issue key of your tasks tracker.

There are available commit verbs:
- `Implemented` (for tasks)
- `Fixed` (for bugs)
- `Refactored` (for commits which contains refactoring only)
- `CR Change(s)` ("changes" or "change", for applying code review changes)

*NOTE:* Actually this validation is hardcoded. It will be moved to configuration to be flexible later.

#### JIRA Integration
Since v1.7.0b an integration with JIRA issues tracker is available.
How it works?
At first you have to set up authorization to JIRA. All what we need: URL to JIRA, username, password.
Open file CommitHook XML configuration file:
```xml
<?xml version="1.0"?>
<config>
    ...
    <task_tracker>jira</task_tracker>
    <jira>
        <url>http://jira.example.com</url>
        <username>my.name</username>
        <password>some-password</password>
    </jira>
    ...
</config>
```
If it's a global configuration you may put it in `~/.commithook/commithook.xml` (`%USERPROFILE%/.commithook/commithook.xml` for Windows CLI, or the same path for GitBash).
(*in next releases a password will be protected.*)
`issue_type` config node isn't supported now but it will be needed for different issue trackers (future feature).

##### Short Issue Commit
So, if you want to be ~~lazy~~ productive... :)
If you tired of copy-pasting issue key and summary that there is good news.
If you'd like to speed up of writing commit-verb that there is good news.

First option.
You may write it shortly:
```
F PRJNM-256
 - Added missed email validator.
```
The system will connect to JIRA and get an issue summary. Also it will recognize the commit-verb.
There are following short-names:
- `I` for `Implemented`
- `F` for `Fixed`
- `R` for `Refactored`
- `C` for `CR Changes`

Actually, you can be more ~~lazy~~ productive and avoid using project. Usually it's the one for all commits.
Please put following config into `PROJECT_DIR/commithook.xml` and commit this file to share it with your team if haven't done this yet.

**Config to set default JIRA project**
```xml
<?xml version="1.0" encoding="UTF-8"?>
<config>
    <jira>
        <project>PRJNM</project>
    </jira>
</config>
```
Of course it will used only when commit message will contain the issue number without project key.

Still complexly? :) Commit message can be more simpler.

Second option. Omit project key.
```
F 256
 - Added missed email validator.
```
In this case the system will find a project key and set it (it should be set in this case).

Third option. Omit verb.
```
256
 - Added missed email validator.
```
In this case the system will take default verb by issue type. For bug - `Fixed`
and for tasks - `Implemented`. Of course if you're making refactoring
or applying code review you have to set related verb.

There is predefined configuration:
```xml
<?xml version="1.0"?>
<config>
    ...
    <filters>
        <ShortCommitMsg>
            ...
            <issue>
                <jira>
                    <issue>
                        <type>
                            <!-- Tasks -->
                            <Task>task</Task>
                            <Sub_Task_Task>task</Sub_Task_Task>
                            <Change_Request>task</Change_Request>
                            <!-- Bugs -->
                            <Bug>bug</Bug>
                        </type>
                    </issue>
                </jira>
            </issue>
        </ShortCommitMsg>
    </filters>
    ...
</config>
```


Please do not forget check issue numbers always!! It's just to be more ~~lazy~~ productive! ;D

###### Caching
Information about JIRA issues cached in file `HOME/.commithook/cache/issues-prjnm-v0` where
- `prjnm` is your JIRA project key,
- `v0` version of cache schema.

###### Future Features with JIRA Integration
- Protect commits into issues with not appropriate status.
- Protect commits with verb Fixed/Implemented into an issue Task/Bug (or auto set it).
.travis.yml

[Back](../README.md)
