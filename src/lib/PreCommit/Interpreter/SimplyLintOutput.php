<?php
/**
 * @license https://raw.githubusercontent.com/andkirby/commithook/master/LICENSE.md
 */
namespace PreCommit\Interpreter;

/**
 * Class SimplyLintOutput
 *
 * It is a stub to pass output to SimplyLint validator
 *
 * @package PreCommit\Interpreter
 */
class SimplyLintOutput implements InterpreterInterface
{
    /**
     * Interpret output of JSHint into an array
     *
     * @param array $data
     * @return array
     */
    public function interpret($data)
    {
        return array(
            array(
                array(
                    implode("\n", $data),
                ),
            ),
        );
    }
}
