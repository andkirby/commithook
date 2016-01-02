<?php
namespace PreCommit\Filter\License;

/**
 * Class Php
 * This filter responsible for adding license block into PHP files
 *
 * @package PreCommit\Filter\License
 */
class Php extends AbstractAdapter
{
    /**
     * {@inheritdoc}
     */
    protected function getInputContentBeforeLicense()
    {
        return '<?php';
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
        return ''; //add one line delimiter
    }
}
