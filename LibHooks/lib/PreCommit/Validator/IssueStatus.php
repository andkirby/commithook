<?php
namespace PreCommit\Validator;

use PreCommit\Config;
use PreCommit\Exception;
use PreCommit\Message;

/**
 * Class code style validator
 *
 * @package PreCommit\Validator
 */
class IssueStatus extends AbstractValidator
{
    /**#@+
     * Error codes
     */
    const CODE_WRONG_ISSUE_STATUS = 'wrongIssueStatus';

    /**#@-*/

    /**
     * Error messages
     *
     * @var array
     */
    protected $errorMessages
        = array(
            self::CODE_WRONG_ISSUE_STATUS => 'The issue status "%value%" does not support to add new commit.',
        );

    /**
     * Set type
     *
     * @param array $options
     * @throws \PreCommit\Exception
     */
    public function __construct(array $options = array())
    {
        parent::__construct($options);

        $this->_type = $this->getConfig()->getNode('hooks/commit-msg/message/type');
        if (!$this->_type) {
            throw new Exception('Type is not set.');
        }
    }

    /**
     * Checking for interpreter errors
     *
     * @param Message $message Absolute path
     * @param string  $file    Omitted parameter
     * @return bool
     */
    public function validate($message, $file)
    {
        if ($message->issue && $message->issue->getStatus()
            && !$this->isAllowed($message->issue->getStatus())
        ) {
            $this->addError('Commit Message', self::CODE_WRONG_ISSUE_STATUS, $message->issue->getStatus());
        }

        return !$this->errorCollector->hasErrors();
    }

    /**
     * Get verbs map
     *
     * @return array
     */
    protected function getStatuses()
    {
        return (array) $this->getConfig()->getNodeArray(
            'validators/IssueStatus/issue/status/'.$this->getTrackerType().'/allowed/'.$this->_type
        )
            ?: (array) $this->getConfig()->getNodeArray(
                'validators/IssueStatus/issue/status/'.$this->getTrackerType().'/allowed/'.$this->_type
            );
    }

    /**
     * Get tracker name
     *
     * @return string
     */
    protected function getTrackerType()
    {
        return (string) $this->getConfig()->getNode('tracker/type');
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

    /**
     * Is status allowed
     *
     * @param string $status
     * @return bool
     */
    protected function isAllowed($status)
    {
        $allowedStatuses = $this->getStatuses();

        return isset($allowedStatuses[$status])
               && $allowedStatuses[$status];
    }
}
