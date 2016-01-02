<?php
/**
 * @license https://raw.githubusercontent.com/andkirby/commithook/master/LICENSE.md
 */
namespace PreCommit\Interpreter;

/**
 * Interface InterpreterInterface
 *
 * @package PreCommit\Interpreter
 */
interface InterpreterInterface
{
    /**
     * Interpret data
     *
     * @param array $data
     * @return $this
     */
    public function interpret($data);
}
