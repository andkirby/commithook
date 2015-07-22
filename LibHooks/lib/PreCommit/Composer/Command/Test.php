<?php
namespace PreCommit\Composer\Command;

use PreCommit\Composer\Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * CommitHooks files remover
 *
 * @package PreCommit\Composer
 */
class Test extends Command
{
    /**
     * Init default helpers
     *
     * @return $this
     */
    protected function configure()
    {
        $this->setName('test');
        $this->setHelp(
            'This command can test your files before committing.'
        );
        $this->setDescription(
            'This command can test your files before committing.'
        );
        return $this;
    }

    /**
     * Execute command
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return int
     * @throws Exception
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        !defined('TEST_MODE') && define('TEST_MODE', true);
        $hookFile = '/pre-commit';
        require_once __DIR__ . '/../../../../runner.php';

        return 0;
    }
}
