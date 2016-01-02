<?php
/**
 * @license https://raw.githubusercontent.com/andkirby/commithook/master/LICENSE.md
 */
namespace PreCommit\Jira;

use chobie\Jira as JiraLib;

/**
 * Class Issue
 *
 * @package PreCommit\Jira\Api
 */
class Issue extends JiraLib\Issue
{
    /**
     * Get sprints simple
     *
     * @param bool $addState
     * @return array
     */
    public function getSprintsSimple($addState = false)
    {
        $sprints = array();
        foreach ($this->getSprints() as $sprint) {
            $sprints[$sprint['id']] = $sprint['name'];
            if ($addState) {
                $sprints[$sprint['id']] .= " ({$sprint['state']})";
            }
        }

        return $sprints;
    }

    /**
     * Get GreenHooper sprints in human readable format
     *
     * @return array
     */
    public function getSprints()
    {
        if (!isset($this->fields['Sprint/s'])) {
            $sprints      = array();
            $sprintsDraft = $this->get('Sprint') ?: array();
            foreach ($sprintsDraft as $draft) {
                $sprints[] = $this->parseSprintString($draft);
            }
            $this->fields['Sprint/s'] = $sprints;
        }

        return $this->fields['Sprint/s'];
    }

    /**
     * Get params from GreenHooper sprint string
     *
     * @param string $sprintDraft
     * @return array
     */
    protected function parseSprintString($sprintDraft)
    {
        $start     = strpos($sprintDraft, '[');
        $paramsStr = trim(substr($sprintDraft, $start), '[]');
        parse_str(str_replace(',', '&', $paramsStr), $params);

        return $params;
    }

    /**
     * Get issue status name
     *
     * @return string
     */
    public function getStatusName()
    {
        $status = $this->getStatus();

        return isset($status['name']) ? $status['name'] : null;
    }

    /**
     * Get issue status
     *
     * @return mixed
     */
    public function getStatus()
    {
        if (isset($this->fields['status'])) {
            return $this->fields['status'];
        }

        return null;
    }

    /**
     * Get issue type name
     *
     * @return string
     */
    public function getTypeName()
    {
        $type = $this->getIssueType();

        return isset($type['name']) ? $type['name'] : null;
    }

    /**
     * Get summary
     *
     * @return string
     */
    public function getIssueType()
    {
        return isset($this->fields['issuetype']['name'])
            ? $this->fields['issuetype']['name'] : null;
    }

    /**
     * Get Affects Version/s names
     *
     * @return array
     */
    public function getAffectsVersionsNames()
    {
        $versions = array();
        foreach ($this->getAffectsVersions() as $version) {
            $versions[] = $version['name'];
        }

        return $versions;
    }

    /**
     * Get Affects Version/s
     *
     * @return array
     */
    public function getAffectsVersions()
    {
        return $this->get('Affects Version/s') ?: array();
    }

    /**
     * Get Fix Version/s names
     *
     * @return array
     */
    public function getFixVersionsNames()
    {
        $versions = array();
        foreach ($this->getFixVersions() as $version) {
            $versions[] = $version['name'];
        }

        return $versions;
    }

    /**
     * Get issue sprints
     *
     * @param array $commits
     * @return array
     */
    public function getAuthors($commits)
    {
        $authors = $this->getIssueVcsAuthors($commits);
        if (!$authors) {
            $authors = $this->getChangeLogResolveAuthors();
        }
        if (!$authors) {
            $authors = array($this->getIssueAssignee().' (assignee)');
        }

        return $authors ?: array();
    }

    /**
     * Get issue VCS authors
     *
     * @param array $commits
     * @return string
     */
    public function getIssueVcsAuthors(array $commits)
    {
        $authors = array();
        if ($commits && isset($commits[$this->getKey()])) {
            $authors = array_keys($commits[$this->getKey()]['hash']);
        }

        return $authors;
    }

    /**
     * Get issue authors from issue change log
     *
     * @return mixed
     */
    public function getChangeLogResolveAuthors()
    {
        /**
         * Try to find author for non-code issue
         */
        $authors = array();
        foreach ($this->getChangeLog() as $changes) {
            if (!is_array($changes)) {
                continue;
            }
            foreach ($changes as $key => $change) {
                foreach ($change['items'] as $item) {
                    if ($item['field'] == $item['field'] && $item['toString'] == 'Resolved') {
                        $authors[] = $change['author']['displayName'];
                        break 3;
                    }
                }
            }
        }

        return $authors;
    }

    /**
     * Get issue assignee
     *
     * @return string
     */
    public function getIssueAssignee()
    {
        $assignee = $this->getAssignee();

        return $assignee['displayName'];
    }

    /**
     * Get issue change log
     *
     * @param bool $reverse
     * @return array
     */
    public function getChangeLog($reverse = true)
    {
        $info      = $this->getExpandedInformation();
        $changeLog = isset($info['changelog']) ? $info['changelog'] : array();
        if ($reverse) {
            $changeLog = array_reverse($changeLog, true);
        }

        return $changeLog;
    }

    /**
     * Get summary
     *
     * @return string
     */
    public function getSummary()
    {
        return $this->fields['summary'];
    }
}
