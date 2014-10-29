<?php
namespace PreCommit;

/**
 * Class XmlMerger
 *
 * @package PreCommit
 */
class XmlMerger
{
    /**
     * Collection nodes
     *
     * @var array
     */
    protected $_collectionNodes = array();

    /**
     * Xpath of a process node
     *
     * @var string
     */
    protected $_processXpath = 'root';

    /**
     * Append XML node
     *
     * @param \SimpleXMLElement $to
     * @param \SimpleXMLElement $from
     */
    public function xmlAppend(\SimpleXMLElement $to, \SimpleXMLElement $from)
    {
        $toDom   = dom_import_simplexml($to);
        $fromDom = dom_import_simplexml($from);
        $toDom->appendChild($toDom->ownerDocument->importNode($fromDom, true));
    }

    /**
     * Add name of nodes which should a collection
     *
     * @param $xpath
     */
    public function addCollectionNode($xpath)
    {
        $this->_collectionNodes[] = 'root/' . $xpath;
    }

    /**
     * Merge two XML
     *
     * @param \SimpleXMLElement $xmlSource
     * @param \SimpleXMLElement $xmlUpdate
     * @return \SimpleXMLElement
     */
    public function merge($xmlSource, $xmlUpdate)
    {
        if (is_string($xmlSource)) {
            $xmlSource = simplexml_load_string($xmlSource);
        }
        if (is_string($xmlUpdate)) {
            $xmlUpdate = simplexml_load_string($xmlUpdate);
        }

        $this->_merge($xmlSource, $xmlUpdate);
        return $xmlSource;
    }

    /**
     * Merge nodes
     *
     * @param \SimpleXMLElement $xmlSource
     * @param \SimpleXMLElement $xmlUpdate
     * @return $this
     */
    protected function _merge($xmlSource, $xmlUpdate)
    {
        /** @var \SimpleXMLElement $node */
        foreach ($xmlUpdate as $name => $node) {
            $this->_addProcessXpathName($name);
            /** @var \SimpleXMLElement $nodeSource */
            $nodeSource = $xmlSource->$name;
            if ($this->_isCollectionXpath() || !$nodeSource) {
                $this->xmlAppend($xmlSource, $node);
            } else {
                $this->_mergeAttributes($nodeSource, $node);

                if ($node->count()) {
                    //merge child nodes
                    $this->_merge($nodeSource, $node);
                } else {
                    //set only value
                    $nodeSource[0] = (string)$node;
                }
            }
            $this->_unsetProcessXpathName($name);
        }
        return $this;
    }

    /**
     * Add node to process xpath
     *
     * @param string $name
     * @return $this
     */
    protected function _addProcessXpathName($name)
    {
        $this->_processXpath .= '/' . $name;
        return $this;
    }

    /**
     * Remove node name from process xpath
     *
     * @param string $name
     * @return $this
     */
    protected function _unsetProcessXpathName($name)
    {
        $length              = strlen($this->_processXpath);
        $lengthName          = strlen($name) + 1;
        $this->_processXpath = substr($this->_processXpath, 0, $length - $lengthName);
        return $this;
    }

    /**
     * Merge attributes
     *
     * @param \SimpleXMLElement $xmlSource
     * @param \SimpleXMLElement $xmlUpdate
     * @return $this
     */
    protected function _mergeAttributes($xmlSource, $xmlUpdate)
    {
        if (!$xmlSource->getName()) {
            return $this;
        }
        $attributes = (array)$xmlSource->attributes();
        $attributes = isset($attributes['@attributes']) ? $attributes['@attributes'] : array();
        foreach ($xmlUpdate->attributes() as $name => $value) {
            if (isset($attributes[$name])) {
                $xmlSource->attributes()->$name = (string)$value;
            } else {
                $xmlSource->addAttribute($name, (string)$value);
            }
        }
        return $this;
    }

    /**
     * Check if such XPath means plenty nodes
     *
     * @return bool
     */
    protected function _isCollectionXpath()
    {
        return in_array($this->_processXpath, $this->_collectionNodes);
    }
}
