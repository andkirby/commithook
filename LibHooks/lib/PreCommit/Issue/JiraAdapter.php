<?php
namespace PreCommit\Issue;

use chobie\Jira\Api\Authentication\Basic;
use PreCommit\Config;
use PreCommit\Jira\Api;
use PreCommit\Jira\Issue;

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
     * Exception code when issue not found
     */
    const EXCEPTION_CODE_ISSUE_NOT_FOUND = 404;

    /**
     * Issue API object
     *
     * @var Issue
     */
    protected $_issue;

    /**
     * Issue API object
     *
     * @var Issue
     */
    protected $_issueKey;

    /**
     * Set issue key
     *
     * @param string $issueKey
     */
    public function __construct($issueKey)
    {
        $this->_issueKey = (string)$issueKey;
    }

    /**
     * Get issue
     *
     * @return \PreCommit\Jira\Issue
     * @throws Api\Exception
     */
    protected function _getIssue()
    {
        if (null !== $this->_issue) {
            return $this->_issue;
        }

        $result = $this->_getCachedResult($this->_issueKey);
        if (!$result) {
            /** @var Api\Result $result */
            $result = $this->_loadIssueData($this->_issueKey);
            if (!$result) {
                throw new Api\Exception(
                    "Issue not {$this->_issueKey} found.", self::EXCEPTION_CODE_ISSUE_NOT_FOUND
                );
            }
            $this->_cacheResult($this->_issueKey, $result->getResult());
        } else {
            $result = new Api\Result($result);
        }
        $this->_issue = new Issue($result->getResult());
        return $this->_issue;
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

    //region Caching methods
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
    //endregion

    //region Interface methods
    /**
     * Get issue summary
     *
     * @return string
     */
    public function getSummary()
    {
        return $this->_getIssue()->getSummary();
    }

    /**
     * Get issue key
     *
     * @return string
     */
    public function getKey()
    {
        return $this->_getIssue()->getKey();
    }

    /**
     * Get issue type
     *
     * @return string
     */
    public function getIssueType()
    {
        return $this->_getIssue()->getIssueType();
    }
    //endregion

    //region API methods
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
            sprintf($this->_getApiUri(), $issueKey),
            array('fields' => $this->_getIssueApiFields())
        );
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
     * Get request API URL
     *
     * "%s" to set issue key
     *
     * @return string
     */
    protected function _getApiUri()
    {
        return '/rest/api/2/issue/%s';
    }

    /**
     * Get API parameter of issue fields
     *
     * @return string
     */
    protected function _getIssueApiFields()
    {
        return implode(',', $this->_getIssueRequestFields());
    }

    /**
     * Get issue request fields list
     *
     * @return string
     */
    protected function _getIssueRequestFields()
    {
        return array('summary', 'issuetype');
    }
    //endregion
}
