<?php
/**
 * @license https://raw.githubusercontent.com/andkirby/commithook/master/LICENSE.md
 */
namespace PreCommit\Validator;

use PreCommit\Validator\Helper\LineFinder;

/**
 * Class XML validator
 *
 * @package PreCommit\Validator
 */
class PhpDoc extends AbstractValidator
{
    /**#@+
     * Error codes
     */
    const CODE_PHP_DOC_MISSED            = 'phpDocMissed';
    const CODE_PHP_DOC_MESSAGE           = 'phpDocMessageMissed';
    const CODE_PHP_DOC_MISSED_GAP        = 'phpDocMissedGap';
    const CODE_PHP_DOC_ENTER_DESCRIPTION = 'phpDocEnterDescription';
    const CODE_PHP_DOC_UNKNOWN           = 'phpDocUnknown';
    const CODE_PHP_DOC_EXTRA_GAP         = 'phpDocExtraGap';
    const CODE_PHP_DOC_VAR_NULL          = 'phpDocVarNull';
    const CODE_PHP_DOC_VAR_EMPTY         = 'phpDocVarEmpty';
    const CODE_PHP_DOC_SINGLE_ASTERISK   = 'phpDocSingleAsterisk';
    /**#@-*/

    /**
     * Error messages
     *
     * @var array
     */
    protected $errorMessages
        = array(
            self::CODE_PHP_DOC_ENTER_DESCRIPTION => 'PHPDoc has incomplete info: Enter description here... - Please, write a reasonable description.',
            self::CODE_PHP_DOC_UNKNOWN           => "PHPDoc has incomplete info: 'unknown_type' - Please, specify a type.",
            self::CODE_PHP_DOC_MISSED            => 'PHPDoc is missing for %value%',
            self::CODE_PHP_DOC_MISSED_GAP        => 'Gap after description is missed in PHPDoc for %value%',
            self::CODE_PHP_DOC_MESSAGE           => "There is PHPDoc message missed or first letter is not in uppercase.\t%value%",
            self::CODE_PHP_DOC_EXTRA_GAP         => 'There are found extra gaps in PHPDoc block at least %value% times.',
            self::CODE_PHP_DOC_VAR_NULL          => 'There are found "@var null" or "@param null" in PHPDoc block at least %value% times. Please describe it with more types.',
            self::CODE_PHP_DOC_VAR_EMPTY         => 'There are found "@var" or "@param" which does not have described type in PHPDoc block at least %value% times. Please describe it',
            self::CODE_PHP_DOC_SINGLE_ASTERISK   => 'There are found inline PHPDoc with single asterisk (*) at least %value% times. Please use double asterisk (e.g.: /** @var $this */).',
        );

    /**
     * Validate PhpDocs
     *
     * @param string $content
     * @param string $file
     * @return bool
     */
    public function validate($content, $file)
    {
        //clean up group comments with nodes
        $content = $this->cleanGroupCommentedNodes($content);

        $text = preg_split('/\x0A\x0D|\x0D\x0A|\x0A|\x0D/', $content);

        foreach ($text as $line => $str) {
            $line++;
            $this->validateEnterDescription($file, $str, $line);
            $this->validateUnknownType($file, $str, $line);
        }
        $this->validateExistPhpDocsForClassItems($content, $file);
        $this->validateExistPhpDocForClass($content, $file);
        $this->validateExistPhpDocMessage($content, $file);
        $this->validateMissedGapAfterPhpDocMessage($content, $file);
        $this->validateExistPhpDocExtraGap($content, $file);
        $this->validateExistPhpDocVarEmptyType($content, $file);
        $this->validateExistPhpDocVarNull($content, $file);
        $this->validateSingleAsterisk($content, $file);

        return !$this->errorCollector->hasErrors();
    }

    /**
     * Remove Group commented nodes
     *
     * @param string $content
     * @return string
     */
    protected function cleanGroupCommentedNodes($content)
    {
        return preg_replace('/\s*\/\*\*\#\@\+(\s|\S)*?\/\*\*\#@\-\*\//', '', $content);
    }

    /**
     * Validate PHPDoc for contained "Enter_description here..."
     *
     * @param string $file
     * @param string $str
     * @param string $line
     * @return $this
     */
    protected function validateEnterDescription($file, $str, $line)
    {
        if (preg_match('/\*\s*Enter '.'description here/i', $str)) {
            $this->addError($file, self::CODE_PHP_DOC_ENTER_DESCRIPTION, null, $line);
        }

        return $this;
    }

    /**
     * Validate PHPDoc for contained unknown_type
     *
     * @param string $file
     * @param string $str
     * @param string $line
     * @return $this
     */
    protected function validateUnknownType($file, $str, $line)
    {
        if (preg_match('/\*\x20\@.*?unknown_type/i', $str)) {
            $this->addError($file, self::CODE_PHP_DOC_UNKNOWN, null, $line);
        }

        return $this;
    }

    /**
     * Validate exist PHPDoc for class items
     *
     * @param string $content
     * @param string $file
     * @return $this
     */
    protected function validateExistPhpDocsForClassItems($content, $file)
    {
        $reg = '/(?<!\*\/\x0D|\*\/)\x0A\x20{4}((?:public function|protected function|private function|function|const|public|protected|private)[^\x0A]*)/i';
        if (preg_match_all($reg, $content, $matches)) {
            foreach ($matches[1] as $match) {
                $this->addError(
                    $file,
                    self::CODE_PHP_DOC_MISSED,
                    $match,
                    $this->findLines($match, $content, true)
                );
            }
        }

        return $this;
    }

