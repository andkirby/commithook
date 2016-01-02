<?php
/**
 * @license https://raw.githubusercontent.com/andkirby/commithook/master/LICENSE.md
 */
namespace PreCommit\Validator\CodeSniffer;

/**
 * Class NonCliSniffer
 *
 * @package PreCommit\Validator\CodeSniffer
 */
class NonCliSniffer extends \PHP_CodeSniffer
{
    /**
     * The listeners array, indexed by token type.
     *
     * @var array
     * @see \PHP_CodeSniffer::$_tokenListeners
     */
    protected $tokenListeners = array();

    /**
     * {@inheritdoc}
     */
    public function processFile($file, $contents = null)
    {
        if ($contents === null && file_exists($file) === false) {
            throw new \PHP_CodeSniffer_Exception("Source file $file does not exist");
        }

        $filePath = self::realpath($file);
        if ($filePath === false) {
            $filePath = $file;
        }

        // Before we go and spend time tokenizing this file, just check
        // to see if there is a tag up top to indicate that the whole
        // file should be ignored. It must be on one of the first two lines.
        $firstContent = $contents;
        if ($contents === null && is_readable($filePath) === true) {
            $handle = fopen($filePath, 'r');
            if ($handle !== false) {
                $firstContent = fgets($handle);
                $firstContent .= fgets($handle);
                fclose($handle);

                if (strpos($firstContent, '@codingStandardsIgnoreFile') !== false) {
                    // We are ignoring the whole file.
                    if (PHP_CODESNIFFER_VERBOSITY > 0) {
                        //echo 'Ignoring '.basename($filePath).PHP_EOL;
                    }

                    return null;
                }
            }
        }

        try {
            $phpcsFile = $this->startProcessFile($file, $contents);
        } catch (\Exception $e) {
            $trace = $e->getTrace();

            /** @var \PHP_CodeSniffer_File $filename */
            $filename = $trace[0]['args'][0];
            if (is_object($filename) === true
                && get_class($filename) === '\PHP_CodeSniffer_File'
            ) {
                $filename = $filename->getFilename();
            } else {
                if (is_numeric($filename) === true) {
                    // See if we can find the PHP_CodeSniffer_File object.
                    foreach ($trace as $data) {
                        if (isset($data['args'][0]) === true
                            && ($data['args'][0] instanceof \PHP_CodeSniffer_File) === true
                        ) {
                            /** @var \PHP_CodeSniffer_File $fileSniffer */
                            $fileSniffer = $data['args'][0];
                            $filename    = $fileSniffer->getFilename();
                        }
                    }
                } else {
                    if (is_string($filename) === false) {
                        $filename = (string) $filename;
                    }
                }
            }

            $errorMessage = '"'.$e->getMessage().'" at '.$e->getFile().':'.$e->getLine();
            $error
                          = "An error occurred during processing; checking has been aborted. The error message was: $errorMessage";

            $phpcsFile = new \PHP_CodeSniffer_File(
                $filename,
                $this->tokenListeners,
                $this->ruleset,
                $this
            );

            $phpcsFile->addError($error, null);
        }

        //omitted interactive mode

        return $phpcsFile;
    }

    /**
     * Process the sniffs for a single file.
     *
     * Does raw processing only. No interactive support or error checking.
     *
     * @param string $file     The file to process.
     * @param string $contents The contents to parse. If NULL, the content
     *                         is taken from the file system.
     *
     * @return \PHP_CodeSniffer_File
     * @see    processFile()
     * @see    \PHP_CodeSniffer::_processFile()
     */
    protected function startProcessFile($file, $contents)
    {
        //omitted process notification

        $phpcsFile = new \PHP_CodeSniffer_File(
            $file,
            $this->tokenListeners,
            $this->ruleset,
            $this
        );

        $phpcsFile->start($contents);

        //omitted extra time report

        return $phpcsFile;
    }

