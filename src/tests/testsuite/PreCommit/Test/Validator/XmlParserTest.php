<?php
/**
 * @license https://raw.githubusercontent.com/andkirby/commithook/master/LICENSE.md
 */
namespace PreCommit\Test\Validator;
use PreCommit\Processor;
use PreCommit\Processor\PreCommit;
use PreCommit\Config;
use PreCommit\Validator\XmlParser;

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
    static protected $_fileTest = 'tests/testsuite/PreCommit/Test/_fixture/empty.xml';

    /**
     * Test model
     *
     * @var \PreCommit\Processor\PreCommit
     */
    static protected $_model;

    /**
     * Set up test model
     */
    static public function setUpBeforeClass()
    {
        //init config object
        Config::getInstance(array('file' => PROJECT_ROOT . '/commithook.xml'));
        Config::setSrcRootDir(PROJECT_ROOT);
        $vcsAdapter = self::_getVcsAdapterMock();

        /** @var PreCommit $processor */
        $processor = Processor::factory('pre-commit', $vcsAdapter);
        $processor->setCodePath(PROJECT_ROOT)
            ->setFiles(array(self::$_fileTest));
        $processor->process();
        self::$_model = $processor;
    }

    /**
     * Get VCS adapter mock
     *
     * @return object
     */
    protected static function _getVcsAdapterMock()
    {
        $generator = new \PHPUnit_Framework_MockObject_Generator();
        $vcsAdapter = $generator->getMock('PreCommit\Vcs\Git');
        $vcsAdapter->expects(self::once())
            ->method('getAffectedFiles')
            ->will(self::returnValue(array()));
        return $vcsAdapter;
    }

    /**
     * Get specific errors list
     *
     * @param string $file
     * @param string $code
     * @param bool $returnLines
     * @return array
     * @throws \PHPUnit_Framework_Exception
     */
    protected function _getSpecificErrorsList($file, $code, $returnLines = false)
    {
        $errors = self::$_model->getErrors();
        if (!isset($errors[$file])) {
            throw new \PHPUnit_Framework_Exception('Errors for file ' . self::$_fileTest . ' not found.');
        }
        $errors = $errors[$file];

        $this->assertArrayHasKey($code, $errors);
        if (!isset($errors[$code])) {
            throw new \PHPUnit_Framework_Exception("Errors for code $code not found.");
        }

        $list = array();
        $key = $returnLines ? 'line' : 'value';
        foreach ($errors[$code] as $item) {
            if ($key == 'value' && isset($item['line'])) {
                $list[$item['line']] = $item[$key];
            } else {
                $list[] = $item[$key];
            }
        }
        return $list;
    }

    /**
     * Test CODE_PHP_OPERATOR_SPACES_MISSED
     */
    public function testEmptyFile()
    {
        $errors = $this->_getSpecificErrorsList(
            self::$_fileTest,
            XmlParser::CODE_XML_ERROR
        );

        $expected = array (
            'DOMDocument::loadXML(): Empty string supplied as input',
        );
        $this->assertEquals($expected, array_values($errors));
    }
}
