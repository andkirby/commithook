<?php
namespace PreCommit\Validator;

use PreCommit\Config;
use PreCommit\Exception;
use PreCommit\Interpreter\InterpreterInterface;

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
        if (!$this->_matchMessage($content)) {
            $this->_addError('Commit Message', self::CODE_BAD_COMMIT_MESSAGE, $content);
        }
        return !$this->_errorCollector->hasErrors();
    }

    /**
     * Match commit message
     *
     * @param string $content
     * @return bool
     * @throws \PreCommit\Exception
     */
    protected function _matchMessage($content)
    {
        foreach ($this->_getExpressions() as $expression) {
            if (is_array($expression)) {
                if ($this->_getInterpreterResult($content, $expression)) {
                    return true;
                }
            } elseif (preg_match($expression, $content)) {
                //here should match at least one of plenty
                return true;
            }
        }
        return false;
    }

    /**
     * Get result by external matching
     *
     * @param string $content
     * @param array $config
     * @return bool
     * @throws \PreCommit\Exception
     */
    protected function _getInterpreterResult($content, array $config)
    {
        $result = $this->_getInterpreter($config)
            ->interpret(array('message' => $content));

        foreach ($this->_getRequiredKeys() as $name => $enabled) {
            if (!$enabled) {
                continue;
            }
            if (!isset($result[$name]) || !$result[$name]) {
                return false;
            }
        }
        return true;
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
