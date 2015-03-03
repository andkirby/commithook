<?php
namespace PreCommit\Filter;

use PreCommit\Config;
use PreCommit\Exception;
use PreCommit\Jira\Api;
use chobie\Jira\Api\Authentication\Basic;
use chobie\Jira\Api\Exception as ApiException;
use PreCommit\Jira\Issue;

/**
 * Class validator for check commit message format
 *
 * @package PreCommit\Validator
 */
class JiraCommitMsg implements InterfaceFilter
{
    /**
     * Filter commit message
     *
     * @param string $content
     * @param string $file
     * @return mixed
     */
    public function filter($content, $file = null)
    {
        $content = trim($content);
        $arr = explode("\n", $content);
        $first = array_shift($arr);

        //improve extra description row
        $secondOrig = array_shift($arr);
        if ($secondOrig && 0 !== strpos(trim($secondOrig), '-')) {
            $content = str_replace($secondOrig, ' - ' . trim($secondOrig), $content);
        }

        //interpret first row to get summary
        preg_match('/^([IRFC]) ([A-Z0-9]+-[0-9]+)[ ]*$/', $first, $m);
        if (!$m) {
            return $content;
        }
        $row = array_shift($m);
        $verb = array_shift($m);
        $issueKey = array_shift($m);

        $summary = $this->_getIssueSummary($issueKey);
        if (!$summary) {
            return $content;
        }
        $verb = $this->_interpretVerb($verb);
        $full = "$verb $issueKey: $summary";
        return str_replace($row, $full, $content);
    }

    /**
     * Get verb key
     *
     * @param string $verb
     * @return string
     * @throws \PreCommit\Exception
     */
    protected function _interpretVerb($verb)
    {
        $map = $this->_getVerbsMap();
        if (!isset($map[$verb])) {
            throw new Exception('Unknown verb key.');
        }

        return $map[$verb];
    }

    /**
     * Get verbs map
     *
     * @return array
     */
    protected function _getVerbsMap()
    {
        return array(
            'I' => 'Implemented',
            'F' => 'Fixed',
            'C' => 'CR Changes',
            'R' => 'Refactored',
        );
    }

    /**
     * Get issue summary
     *
     * @param string $issueKey
     * @return mixed
     */
    protected function _getIssueSummary($issueKey)
    {
        $summary = $this->_getCachedSummary($issueKey);
        if ($summary) {
            return $summary;
        }

        try {
            $summary = $this->_getIssue($issueKey)->getSummary();
            if (!$summary) {
                return false;
            }
        } catch (Api\Exception $e) {
            return false;
        } catch (ApiException $e) {
            return false;
        }

        $this->_cacheSummary($issueKey, $summary);
        return $summary;
    }

    /**
     * Get cache point key
     *
     * @param string $number
     * @return string
     */
    protected function _getCacheStringKey($number)
    {
        return '|' . $number . ': ';
    }

    /**
     * Get cache file
     *
     * @param string $project
     * @return string
     */
    protected function _getCacheFile($project)
    {
        $project = strtolower($project);
        return $this->_getCacheDir()
            . "/issues-$project-v0";
    }

    /**
     * Get cache summary string
     *
     * @param string $issueKey
     * @return string
     */
    protected function _getCachedSummary($issueKey)
    {
        list($project, $number) = $this->_interpretIssueKey($issueKey);
        $cacheFile = $this->_getCacheFile($project);

        if (!is_file($cacheFile)) {
            return false;
        }
        $fileData = file_get_contents($cacheFile);
        $key = $this->_getCacheStringKey($number);
        $position = strpos($fileData, $key);

        if (false === $position) {
            return false;
        }

        $fileData = substr($fileData, $position + strlen($key));
        $position = strpos($fileData, "\n");
        if (false !== $position) {
            $fileData = substr($fileData, 0, $position);
        }
        return $fileData;
    }

    /**
     * Get JIRA API
     *
     * @return Api
     */
    protected function _getApi()
    {
        return new Api(
            Config::getInstance()->getNode('jira/url'),
            new Basic(
                Config::getInstance()->getNode('jira/username'),
                Config::getInstance()->getNode('jira/password')
            )
        );
    }

    /**
     * Get issue
     *
     * @param string $issueKey
     * @return \PreCommit\Jira\Issue
     */
    protected function _getIssue($issueKey)
    {
        $api = $this->_getApi();

        /** @var Api\Result $result */
        $result = $api->api(
            Api::REQUEST_GET,
            sprintf("/rest/api/2/issue/%s", $issueKey),
            array('fields' => 'summary')
        );
        return new Issue($result->getResult());
    }

    /**
     * Explode issue key to PROJECT and issue number
     *
     * @param string $issueKey
     * @return array
     */
    protected function _interpretIssueKey($issueKey)
    {
        list($project, $number) = explode('-', $issueKey);
        $project = strtoupper($project);
        return array($project, $number);
    }

    /**
     * Write summary to cache file
     *
     * @param string $issueKey
     * @param string $summary
     * @return $this
     */
    protected function _cacheSummary($issueKey, $summary)
    {
        list($project, $number) = $this->_interpretIssueKey($issueKey);
        $file = $this->_getCacheFile($project);
        $summaryCache = $this->_getCacheStringKey($number) . $summary;
        file_put_contents($file, $summaryCache, FILE_APPEND);
        return $this;
    }

    /**
     * Get cache directory
     *
     * @return string
     */
    protected function _getCacheDir()
    {
        return Config::getInstance()->getCacheDir(COMMIT_HOOKS_ROOT);
    }
}
