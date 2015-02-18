<?php
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
     * @skipHookMethodNaming _testSkipNameValidation2
     */
    public function _testSkipNameValidation2($param1, $param2)
    {
        //empty
    }

    /**
     * Some message
     *
     * @skipHookMethodNaming testSkipProtectedNameValidation
     */
    protected function testSkipProtectedNameValidation($param1, $param2)
    {
        //empty
    }
}
