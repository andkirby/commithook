<?php
namespace PreCommit\Validator\Magento;

use PreCommit\Config;
use PreCommit\Validator\AbstractValidator;

/**
 * Class ModelEventFields to validate exist declared event properties in Magento data model
 *
 * @package PreCommit\Validator\Magento
 */
class ModelEventFields extends AbstractValidator
{
    /**#@+
     * Error codes
     */
    const CODE_MODEL_MISSED_EVENT_PREFIX = 'mageDataModelMissedEventPrefix';

    const CODE_MODEL_MISSED_EVENT_OBJECT = 'mageDataModelMissedEventObject';

    /**#@-*/

    /**
     * Error messages
     *
     * @var array
     */
    protected $errorMessages
        = array(
            self::CODE_MODEL_MISSED_EVENT_PREFIX => "Missed declaring \$this->_eventPrefix in data model. Please declare it in _construct() method. E.g.: 'namespace_module_name_export_order' for class Namespace_ModuleName_Export_Order.",
            self::CODE_MODEL_MISSED_EVENT_OBJECT => "Missed declaring \$this->_eventObject in data model. Please declare it in _construct() method. E.g.: 'export_order' for class Namespace_ModuleName_Export_Order.",
        );

    /**
     * Validate method
     *
     * @param string $content
     * @param string $file    Validated file
     * @return bool
     */
    public function validate($content, $file)
    {
        if (!$this->isDataModel($content)) {
            return true;
        }
        if ($this->isAbstractClass($content)) {
            return true;
        }
        $this->checkEventPrefix($content, $file);
        $this->checkEventObject($content, $file);

        return !$this->errorCollector->hasErrors();
    }

    /**
     * Check if class is being as a Magento data model
     *
     * @param string $content
     * @return bool
     */
    protected function isDataModel($content)
    {
        foreach ($this->getAbstractDataModelClasses() as $class) {
            if (strpos($content, 'extends '.$class)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if class is abstract
     *
     * @param string $content
     * @return bool
     */
    protected function isAbstractClass($content)
    {
        return (bool) strpos($content, 'abstract class ');
    }

    /**
     * Check if added _eventPrefix declaration
     *
     * @param string $content
     * @param string $file
     * @return $this
     */
    protected function checkEventPrefix($content, $file)
    {
        if (!preg_match('/_eventPrefix[ ]+=[ ]/', $content)) {
            $this->_addError($file, self::CODE_MODEL_MISSED_EVENT_PREFIX);
        }

        return $this;
    }

    /**
     * Check if added _eventObject declaration
     *
     * @param string $content
     * @param string $file
     * @return $this
     */
    protected function checkEventObject($content, $file)
    {
        if (!preg_match('/_eventObject[ ]+=[ ]/', $content)) {
            $this->_addError($file, self::CODE_MODEL_MISSED_EVENT_OBJECT);
        }

        return $this;
    }

    /**
     * Get possible extended data model classes
     *
     * @return array
     * @todo Add an ability to extend the classes list - read from configuration
     */
    protected function getAbstractDataModelClasses()
    {
        return $this->getConfig()->getNodeArray('validators/Magento-ModelEventFields/abstract_class');
    }

    /**
     * Get config model
     *
     * @return Config
     * @throws \PreCommit\Exception
     */
    protected function getConfig()
    {
        return Config::getInstance();
    }
}
