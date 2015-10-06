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
 * Simple version of question helper
 *
 * @package PreCommit\Composer\Command\Helper
 */
class SimpleQuestion extends Helper
{
    const MAX_ATTEMPTS = 3;
    /**
     * Helper name
     */
    const NAME = 'simple_question';

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * Get the question object with a formatted question
     *
     * @param string          $question
     * @param string|int|null $default
     * @param array           $options
     * @param bool            $required
     * @return \Symfony\Component\Console\Question\Question
     */
    public function getQuestionConfirm(
        $question, $default = 'y', array $options = array('y', 'n'), $required = true
    ) {
        return $this->getQuestion($question, $default, $options, $required);
    }

    /**
     * Get the question object with a formatted question
     *
     * @param string          $question
     * @param string|int|null $default
     * @param array           $options
     * @param bool            $required
     * @return \Symfony\Component\Console\Question\Question
     */
    public function getQuestion(
        $question, $default = null, array $options = array(), $required = true
    ) {
        $instance = new Question(
            $this->getFormattedQuestion($question, $default, $options),
            $default
        );
        $instance->setMaxAttempts(self::MAX_ATTEMPTS);
        $instance->setValidator(
            $this->getValidator($options, $required)
        );
        return $instance;
    }

    /**
     * Get formatted question
     *
     * @param string $question
     * @param string $default
     * @param array  $options
     * @return string
     */
    protected function getFormattedQuestion($question, $default, array $options)
    {
        if ($this->isList($options)) {
            /**
             * Options list mode
             */
            return $this->getFormattedListQuestion($question, $default, $options);
        } else {
            /**
             * Simple options mode
             */
            return $this->getFormattedSimpleQuestion($question, $default, $options);
        }
    }

    /**
     * Check if it's a question with "list" mode
     *
     * @param array $options
     * @return bool
     */
    protected function isList(array $options)
    {
        return $options && !isset($options[0]);
    }

    /**
     * Get formatted "list" question
     *
     * @param array      $question
     * @param string|int $default
     * @param array      $options
     * @return string
     */
    protected function getFormattedListQuestion($question, $default, array $options)
    {
        $list = '';
        foreach ($options as $key => $title) {
            $list .= " $key - $title" . ($default == $key ? ' (Default)' : '') . "\n";
        }
        $question .= ":\n" . $list . ($default ? ' [' . $default . ']: ' : ' : ');
        return $question;
    }

    /**
     * Get formatted "simple" question
     *
     * @param       $question
     * @param       $default
     * @param array $options
     * @return string
     */
    protected function getFormattedSimpleQuestion($question, $default, array $options)
    {
        $question .= '%s%s: ';
        $question = sprintf(
            $question,
            ($options ? ' (' . implode('/', $options) . ')' : ''),
            ($default ? ' [' . $default . ']' : '')
        );
        return $question;
    }

    /**
     * Get value validator
     *
     * @param array $options
     * @param bool  $required
     * @return \Closure
     */
    protected function getValidator(array $options, $required)
    {
        if ($options) {
            $isList = $this->isList($options);
            return function ($value) use ($options, $isList, $required) {
                if ($isList && !array_key_exists($value, $options)
                    || !$isList && !in_array($value, $options, true)
                ) {
                    throw new Exception("Incorrect value '$value'.");
                }
                return $isList ? $options[$value] : $value;
            };
        } else {
            return function ($value) use ($required) {
                if ($required && (null === $value)) {
                    throw new Exception("Empty value.");
                }
                return $value;
            };
        }
    }
}