    /**
     * Processes the files/directories that PHP_CodeSniffer was constructed with.
     *
     * @param string|array $files The files and directories to process. For
     *                            directories, each sub directory will also
     *                            be traversed for source files.
     * @param boolean      $local If true, don't recurse into directories.
     *
     * @return array
     * @throws \PHP_CodeSniffer_Exception If files are invalid.
     */
    public function processFiles($files, $local = false)
    {
        $files = (array) $files;

        if (empty($this->allowedFileExtensions) === true) {
            $this->allowedFileExtensions = $this->defaultFileExtensions;
        }

        $todo = $this->getFilesToProcess($files, $local);

        //omitted process notification

        $numProcessed = 0;
        $lastDir      = '';
        $result       = array();
        foreach ($todo as $file) {
            $this->file = $file;
            $currDir    = dirname($file);
            if ($lastDir !== $currDir) {
                //omitted process notification

                $lastDir = $currDir;
            }

            $phpcsFile = $this->processFile($file, null);
            $numProcessed++;

            $itemResult = $this->getFileResult($phpcsFile);

            $result[$phpcsFile->getFilename()] = $itemResult;
            //omitted process notification
        }

        //omitted process notification

        return $result;
    }

    /**
     * Get simple result of processed file
     *
     * @param \PHP_CodeSniffer_File $phpcsFile
     * @return array
     */
    protected function getFileResult($phpcsFile)
    {
        $report = array();
        if ($phpcsFile->getErrorCount() || $phpcsFile->getWarningCount()) {
            $report = array(
                'errors'   => $phpcsFile->getErrors(),
                'warnings' => $phpcsFile->getWarnings(),
            );
        }

        return $report;
    }

    //region Copied code for using self _tokenListeners property
    /**
     * Gets the array of PHP_CodeSniffer_Sniff's indexed by token type.
     *
     * @return array
     */
    public function getTokenSniffs()
    {
        return $this->tokenListeners;
    }

    /**
     * Populates the array of PHP_CodeSniffer_Sniff's for this file.
     *
     * @return void
     * @throws \PHP_CodeSniffer_Exception If sniff registration fails.
     */
    public function populateTokenListeners()
    {
        // Construct a list of listeners indexed by token being listened for.
        $this->tokenListeners = array();

        foreach ($this->sniffs as $listenerClass) {
            // Work out the internal code for this sniff. Detect usage of namespace
            // separators instead of underscores to support PHP namespaces.
            if (strstr($listenerClass, '\\') === false) {
                $parts = explode('_', $listenerClass);
            } else {
                $parts = explode('\\', $listenerClass);
            }

            $code = $parts[0].'.'.$parts[2].'.'.$parts[3];
            $code = substr($code, 0, -5);

            $this->listeners[$listenerClass] = new $listenerClass();

            // Set custom properties.
            if (isset($this->ruleset[$code]['properties']) === true) {
                foreach ($this->ruleset[$code]['properties'] as $name => $value) {
                    $this->setSniffProperty($listenerClass, $name, $value);
                }
            }

            $tokenizers = array();
            $vars       = get_class_vars($listenerClass);
            if (isset($vars['supportedTokenizers']) === true) {
                foreach ($vars['supportedTokenizers'] as $tokenizer) {
                    $tokenizers[$tokenizer] = $tokenizer;
                }
            } else {
                $tokenizers = array('PHP' => 'PHP');
            }

            $tokens = $this->listeners[$listenerClass]->register();
            if (is_array($tokens) === false) {
                $msg = "Sniff $listenerClass register() method must return an array";
                throw new \PHP_CodeSniffer_Exception($msg);
            }

            $parts          = explode('_', str_replace('\\', '_', $listenerClass));
            $listenerSource = $parts[0].'.'.$parts[2].'.'.substr($parts[3], 0, -5);
            $ignorePatterns = array();
            $patterns       = $this->getIgnorePatterns($listenerSource);
            foreach ($patterns as $pattern => $type) {
                // While there is support for a type of each pattern
                // (absolute or relative) we don't actually support it here.
                $replacements = array(
                    '\\,' => ',',
                    '*'   => '.*',
                );

                $ignorePatterns[] = strtr($pattern, $replacements);
            }

            foreach ($tokens as $token) {
                if (isset($this->tokenListeners[$token]) === false) {
                    $this->tokenListeners[$token] = array();
                }

                if (isset($this->tokenListeners[$token][$listenerClass]) === false) {
                    $this->tokenListeners[$token][$listenerClass] = array(
                        'class'      => $listenerClass,
                        'source'     => $listenerSource,
                        'tokenizers' => $tokenizers,
                        'ignore'     => $ignorePatterns,
                    );
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function initStandard($standards, array $restrictions = array())
    {
        $this->tokenListeners = array();
        parent::initStandard($standards, $restrictions);
    }
    //endregion
}
