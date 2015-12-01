<?php
namespace PreCommit\Filter\ShortCommitMsg\GitHub;

use PreCommit\Interpreter\InterpreterInterface;
use PreCommit\Filter\ShortCommitMsg;

/**
 * Class filter to parse short message
 *
 * @package PreCommit\Filter\ShortCommitMsg\GitHub
 */
class Parser
    extends ShortCommitMsg\Jira\Parser
    implements InterpreterInterface
{
    /**
     * Convert issue number to issue key
     *
     * Add project key to issue number when it did not set.
     *
     * @param string $issueNo
     * @return string
     * @throws \PreCommit\Exception
     */
    protected function _normalizeIssueKey($issueNo)
    {
        return "#" . ltrim($issueNo, '#');
    }
}
