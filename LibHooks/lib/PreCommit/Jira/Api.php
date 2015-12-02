<?php
namespace PreCommit\Jira;

use chobie\Jira as JiraLib;
use chobie\Jira\Api\Authentication\AuthenticationInterface;
use chobie\Jira\Api\Client\ClientInterface;

/**
 * Class Api
 *
 * @package PreCommit\Jira
 * @method Api\Result getVersions()
 */
class Api extends JiraLib\Api
{
    /**
     * Exception code when one of credentials is empty
     */
    const ERROR_EMPTY_CREDENTIALS = 401;

    /**
     * Process errors flag
     *
     * @var bool
     */
    protected $processErrors = true;

    /**
     * Check credentials
     *
     * @param string                  $endpoint
     * @param AuthenticationInterface $authentication
     * @param ClientInterface         $client
     * @throws Api\Exception
     */
    public function __construct(
        $endpoint,
        AuthenticationInterface $authentication,
        ClientInterface $client = null
    ) {
        if (!$authentication->getId() || !$authentication->getPassword()) {
            throw new Api\Exception('Username or password is empty.', self::ERROR_EMPTY_CREDENTIALS);
        }
        parent::__construct($endpoint, $authentication, $client);
    }

    /**
     * Make API request
     *
     * @param string $method
     * @param string $url
     * @param array  $data
     * @param bool   $asJson
     * @param bool   $isFile
     * @param bool   $debug
     * @return JiraLib\Api\Result
     */
    public function api(
        $method = Api::REQUEST_GET,
        $url = '',
        $data = array(),
        $asJson = false,
        $isFile = false,
        $debug = false
    ) {
        $result = parent::api($method, $url, $data, true, $isFile, $debug);
        if ($result) {
            $this->processErrors($result);
            if (!$asJson) {
                $result = new Api\Result($result);
            }
        }

        return $result;
    }

    /**
     * Process JIRA API errors
     *
     * @param JiraLib\Api\Result|array $result
     * @throws JiraLib\Api\Exception
     * @return $this
     */
    protected function processErrors($result)
    {
        if ($result instanceof JiraLib\Api\Result) {
            $result = $result->getResult();
        }
        if (!empty($result['errorMessages'])) {
            throw new JiraLib\Api\Exception(
                'API errors: '.PHP_EOL
                .implode(PHP_EOL, $result['errorMessages'])
            );
        }

        return $this;
    }
}
