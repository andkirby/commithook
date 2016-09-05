<?php
/**
 * @license https://raw.githubusercontent.com/andkirby/commithook/master/LICENSE.md
 */
namespace PreCommit\Test\Validator;

use PreCommit\Config;
use PreCommit\Processor;
use PreCommit\Validator\UnresolvedConflict;

/**
 * Class test UnresolvedConflictTest
 */
class UnresolvedConflictTest extends \PHPUnit_Framework_TestCase
{
    /**
     * File to test hooks
     *
     * @var string
     */
    protected static $fileTest = 'tests/testsuite/PreCommit/Test/_fixture/TestGitConflict.css';

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
        Config::initInstance(['file' => PROJECT_ROOT.'/commithook.xml']);
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
     * Test MERGE_CONFLICT
     */
    public function testConflictFinding()
    {
        $errors = $this->getSpecificErrorsList(
            self::$fileTest,
            UnresolvedConflict::MERGE_CONFLICT
        );
        $this->assertCount(1, $errors);
    }

    /**
     * Get VCS adapter mock
     *
     * @return object
     */
    protected static function getVcsAdapterMock()
    {
        $generator  = new \PHPUnit_Framework_MockObject_Generator();
        $vcsAdapter = $generator->getMock('PreCommit\Vcs\Git');
        $vcsAdapter->expects(self::once())
            ->method('getAffectedFiles')
            ->will(self::returnValue([]));

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
