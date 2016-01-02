<?php
/**
 * @license https://raw.githubusercontent.com/andkirby/commithook/master/LICENSE.md
 */
namespace PreCommit\Processor;

/**
 * Class Error Collector
 *
 * @package PreCommit\Processor
 */
class ErrorCollector
{
    /**
     * Errors list
     *
     * @var array
     */
    protected $errors = array();

    /**
     * Add an error
     *
     * @param string       $file
     * @param string       $code
     * @param int          $message
     * @param string|array $value
     * @param int|null     $line
     * @return $this
     */
    public function addError($file, $code, $message, $value = null, $line = null)
    {
        if ($value !== (array) $value) {
            $value = array('value' => $value);
        }
        foreach ($value as $key => $val) {
            $val     = trim($val);
            $message = str_replace("%$key%", $val, $message);
        }

        if ($line) {
            $lineValue                    = is_array($line) ? implode(',', $line) : $line;
            $message                      = "Line: $lineValue. ".$message;
            $this->errors[$file][$code][] = array(
                'line'    => $line,
                'value'   => $value['value'],
                'message' => $message,
            );
        } else {
            $this->errors[$file][$code][] = array(
                'value'   => $value['value'],
                'message' => $message,
            );
        }

        return $this;
    }

    /**
     * Get errors
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Check having errors
     *
     * @return bool
     */
    public function hasErrors()
    {
        return (bool) $this->errors;
    }
}
