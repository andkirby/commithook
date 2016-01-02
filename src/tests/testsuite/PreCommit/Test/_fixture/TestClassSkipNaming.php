<?php
/**
 * @license https://raw.githubusercontent.com/andkirby/commithook/master/LICENSE.md
 */
class Some_testClassSkip extends stdClass
{
    /**
     * Some message
     *
     * @skipPublicMethodNaming _testSkipNameValidation
     */
    public function _testSkipNameValidation($param1, $param2)
    {
        //empty
    }

    /**
     * Some message
     *
     * @skipCommitHookMethodNaming _testSkipNameValidation2
     */
    public function _testSkipNameValidation2($param1, $param2)
    {
        //empty
    }

    /**
     * Some message
     *
     * @skipCommitHookMethodNaming testSkipProtectedNameValidation
     */
    protected function testSkipProtectedNameValidation($param1, $param2)
    {
        //empty
    }
}
