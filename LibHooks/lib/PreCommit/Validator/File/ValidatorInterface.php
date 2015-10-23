<?php
namespace PreCommit\Validator\File;

/**
 * Class InterfaceFilter
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
