<?php
/**
 * @license https://raw.githubusercontent.com/andkirby/commithook/master/LICENSE.md
 */
namespace PreCommit\Validator;

/**
 * Class XML validator
 *
 * @package PreCommit\Validator
 */
class XmlParser extends AbstractValidator
{
    /**#@+
     * Error codes
     */
    const CODE_XML_ERROR = 'xmlParse';

    /**#@-*/

    /**
     * Error messages
     *
     * @var array
     */
    protected $errorMessages
        = array(
            self::CODE_XML_ERROR => '%value%',
        );

    /**
     * Validate XML
     *
     * @param string $content
     * @param string $file
     * @return bool
     */
    public function validate($content, $file)
    {
        try {
            libxml_use_internal_errors(true);
            $doc = new \DOMDocument('1.0', 'utf-8');
            $doc->loadXML($content);

            $xmlErrors = libxml_get_errors();

            if (empty($xmlErrors)) {
                return true;
            }

            $error = $xmlErrors[0];
            if ($error->level < 3) {
                return true;
            }
            $this->addError(
                $file,
                self::CODE_XML_ERROR,
                str_replace("\n", '', $error->message),
                $error->line
            );
        } catch (\Exception $e) {
            $this->addError(
                $file,
                self::CODE_XML_ERROR,
                str_replace("\n", '', $e->getMessage())
            );
        }

        return !$this->errorCollector->hasErrors();
    }
}
