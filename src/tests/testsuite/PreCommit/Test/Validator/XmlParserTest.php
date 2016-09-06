<?php
/**
 * @license https://raw.githubusercontent.com/andkirby/commithook/master/LICENSE.md
 */
namespace PreCommit\Test\Validator;

use PreCommit\Config;
use PreCommit\Processor;
use PreCommit\Processor\PreCommit;
use PreCommit\Validator\XmlParser;
use PreCommit\Vcs\Git;

/**
 * Class test for Processor
 */
class XmlParserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Php file for text hooks
     *
     * @var string
     */
    static protected $fileTest = 'tests/testsuite/PreCommit/Test/_fixture/empty.xml';

    /**
     * Test model
     *
     * @var \PreCommit\Processor\PreCommit
     */
    static protected $model;

    /**
     * Set up test model
     */
    public static function setUpBeforeClass()
    {
        //init config object
        Config::initInstance(['file' => PROJECT_ROOT.'/config/root.xml']);
        Config::setSrcRootDir(PROJECT_ROOT);
        $vcsAdapter = self::getVcsAdapterMock();

        /** @var PreCommit $processor */
        $processor = Processor::factory('pre-commit', $vcsAdapter);
        $processor->setCodePath(PROJECT_ROOT)
            ->setFiles([self::$fileTest]);
        $processor->process();
        self::$model = $processor;
    }

    /**
     * Test CODE_PHP_OPERATOR_SPACES_MISSED
     */
    public function testEmptyFile()
    {
        $errors = $this->getSpecificErrorsList(
            self::$fileTest,
            XmlParser::CODE_XML_ERROR
        );

        $expected = 'Empty string supplied as input';
        $this->assertContains($expected, array_shift($errors));
    }

    /**
     * Get VCS adapter mock
     *
     * @return object
     */
    protected static function getVcsAdapterMock()
    {
        $vcsAdapter = new Git();
        $vcsAdapter->setAffectedFiles([]);

        return $vcsAdapter;
    }

    /**
     * Get specific errors list
     *
     * @param string $file
     * @param string $code
     * @param bool   $returnLines
     * @return array
     * @throws \PHPUnit_Framework_Exception
     */
    protected function getSpecificErrorsList($file, $code, $returnLines = false)
    {
        $errors = self::$model->getErrors();
        if (!isset($errors[$file])) {
            throw new \PHPUnit_Framework_Exception('Errors for file '.self::$fileTest.' not found.');
        }
        $errors = $errors[$file];

        $this->assertArrayHasKey($code, $errors);
        if (!isset($errors[$code])) {
            throw new \PHPUnit_Framework_Exception("Errors for code $code not found.");
        }

        $list = [];
        $key  = $returnLines ? 'line' : 'value';
        foreach ($errors[$code] as $item) {
            if ($key == 'value' && isset($item['line'])) {
                $list[$item['line']] = $item[$key];
            } else {
                $list[] = $item[$key];
            }
        }

        return $list;
    }
}
