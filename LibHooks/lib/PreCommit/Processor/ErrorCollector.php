<?php
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
    protected $_errors = array();

    /**
     * Add an error
     *
     * @param string $file
     * @param int $type
     * @param int $message
     * @param string|array $value
     * @param int|null $line
     * @return $this
     */
    public function addError($file, $type, $message, $value = null, $line = null)
    {
        $line = (int) $line;
        if (!is_array($value)) {
            $value = array('value' => $value);
        }
        foreach ($value as $key => $val) {
            $val = trim($val);
            $message = str_replace("%$key%", $val, $message);
        }

        if ($line) {
            $lineValue = ($line === (array)$line) ? implode(', ', $line) : $line;
            $message = "Line: $lineValue. " . $message;
            $this->_errors[$file][$type][] = array(
                'line'    => $line,
                'value'   => $value['value'],
                'message' => $message,
            );
        } else {
            $this->_errors[$file][$type][] = array(
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
        return $this->_errors;
    }

    /**
     * Check having errors
     *
     * @return bool
     */
    public function hasErrors()
    {
        return (bool)$this->_errors;
    }
}
