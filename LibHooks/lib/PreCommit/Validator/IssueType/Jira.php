<?php
namespace PreCommit\Validator\IssueType;
use PreCommit\Validator\AbstractValidator;

/**
 * Class code style validator
 *
 * @package PreCommit\Validator
 */
class Jira extends AbstractValidator
{
    /**#@+
     * Error codes
     */
    const CODE_WRONG_TYPE = 'jiraWrongIssueType';
    /**#@-*/

    /**
     * Error messages
     *
     * @var array
     */
    protected $_errorMessages = array(
        self::CODE_WRONG_TYPE => "Issue type '%value%' cannot have commits. Please find another issue or change issue type.",
    );

    /**
     * Validate JIRA issue type
     *
     * @param string $content
     * @param string $file
     * @return bool
     */
    public function validate($content, $file)
    {
        return true;
        $this->_addError('Commit Message', self::CODE_WRONG_TYPE, 'issue-type-here');
        return !$this->_errorCollector->hasErrors();
    }
}
