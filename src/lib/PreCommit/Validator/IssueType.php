<?php
/**
 * @license https://raw.githubusercontent.com/andkirby/commithook/master/LICENSE.md
 */
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
    protected $errorMessages
        = array(
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
        if (false === strpos($message->body, '@@add') && $message->issue && !$message->issue->getType()) {
            $this->addError('Commit Message', self::CODE_WRONG_ISSUE_TYPE, $message->issue->getOriginalType());
        }

        return !$this->errorCollector->hasErrors();
    }

    /**
     * Get config model
     *
     * @return Config
     */
    protected function getConfig()
    {
        return Config::getInstance();
    }
}
