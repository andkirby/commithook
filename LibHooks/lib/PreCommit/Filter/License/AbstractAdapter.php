<?php
namespace PreCommit\Filter\License;

use PreCommit\Exception;

/**
 * Class AbstractAdapter
 * It's a base functionality of license generator/filter
 *
 * @package PreCommit\Validator\License\Generator
 */
abstract class AbstractAdapter
{
    /**
     * File
     *
     * @var string
     */
    protected $content;

    /**
     * License
     *
     * @var string
     */
    protected $license;

    /**
     * License text for testing
     *
     * @var string
     */
    protected $testLicense;

    /**
     * The code which should be placed before license
     *
     * E.g. for XML it can be "<!--"
     *
     * @var string
     */
    protected $codeBeforeLicense = '';

    /**
     * The code which should be placed after license
     *
     * E.g. for XML it can be "-->"
     *
     * @var string
     */
    protected $codeAfterLicense = '';

    /**
     * The number of line of input content which should be placed before license
     *
     * @var int
     */
    protected $lineInputContent = 0;

    /**
     * Generate an exception when input content not found
     *
     * It could be redundant for example for PHTML where <?php ?> block should be set
     *
     * @var string
     */
    protected $exceptionOnMissedInputContent = true;

    /**
     * Use input content license block
     *
     * It should be useful for example for PHTML files
     * E.g. when file starts with HTML code only you need to add PHP block
     * <?php
     * /** license * /
     * ?>
     *
     * @var bool
     */
    protected $useInputContentInLicenseBlock = false;

    /**
     * Generate license in content
     *
     * @return string|null Return NULL when license is already set
     * @throws Exception
     */
    public function generate()
    {
        if ($this->testLicense()) {
            return null;
        }

        $this->init();

        $contentLines = $this->insertLicense(
            explode("\n", $this->getContent()),
            $this->getTargetLine()
        );

        return implode("\n", $contentLines);
    }

    /**
     * Test license in content
     *
     * @return bool
     */
    protected function testLicense()
    {
        return false !== strpos($this->getContent(), $this->getTestLicense());
    }

    /**
     * Initialize parameters for inserting license block
     *
     * @throws Exception
     */
    protected function init()
    {
        if ($this->getInputContentBeforeLicense()) {
            if ($this->lineInputContent === 0) {
                if (0 === strpos($this->getContent(), $this->getInputContentBeforeLicense())) {
                    $this->lineInputContent              = 0;
                    $this->useInputContentInLicenseBlock = false;
                } else {
                    if ($this->exceptionOnMissedInputContent) {
                        throw new Exception('Input block not found.');
                    }
                    $this->lineInputContent              = - 1;
                    $this->useInputContentInLicenseBlock = true;
                }
            } else {
                throw new Exception('This case not implemented yet.');
            }
        } else {
            $this->lineInputContent              = 0;
            $this->useInputContentInLicenseBlock = false;
        }
    }

    /**
     * Insert license
     *
     * @param array $contentLines
     * @param int   $targetLine
     * @return array
     */
    protected function insertLicense($contentLines, $targetLine)
    {
        if (0 === $targetLine) {
            $start = array();
            $end   = $contentLines;
        } else {
            $start = array_slice($contentLines, 0, $targetLine);
            $end   = array_slice($contentLines, $targetLine);
        }
        $contentLines = array_merge($start, array($this->getLicenseBlock()), $end);

        return $contentLines;
    }

    /**
     * Get content
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set content
     *
     * @param string $content
     * @return $this
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get target line
     *
     * @return int
     */
    protected function getTargetLine()
    {
        return $this->lineInputContent + 1;
    }

    /**
     * Get license block for testing it in content
     *
     * @return string
     */
    protected function getTestLicense()
    {
        if (!$this->testLicense) {
            return $this->getLicense();
        }

        return $this->testLicense;
    }

    /**
     * Set license
     *
     * @param string $text
     * @return $this
     */
    public function setTestLicense($text)
    {
        $this->testLicense = $text;

        return $this;
    }

    /**
     * Get input content which should be presented before license block
     *
     * @return string
     */
    abstract protected function getInputContentBeforeLicense();

    /**
     * Get complete license block
     *
     * @return string
     */
    protected function getLicenseBlock()
    {
        $block = $this->getLicense();

        //wrap block with default code (e.g. for XML <!-- and -->)
        if ($this->getWrapStringBeforeLicense()) {
            $block = $this->getWrapStringBeforeLicense()."\n".$block;
        }
        if ($this->getWrapStringAfterLicense()) {
            $block .= "\n".$this->getWrapStringAfterLicense();
        }

        //wrap block with input content
        if ($this->useInputContentInLicenseBlock) {
            if ($this->getInputContentBeforeLicense()) {
                $block = $this->getInputContentBeforeLicense()."\n".$block;
            }
            if ($this->getInputContentAfterLicense()) {
                $block .= "\n".$this->getInputContentAfterLicense();
            }
        }

        return $block;
    }

    /**
     * Get license
     *
     * @return string
     */
    public function getLicense()
    {
        return $this->license;
    }

    /**
     * Set license
     *
     * @param string $license
     * @return $this
     */
    public function setLicense($license)
    {
        $this->license = $license;

        return $this;
    }

    /**
     * Get wrap string before license
     *
     * @return string
     */
    abstract public function getWrapStringBeforeLicense();

    /**
     * Get wrap string after license
     *
     * @return string
     */
    abstract public function getWrapStringAfterLicense();

    /**
     * Get input content which should be presented before license block
     *
     * @return string
     */
    abstract protected function getInputContentAfterLicense();
}
