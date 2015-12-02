<?php
namespace PreCommit\Issue;

use chobie\Jira\Api\Authentication\Basic;
use PreCommit\Jira\Api;
use PreCommit\Jira\Issue;
use Zend\Cache\Storage\Adapter\Filesystem as CacheAdapter;

/**
 * Class JiraAdapter
 *
 * @package PreCommit\Issue
 */
class JiraAdapter extends AbstractAdapter implements AdapterInterface
{
    /**
     * Cache schema version
     */
    const CACHE_SCHEMA_VERSION = 2;

    /**
     * Exception code when issue not found
     */
    const EXCEPTION_CODE_ISSUE_NOT_FOUND = 404;

    /**
     * Issue API object
     *
     * @var Issue
     */
    protected $issue;

    /**
     * Issue API object
     *
     * @var Issue
     */
    protected $issueKey;

    /**
     * Set issue key
     *
     * @param string $issueKey
     */
    public function __construct($issueKey)
    {
        $this->issueKey = (string) $issueKey;
        parent::__construct($issueKey);
    }

    /**
     * Get issue
     *
     * @return \PreCommit\Jira\Issue
     * @throws Api\Exception
     */
    protected function _getIssue()
    {
        if (null !== $this->issue) {
            return $this->issue;
        }

        $result = $this->_getCachedResult($this->issueKey);
        if (!$result) {
            /** @var Api\Result $result */
            $result = $this->_loadIssueData($this->issueKey);
            if (!$result) {
                throw new Api\Exception(
                    "Issue not {$this->issueKey} found.", self::EXCEPTION_CODE_ISSUE_NOT_FOUND
                );
            }
            $this->_cacheResult($this->issueKey, $result->getResult());
        } else {
            $result = new Api\Result($result);
        }
        $this->issue = new Issue($result->getResult());

        return $this->issue;
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
     * @param array  $result
     * @return $this
     */
    protected function _cacheResult($issueKey, $result)
    {
        $cache = $this->_getCache();
        list($project,) = $this->_interpretIssueKey($issueKey);
        $cache->setTags($issueKey, array($project));
        if ($result) {
            $cache->setItem($issueKey, serialize($result));
        } else {
            $cache->removeItem($issueKey);
        }

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
        $data = $this->_getCache()->getItem($issueKey);

        return $data ? unserialize($data) : null;
    }

    /**
     * Get cache directory
     *
     * @return string
     */
    protected function _getCacheDir()
    {
        return $this->getConfig()->getCacheDir();
    }

    /**
     * Get cache adapter
     *
     * @return \Zend\Cache\Storage\Adapter\Filesystem
     */
    protected function _getCache()
    {
        return new CacheAdapter(
            array(
                'cache_dir' => $this->_getCacheDir(),
                'ttl'       => 7200,
                'namespace' => 'issue-jira-'.self::CACHE_SCHEMA_VERSION,
            )
        );
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
    public function getOriginalType()
    {
        return $this->_getIssue()->getIssueType();
    }

    /**
     * Get status name
     *
     * @return string
     * @throws \PreCommit\Jira\Api\Exception
     */
    public function getStatus()
    {
        return $this->normalizeName($this->_getIssue()->getStatusName());
    }

    /**
     * Cache issue
     *
     * @return $this
     * @throws \PreCommit\Jira\Api\Exception
     */
    public function ignoreIssue()
    {
        $this->_cacheResult($this->issueKey, array());

        return $this;
    }
    //endregion

    //region API methods
    /**
     * Load issue by API
     *
     * @param string $issueKey
     * @return \chobie\Jira\Api\Result
     * @throws Api\Exception
     */
    protected function _loadIssueData($issueKey)
    {
        if (!$this->_canRequest()) {
            throw new Api\Exception('Connection params not fully set.');
        }

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
            $this->getConfig()->getNode('tracker/jira/url'),
            new Basic(
                $this->getConfig()->getNode('tracker/jira/username'),
                $this->getConfig()->getNode('tracker/jira/password')
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
        return array('summary', 'issuetype', 'status');
    }

    /**
     * Check if can make a request
     *
     * @return bool
     */
    protected function _canRequest()
    {
        return $this->getConfig()->getNode('tracker/jira/url')
               && $this->getConfig()->getNode('tracker/jira/username')
               && $this->getConfig()->getNode('tracker/jira/password');
    }
    //endregion
}
