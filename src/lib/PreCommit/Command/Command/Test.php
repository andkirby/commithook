<?php
/**
 * @license https://raw.githubusercontent.com/andkirby/commithook/master/LICENSE.md
 */
namespace PreCommit\Command\Command;

use PreCommit\Command\Exception;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * CommitHooks command tester
 *
 * It will test all modified files
 *
 * @package PreCommit\Command
 */
class Test extends AbstractCommand
{
    /**
     * Init default helpers
     *
     * @return $this
     */
    protected function configureCommand()
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
        parent::execute($input, $output);
        !defined('TEST_MODE') && define('TEST_MODE', true);
        /** @noinspection PhpUnusedLocalVariableInspection */
        $hookFile = $this->askProjectDir($input, $output).'/.git/hooks/pre-commit';
        require_once __DIR__.'/../../../../runner.php';

        return 0;
    }
}