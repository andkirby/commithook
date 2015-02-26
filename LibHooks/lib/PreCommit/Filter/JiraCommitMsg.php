<?php
namespace PreCommit\Filter;

use PreCommit\Exception;
use PreCommit\Jira\Api;
use chobie\Jira\Api\Authentication\Basic;
use chobie\Jira\Api\Exception as ApiException;
use PreCommit\Jira\Issue;

/**
 * Class validator for check commit message format
 *
 * @package PreCommit\Validator
 */
class JiraCommitMsg implements InterfaceFilter
{
    /**
     * Filter commit message
     *
     * @param string $content
     * @param string $file
     * @return mixed
     */
    public function filter($content, $file = null)
    {
        $arr = explode("\n", $content);
        $first = $arr[0];
        preg_match('/^([IRFC]) ([A-Z0-9]+-[0-9]+)[ ]*$/', $first, $m);

        $row = array_shift($m);
        $verb = array_shift($m);
        $issueKey = array_shift($m);

        try {
            $summary = $this->_getIssueSummary($issueKey);
        } catch (Api\Exception $e) {
            return $content;
        } catch (ApiException $e) {
            return $content;
        }
        $verb = $this->_interpretVerb($verb);
        $full = "$verb $issueKey: $summary";
        return str_replace($row, $full, $content);
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
        $map = array(
            'I' => 'Implemented',
            'F' => 'Fixed',
            'C' => 'CR Changes',
            'R' => 'Refactored',
        );
        if (!isset($map[$verb])) {
            throw new Exception('Unknown verb key.');
        }

        return $map[$verb];
    }

    /**
     * @param $issueKey
     * @return mixed
     */
    protected function _getIssueSummary($issueKey)
    {
        $api = new Api(
            '',
            new Basic(
                '', ''
            )
        );

        /** @var Api\Result $result */
        $result = $api->api(
            Api::REQUEST_GET,
            sprintf("/rest/api/2/issue/%s", $issueKey),
            array('fields' => 'summary')
        );
        $issue = new Issue($result->getResult());
        $summary = $issue->getSummary();
        return $summary;
    }
}
