<?php
/**
 * @license https://raw.githubusercontent.com/andkirby/commithook/master/LICENSE.md
 */
namespace PreCommit\Validator;

/**
 * Class FileFilter validator
 *
 * @package PreCommit\Validator
 */
class IgnoreContentFilter extends AbstractValidator
{
    /**
     * Check if used tag for ignoring content
     *
     * @param string $content
     * @param string $file
     * @return bool
     */
    public function validate($content, $file)
    {
        return false === strpos($content, '// @codingStandardsIgnoreFile');
    }
}
