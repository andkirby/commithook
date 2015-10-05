<?php
/**
 * OnePica
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to codemaster@onepica.com so we can send you a copy immediately.
 *
 * @category  OnePica
 * @package   OnePica_${PACKAGE}
 * @copyright Copyright (c) 2012 One Pica, Inc. (http://www.onepica.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace PreCommit\Composer\Command\Helper;

use PreCommit\Composer\Exception;
use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

/**
 * Helper for writing config
 *
 * @package PreCommit\Composer\Command\Helper
 */
class Config extends Helper
{
    /**
     * Helper name
     */
    const NAME = 'commithook_config';

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return self::NAME;
    }
}
