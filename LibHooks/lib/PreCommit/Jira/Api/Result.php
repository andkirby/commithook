<?php
namespace PreCommit\Jira\Api;

use chobie\Jira as JiraLib;
use PreCommit\Jira\Issue;

/**
 * Class Result
 *
 * @package PreCommit\Jira\Api
 */
class Result extends JiraLib\Api\Result
{
    /**
     * Get issues
     *
     * It has been rewritten to use self Issue class
     *
     * @return array
     */
    public function getIssues()
    {
        $result = array();
        if (isset($this->result['issues'])) {
            $result = array();
            foreach ($this->result['issues'] as $issue) {
                $result[] = new Issue($issue);
            }
        }

        return $result;
    }
}
