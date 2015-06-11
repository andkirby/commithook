<?php
namespace PreCommit\Filter\ShortCommitMsg;

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
    const CACHE_SCHEMA_VERSION = 1;

    /**
     * Filter commit message
     *
     * @param string $content
     * @param string $file
     * @return string
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

        $issueData = $this->_getIssueData($issueKey);
        if (!$issueData) {
            return $content;
        }

        //get commit message verb
        if ($verb) {
            $verb = $this->_interpretVerb($verb);
        } else {
            $verb = $this->_getVerbByIssueType($issueData['type']);
        }
        $full = "$verb $issueKey: {$issueData['summary']}";
        return str_replace($first, $full, $content);
    }

    /**
     * Get verb by issue type
     *
     * @param string $type
     * @return string
     * @throws \PreCommit\Exception
     */
    protected function _getVerbByIssueType($type)
    {
        $type = preg_replace('/[^A-z]/', '_', $type); //normalize name
        $xpath = 'filters/ShortCommitMsg/issue/jira/issue/type/' . $type;
        $generalType = $this->_getConfig()->getNode($xpath);
        if ('task' === $generalType) {
            return $this->_interpretVerb('I');
        } elseif ('bug' === $generalType) {
            return $this->_interpretVerb('F');
        }
        throw new Exception("Invalid type for config node: '$xpath'.");
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
    protected function _getIssueData($issueKey)
    {
        $issueData = $this->_getCachedIssueData($issueKey);
        if ($issueData) {
            return $issueData;
        }

        try {
            $issue = $this->_getIssue($issueKey);
            $issueData = $this->_collectIssueData($issue);

            if (!$this->_isIssueDataValid($issueData)) {
                return false;
            }
        } catch (Api\Exception $e) {
            //add verbosity
            return false;
        } catch (ApiException $e) {
            //add verbosity
            return false;
        }

        $this->_cacheIssue($issueKey, $issue);
        return $issueData;
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
     * @todo Refactoring needed
     */
    protected function _getCachedIssueData($issueKey)
    {
        list($project, $number) = $this->_interpretIssueKey($issueKey);
        $cacheFile = $this->_getCacheFile($project);

        if (!is_file($cacheFile)) {
            //no cache file
            return false;
        }
        $cacheContent = file_get_contents($cacheFile);
        $cacheKey = $this->_getCacheStringKey($number);
        $position = strpos($cacheContent, $cacheKey);

        if (false === $position) {
            //cache not found
            return false;
        }

        //find cache data
        $dataStr = substr($cacheContent, $position + strlen($cacheKey));
        $position = strpos($dataStr, "\n");
        if (false !== $position) {
            //cut target string if it's not in the beginning
            $dataStr = substr($dataStr, 0, $position);
        }

        $fileData = unserialize($dataStr);

        if ($this->_isIssueDataValid($fileData)) {
            //not full data was cached
            //invalidate cache element
            $cacheContent = str_replace($cacheKey . $dataStr . "\n", '', $cacheContent);
            file_put_contents($cacheFile, $cacheContent);
            return false;
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
            array('fields' => 'summary,issuetype')
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
                throw new Exception('JIRA project key is not set. Please add it to issue-key or add by XPath "jira/project" in project configuration file "commithook.xml" within current project.');
            }
            $issueKey = "$project-$issueKey";
        }
        return $issueKey;
    }

    /**
     * Write summary to cache file
     *
     * @param string $issueKey
     * @param Issue $issue
     * @return $this
     */
    protected function _cacheIssue($issueKey, $issue)
    {
        list($project, $number) = $this->_interpretIssueKey($issueKey);
        $file = $this->_getCacheFile($project);
        $cacheString = $this->_getCacheStringKey($number)
                       . serialize($this->_collectIssueData($issue));
        file_put_contents($file, $cacheString . PHP_EOL, FILE_APPEND);
        return $this;
    }

    /**
     * Get data for caching
     *
     * @param Issue $issue
     * @return array
     */
    protected function _collectIssueData($issue)
    {
        return array(
            'summary' => $issue->getSummary(),
            'type'    => $issue->getIssueType()
        );
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
        preg_match('/^([IRFC] )?(([A-Z0-9]+-)?[0-9]+)/', $message, $m);
        if (!$m) {
            return false;
        }
        //skip first match
        array_shift($m);

        $commitVerb = trim(array_shift($m));
        $issueKey = $this->_normalizeIssueKey(array_shift($m));
        return array($commitVerb, $issueKey);
    }

    /**
     * Validate issue data
     *
     * @param array $issueData
     * @return bool
     */
    protected function _isIssueDataValid($issueData)
    {
        return !empty($issueData['summary']) && !empty($issueData['type']);
    }
}
