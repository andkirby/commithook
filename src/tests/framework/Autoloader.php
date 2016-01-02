<?php
/**
 * @license https://raw.githubusercontent.com/andkirby/commithook/master/LICENSE.md
 */
/**
 * Class for autoload classes
 */
class Autoloader
{
    const EXCEPTION_CODE = 564;

    static public function autoload($class)
    {
        if (strpos($class, '_')) {
            $class = str_replace('_', '/', $class);
        }
        $file = str_replace('\\', '/', $class) . '.php';
        if (self::_isExist($file)) {
            require_once $file;
        } else {
            throw new \Exception('Could not load file ' . $file . ' in include path: ' .
                get_include_path(), self::EXCEPTION_CODE);
        }
    }

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

    static public function register()
    {
        spl_autoload_register(__NAMESPACE__ . '\Autoloader::autoload');
    }
}
