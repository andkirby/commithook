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
}
