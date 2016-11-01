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
     * @param mixed $data
     * @return mixed
     */
    public function interpret($data);
}
