<?php
namespace PreCommit;

/**
 * Message data container
 *
 * @package PreCommit
 */
class Message
{
    /**
     * Whole message body
     *
     * @var string
     */
    public $body;

    /**
     * Issue object
     *
     * @var Issue\AdapterInterface
     */
    public $issue;

    /**
     * Issue key
     *
     * @var string
     */
    public $issueKey;

    /**
     * Commit message short verb
     *
     * @var string
     */
    public $shortVerb;

    /**
     * Commit message verb
     *
     * @var string
     */
    public $verb;

    /**
     * Issue summary
     *
     * @var string
     */
    public $summary;

    /**
     * Head of whole commit message
     *
     * @var string
     */
    public $head;

    /**
     * Custom user message
     *
     * @var string
     */
    public $userBody;

    /**
     * Cap to prevent errors
     *
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        return null;
    }
}
