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

*NOTE:* Actually this can be extended. Please take a look [some specific customization of commit message format](https://gist.github.com/andkirby/12175e1a46d2a9e6f2bb).

#### JIRA Integration
Please take a look [wizard example](example-wizard.md).

##### Short Issue Commit
So, if you want to be ~~lazy~~ productive... :)
If you tired of copy-pasting issue key and summary that there is good news.
If you'd like to speed up of writing commit-verb that there is good news.

###### Option #1
You may write it shortly with using JIRA project key:
```
F PRJNM-256 Added missed email validator.
```
The system will connect to JIRA and get an issue summary. Also it will recognize the commit-verb.
There are following short-names:
- `I` for `Implemented`
- `F` for `Fixed`
- `R` for `Refactored`
- `C` for `CR Changes`


###### Option #2
And JIRA project key can be omitted.

Second option. Omit project key.
```
F 256
 - Added missed email validator.
```
In this case the system will find a project key and set it (it should be set in this case).

###### Option #3
You may omit verbs `F` and `I`. It will be identified by issue type. 
```
256 Added missed email validator.
```
or verb `R` for refactoring (`C` - for `CR Changes`)
```
R 256 Reformatted code
```
or for list
```
256 Added missed email validator.
 - Reformatted code
```
In this case the system will take default verb by issue type. For bug - `Fixed`
and for tasks - `Implemented`. Of course if you're making refactoring
or applying code review you have to set related verb.

###### Option #4
Also, you may declare "active task" by similar command:
```shell
$ commithook config task 256
```
The value can be checked w/o last argument.

##### JIRA issue type configuration map
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
You extend it with adding new nodes by adding new config node. E.g. we need to map `NEW_TYPE` to type `task`.
```
$ commithook config --xpath hooks/commit-msg/message/issue/type/tracker/jira/default/NEW_TYPE task
```

#### Be aware about numbers. :)
Please always keep an eye on issue numbers. That's all just to be more ~~lazy~~ productive! ;D

[Back](../README.md)
