<?php
/**
 * Created by JetBrains PhpStorm.
 * User: a.roslik
 * Date: 5/14/13
 * Time: 7:49 PM
 * To change this template use File | Settings | File Templates.
 */

namespace PreCommit\Filter;

/**
 * Class InterfaceFilter
 *
 * @package PreCommit\Filter
 */
interface InterfaceFilter
{
    /**
     * @param string $content
     * @param string $file
     * @return mixed
     */
    public function filter($content, $file = null);
}
