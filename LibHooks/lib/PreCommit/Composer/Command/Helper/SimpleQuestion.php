<?php
namespace PreCommit\Composer\Command\Helper;

use PreCommit\Composer\Exception;
use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\Console\Question\Question;

/**
 * Simple version of question helper
 *
 * @package PreCommit\Composer\Command\Helper
 */
class SimpleQuestion extends Helper
{
    /**
     * Helper name
     */
    const NAME = 'simple_question';

    /**
     * Max attempts
     */
    const MAX_ATTEMPTS = 3;

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
     * @param string          $message        Question message
     * @param string|int|null $default
     * @param array           $options
     * @param bool            $required
     * @param bool            $useOptionValue Value from options should be considered as an answer
     * @return \Symfony\Component\Console\Question\Question
     */
    public function getQuestion(
        $message, $default = null, array $options = array(), $required = true, $useOptionValue = true
    ) {
        $default = (!$useOptionValue && $this->isList($options) && $default !== null)
            ? $options[$default] : $default;

        $question = new Question(
            $this->getFormattedQuestion($message, $default, $options),
            $default
        );
        $question->setMaxAttempts(self::MAX_ATTEMPTS);
        $question->setValidator(
            $this->getValidator($options, $required, $useOptionValue)
        );
        return $question;
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
     * @param bool  $optionValueIsAnswer Value from options should be considered as an answer
     * @return \Closure
     */
    protected function getValidator(array $options, $required, $optionValueIsAnswer)
    {
        if ($options) {
            $useValue = $this->isList($options) && $optionValueIsAnswer;
            return function ($value) use ($options, $useValue) {
                if ($useValue && !array_key_exists($value, $options)
                    || !$useValue && !in_array($value, $options, true)
                ) {
                    throw new Exception("Incorrect value '$value'.");
                }
                return $useValue ? $options[$value] : $value;
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
