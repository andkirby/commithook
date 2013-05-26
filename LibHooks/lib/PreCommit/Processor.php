<?php
namespace PreCommit;

/**
 * Class Processor. Input point for validate files
 * @package PreCommit
 */
class Processor
{
    /**
     * Factory method
     *
     * @param string $adapter
     * @param string $vcsType
     * @return Processor\AbstractAdapter
     * @throws Exception
     */
    static public function factory($adapter, $vcsType)
    {
        if (!$adapter) {
            throw new Exception('Adapter name cannot be empty.');
        }

        $class = self::_getAdapterClassName($adapter);

        try {
            return new $class($vcsType);
        } catch (Exception $e) {
            $file = str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';
            if ($e->getCode() == Autoloader::EXCEPTION_CODE && strpos($e->getMessage(), $file)) {
                throw new Exception("Seems adapter '$adapter' does not implemented.");
            }
        }
    }

    /**
     * Get class name of adapter
     *
     * @param string $adapter
     * @return array
     */
    protected static function _getAdapterClassName($adapter)
    {
        $adapter = explode('-', $adapter);
        foreach ($adapter as &$part) {
            $part = ucfirst($part);
        }
        return '\\' . __CLASS__ . '\\' . implode('', $adapter);
    }
}





