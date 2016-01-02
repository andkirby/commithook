<?php
/**
 * @license https://raw.githubusercontent.com/andkirby/commithook/master/LICENSE.md
 */

namespace PreCommit\Filter\License;

/**
 * Class AbstractSimpleText
 * This filter responsible for adding license block into simple text files
 *
 * @package PreCommit\Filter\License
 */
abstract class AbstractSimpleText extends AbstractAdapter
{
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
}
