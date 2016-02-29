<?php
/**
 * @license https://raw.githubusercontent.com/andkirby/commithook/master/LICENSE.md
 */
namespace PreCommit\Command\Command;

use PreCommit\Command\Exception;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

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
     * Init input definitions
     *
     * @return $this
     */
    protected function configureInput()
    {
        parent::configureInput();

        $this->addArgument(
            'path',
            InputArgument::OPTIONAL,
            'Path to file/directory which should be tested.'
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

        $customFiles = $this->getCustomTestFiles($input, $output);

        /**
         * Define $vcsFiles for runner.php
         *
         * @see src/runner.php:87
         * @var array|null $vcsFiles
         */
        /** @noinspection PhpUnusedLocalVariableInspection */
        $vcsFiles = $customFiles ?: null;

        /** @noinspection PhpUnusedLocalVariableInspection */
        $hookFile = $this->askProjectDir($input, $output).'/.git/hooks/pre-commit';
        require_once __DIR__.'/../../../../runner.php';

        return 0;
    }

    /**
     * Get custom files list for validation
     *
     * @param InputInterface                                    $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return array
     * @throws \PreCommit\Command\Exception
     */
    protected function getCustomTestFiles(InputInterface $input, OutputInterface $output)
    {
        if (!$input->getArgument('path')) {
            //ignore an empty path
            return null;
        }

        $customFiles = array();
        $paths       = explode(' ', $input->getArgument('path'));
        foreach ($paths as $path) {
            $fs = new Filesystem();
            if ($fs->isAbsolutePath($path)) {
                throw new Exception('Sorry, absolute path is not supported so far.');
            }

            $finder = new Finder();
            $finder->files()->in($path);

            /** @var SplFileInfo $file */
            foreach ($finder as $file) {
                $customFiles[] = rtrim($path, '\\/').DIRECTORY_SEPARATOR.$file->getRelativePathname();
            }
        }

        return $customFiles;
    }
}
