<?php
/**
 * @license https://raw.githubusercontent.com/andkirby/commithook/master/LICENSE.md
 */
namespace PreCommit\Console\Command\Helper;

use PreCommit\Config as AppConfig;
use Symfony\Component\Console\Helper\Helper;

/**
 * Helper for getting project directory
 *
 * @package PreCommit\Console\Command\Helper
 */
class ValidatorHelper extends Helper
{
    /**
     * Helper name
     */
    const NAME = 'code_validator';
    /**
     * XML path expression (mask) to disabled validators
     */
    const XPATH_ALL_VALIDATORS = "hooks/pre-commit/filetype/%s/validators";
    /**
     * XML path to all file types
     */
    const XPATH_FILE_TYPES = 'hooks/pre-commit/filetype/*';

    /**
     * File types list
     *
     * @var array
     */
    protected $fileTypes;

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * Get file types where mentioned validator
     *
     * @param AppConfig $config
     * @return array
     */
    public function getValidatorFileTypes(AppConfig $config)
    {
        if ($this->fileTypes === null) {
            $nodes = $config->getNodesExpr(
                self::XPATH_FILE_TYPES
            );

            $this->fileTypes = array_keys($nodes);
        }

        return $this->fileTypes;
    }

    /**
     * Fetch validators list
     *
     * @param AppConfig $config
     * @return array
     */
    public function fetchUniqueList(AppConfig $config)
    {
        $list = [];
        foreach ($this->getValidatorFileTypes($config) as $type) {
            $list = array_merge(
                $list,
                $config->getNodeArray(
                    sprintf(self::XPATH_ALL_VALIDATORS, $type)
                )
            );
        }

        return $list ? array_keys($list) : [];
    }

    /**
     * Fetch validators list
     *
     * @param AppConfig $config
     * @return array
     */
    public function fetchListByTypes(AppConfig $config)
    {
        $list = [];
        foreach ($this->getValidatorFileTypes($config) as $type) {
            $list[$type] = $config->getNodeArray(
                sprintf(self::XPATH_ALL_VALIDATORS, $type)
            );
        }

        return $list;
    }
}
