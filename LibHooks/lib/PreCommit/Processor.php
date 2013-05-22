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
     * @return Processor\AbstractAdapter
     * @throws Exception
     */
    static public function factory($adapter)
    {
        if ($adapter) {
            throw new Exception('Adapter name cannot be empty.');
        }

        $adapter = explode('-', $adapter);
        foreach ($adapter as &$part) {
            $part = ucfirst($part);
        }
        $class = '\\' . __NAMESPACE__ . '\\' . __CLASS__ . '\\' . implode('', $adapter);
        try {
            return new $class();
        } catch (Exception $e) {
            $file = str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';
            if ($e->getCode() == Autoloader::EXCEPTION_CODE && strpos($e->getMessage(), $file)) {
                throw new Exception("Seems adapter '$adapter' does not implemented.");
            }
        }
    }
}





