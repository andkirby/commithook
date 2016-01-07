<?php
/**
 * @license https://raw.githubusercontent.com/andkirby/commithook/master/LICENSE.md
 */
namespace PreCommit\Validator;

use PreCommit\Interpreter\JsHintOutput;
use PreCommit\Validator\Linter\AbstractLintValidator;

/**
 * Class JsHint
 *
 * This class responsible of getting errors which can be Javascript files by JSHint code sniffer
 *
 * @package PreCommit\Validator
 */
class JsHint extends AbstractLintValidator
{
    /**#@+
     * Error codes
     */
    const CODE_JSHINT_ERROR = 'jsHintError';
    /**#@-*/

    /**
     * Error messages
     *
     * @var array
     */
    protected $errorMessages
        = array(
            self::CODE_JSHINT_ERROR => '(jshint) %value%',
        );

    /**
     * {@inheritdoc}
     */
    protected function getOutputInterpreter()
    {
        return new JsHintOutput();
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultErrorCode()
    {
        return self::CODE_JSHINT_ERROR;
    }

    /**
     * {@inheritdoc}
     */
    protected function getValidatorCode()
    {
        return 'JsHint';
    }
}
