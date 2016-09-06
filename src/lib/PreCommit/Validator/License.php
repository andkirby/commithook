<?php
/**
 * @license https://raw.githubusercontent.com/andkirby/commithook/master/LICENSE.md
 */

namespace PreCommit\Validator;

use PreCommit\Helper\License as LicenseHelper;

/**
 * Class to validate license block in files
 *
 * @package PreCommit\Validator
 */
class License extends AbstractValidator
{
    /**
     * Error code
     */
    const CODE_MISSED_LICENSE = 'missedLicenseBlock';

    /**
     * Error messages
     *
     * @var array
     */
    protected $errorMessages = [
        self::CODE_MISSED_LICENSE => 'Missed license block.',
    ];

    /**
     * Validate content
     *
     * @param string $content
     * @param string $file
     * @return bool
     */
    public function validate($content, $file)
    {
        $helper = new LicenseHelper();
        if (!$helper->isLicenseRequired($file)) {
            return true;
        }

        if (!$helper->contentHasLicense($content)) {
            $this->addError($file, self::CODE_MISSED_LICENSE);
        }

        return !$this->errorCollector->hasErrors();
    }
}
