<?php
/**
 * @license https://raw.githubusercontent.com/andkirby/commithook/master/LICENSE.md
 */
namespace PreCommit\Interpreter;

/**
 * Class JsHintOutput
 *
 * @package PreCommit\Interpreter
 */
class JsHintOutput implements InterpreterInterface
{
    /**
     * Interpret output of JSHint into an array
     *
     * @param array $data
     * @return array
     */
    public function interpret($data)
    {
        $result = array();
        foreach ($data as $output) {
            $output = explode("\n", $output);
            foreach ($output as $row) {
                preg_match('#([^:]+): line (\d+), col (\d+), (.+)#', $row, $matches);
                if (!$matches) {
                    continue;
                }
                $result[$matches[1]][] = array(
                    $matches[4], //message
                    $matches[2], //line
                    $matches[3], //column
                );
            }
        }

        return $result;
    }
}
