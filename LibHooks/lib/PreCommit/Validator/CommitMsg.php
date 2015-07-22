<?php
namespace PreCommit\Validator;

use PreCommit\Config;
use PreCommit\Exception;
use PreCommit\Interpreter\InterpreterInterface;
use PreCommit\Issue;
use PreCommit\Message;

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
    const CODE_VERB_INCORRECT = 'badCommitVerb';
    const CODE_VERB_NOT_FOUND = 'commitVerbNotFound';
    const CODE_KEY_NOT_SET = 'commitKeyNotSet';
    /**#@-*/

    /**
     * Error messages
     *
     * @var array
     */
    protected $_errorMessages = array(
        self::CODE_BAD_COMMIT_MESSAGE => 'Head of commit message "%value%" has improper form.',
        self::CODE_VERB_INCORRECT     => 'Commit verb "%value%" is not suitable for the issue.',
        self::CODE_VERB_NOT_FOUND     => 'Commit verb "%value%" not found.',
        self::CODE_KEY_NOT_SET        => 'Required commit key "%value%" is not set.',
    );

    /**
     * Message interpreting type
     *
     * @var string
     */
    protected $_type;

    /**
     * Set type
     *
     * @param array $options
     * @throws \PreCommit\Exception
     */
    public function __construct(array $options)
    {
        parent::__construct($options);
        if (isset($options['type'])) {
            $this->_type = $options['type'];
        } else {
            $this->_type = $this->_getConfig()->getNode('hooks/commit-msg/message/type');
        }
        if (!$this->_type) {
            throw new Exception('Type is not set.');
        }
    }

    /**
     * Checking for interpreter errors
     *
     * @param Message $message
     * @param string $file
     * @return bool
     */
    public function validate($message, $file)
    {
        if (!$this->_matchMessage($message)) {
            $this->_addError('Commit Message', self::CODE_BAD_COMMIT_MESSAGE, $message->head);
        }
        return !$this->_errorCollector->hasErrors();
    }

    /**
     * Match commit message
     *
     * @param Message $message
     * @return bool
     * @throws \PreCommit\Exception
     */
    protected function _matchMessage($message)
    {
        foreach ($this->_getExpressions() as $name => $expression) {
            if (is_array($expression)) {
                if ($this->_getInterpreterResult($message, $expression)) {
                    return true;
                }
            } elseif (preg_match($expression, $message->head)) {
                //here should match at least one of plenty
                return true;
            }
        }
        return false;
    }

    /**
     * Get result by external matching
     *
     * @param Message $message
     * @param array $config
     * @return bool
     * @throws \PreCommit\Exception
     */
    protected function _getInterpreterResult($message, array $config)
    {
        $result = null;
        if (!$message->verb) {
            $result = $this->_getInterpreter($config)
                ->interpret(array('message' => $message));

            /**
             * Set interpreted keys to message object
             */
            if (!empty($result['verb'])) {
                $message->verb = $result['verb'];
            }
            if (!empty($result['issue_key'])) {
                $message->issueKey = $result['issue_key'];
            }
            if (!empty($result['summary'])) {
                $message->summary = $result['summary'];
            }
        }

        //Initialize issue adapter
        if (!$message->issue && $message->issueKey) {
            $message->issue = Issue::factory($message->issueKey);
        }

        /**
         * Check empty required keys from interpreted result
         */
        if ($result) {
            foreach ($this->_getRequiredKeys() as $name => $enabled) {
                if (!$enabled) {
                    continue;
                }
                if (!isset($result[$name]) || !$result[$name]) {
                    //$this->_addError('Commit Message', self::CODE_VERB_INCORRECT, $name);
                    return false;
                }
            }
        }

        /**
         * Check verb is allowed for issue type
         */
        if ($message->verb) {
            //find verb key
            $key = array_search($message->verb, $this->_getVerbs());
            if (false === $key) {
                //$this->_addError('Commit Message', self::CODE_VERB_NOT_FOUND, $message->verb);
                return false;
            }

            //check allowed verb by issue type
            $allowed = $this->_getAllowedVerbs($message->issue->getType());
            if ($message->issue && (!isset($allowed[$key]) || !$allowed[$key])) {
                //$this->_addError('Commit Message', self::CODE_VERB_INCORRECT, $message->verb);
                return false;
            }
        }
        return true;
    }

    /**
     * Get verbs map
     *
     * @param string $type
     * @return array
     */
    protected function _getAllowedVerbs($type)
    {
        return (array)$this->_getConfig()->getNodeArray('validators/IssueType/issue/verb/allowed/'
                                                        . $this->_type . '/' . $type)
            ?: (array)$this->_getConfig()->getNodeArray('validators/IssueType/issue/verb/allowed/default/'
                                                        . $type);
    }

    /**
     * Get verbs map
     *
     * @return array
     */
    protected function _getVerbs()
    {
        return (array)$this->_getConfig()->getNodeArray('hooks/commit-msg/message/verb/list/' . $this->_type)
            ?: (array)$this->_getConfig()->getNodeArray('hooks/commit-msg/message/verb/list/default');
    }

    /**
     * Get regular expressions to match
     *
     * @return array|null
     */
    protected function _getExpressions()
    {
        return $this->_getConfig()->getNodeArray('validators/CommitMessage/match');
    }

    /**
     * Get required keys
     *
     * @return array|null
     */
    protected function _getRequiredKeys()
    {
        return $this->_getConfig()->getNodeArray('validators/CommitMessage/match/full/required');
    }

    /**
     * Get interpreter
     *
     * @param array $config
     * @return \PreCommit\Interpreter\InterpreterInterface
     * @throws Exception
     */
    protected function _getInterpreter(array $config)
    {
        if (empty($config['interpreter']['class'])) {
            throw new Exception('Interpreter class is not set.');
        }
        /** @var InterpreterInterface $interpreter */
        if (empty($config['interpreter']['options'])) {
            return new $config['interpreter']['class'];
        } else {
            return new $config['interpreter']['class'](
                $config['interpreter']['options']
            );
        }
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
