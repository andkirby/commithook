<?php
namespace PreCommit\Issue;

use Github\Client as Api;
use Zend\Cache\Storage\Adapter\Filesystem as CacheAdapter;

/**
 * Class GitHubAdapter
 *
 * @package PreCommit\Issue
 */
class GitHubAdapter extends AdapterAbstract implements AdapterInterface
{
    /**
     * Cache schema version
     */
    const CACHE_SCHEMA_VERSION = 0;

    /**
     * Exception code when issue not found
     */
    const EXCEPTION_CODE_ISSUE_NOT_FOUND = 404;

    /**
     * Issue data
     *
     * @var array
     */
    protected $_issue;

    /**
     * Issue key
     *
     * @var string
     */
    protected $_issueKey;

    /**
     * Allowed labels which can be used to determine issue type
     *
     * @var array
     */
    protected $_labelTypes = array(
        'bug', 'enhancement'
    );

    /**
     * Default issue type (label)
     *
     * @var array
     */
    protected $_defaultLabelType = 'enhancement';

    /**
     * GitHub API client
     *
     * @var Api
     */
    protected $_api;

    /**
     * Set issue key
     *
     * @param string $issueKey
     */
    public function __construct($issueKey)
    {
        parent::__construct($issueKey);
        $this->_issueKey = (string)$issueKey;
    }

    /**
     * Get issue
     *
     * @return array
     * @throws \PreCommit\Issue\Exception
     */
    protected function _getIssue()
    {
        if (null === $this->_issue) {
            $this->_issue = $this->_getCachedResult(
                $this->_getCacheKey()
            );
            if (!$this->_issue) {
                $this->_issue = $this->_loadIssueData();
                if (!$this->_issue) {
                    throw new Exception(
                        "Issue not {$this->_issueKey} found.",
                        self::EXCEPTION_CODE_ISSUE_NOT_FOUND
                    );
                }
                $this->_cacheResult(
                    $this->_getCacheKey(), $this->_issue
                );
            }
        }
        return $this->_issue;
    }

    /**
     * Get issue number
     *
     * @return int
     */
    protected function _getIssueNumber()
    {
        return (int)ltrim($this->_issueKey, '#');
    }

    //region Caching methods
    /**
     * Get cache key
     *
     * @return string
     */
    protected function _getCacheKey()
    {
        return $this->_getVendorName() . '-' . $this->_getRepositoryName()
               . '-' . $this->_getIssueNumber();
    }

    /**
     * Write summary to cache file
     *
     * @param string $key
     * @param array  $result
     * @return $this
     */
    protected function _cacheResult($key, $result)
    {
        if ($result) {
            $this->_getCache()->setItem($key, serialize($result));
        } else {
            $this->_getCache()->removeItem($key);
        }
        return $this;
    }

    /**
     * Get cache summary string
     *
     * @param string $key
     * @return string|bool
     */
    protected function _getCachedResult($key)
    {
        $data = $this->_getCache()->getItem($key);
        return $data ? unserialize($data) : null;
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
                'namespace' => 'issue-github-' . self::CACHE_SCHEMA_VERSION
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
        $issue = $this->_getIssue();
        return $issue['title'];
    }

    /**
     * Get issue key
     *
     * @return string
     */
    public function getKey()
    {
        $issue = $this->_getIssue();
        return $issue['number'];
    }

    /**
     * Get issue type
     *
     * @return string
     */
    public function getOriginalType()
    {
        $issue = $this->_getIssue();
        if (!empty($issue['labels'])) {
            foreach ($issue['labels'] as $label) {
                if (in_array($label['name'], $this->_labelTypes)) {
                    return $label['name'];
                }
            }
        }
        return $this->_defaultLabelType;
    }

    /**
     * Get status name
     *
     * @return string
     */
    public function getStatus()
    {
        $issue = $this->_getIssue();
        return $this->_normalizeName($issue['state']);
    }

    /**
     * Cache issue
     *
     * @return $this
     */
    public function ignoreIssue()
    {
        $this->_cacheResult($this->_getCacheKey(), array());
        return $this;
    }
    //endregion

    //region API methods
    /**
     * Load issue by API
     *
     * @return array
     * @throws Exception
     */
    protected function _loadIssueData()
    {
        if (!$this->_canRequest()) {
            throw new Exception('Connection params not fully set.');
        }
        return $this->_getApi()->issue()->show(
            $this->_getVendorName(),
            $this->_getRepositoryName(),
            $this->_getIssueNumber()
        );
    }

    /**
     * Get GitHub API
     *
     * @return Api
     */
    protected function _getApi()
    {
        if ($this->_api === null) {
            $this->_api = new Api();
            $this->_api->authenticate('andkirby', 'gigaleon33');
        }
        return $this->_api;
    }

    /**
     * Check if can make a request
     *
     * @return bool
     */
    protected function _canRequest()
    {
        return $this->_getVendorName()
               && $this->_getRepositoryName();
    }
    //endregion

    /**
     * Get vendor name
     *
     * @return string
     */
    protected function _getVendorName()
    {
        return $this->_getConfig()->getNode('tracker/github/name');
    }

    /**
     * Get repository name
     *
     * @return string
     */
    protected function _getRepositoryName()
    {
        return $this->_getConfig()->getNode('tracker/github/repository');
    }
}