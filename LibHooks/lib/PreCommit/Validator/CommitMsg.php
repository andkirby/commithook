<?php
namespace PreCommit\Validator;

use PreCommit\Config;

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
     */
    protected function _matchMessage($content)
    {
        foreach ($this->_getRegularExpressions() as $regular) {
            //here should match at least one of plenty
            if (preg_match($regular, $content)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get regular expressions to match
     *
     * @return array|null
     */
    protected function _getRegularExpressions()
    {
        return $this->_getConfig()->getNodeArray('validators/CommitMessage/match');
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
