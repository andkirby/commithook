<?php
namespace PreCommit\Validator;

/**
 * Class validator for check commit message format
 *
 * @package PreCommit\Validator
 */
class CommitMsg extends AbstractValidator
{
    /**#@+
     * Error codes
     */
    const CODE_BAD_COMMIT_MESSAGE = 'badCommitMessage';
    /**#@-*/

    /**
     * Error messages
     *
     * @var array
     */
    protected $_errorMessages = array(
        self::CODE_BAD_COMMIT_MESSAGE => 'Your commit message "%value%" has improper form.',
    );

    /**
     * Checking for interpreter errors
     *
     * @param string $content  Absolute path
     * @param string $file
     * @return bool
     */
    public function validate($content, $file)
    {
        if (!preg_match('/^Merge /', $content) && !preg_match('/^Revert /', $content)
            && !preg_match('/^(Implemented|Fixed|CR Changes?|Refactored) [A-Z0-9]{2,}-[0-9]+: /', $content)
        ) {
                $this->_addError('Commit Message', self::CODE_BAD_COMMIT_MESSAGE, $content);
        }
        return !$this->_errorCollector->hasErrors();
    }
}
