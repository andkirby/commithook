<?php
/**
 * @license https://raw.githubusercontent.com/andkirby/commithook/master/LICENSE.md
 */
namespace PreCommit\Validator;

/**
 * Class unresolved conflict validator
 *
 * @package PreCommit\Validator
 */
class UnresolvedConflict extends AbstractValidator
{
    /**#@+
     * Error codes
     */
    const MERGE_CONFLICT = 'mergeConflictGarbage';

    /**#@-*/

    /**
     * Error messages
     *
     * @var array
     */
    protected $errorMessages
        = array(
            self::MERGE_CONFLICT => 'File contains unresolved VCS conflict.',
        );

    /**
     * Check exists VCS conflicts
     *
     * @param string $content
     * @param string $file
     * @return bool
     */
    public function validate($content, $file)
    {
        $this->validateGitConflict($content, $file);

        return !$this->errorCollector->hasErrors();
    }

    /**
     * Checks if there is any lines of conflict block
     *
     * @param string $content
     * @param string $file
     * @return $this
     */
    protected function validateGitConflict($content, $file)
    {
        //checking for windows line breaks
        if (strpos($content, '<<<<<<<'.' HEAD') || strpos($content, "\n>>>>>>> ")) {
            $this->addError(
                $file,
                self::MERGE_CONFLICT
            );
        }

        return $this;
    }
}
