<?php
namespace PreCommit\Composer\Command\Install;

use PreCommit\Composer\Exception;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * CommitHooks files remover
 *
 * @package PreCommit\Composer
 */
class Remove extends CommandAbstract
{
    /**
     * Init default helpers
     *
     * @return $this
     */
    protected function configureCommand()
    {
        $this->setName('remove');
        $this->setHelp(
            'This command can remove installed hook files in your project.'
        );
        $this->setDescription(
            'This command can remove installed hook files in your project.'
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
        try {
            $hooksDir = $this->getHooksDir(
                $output, $this->askProjectDir($input, $output)
            );
            $files = $this->getTargetFiles($input, $output);
            $status = $this->removeHookFiles($output, $hooksDir, $files);
        } catch (Exception $e) {
            if ($this->isVeryVerbose($output)) {
                throw $e;
            } else {
                $output->writeln($e->getMessage());
                return 1;
            }
        }
        if ($status) {
            if ($this->isVerbose($output)) {
                $output->writeln("Existed CommitHook file(s) has been removed from '$hooksDir'.");
            } else {
                $output->writeln('Existed CommitHook file(s) has been removed.');
            }
        } else {
            $output->writeln('No CommitHook file(s) to remove.');
        }
        return 0;
    }

    /**
     * Remove hook files
     *
     * @param OutputInterface $output
     * @param string          $hooksDir
     * @param array           $files
     * @return bool
     */
    protected function removeHookFiles(OutputInterface $output, $hooksDir,
        array $files
    ) {
        $status = false;
        foreach ($files as $filename) {
            $file = $hooksDir . DIRECTORY_SEPARATOR . $filename;
            if (!is_file($file)) {
                //file not found
                if ($this->isVerbose($output)) {
                    $output->writeln("Hook file '$filename' not found. Skipped.");
                }
                continue;
            } else {
                if (!unlink($file)) {
                    //cannot remove
                    $output->writeln("Hook file '$filename' cannot be removed. Skipped.");
                    continue;
                } elseif ($this->isVerbose($output)) {
                    //success removing
                    $output->writeln("Hook file '$filename' has removed.");
                }
                $status = true;
            }
        }
        return $status;
    }

    /**
     * Get custom hook option description
     *
     * @return string
     */
    protected function getCustomHookOptionDescription()
    {
        return 'Set specific hook file to remove.';
    }

    /**
     * Get hook option description
     *
     * @param string $hook
     * @return string
     */
    protected function getHookOptionDescription($hook)
    {
        return "Set '$hook' hook file to remove.";
    }
}
