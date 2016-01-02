<?php
/**
 * @license https://raw.githubusercontent.com/andkirby/commithook/master/LICENSE.md
 */

namespace PreCommit\Helper;

/**
 * Class PathMatch
 *
 * @package PreCommit\Helper
 */
class PathMatch
{
    /**
     * Allowed paths
     *
     * @var array
     */
    protected $allowed = array();

    /**
     * Protected paths
     *
     * @var array
     */
    protected $protected = array();

    /**
     * Test file path
     *
     * @param string $file
     * @return bool
     */
    public function test($file)
    {
        $file = str_replace('\\', '/', $file);

        if ($this->protected && $this->matchList($this->protected, $file)) {
            return false;
        }

        if ($this->allowed && !$this->matchList($this->allowed, $file)) {
            return false;
        }

        return true;
    }

    /**
     * Match path to with nodes in a list
     *
     * @param array $list
     * @param string $file
     * @return bool
     */
    protected function matchList(array $list, $file)
    {
        foreach ($list as $path) {
            $path = str_replace('\\', '/', $path);
            if (false !== strpos($path, '*')) {
                $reg = $path;
                //unknown directories structure
                $reg = str_replace('**', '([^<>:"/\|?__ASTERISK__]|\x2F)+', $reg);
                //unknown directory
                $reg = str_replace('*', '[^<>:"/\|?*]+', $reg);
                $reg = str_replace('__ASTERISK__', '*', $reg);

                $reg = str_replace('.', '\.', $reg);
                $reg = str_replace('#', '\#', $reg);

                if ('/' !== substr($path, -1)) {
                    //path is being path to file (not directory)
                    $reg .= '$';
                }

                if (preg_match('#^'.$reg.'#', $file)) {
                    return true;
                }
            } elseif (0 === strpos($file, $path)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Set allowed paths
     *
     * @param array $allowed
     * @return $this
     */
    public function setAllowed($allowed)
    {
        $this->allowed = $allowed;

        return $this;
    }

    /**
     * Set protected paths
     *
     * @param array $protected
     * @return $this
     */
    public function setProtected($protected)
    {
        $this->protected = $protected;

        return $this;
    }
}
