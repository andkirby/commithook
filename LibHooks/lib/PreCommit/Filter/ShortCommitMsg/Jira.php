<?php
namespace PreCommit\Filter\ShortCommitMessage;

use PreCommit\Config;
use PreCommit\Exception;
use PreCommit\Filter\InterfaceFilter;
use PreCommit\Jira\Api;
use chobie\Jira\Api\Authentication\Basic;
use chobie\Jira\Api\Exception as ApiException;
use PreCommit\Jira\Issue;

/**
 * Class validator for check commit message format
 *
 * @package PreCommit\Validator
 */
class Jira implements InterfaceFilter
{
    /**
     * Cache schema version
     */
    const CACHE_SCHEMA_VERSION = "0";

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
        $interpretResult = $this->_interpretMessageTitle($first);
        if (!$interpretResult) {
            return $content;
        }
        list($verb, $issueKey) = $interpretResult;

        $summary = $this->_getIssueSummary($issueKey);
        if (!$summary) {
            return $content;
        }
        $verb = $this->_interpretVerb($verb);
        $full = "$verb $issueKey: $summary";
        return str_replace($first, $full, $content);
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
            //add verbosity
            return false;
        } catch (ApiException $e) {
            //add verbosity
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
            . "/issues-$project-v" . self::CACHE_SCHEMA_VERSION;
    }

    /**
     * Get cache summary string
     *
     * @param string $issueKey
     * @return string|bool
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
            $this->_getConfig()->getNode('jira/url'),
            new Basic(
                $this->_getConfig()->getNode('jira/username'),
                $this->_getConfig()->getNode('jira/password')
            )
        );
    }

    /**
     * Get issue
     *
     * @param string $issueKey
     * @return \PreCommit\Jira\Issue
     * @throws Api\Exception
     */
    protected function _getIssue($issueKey)
    {
        //TODO Move getting an issue into JIRA namespace.

        /** @var Api\Result $result */
        $result = $this->_getApi()->api(
            Api::REQUEST_GET,
            sprintf("/rest/api/2/issue/%s", $issueKey),
            array('fields' => 'summary')
        );
        if ($result) {
            return new Issue($result->getResult());
        }
        throw new Api\Exception('Result is empty.');
    }

    /**
     * Explode issue key to PROJECT and issue number
     *
     * It takes project key from configuration if it was set.
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
     * Normalize issue-key
     *
     * Add project key to issue number when it did not set.
     *
     * @param string $issueKey
     * @return string
     * @throws \PreCommit\Exception
     */
    protected function _normalizeIssueKey($issueKey)
    {
        if ((string)(int)$issueKey === $issueKey) {
            $project = $this->_getConfig()->getNode('jira/project');
            if (!$project) {
                throw new Exception('JIRA project key is not set. Please add it to issue-key or add by XPath "jira/project" in project configuration file "commithook-project.xml".');
            }
            $issueKey = "$project-$issueKey";
        }
        return $issueKey;
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
        file_put_contents($file, $summaryCache . PHP_EOL, FILE_APPEND);
        return $this;
    }

    /**
     * Get cache directory
     *
     * @return string
     */
    protected function _getCacheDir()
    {
        return $this->_getConfig()->getCacheDir(COMMIT_HOOKS_ROOT);
    }

    /**
     * Get config model
     *
     * @return Config
     */
    protected function _getConfig()
    {
        return Config::getInstance();
    }

    /**
     * Interpret message title
     *
     * @param string $message
     * @return array
     * @throws \PreCommit\Exception
     */
    protected function _interpretMessageTitle($message)
    {
        preg_match('/^([IRFC]) (([A-Z0-9]+-)?[0-9]+)[ ]*$/', $message, $m);
        if (!$m) {
            return false;
        }
        //skip first match
        array_shift($m);

        $commitVerb = array_shift($m);
        $issueKey = $this->_normalizeIssueKey(array_shift($m));
        return array($commitVerb, $issueKey);
    }
}
