<?php
/**
 * @license https://raw.githubusercontent.com/andkirby/commithook/master/LICENSE.md
 */

namespace PreCommit\Filter\License;

/**
 * Class SimpleCode
 * This filter responsible for adding license block into simple code files
 *
 * @package PreCommit\Filter\License
 */
class SimpleCode extends AbstractAdapter
{
    /**
     * {@inheritdoc}
     */
    public function getWrapStringBeforeLicense()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getWrapStringAfterLicense()
    {
        return ''; //add extra empty line
    }

    /**
     * {@inheritdoc}
     */
    protected function getInputContentBeforeLicense()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    protected function getInputContentAfterLicense()
    {
        return null;
    }
}
