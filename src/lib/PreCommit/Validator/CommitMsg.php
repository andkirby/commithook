<?php
/**
 * @license https://raw.githubusercontent.com/andkirby/commithook/master/LICENSE.md
 */
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
    const CODE_ISSUE_TYPE_INCORRECT = 'badIssueType';
    const CODE_VERB_NOT_FOUND = 'commitVerbNotFound';
    const CODE_KEY_NOT_SET = 'commitKeyNotSet';
    /**#@-*/

    /**
     * Error messages
     *
     * @var array
     */
    protected $errorMessages
        = array(
            self::CODE_BAD_COMMIT_MESSAGE   => 'Head of commit message "%value%" has improper form.',
            self::CODE_VERB_INCORRECT       => 'Commit verb "%value%" is not suitable for the issue.',
            self::CODE_ISSUE_TYPE_INCORRECT => 'Issue type "%value%" is not suitable to check verb properly. Please take a look your configuration.',
            self::CODE_VERB_NOT_FOUND       => 'Commit verb "%value%" not found.',
            self::CODE_KEY_NOT_SET          => 'Required commit key "%value%" is not set.',
        );

    /**
     * Message interpreting type
     *
     * @var string
     */
    protected $type;

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
            $this->type = $options['type'];
        } else {
            $this->type = $this->getConfig()->getNode('hooks/commit-msg/message/type');
        }
        if (!$this->type) {
            throw new Exception('Type is not set.');
        }
    }

    /**
     * Checking for interpreter errors
     *
     * @param Message $message
     * @param string  $file
     * @param bool    $test    Make a test without adding errors
     * @return bool
     */
    public function validate($message, $file, $test = false)
    {
        $matched = $this->matchMessage($message);
        if ($test) {
            return $matched;
        }
        if (!$matched) {
            $this->addError('Commit Message', self::CODE_BAD_COMMIT_MESSAGE, $message->head);
        }

        return !$this->errorCollector->hasErrors();
    }

    /**
     * Match commit message
     *
     * @param Message $message
     * @return bool
     * @throws \PreCommit\Exception
     */
    protected function matchMessage($message)
    {
        foreach ($this->getExpressions() as $name => $expression) {
            if (is_array($expression)) {
                if ($this->getInterpreterResult($message, $expression)) {
                    return true;
                }
            } elseif (preg_match($expression, $message->body)) {
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
     * @param array   $config
     * @return bool
     * @throws \PreCommit\Exception
     */
    protected function getInterpreterResult($message, array $config)
    {
        $result = null;
        if (!$message->verb) {
            $result = $this->getInterpreter($config)
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

        if (!$message->issueKey) {
            //cannot recognize message format
            return false;
        }

        //Initialize issue adapter
        if (!$message->issue && $message->issueKey) {
            $message->issue = Issue::factory($message->issueKey);
        }

        /**
         * Check empty required keys from interpreted result
         *
         * At least issue key should be set after interpreting
         */
        if ($result) {
            foreach ($this->getRequiredKeys() as $name => $enabled) {
                if (!$enabled) {
                    continue;
                }
                if (!isset($result[$name]) || !$result[$name]) {
                    $this->addError('Commit Message', self::CODE_VERB_INCORRECT, $name);

                    return false;
                }
            }
        }

        /**
         * Check verb is allowed for issue type
         */
        if ($message->issue) {
            //find verb key
            $key = array_search($message->verb, $this->getVerbs());
            if (false === $key) {
                $this->addError('Commit Message', self::CODE_VERB_NOT_FOUND, $message->verb);

                return false;
            }

            //check allowed verb by issue type
            if (!$this->errorCollector->hasErrors()) {
                if (!$message->issue->getType()) {
                    $this->addError(
                        'Commit Message',
                        self::CODE_ISSUE_TYPE_INCORRECT,
                        $message->issue->getOriginalType()
                    );

                    return false;
                }
                //it's cannot be processed if issue type is not valid
                $allowed = $this->getAllowedVerbs($message->issue->getType());
                if (!isset($allowed[$key]) || !$allowed[$key]) {
                    $this->addError('Commit Message', self::CODE_VERB_INCORRECT, $message->verb);

                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Get verbs map
     *
     * @param string $type
     * @return array
     * @throws \PreCommit\Exception
     */
    protected function getAllowedVerbs($type)
    {
        if (!$type) {
            throw new Exception('Empty issue type.');
        }

        $verbs = (array) $this->getConfig()->getNodeArray(
            'validators/IssueType/issue/verb/allowed/'.$this->type.'/'.$type
        );
        if (!$verbs) {
            $verbs = (array) $this->getConfig()->getNodeArray(
                'validators/IssueType/issue/verb/allowed/default/'.$type
            );
        }

        return $verbs;
    }

    /**
     * Get verbs map
     *
     * @return array
     */
    protected function getVerbs()
    {
        $verbs = (array) $this->getConfig()->getNodeArray('hooks/commit-msg/message/verb/list/'.$this->type);
        if (!$verbs) {
            $verbs = (array) $this->getConfig()->getNodeArray('hooks/commit-msg/message/verb/list/default');
        }

        return $verbs;
    }

    /**
     * Get regular expressions to match
     *
     * @return array|null
     */
    protected function getExpressions()
    {
        return $this->getConfig()->getNodeArray('validators/CommitMessage/match');
    }

    /**
     * Get required keys
     *
     * @return array|null
     */
    protected function getRequiredKeys()
    {
        return $this->getConfig()->getNodeArray('validators/CommitMessage/match/full/required');
    }

    /**
     * Get interpreter
     *
     * @param array $config
     * @return \PreCommit\Interpreter\InterpreterInterface
     * @throws Exception
     */
    protected function getInterpreter(array $config)
    {
        if (empty($config['interpreter']['class'])) {
            throw new Exception('Interpreter class is not set.');
        }
        $class = $config['interpreter']['class'];
        /** @var InterpreterInterface $interpreter */
        if (empty($config['interpreter']['options'])) {
            return new $class();
        } else {
            return new $class(
                $config['interpreter']['options']
            );
        }
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
