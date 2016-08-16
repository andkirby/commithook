<?php
/**
 * @license https://raw.githubusercontent.com/andkirby/commithook/master/LICENSE.md
 */

namespace PreCommit\Helper;

/**
 * This class may match paths by rules
 *
 * Examples:
 * 1) All protected, empty rules
 *   - allowedByDefault = FALSE
 * 2) All allowed, empty rules
 *   - allowedByDefault = TRUE
 * 3) All protected, aa/cc is allowed, but aa/cc/bb is protected
 *   - allowedByDefault = FALSE
 *   - aa/cc/bb - protected
 *   - aa/cc    - allowed
 *  Result:
 *     aa/cc/bb/file => FALSE
 *     aa/cc/gg/file => TRUE
 * 4) All allowed, aa/cc is protected, but aa/cc/bb is allowed
 *   - allowedByDefault = TRUE
 *   - aa/cc/bb - allowed
 *   - aa/cc    - protected
 *  Result:
 *     aa/cc/bb/file => TRUE
 *     aa/cc/gg/file => FALSE
 *
 * Asterisk can be used in paths.
 * Examples:
 * 1) Using asterisk (*) for match any node in path
 *      path/[::asterisk::]/to == path/some/to
 * 2) Using double asterisk (**) for recursive match
 *      path/[::asterisk::][::asterisk::]/to == path/any/an/other/to
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
    protected $allowed = [];

    /**
     * Protected paths
     *
     * @var array
     */
    protected $protected = [];

    /**
     * Set allowed by default
     *
     * @see PathMatch::setAllowedByDefault()
     * @var bool
     */
    protected $allowedByDefault = false;

    /**
     * Matched path
     *
     * @var string
     */
    protected $matched;

    /**
     * Set allowed by default
     *
     * Allowed list will have higher priority upon protected one if TRUE
     *
     * @return bool
     */
    public function getAllowedByDefault()
    {
        return $this->allowedByDefault;
    }

    /**
     * Set allowed by default
     *
     * Allowed list will have higher priority upon protected one if TRUE
     *
     * @param bool $flag
     * @return $this
     */
    public function setAllowedByDefault($flag = true)
    {
        $this->allowedByDefault = (bool) $flag;

        return $this;
    }

    /**
     * Get matched path
     *
     * @return string
     */
    public function getMatch()
    {
        return $this->matched;
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

    /**
     * Test path
     *
     * @param string $file
     * @return bool
     */
    public function test($file)
    {
        $file = str_replace('\\', '/', $file);

        if ($this->allowedByDefault
            && ($this->protected && $this->matchList($this->protected, $file)
                && (!$this->allowed || !$this->matchList($this->allowed, $file)))
        ) {
            /**
             * In this case "allowed" list will be ended rule and will have highest priority.
             * If there is no "allowed" or "protected" list it will be ignored.
             */
            return false;
        } elseif (!$this->allowedByDefault
            && (!$this->allowed || $this->protected && $this->matchList($this->protected, $file)
                || $this->allowed && !$this->matchList($this->allowed, $file))
        ) {
            /**
             * In this case "protected" list will be ended rule and will have highest priority.
             * It will return TRUE only if "allowed" list covers a path.
             * If there is no "allowed" or "protected" list it will be ignored.
             */
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
            $path = $path ?: '/';
            if (false !== strpos($path, '*')) {
                $reg = $path;
                //unknown directories structure
                $reg = str_replace('**', '([^<>:"/\|?__ASTERISK__]|\x2F)+', $reg);
                //unknown directory
                $reg = str_replace('*', '[^<>:"/\|?*]*', $reg);
                $reg = str_replace('__ASTERISK__', '*', $reg);

                $reg = str_replace('.', '\.', $reg);
                $reg = str_replace('#', '\#', $reg);

                if ('/' !== substr($path, -1)) {
                    //path is being path to file (not directory)
                    $reg .= '$';
                }

                if (preg_match('#^'.$reg.'#', $file)) {
                    $this->matched = $path;

                    return true;
                }
            } elseif ('/' === $path || $file === $path || 0 === strpos($file, rtrim($path, '/').'/')) {
                $this->matched = $path;

                return true;
            }
        }

        return false;
    }
}
