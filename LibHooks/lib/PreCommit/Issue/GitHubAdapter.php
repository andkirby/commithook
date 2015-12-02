<?php
namespace PreCommit\Issue;

use Github\Client as Api;
use Zend\Cache\Storage\Adapter\Filesystem as CacheAdapter;

/**
 * Class GitHubAdapter
 *
 * @package PreCommit\Issue
 */
class GitHubAdapter extends AbstractAdapter implements AdapterInterface
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
    protected $issue;

    /**
     * Issue key
     *
     * @var string
     */
    protected $issueKey;

    /**
     * Allowed labels which can be used to determine issue type
     *
     * @var array
     */
    protected $labelTypes
        = array(
            'bug',
            'enhancement',
        );

    /**
     * Default issue type (label)
     *
     * @var array
     */
    protected $defaultLabelType = 'enhancement';

    /**
     * GitHub API client
     *
     * @var Api
     */
    protected $api;

    /**
     * Set issue key
     *
     * @param string $issueKey
     */
    public function __construct($issueKey)
    {
        parent::__construct($issueKey);
        $this->issueKey = (string) $issueKey;
    }

    /**
     * Get issue summary
     *
     * @return string
     */
    public function getSummary()
    {
        $issue = $this->getIssue();

        return $issue['title'];
    }

    /**
     * Get issue
     *
     * @return array
     * @throws \PreCommit\Issue\Exception
     */
    protected function getIssue()
    {
        if (null === $this->issue) {
            $this->issue = $this->getCachedResult(
                $this->getCacheKey()
            );
            if (!$this->issue) {
                $this->issue = $this->loadIssueData();
                if (!$this->issue) {
                    throw new Exception(
                        "Issue not {$this->issueKey} found.",
                        self::EXCEPTION_CODE_ISSUE_NOT_FOUND
                    );
                }
                $this->cacheResult(
                    $this->getCacheKey(),
                    $this->issue
                );
            }
        }

        return $this->issue;
    }

    //region Caching methods

    /**
     * Get cache summary string
     *
     * @param string $key
     * @return string|bool
     */
    protected function getCachedResult($key)
    {
        $data = $this->getCache()->getItem($key);

        return $data ? unserialize($data) : null;
    }

    /**
     * Get cache key
     *
     * @return string
     */
    protected function getCacheKey()
    {
        return $this->getVendorName().'-'.$this->getRepositoryName()
               .'-'.$this->getIssueNumber();
    }

    /**
     * Load issue by API
     *
     * @return array
     * @throws Exception
     */
    protected function loadIssueData()
    {
        if (!$this->canRequest()) {
            throw new Exception('Connection params not fully set.');
        }

        return $this->getApi()->issue()->show(
            $this->getVendorName(),
            $this->getRepositoryName(),
            $this->getIssueNumber()
        );
    }

    /**
     * Write summary to cache file
     *
     * @param string $key
     * @param array  $result
     * @return $this
     */
    protected function cacheResult($key, $result)
    {
        if ($result) {
            $this->getCache()->setItem($key, serialize($result));
        } else {
            $this->getCache()->removeItem($key);
        }

        return $this;
    }

    /**
     * Get cache adapter
     *
     * @return \Zend\Cache\Storage\Adapter\Filesystem
     */
    protected function getCache()
    {
        return new CacheAdapter(
            array(
                'cache_dir' => $this->getCacheDir(),
                'ttl'       => 7200,
                'namespace' => 'issue-github-'.self::CACHE_SCHEMA_VERSION,
            )
        );
    }
    //endregion

    //region Interface methods

    /**
     * Get vendor name
     *
     * @return string
     */
    protected function getVendorName()
    {
        return $this->getConfig()->getNode('tracker/github/name');
    }

    /**
     * Get repository name
     *
     * @return string
     */
    protected function getRepositoryName()
    {
        return $this->getConfig()->getNode('tracker/github/repository');
    }

    /**
     * Get issue number
     *
     * @return int
     */
    protected function getIssueNumber()
    {
        return (int) ltrim($this->issueKey, '#');
    }

    /**
     * Check if can make a request
     *
     * @return bool
     */
    protected function canRequest()
    {
        return $this->getVendorName()
               && $this->getRepositoryName();
    }

    /**
     * Get GitHub API
     *
     * @return Api
     */
    protected function getApi()
    {
        if ($this->api === null) {
            $this->api = new Api();
            $this->api->authenticate(
                $this->getConfig()->getNode('tracker/github/username'),
                $this->getConfig()->getNode('tracker/github/password')
            );
        }

        return $this->api;
    }
    //endregion

    //region API methods

    /**
     * Get cache directory
     *
     * @return string
     */
    protected function getCacheDir()
    {
        return $this->getConfig()->getCacheDir();
    }

    /**
     * Get issue key
     *
     * @return string
     */
    public function getKey()
    {
        $issue = $this->getIssue();

        return $issue['number'];
    }

    /**
     * Get issue type
     *
     * @return string
     */
    public function getOriginalType()
    {
        $issue = $this->getIssue();
        if (!empty($issue['labels'])) {
            foreach ($issue['labels'] as $label) {
                if (in_array($label['name'], $this->labelTypes)) {
                    return $label['name'];
                }
            }
        }

        return $this->defaultLabelType;
    }
    //endregion

    /**
     * Get status name
     *
     * @return string
     */
    public function getStatus()
    {
        $issue = $this->getIssue();

        return $this->normalizeName($issue['state']);
    }

    /**
     * Cache issue
     *
     * @return $this
     */
    public function ignoreIssue()
    {
        $this->cacheResult($this->getCacheKey(), array());

        return $this;
    }
}
