<?php
/**
 * @license https://raw.githubusercontent.com/andkirby/commithook/master/LICENSE.md
 */
namespace PreCommit\Validator\File;

/**
 * Class FilterInterface
 *
 * @package PreCommit\Filter
 */
interface ValidatorInterface
{
    /**
     * Validate file content
     *
     * @param string $content
     * @param string $file
     * @return bool
     */
    public function validate($content, $file = null);
}
