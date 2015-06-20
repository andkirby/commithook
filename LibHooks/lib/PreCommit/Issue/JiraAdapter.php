<?php
namespace PreCommit\Issue;

use chobie\Jira\Api\Authentication\Basic;
use PreCommit\Config;
use PreCommit\Jira\Api;
use PreCommit\Jira\Issue;
use chobie\Jira\Api\Exception as ApiException;

/**
 * Class JiraAdapter
 *
 * @package PreCommit\Issue
 */
class JiraAdapter extends AdapterAbstract implements AdapterInterface
{
    /**
     * Cache schema version
     */
    const CACHE_SCHEMA_VERSION = 1;

    /**
     * Issue API object
     *
     * @var Issue
     */
    protected $_issue;

    /**
     * Get issue
     *
     * @param string $issueKey
     * @return \PreCommit\Jira\Issue
     * @throws Api\Exception
     */
    protected function _getIssue($issueKey)
    {
        if (null === $this->_issue) {
            $result = $this->_getCachedResult($issueKey);
            $cached = (bool)$result;
            if (!$cached) {
                try {
                    /** @var Api\Result $result */
                    $result = $this->_loadIssueData($issueKey);
                    if (!$result) {
                        throw new Api\Exception('Issue request result is empty.');
                    }
                } catch (Api\Exception $e) {
                    //add verbosity
                    return false;
                } catch (ApiException $e) {
                    //add verbosity
                    return false;
                }
            } else {
                $result = new Api\Result($result);
            }
            $this->_issue = new Issue($result->getResult());
            if (!$cached) {
                $this->_cacheResult($this->_issue->getKey(), $result->getResult());
            }
        }
        return $this->_issue;
    }

    /**
     * Write summary to cache file
     *
     * @param string $issueKey
     * @param array $result
     * @return $this
     */
    protected function _cacheResult($issueKey, $result)
    {
        list($project, $number) = $this->_interpretIssueKey($issueKey);
        $file = $this->_getCacheFile($project);
        $cacheString = $this->_getCacheStringKey($number)
                       . serialize($result);
        file_put_contents($file, $cacheString . PHP_EOL, FILE_APPEND);
        return $this;
    }

    /**
     * Get cache summary string
     *
     * @param string $issueKey
     * @return string|bool
     * @todo Refactoring needed
     */
    protected function _getCachedResult($issueKey)
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

        return unserialize($dataStr);
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
     * Get cache directory
     *
     * @return string
     */
    protected function _getCacheDir()
    {
        return $this->_getConfig()->getCacheDir(COMMIT_HOOKS_ROOT);
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
     * Get config model
     *
     * @return Config
     */
    protected function _getConfig()
    {
        return Config::getInstance();
    }

    /**
     * Load issue by API
     *
     * @param string $issueKey
     * @return \chobie\Jira\Api\Result
     */
    protected function _loadIssueData($issueKey)
    {
        return $this->_getApi()->api(
            Api::REQUEST_GET,
            sprintf("/rest/api/2/issue/%s", $issueKey),
            array('fields' => 'summary,issuetype')
        );
    }

    /**
     * Get issue summary
     *
     * @return string
     */
    public function getSummary()
    {
        return $this->_issue->getSummary();
    }

    /**
     * Get issue key
     *
     * @return string
     */
    public function getKey()
    {
        return $this->_issue->getKey();
    }

    /**
     * Get issue type
     *
     * @return string
     */
    public function getType()
    {
        return $this->_issue->getIssueType();
    }
}
