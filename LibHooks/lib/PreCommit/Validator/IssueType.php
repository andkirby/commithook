<?php
namespace PreCommit\Validator;

use PreCommit\Config;
use PreCommit\Message;

/**
 * Class code style validator
 *
 * @package PreCommit\Validator
 */
class IssueType extends AbstractValidator
{
    /**#@+
     * Error codes
     */
    const CODE_WRONG_ISSUE_TYPE = 'wrongIssueType';
    /**#@-*/

    /**
     * Error messages
     *
     * @var array
     */
    protected $_errorMessages = array(
        self::CODE_WRONG_ISSUE_TYPE => 'Issue type "%value%" is not suitable to have commits.',
    );

    /**
     * Checking for interpreter errors
     *
     * @param Message $message Absolute path
     * @param string  $file    Omitted parameter
     * @return bool
     */
    public function validate($message, $file)
    {
        if ($message->issue && !$message->issue->getType()) {
            $this->_addError('Commit Message', self::CODE_WRONG_ISSUE_TYPE, $message->issue->getOriginalType());
        }
        return !$this->_errorCollector->hasErrors();
    }

    /**
     * Get config model
     *
     * @return Config
     */
    protected function _getConfig()
    {
        return Config::getInstance();
    }
}
