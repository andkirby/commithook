<?php
namespace PreCommit\Filter\License;

/**
 * Class Phtml
 * This filter responsible for adding license block into PHTML files
 *
 * @package PreCommit\Filter\License
 */
class Phtml extends AbstractAdapter
{
    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        $this->exceptionOnMissedInputContent = false;
    }

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
        return '?>';
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
        return null;
    }
}
