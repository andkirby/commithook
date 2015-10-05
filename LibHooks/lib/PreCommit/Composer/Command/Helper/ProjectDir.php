<?php
/**
 * OnePica
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to codemaster@onepica.com so we can send you a copy immediately.
 *
 * @category  OnePica
 * @package   OnePica_${PACKAGE}
 * @copyright Copyright (c) 2012 One Pica, Inc. (http://www.onepica.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace PreCommit\Composer\Command\Helper;

use PreCommit\Composer\Exception;
use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

/**
 * Helper for getting project directory
 *
 * @package PreCommit\Composer\Command\Helper
 */
class ProjectDir extends Helper
{
    /**
     * Helper name
     */
    const NAME = 'project_dir';

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * Get project directory
     *
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return string
     * @throws \PreCommit\Composer\Exception
     */
    public function getProjectDir(InputInterface $input, OutputInterface $output)
    {
        $dir = $input->getOption('project-dir');
        if (!$dir) {
            $dir = $this->getVcsDir();
        }
        if (!$dir) {
            $dir = $this->getCommandDir();
        }
        $validator = $this->getValidator();
        return $validator($dir) ? $dir : $this->askProjectDir($input, $output, $dir);
    }

    /**
     * Get VCS directory (GIT)
     *
     * @return string
     */
    public function getVcsDir()
    {
        //TODO Move to adapter
        return realpath(trim(`git rev-parse --show-toplevel 2>&1`));
    }

    /**
     * Get CLI directory (pwd)
     *
     * @return string
     */
    public function getCommandDir()
    {
        //@startSkipCommitHooks
        return $_SERVER['PWD'];
        //@finishSkipCommitHooks
    }

    /**
     * Get GIT root directory validator
     *
     * @return \Closure
     */
    protected function getValidator()
    {
        return function ($dir) {
            $dir = rtrim($dir, '\\/');
            return is_dir($dir . '/.git');
        };
    }

    /**
     * Ask about GIT project root dir
     *
     * It will skip asking if system is able to identify it.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @param string|null     $dir
     * @return string
     * @throws \PreCommit\Composer\Exception
     */
    public function askProjectDir(InputInterface $input, OutputInterface $output, $dir = null)
    {
        $validator = $this->getValidator();

        $max = 3;
        $i   = 0;
        while (!$dir || !$validator($dir)) {
            if ($dir) {
                $output->writeln(
                    'Sorry, selected directory does not contain ".git" directory.'
                );
            }
            $dir = $this->getDialog()->ask(
                $input, $output,
                new Question("Please set your root project directory [$dir]: ", $dir)
            );
            if (++$i > $max) {
                throw new Exception('Project directory is not set.');
            }
        }

        return rtrim($dir, '\\/');
    }

    /**
     * Get dialog helper
     *
     * @return QuestionHelper
     */
    protected function getDialog()
    {
        return $this->getHelperSet()->get('question');
    }
}
