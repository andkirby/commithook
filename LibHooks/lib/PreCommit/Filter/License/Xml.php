<?php
namespace PreCommit\Filter\License;

/**
 * Class Phtml
 * This filter responsible for adding license block into XML files
 *
 * @package PreCommit\Filter\License
 */
class Xml extends AbstractAdapter
{
    /**
     * {@inheritdoc}
     */
    protected function getInputContentBeforeLicense()
    {
        return '<?xml';
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
        return '<!--';
    }

    /**
     * {@inheritdoc}
     */
    public function getWrapStringAfterLicense()
    {
        return '-->';
    }
}
