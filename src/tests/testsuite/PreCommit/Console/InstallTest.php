<?php
/**
 * @license https://raw.githubusercontent.com/andkirby/commithook/master/LICENSE.md
 */
namespace PreCommit\Console;

use PreCommit\Console\Command\Install\Install;
use Rikby\Console\Helper\Shell\ShellHelper;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Test CLI install command
 *
 * @package PreCommit\Command
 */
class InstallTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Temp directory
     *
     * @var string
     */
    protected $tmp;

    /**
     * Bin directory
     *
     * @var string
     */
    protected $bin;

    /**
     * Test hook files installation
     */
    public function testInstall()
    {
        //test commit hook file contains proper body
        $install = new Install('');
        $phpExe  = $install->getSystemPhpPath();

        //install commit hooks
        $gitBin = GIT_BIN;
        $output = $this->getShellHelper()->shellExec(
            "cd {$this->tmp} && {$gitBin} init && $phpExe -f {$this->bin}/commithook.php install -n",
            true,
            ''
        );

        //test success output
        $this->assertContains('PHP CommitHook files have been created.', $output);

        $preCommitFile = $this->tmp.'/.git/hooks/pre-commit';
        $commitMsgFile = $this->tmp.'/.git/hooks/commit-msg';

        //test commit hooks files are exist
        $this->assertFileExists($preCommitFile);
        $this->assertFileExists($commitMsgFile);

        $ds = DIRECTORY_SEPARATOR;
// @codingStandardsIgnoreStart
        $body = <<<BODY
#!/usr/bin/env $phpExe
<?php
\$hookName = __FILE__;
require_once '{$this->bin}{$ds}runner.php';
BODY;
// @codingStandardsIgnoreEnd
        $this->assertEquals(
            $body,
            file_get_contents($preCommitFile)
        );
        $this->assertEquals(
            $body,
            file_get_contents($commitMsgFile)
        );
    }

    /**
     * Set work directories
     */
    protected function setUp()
    {
        $this->bin = realpath(__DIR__.'/../../../../../bin');
        $this->tmp = realpath(__DIR__.'/../../../tmp');
        if (!$this->tmp) {
            $this->fail("'Temp directory '{$this->tmp}' not found.'");
        }
        if (!$this->bin) {
            $this->fail('"bin" directory is not set.');
        }
        // @codingStandardsIgnoreStart
        $gitBin = GIT_BIN;
        if (false === strpos(`$gitBin --version 2>&1`, 'git version')) {
            $this->fail('VCS GIT console command not found.');
        }
        // @codingStandardsIgnoreEnd
    }

    /**
     * Clean up directory
     */
    protected function tearDown()
    {
        if (is_dir($this->tmp)) {
            // @codingStandardsIgnoreStart
            self::rrmdir($this->tmp, false, ['..', '.', '.gitignore']);
            // @codingStandardsIgnoreEnd
        }
        parent::tearDown();
    }

    /**k
     * Remove directory
     *
     * @param string $dir
     * @param bool   $remove
     * @param array  $ignore
     */
    protected static function rrmdir($dir, $remove = true, $ignore = ['..', '.'])
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if (!in_array($object, $ignore)) {
                    if (filetype($dir."/".$object) == "dir") {
                        self::rrmdir($dir."/".$object);
                    } else {
                        unlink($dir."/".$object);
                    }
                }
            }

            reset($objects);
            $remove && rmdir($dir);
        }
    }

    /**
     * Get shell helper
     *
     * @return ShellHelper
     */
    protected function getShellHelper()
    {
        $shell         = new ShellHelper();
//        $outputConsole = new ConsoleOutput();
//        $outputConsole->setVerbosity(OutputInterface::VERBOSITY_VERY_VERBOSE);
//        $shell->setOutput($outputConsole);

        return $shell;
    }
}
