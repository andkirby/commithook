<?php
/**
 * @license https://raw.githubusercontent.com/andkirby/commithook/master/LICENSE.md
 */

namespace PreCommit\Filter\License;

/**
 * Class Sh (shell, bash files)
 * This filter responsible for adding license block into shell files
 *
 * @package PreCommit\Filter\License
 */
class Sh extends AbstractAdapter
{
    /**
     * {@inheritdoc}
     */
    public function getWrapStringBeforeLicense()
    {
        return ': <<\'LICENSE_BLOCK\'';
    }

    /**
     * {@inheritdoc}
     */
    public function getWrapStringAfterLicense()
    {
        return 'LICENSE_BLOCK';
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
