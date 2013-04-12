<?php
namespace PreCommit;

/**
 * Class for autoload classes
 */
class Autoloader
{
    /**
     * Exception code
     */
    const EXCEPTION_CODE = 564;

    /**
     * Class autoloader method
     *
     * @param string $class
     * @throws Exception
     */
    static public function autoload($class)
    {
        if (strpos($class, '_')) {
            $class = str_replace('_', '/', $class);
        }
        $file = str_replace('\\', '/', $class) . '.php';
        if (self::_isExist($file)) {
            require_once $file;
        } else {
            throw new Exception('File ' . $file . ' not found in include path: ' . get_include_path(), self::EXCEPTION_CODE);
        }
    }

    /**
     * Check exist file
     *
     * @param string $file
     * @return bool
     */
    static protected function _isExist($file)
    {
        foreach (explode(PATH_SEPARATOR, get_include_path()) as $path) {
            $path = rtrim($path, '\\/');
            if (file_exists($path . '/' . $file)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Register autoloader
     */
    static public function register()
    {
        spl_autoload_register(__NAMESPACE__ . '\Autoloader::autoload');
    }
}

