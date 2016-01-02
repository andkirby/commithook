<?php
/**
 * @license https://raw.githubusercontent.com/andkirby/commithook/master/LICENSE.md
 */
namespace PreCommit\Validator\CodeSniffer;

/**
 * Class Collector of Code Sniffer errors
 *
 * @package PreCommit\Validator\CodeSniffer
 */
class Collector extends \PHP_CodeSniffer_CLI
{
    /**
     * Runs PHP_CodeSniffer over files and directories.
     *
     * @param array $values An array of values determined from CLI args.
     * @return array Returns list of errors/warnings
     * @throws \Exception
     * @throws \PHP_CodeSniffer_Exception
     * @see    \PHP_CodeSniffer_CLI::getCommandLineValues()
     * @see    \PHP_CodeSniffer_CLI::process()
     */
    public function process($values = array())
    {
        if (empty($values) === true) {
            $values = $this->getCommandLineValues();
        } else {
            $values       = array_merge($this->getDefaults(), $values);
            $this->values = $values;
        }

        if ($values['generator'] !== '') {
            throw new Exception('This parameter is not supported.');
        }

        // If no standard is supplied, get the default.
        $values['standard'] = $this->validateStandard($values['standard']);
        foreach ($values['standard'] as $standard) {
            if (\PHP_CodeSniffer::isInstalledStandard($standard) === false) {
                // They didn't select a valid coding standard, so help them
                // out by letting them know which standards are installed.
                throw new \PHP_CodeSniffer_Exception('ERROR: the "'.$standard.'" coding standard is not installed.');
            }
        }

        $phpcs = $this->getSniffer($values);
        $phpcs->setCli($this);
        $phpcs->initStandard($values['standard'], $values['sniffs']);
        $values = $this->values;

        $phpcs->setTabWidth($values['tabWidth']);
        $phpcs->setEncoding($values['encoding']);
        $phpcs->setInteractive($values['interactive']);

        // Set file extensions if they were specified. Otherwise,
        // let PHP_CodeSniffer decide on the defaults.
        if (empty($values['extensions']) === false) {
            $phpcs->setAllowedFileExtensions($values['extensions']);
        }

        // Set ignore patterns if they were specified.
        if (empty($values['ignored']) === false) {
            $ignorePatterns = array_merge($phpcs->getIgnorePatterns(), $values['ignored']);
            $phpcs->setIgnorePatterns($ignorePatterns);
        }

        // Set some convenience member vars.
        if ($values['errorSeverity'] === null) {
            $this->errorSeverity = PHPCS_DEFAULT_ERROR_SEV;
        } else {
            $this->errorSeverity = $values['errorSeverity'];
        }

        if ($values['warningSeverity'] === null) {
            $this->warningSeverity = PHPCS_DEFAULT_WARN_SEV;
        } else {
            $this->warningSeverity = $values['warningSeverity'];
        }

        if (empty($values['reports']) === true) {
            $values['reports']['full'] = $values['reportFile'];
            $this->values['reports']   = $values['reports'];
        }

        $result = $phpcs->processFiles($values['files'], $values['local']);

        if (empty($values['files']) === true) {
            // Check if they are passing in the file contents.
            $handle       = fopen('php://stdin', 'r');
            $fileContents = stream_get_contents($handle);
            fclose($handle);

            if ($fileContents === '') {
                // No files and no content passed in.
                throw new \Exception('ERROR: You must supply at least one file or directory to process.');
            } else {
                if ($fileContents !== '') {
                    $phpcs->processFile('STDIN', $fileContents);
                }
            }
        }

        // Interactive runs don't require a final report and it doesn't really
        // matter what the return value is because we know it isn't being read
        // by a script.
        if ($values['interactive'] === true) {
            return array();
        }

        return $result;
    }//end process()

    /**
     * Get internal code sniffer
     *
     * This method return Code Sniffer which should return errors as an array
     *
     * @param  array $values
     * @return NonCliSniffer
     */
    protected function getSniffer($values)
    {
        return new NonCliSniffer($values['verbosity'], null, null, null);
    }
}
