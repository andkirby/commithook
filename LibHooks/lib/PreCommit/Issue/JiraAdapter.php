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
    protected function getIssue()
    {
        if (null !== $this->issue) {
            return $this->issue;
        }

        $result = $this->getCachedResult($this->issueKey);
        if (!$result) {
            /** @var Api\Result $result */
            $result = $this->loadIssueData($this->issueKey);
            if (!$result) {
                throw new Api\Exception(
                    "Issue not {$this->issueKey} found.",
                    self::EXCEPTION_CODE_ISSUE_NOT_FOUND
                );
            }
            $this->cacheResult($this->issueKey, $result->getResult());
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
    protected function interpretIssueKey($issueKey)
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
    protected function cacheResult($issueKey, $result)
    {
        $cache = $this->getCache();
        list($project) = $this->interpretIssueKey($issueKey);
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
    protected function getCachedResult($issueKey)
    {
        $data = $this->getCache()->getItem($issueKey);

        return $data ? unserialize($data) : null;
    }

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
        return $this->getIssue()->getSummary();
    }

    /**
     * Get issue key
     *
     * @return string
     */
    public function getKey()
    {
        return $this->getIssue()->getKey();
    }

    /**
     * Get issue type
     *
     * @return string
     */
    public function getOriginalType()
    {
        return $this->getIssue()->getIssueType();
    }

    /**
     * Get status name
     *
     * @return string
     * @throws \PreCommit\Jira\Api\Exception
     */
    public function getStatus()
    {
        return $this->normalizeName($this->getIssue()->getStatusName());
    }

    /**
     * Cache issue
     *
     * @return $this
     * @throws \PreCommit\Jira\Api\Exception
     */
    public function ignoreIssue()
    {
        $this->cacheResult($this->issueKey, array());

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
    protected function loadIssueData($issueKey)
    {
        if (!$this->canRequest()) {
            throw new Api\Exception('Connection params not fully set.');
        }

        return $this->getApi()->api(
            Api::REQUEST_GET,
            sprintf($this->getApiUri(), $issueKey),
            array('fields' => $this->getIssueApiFields())
        );
    }

    /**
     * Get JIRA API
     *
     * @return Api
     */
    protected function getApi()
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
    protected function getApiUri()
    {
        return '/rest/api/2/issue/%s';
    }

    /**
     * Get API parameter of issue fields
     *
     * @return string
     */
    protected function getIssueApiFields()
    {
        return implode(',', $this->getIssueRequestFields());
    }

    /**
     * Get issue request fields list
     *
     * @return string
     */
    protected function getIssueRequestFields()
    {
        return array('summary', 'issuetype', 'status');
    }

    /**
     * Check if can make a request
     *
     * @return bool
     */
    protected function canRequest()
    {
        return $this->getConfig()->getNode('tracker/jira/url')
               && $this->getConfig()->getNode('tracker/jira/username')
               && $this->getConfig()->getNode('tracker/jira/password');
    }
    //endregion
}
