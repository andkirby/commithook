<?php
namespace PreCommit\Composer\Command\Helper;

use PreCommit\Composer\Exception;
use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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
     * @param null|string                                       $optionDir
     * @return string
     */
    public function getProjectDir(InputInterface $input, OutputInterface $output, $optionDir = null)
    {
        $dir = $optionDir;
        if (!$dir) {
            $dir = $this->getVcsDir();
        }
        if (!$dir) {
            $dir = $this->getCommandDir();
        }
        $validator = $this->getValidator();
        try {
            return $validator($dir);
        } catch (Exception $e) {
        }
        return $this->askProjectDir($input, $output, $dir);
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
            if (!is_dir($dir . '/.git')) {
                throw new Exception("Directory '$dir' does not contain '.git' subdirectory.");
            }
            return $dir;
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
        $question = $this->getSimpleQuestion()
            ->getQuestion('Please set your root project directory.', $dir);
        $question->setValidator(
            $this->getValidator()
        );

        $dir = $this->getQuestionHelper()->ask(
            $input, $output,
            $question
        );

        return rtrim($dir, '\\/');
    }

    /**
     * Get question helper
     *
     * @return SimpleQuestion
     */
    protected function getSimpleQuestion()
    {
        return $this->getHelperSet()->get('simple_question');
    }

    /**
     * Get dialog helper
     *
     * @return QuestionHelper
     */
    protected function getQuestionHelper()
    {
        return $this->getHelperSet()->get('question');
    }
}
