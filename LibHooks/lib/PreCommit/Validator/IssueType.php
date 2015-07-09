<?php
namespace PreCommit\Validator;

/**
 * Class code style validator
 *
 * @package PreCommit\Validator
 */
class IssueType extends AbstractValidator
{
    /**
     * Adopted tracker validator
     *
     * @var AbstractValidator
     */
    protected $_adoptedValidator;

    /**
     * Init adopted validator
     *
     * @param array $options
     * @throws \PreCommit\Exception
     */
    public function __construct(array $options)
    {
        $this->_adoptedValidator = new IssueType\Jira($options);
        parent::__construct($options);
    }

    /**
     * Validate issue type
     *
     * Actually call to adopted validator
     *
     * @param string $content
     * @param string $file
     * @return bool
     */
    public function validate($content, $file)
    {
        return $this->_adoptedValidator->validate($content, $file);
    }
}
