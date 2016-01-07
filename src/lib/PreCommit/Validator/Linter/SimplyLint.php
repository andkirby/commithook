<?php
/**
 * @license https://raw.githubusercontent.com/andkirby/commithook/master/LICENSE.md
 */
namespace PreCommit\Validator\Linter;

use PreCommit\Interpreter\SimplyLintOutput;

/**
 * Class SimplyLint
 *
 * This class responsible of getting errors list of a linter
 *
 * @package PreCommit\Validator
 */
class SimplyLint extends AbstractLintValidator
{
    /**
     * Linter type
     */
    const TYPE = 'Magento-SimplyLint';
    /**#@+
     * Error codes
     */
    const CODE_SIMPLY_LINT_ERROR = 'simplyLintError';
    /**#@-*/

    /**
     * Extension of target file
     *
     * @var string
     */
    protected $fileExt;

    /**
     * Error messages
     *
     * @var array
     */
    protected $errorMessages
        = array(
            self::CODE_SIMPLY_LINT_ERROR => "(lint)\n%value%\n(lint end)",
        );

    /**
     * Set file extension
     *
     * {@inheritdoc}
     */
    public function validate($content, $file)
    {
        $this->fileExt = pathinfo($file, PATHINFO_EXTENSION);

        return parent::validate($content, $file);
    }

    /**
     * {@inheritdoc}
     */
    protected function getOutputInterpreter()
    {
        return new SimplyLintOutput();
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultErrorCode()
    {
        return self::CODE_SIMPLY_LINT_ERROR;
    }

    /**
     * {@inheritdoc}
     */
    protected function getValidatorCode()
    {
        return 'Linter-SimplyLint/'.$this->fileExt;
    }
}
