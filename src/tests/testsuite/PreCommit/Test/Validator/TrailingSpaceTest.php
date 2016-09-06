<?php
/**
 * @license https://raw.githubusercontent.com/andkirby/commithook/master/LICENSE.md
 */
namespace PreCommit\Test\Validator;

use PreCommit\Config;
use PreCommit\Processor;
use PreCommit\Validator\TrailingSpace;
use PreCommit\Vcs\Git;

/**
 * Class test for Processor
 */
class TrailingSpaceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Php file for text hooks
     *
     * @var string
     */
    protected static $fileTest = 'tests/testsuite/PreCommit/Test/_fixture/file-have-trailing-space-and-dont-have-last-empty.php';

    /**
     * Test model
     *
     * @var Processor\PreCommit
     */
    protected static $model;

    /**
     * Set up test model
     */
    public static function setUpBeforeClass()
    {
        //init config object
        Config::initInstance(['file' => PROJECT_ROOT.'/config/root.xml']);
        Config::setSrcRootDir(PROJECT_ROOT);
        $vcsAdapter = self::getVcsAdapterMock();

        /** @var Processor\PreCommit $processor */
        $processor = Processor::factory('pre-commit', $vcsAdapter);
        $processor->setCodePath(PROJECT_ROOT)
            ->setFiles([self::$fileTest]);
        $processor->process();
        self::$model = $processor;
    }

    /**
     * Test CODE_PHP_OPERATOR_SPACES_MISSED
     */
    public function testExistTrailingSpaces()
    {
        $errors = $this->getSpecificErrorsList(
            self::$fileTest,
            TrailingSpace::CODE_PHP_REDUNDANT_TRAILING_SPACES
        );

        $expected = [3];
        $this->assertEquals($expected, $errors);
    }

    /**
     * Test CODE_PHP_OPERATOR_SPACES_MISSED
     */
    public function testNotExistsTrailingLine()
    {
        $errors = $this->getSpecificErrorsList(
            self::$fileTest,
            TrailingSpace::CODE_PHP_NO_END_TRAILING_LINE
        );

        $this->assertCount(1, $errors);
    }

    /**
     * Test finding trailing space and not exist trailing spaces (full test)
     */
    public function testFindTrailingLineAndNotExistTrailingSpaces()
    {
        $errorCollector = $this->getMock(
            '\PreCommit\Processor\ErrorCollector',
            ['addError']
        );
        $errorCollector->expects($this->never())->method('addError');
        $str = <<<CONTENT
<?php
\$space = 1;
\$tab = 2;
\$noTail = 33;

CONTENT;

        $validator = new TrailingSpace(['errorCollector' => $errorCollector]);
        $validator->validate($str, '');
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