    /**
     * Validate exist PHPDoc for class
     *
     * @param string $content
     * @param string $file
     * @return $this
     */
    protected function validateExistPhpDocForClass($content, $file)
    {
        if (preg_match_all('/(?<!\*\/\x0D|\*\/)\x0A(class[^\x0A]*)/i', $content, $matches)) {
            foreach ($matches[1] as $match) {
                $this->addError($file, self::CODE_PHP_DOC_MISSED, $match);
            }
        }

        return $this;
    }

    /**
     * Validate exist PHPDoc Message
     *
     * @param string $content
     * @param string $file
     * @return $this
     */
    protected function validateExistPhpDocMessage($content, $file)
    {
        if (preg_match_all(
            '/\x20+\/\*\*\x0D?\x0A\x20+\*([^ ][^A-Z]|\x20[^A-Z])(\s|\S)*?\*\//',
            $content,
            $matches
        )) {
            $findings = array();
            foreach ($matches[0] as $match) {
                if (stripos($match, ' * {@inheritdoc}') || stripos($match, ' * @inheritdoc')) {
                    continue;
                }
                $findings[] = $match;
            }

            if (!$findings) {
                return $this;
            }

            //region Find lines
            sort($findings);
            $findings = array_unique($findings);
            $lines    = array();
            foreach ($findings as $find) {
                $lines = array_merge($lines, $this->findLines($find, $content));
            }
            sort($lines);
            //endregion

            $this->addError($file, self::CODE_PHP_DOC_MESSAGE, null, $lines);
        }

        return $this;
    }

    /**
     * Validate missed gap after PhpDoc description
     *
     * @param string $content
     * @param string $file
     * @return $this
     */
    protected function validateMissedGapAfterPhpDocMessage($content, $file)
    {
        if (preg_match_all(
            '/\x20+\* \w.*(?=\x0D?\x0A\x20+\*\x20@)/',
            $content,
            $matches
        )) {
            foreach ($matches[0] as $match) {
                $this->addError($file, self::CODE_PHP_DOC_MISSED_GAP, $match);
            }
        }

        return $this;
    }

    /**
     * Validate missed gap after PhpDoc description
     *
     * @param string $content
     * @param string $file
     * @return $this
     */
    protected function validateExistPhpDocExtraGap($content, $file)
    {
        if (preg_match_all(
            '/\x0D?\x0A\x20+\*\x0D?\x0A\x20+\*(\x0D?\x0A|\/)/',
            $content,
            $matches
        )) {
            $lines = $this->findLines(rtrim($matches[0][0]), $content);
            $this->addError($file, self::CODE_PHP_DOC_EXTRA_GAP, count($matches[0]), $lines);
        }

        return $this;
    }

    /**
     * Validate empty types in tags "var" and "param"
     *
     * @param string $content
     * @param string $file
     * @return $this
     */
    protected function validateExistPhpDocVarEmptyType($content, $file)
    {
        if (preg_match_all(
            '/\x0D?\x0A\x20+\*\x20(@(param|var)((\x20+\$.+)|(\x0D?\x0A)))/',
            $content,
            $matches
        )) {
            $lines = array();
            foreach ($matches[0] as $match) {
                $lines[] = $this->findLines(trim($match), $content, true);
            }
            $this->addError($file, self::CODE_PHP_DOC_VAR_EMPTY, count($matches[0]), $lines);
        }

        return $this;
    }

    /**
     * Validate NULL types in tags "var" and "param"
     *
     * @param string $content
     * @param string $file
     * @return $this
     */
    protected function validateExistPhpDocVarNull($content, $file)
    {
        if (preg_match_all(
            '/\x0D?\x0A\x20+\*\x20@(param|var)\x20(null|NULL)(\x0D?\x0A|\x20)/',
            $content,
            $matches
        )) {
            $lines    = array();
            $findings = array(
                ' * @var null ',
                ' * @param null ',
                ' * @var null'."\n",
                ' * @param null'."\n",
            );
            foreach ($findings as $find) {
                $lines = array_merge($lines, $this->findLines($find, $content));
            }
            sort($lines);
            $this->addError($file, self::CODE_PHP_DOC_VAR_NULL, count($matches[0]), $lines);
        }

        return $this;
    }

    /**
     * Validate single asterisk in inline PHPDoc block
     *
     * @param string $content
     * @param string $file
     * @return $this
     */
    protected function validateSingleAsterisk($content, $file)
    {
        //@startSkipCommitHooks
        $target = '/* @var ';
        //@finishSkipCommitHooks
        str_replace($target, '|||', $content, $count);
        if ($count) {
            $lines = $this->findLines($target, $content);
            $this->addError($file, self::CODE_PHP_DOC_SINGLE_ASTERISK, $count, $lines);
        }

        return $this;
    }

    /**
     * Find lines for a string
     *
     * @param string $find
     * @param string $content
     * @param bool   $once
     * @return array|int
     */
    protected function findLines($find, $content, $once = false)
    {
        return LineFinder::findLines($find, $content, $once);
    }
}
