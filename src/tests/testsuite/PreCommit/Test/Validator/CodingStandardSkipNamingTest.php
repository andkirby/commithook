<?php
/**
 * @license https://raw.githubusercontent.com/andkirby/commithook/master/LICENSE.md
 */
namespace PreCommit\Test\Validator;

use PreCommit\Config;
use PreCommit\Processor;
use PreCommit\Validator\CodingStandard;

/**
 * Class test for Processor
 */
class CodingStandardSkipNamingTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Php file for text hooks
     *
     * @var string
     */
    static protected $_classTest = 'tests/testsuite/PreCommit/Test/_fixture/TestClassSkipNaming.php';

    /**
     * Test model
     *
     * @var Processor\PreCommit
     */
    static protected $_model;

    /**
     * Set up test model
     */
    static public function setUpBeforeClass()
    {
        //init config object
        Config::getInstance(array('file' => PROJECT_ROOT . '/config.xml'));
        Config::setSrcRootDir(PROJECT_ROOT);
        Config::mergeExtraConfig();

        $vcsAdapter = self::_getVcsAdapterMock();

        /** @var Processor\PreCommit $processor */
        $processor = Processor::factory('pre-commit', $vcsAdapter);
        $processor->setCodePath(PROJECT_ROOT)
            ->setFiles(array(self::$_classTest));
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
     * @param object $model
     * @return array
     * @throws \PHPUnit_Framework_Exception
     */
    protected function _getSpecificErrorsList($file, $code, $returnLines = false, $model = null)
    {
        if (!$model) {
            $model = self::$_model;
        }
        $errors = $model->getErrors();
        if (!isset($errors[$file])) {
            return array();
        }
        $errors = $errors[$file];

        if (!isset($errors[$code])) {
            return array();
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
     * Test skip method naming validation
     */
    public function testSkipPublicFunctionNaming()
    {
        $errors = $this->_getSpecificErrorsList(
            self::$_classTest,
            CodingStandard::CODE_PHP_PUBLIC_METHOD_NAMING_INVALID
        );
        $this->assertEmpty(array_values($errors));

        $errors = $this->_getSpecificErrorsList(
            self::$_classTest,
            CodingStandard::CODE_PHP_PROTECTED_METHOD_NAMING_INVALID
        );
        $this->assertEmpty(array_values($errors));
    }
}
