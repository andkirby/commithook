<?php
/**
 * @license https://raw.githubusercontent.com/andkirby/commithook/master/LICENSE.md
 */

namespace PreCommit\Filter\License;

/**
 * Class Coffee (CoffeeScript files)
 * This filter responsible for adding license block into .coffee files
 *
 * @package PreCommit\Filter\License
 */
class Coffee extends AbstractAdapter
{
    /**
     * Set content. Remove start/end of comment block not allow in CoffeeScript
     *
     * @param string $content
     * @return $this
     */
    public function setContent($content)
    {
        $content = str_replace("/**\n", '', $content);
        $content = str_replace("\n */", '', $content);

        return parent::setContent($content);
    }

    /**
     * {@inheritdoc}
     */
    public function getWrapStringBeforeLicense()
    {
        return '###';
    }

    /**
     * {@inheritdoc}
     */
    public function getWrapStringAfterLicense()
    {
        return '###';
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
