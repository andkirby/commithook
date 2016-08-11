<?php
/**
 * @license https://raw.githubusercontent.com/andkirby/commithook/master/LICENSE.md
 */
namespace PreCommit\Command;

use PreCommit\Console\Command\Install\Install;

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
        //install commit hooks
        // @codingStandardsIgnoreStart
        $output = `cd {$this->tmp} && git init && php -f {$this->bin}/commithook.php install -n`;
        // @codingStandardsIgnoreEnd

        //test success output
        $this->assertContains('PHP CommitHook files have been created.', $output);

        $preCommitFile = $this->tmp.'/.git/hooks/pre-commit';
        $commitMsgFile = $this->tmp.'/.git/hooks/commit-msg';

        //test commit hooks files are exist
        $this->assertFileExists($preCommitFile);
        $this->assertFileExists($commitMsgFile);

        //test commit hook file contains proper body
        $install = new Install('');
        $phpExe = $install->getSystemPhpPath();

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
            $this->fail('Temp directory not found.');
        }
        if (!$this->bin) {
            $this->fail('"bin" directory is not set.');
        }
        // @codingStandardsIgnoreStart
        if (false === strpos(`git --version 2>&1`, 'git version')) {
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
            `rm -rf {$this->tmp}/*`;
            // @codingStandardsIgnoreEnd
        }
        parent::tearDown();
    }
}
