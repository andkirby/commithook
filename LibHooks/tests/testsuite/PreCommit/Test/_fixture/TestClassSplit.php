<?php
/**
 * Class Some_testClass2
 */
class Some_testClass2
{
    /**
     * Render request
     *
     * @param array       $data
     * @param string|null $template
     * @param string|null $blockClass
     * @return $this
     */
    protected function _render(array $data = array(), $template = null, $blockClass = null)
    {
        //create block
        if ($blockClass) {
            $blockClass = '\\App\\Block' . '\\' . $blockClass;
            $block = new $blockClass($data);
        } else {
            $block = new Renderer($data);
        }

        if (!$template) {
            //set template
            $template = $this->getControllerName() . DIRECTORY_SEPARATOR
                . $this->getActionName() . '.phtml';
        }
        $block->setTemplate($template);

        //get action HTML
        $actionHtml = $block->toHtml();

        if (!$this->isAjax()) {
            $block = new Renderer(array('content' => $actionHtml));
            $block->setTemplate('index.phtml');

            //add system messages block
            $message = new Message();
            $message->setTemplate('index/messages.phtml');
            $block->setChild('message', $message);

            echo $block->toHtml();
        } else {
            echo $actionHtml;
        }
        return $this;
    }
}
