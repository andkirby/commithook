<?php
/**
 * @license https://raw.githubusercontent.com/andkirby/commithook/master/LICENSE.md
 */
namespace PreCommit\Command;

use PreCommit\Command\Command\Install;

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
    protected $_tmp;

    /**
     * Bin directory
     *
     * @var string
     */
    protected $_bin;

    /**
     * Test hook files installation
     */
    public function testInstall()
    {
        //install commit hooks
        $output = `cd {$this->_tmp} && git init && php -f {$this->_bin}/commithook.php install -n`;

        //test success output
        $this->assertContains('PHP CommitHook files have been created.', $output);

        $preCommitFile = $this->_tmp . '/.git/hooks/pre-commit';
        $CommitMsgFile = $this->_tmp . '/.git/hooks/commit-msg';

        //test commit hooks files are exist
        $this->assertFileExists($preCommitFile);
        $this->assertFileExists($CommitMsgFile);

        //test commit hook file contains proper body
        $install = new Install('');
        $phpExe = $install->getSystemPhpPath();

        $ds = DIRECTORY_SEPARATOR;
        $body = <<<BODY
#!/usr/bin/env $phpExe
<?php
\$hookName = __FILE__;
require_once '{$this->_bin}{$ds}runner.php';
BODY;
        $this->assertEquals(
            $body, file_get_contents($preCommitFile)
        );
        $this->assertEquals(
            $body, file_get_contents($CommitMsgFile)
        );
    }

    /**
     * Set work directories
     */
    protected function setUp()
    {
        $this->_bin = realpath(__DIR__ . '/../../../../../bin');
        $this->_tmp = realpath(__DIR__ . '/../../../tmp');
        if (!$this->_tmp) {
            $this->fail('Temp directory not found.');
        }
        if (!$this->_bin) {
            $this->fail('"bin" directory is not set.');
        }
        if (false === strpos(`git --version 2>&1`, 'git version')) {
            $this->fail('VCS GIT console command not found.');
        }
    }

    /**
     * Clean up directory
     */
    protected function tearDown()
    {
        if (is_dir($this->_tmp)) {
            `rm -rf {$this->_tmp}/*`;
        }
        parent::tearDown();
    }
}
