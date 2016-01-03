<?php
/**
 * @license https://raw.githubusercontent.com/andkirby/commithook/master/LICENSE.md
 */
namespace PreCommit\Test\Interpreter;

use PreCommit\Interpreter\JsHintOutput;

/**
 * Class JsHintOutputTest
 *
 * @package PreCommit\Test\Interpreter
 */
class JsHintOutputTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test interpreting
     */
    public function testInterpreting()
    {
        //@startSkipCommitHooks
        $output
            = <<<OUTPUT
js/google/ga.js: line 150, col 10, Missing semicolon.
js/google/ga.js: line 152, col 14, Creating global 'for' variable. Should be 'for (var blockName ...'.
OUTPUT;
        //@finishSkipCommitHooks

        $expected = array(
            'js/google/ga.js' => array(
                array(
                    150,
                    10,
                    'Missing semicolon.',
                ),
                array(
                    152,
                    14,
                    'Creating global \'for\' variable. Should be \'for (var blockName ...\'.',
                ),
            ),
        );

        $interpreter = new JsHintOutput();
        $this->assertEquals($expected, $interpreter->interpret(array($output)));
    }
}
