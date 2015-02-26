<?php
namespace PreCommit\Jira;

use chobie\Jira as JiraLib;

/**
 * Class Api
 *
 * @package PreCommit\Jira
 * @method Api\Result getVersions()
 */
class Api extends JiraLib\Api
{
    /**
     * Process errors flag
     *
     * @var bool
     */
    protected $_processErrors = true;

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
        $debug = false)
    {
        $result = (array)parent::api($method, $url, $data, true, $isFile, $debug);
        if ($result) {
            $this->_processErrors($result);
            $result = new Api\Result($result);
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
    protected function _processErrors($result)
    {
        if ($result instanceof JiraLib\Api\Result) {
            $result = $result->getResult();
        }
        if (!empty($result['errorMessages'])) {
            throw new JiraLib\Api\Exception(
                'API errors: ' . PHP_EOL
                . implode(PHP_EOL, $result['errorMessages'])
            );
        }
        return $this;
    }
}
