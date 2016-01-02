<?php
/**
 * @license https://raw.githubusercontent.com/andkirby/commithook/master/LICENSE.md
 */
namespace PreCommit\Validator;

/**
 * Class of stub validator
 *
 * @package PreCommit\Validator
 */
class Stub extends AbstractValidator
{
    /**
     * Stub validation method
     *
     * @param string $content
     * @param string $file
     * @return bool
     */
    public function validate($content, $file)
    {
        return true;
    }
}
