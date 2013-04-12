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
     * @param string $value
     * @param int|null $line
     * @return $this
     */
    public function addError($file, $type, $message, $value = null, $line = null)
    {
        $line = (int) $line;
        $value = trim($value);
        $message = str_replace('%value%', $value, $message);
        $message = str_replace('%line%', $value, $message);

        if ($line) {
            $message = "Line: $line. " . $message;
            $this->_errors[$file][$type][] = array(
                'line'    => $line,
                'value'   => $value,
                'message' => $message,
            );
        } else {
            $this->_errors[$file][$type][] = array(
                'value'   => $value,
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
}
