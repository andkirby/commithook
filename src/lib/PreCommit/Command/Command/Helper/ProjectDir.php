<?php
/**
 * @license https://raw.githubusercontent.com/andkirby/commithook/master/LICENSE.md
 */
namespace PreCommit\Command\Command\Helper;

use PreCommit\Command\Exception;
use Rikby\Console\Helper\SimpleQuestionHelper;
use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Helper for getting project directory
 *
 * @package PreCommit\Command\Command\Helper
 */
class ProjectDir extends Helper
{
    /**
     * Helper name
     */
    const NAME = 'project_dir';

    /**
     * {@inheritdoc}
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
        //@startSkipCommitHooks
        return realpath(trim(`git rev-parse --show-toplevel 2>&1`));
        //@finishSkipCommitHooks
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
     * Ask about GIT project root dir
     *
     * It will skip asking if system is able to identify it.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @param string|null     $dir
     * @return string
     * @throws \PreCommit\Command\Exception
     */
    public function askProjectDir(InputInterface $input, OutputInterface $output, $dir = null)
    {
        $question = $this->getSimpleQuestion()
            ->getQuestion('Please set your root project directory.', $dir);
        $question->setValidator(
            $this->getValidator()
        );

        $io  = new SymfonyStyle($input, $output);
        $dir = $io->askQuestion($question);

        return rtrim($dir, '\\/');
    }

    /**
     * Get GIT root directory validator
     *
     * @return \Closure
     */
    protected function getValidator()
    {
        //@startSkipCommitHooks
        return function ($dir) {
            $dir = rtrim($dir, '\\/');
            if (!is_dir($dir.'/.git')) {
                throw new Exception("Directory '$dir' does not contain '.git' subdirectory.");
            }

            return $dir;
        };
        //@finishSkipCommitHooks
    }

    /**
     * Get question helper
     *
     * @return SimpleQuestionHelper
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
