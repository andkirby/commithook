<?php
/**
 * @license https://raw.githubusercontent.com/andkirby/commithook/master/LICENSE.md
 */
/**
 * Created by PhpStorm.
 * User: a.roslik
 * Date: 8/29/16 029
 * Time: 7:01 PM
 */

namespace PreCommit\Filter\ShortCommitMsg\Parser;

/**
 * Interface InterpreterInterface
 *
 * @package PreCommit\Interpreter
 */
interface IssueParserInterface
{
    /**
     * Normalize issue number/key
     *
     * @param string|int $issueNo
     * @return string
     */
    public function normalizeIssueKey($issueNo);

    /**
     * Get active issue key
     *
     * @return string
     */
    public function getActiveIssueKey();
}
