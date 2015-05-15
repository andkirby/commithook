[Back](README.md)
### Ignore code validators

#### Skip Method Name Validation
To skip validation of methods name just add PHPDoc block tag `@skipCommitHookMethodNaming` like following:

```php
    /**
     * My method
     *
     * @skipCommitHookMethodNaming myMethod
     */
    protected function myMethod()
    {
        //...
    }
```

#### Skip Code Block Full Validation
Also you may skip validation fully for a particular code block:

```php
//@startSkipCommitHooks
//some bad code or code which cannot be validated properly
$a=function($b){return $b};
//@finishSkipCommitHooks
```
[Back](README.md)
