<?php

namespace App\Model\core;

class Layout extends AbstractModel
{

    protected $_xml = array();
    protected $_blocks = array();


    public function loadLayout()
    {
        $this->getLayoutXml();

        $defaultXml = $this->getHandlerXml('default');
        $handleXml = $this->getHandlerXml();

        try {
            $this->generateBlocks($defaultXml);
            $this->generateBlocks($handleXml);

        } catch (\Exception $e) {
            echo $e->getMessage();
        }

        return $this;
    }

    public function generateBlocks($nodes)
    {
        foreach ($nodes as $node) {

            if ($node->block) {

                if (count($node->block) > 1) {
                    foreach ($node->block as $child) {
                        $this->_generateBlocks($child, $node);
                    }
                } else {
                    $this->_generateBlocks($node->block, $node);
                }
                $this->generateBlocks($node->block);
            }
            if ($node->reference) {

                if (count($node->reference) > 1) {
                    foreach ($node->reference as $reference) {
                        $this->_generateReference($reference);
                    }
                } else {
                    $this->_generateReference($node->reference);
                }
                $this->generateBlocks($node->reference);
            }
            if ($node->action) {
                $this->_generateAction($node);
            }
        }
    }

    /**
     * @param \SimpleXMLElement $node
     * @param \SimpleXMLElement $parent
     * @return \Blocks\BlockAbstract
     * @throws \Exception
     */
    private function _generateBlocks($node, $parent = null)
    {
        $block = $this->_createBlock($node);
        $block->setLayout($this);

        if ($parent['name']) {

            if ($this->getBlock((string)$parent['name'])) {
                $parentBlock = $this->getBlock((string)$parent['name']);
            } else {
                $parentBlock = $this->_createBlock($parent);
            }

            if (!$parentBlock->addChild($block, (string)$node['name'])) {
                throw new \Exception("Unable to add child");
            }
        }

        return $this;
    }

    /**
     * @param \SimpleXMLElement $node
     * @return \Blocks\BlockAbstract
     * @throws \Exception
     */
    private function _generateReference($node)
    {
        $parentName = (string)$node['name'];
        if ($parent = $this->getBlock($parentName)) {

            if (count($node->block) > 1) {
                foreach ($node->block as $child) {
                    $block = $this->_createBlock($child);
                    $parent->addChild($block, (string)$child['name']);
                }
            } elseif ($node->block) {
                $child = $node->block;
                $block = $this->_createBlock($node->block);
                $parent->addChild($block, (string)$child['name']);
            } elseif ($node->remove) {
                $blockName = (string)$node->remove['name'];
                $parent->removeChild($blockName);
            }
        } else {
            throw new \Exception("parent block $parentName is not defined");
        }

        return $this;
    }

    private function _generateAction($node)
    {
        $blockName = $node['name'];
        if ($block = $this->getBlock($blockName)) {

            foreach ($node->action as $action) {
                $method = (string)$action['method'];
                $params = (array)$action;
                unset($params['@attributes']);

                if (method_exists($block, $method)) {
                    call_user_func_array(array($block, $method), $params);
                }
            }
        }

        return $this;
    }

    /**
     * @return \SimpleXMLElement
     */
    protected function getLayoutXml()
    {
        if (!$this->_xml) {
            $config = $this->getConfig();
            $layoutPath = $config['path']['layout'];
            $xml = simplexml_load_file($layoutPath);

            $this->_xml = $xml;
        }

        return $this->_xml;
    }

    public function getLayoutHandle()
    {
        $handle = '';

        $controller = \App::getController();
        $action = \App::getAction();

        $controllerNameArr = explode('\\', get_class($controller));
        $controllerName = str_replace('controller', '', strtolower($controllerNameArr[1]));
        $actionName = str_replace('Action', '', $action);
        $handle = $controllerName . '_' . $actionName;

        return $handle;
    }

    /**
     * @param string|null $handle
     * @return \SimpleXMLElement $node
     */
    public function getHandlerXml($handle = null)
    {
        if (!$handle) {
            $handle = $this->getLayoutHandle();
        }

        return $this->_xml->xpath($handle);
    }

    /**
     * @param string $identifier
     * @return \Blocks\BlockAbstract
     */
    public function getBlock($identifier)
    {
        if (isset($this->_blocks[(string)$identifier])) {
            return $this->_blocks[(string)$identifier];
        }
        return false;
    }

    /**
     * @return array
     */
    public function getBlocks()
    {
        return $this->_blocks;
    }

    /**
     * Block Factory
     *
     * @param     string $type
     * @param     string $name
     * @return    \Blocks\BlockAbstract
     * @throws    \Exception
     */
    public function createBlock($type, $name = '')
    {
        $typeArr = explode('/', $type);
        $typeArr = array_map('ucfirst', $typeArr);
        $type = '\\Blocks\\' . implode('\\', $typeArr);
        if (class_exists($type)) {
            /** @var \Blocks\BlockAbstract $block */

            $block = new $type();
            $block->setLayout($this);

            $name = (!empty($name)) ? (string)$name : 'ANONYMOUS_'.sizeof($this->_blocks);
            $block->setName($name);


            $this->_blocks[$name] = $block;
        } else {
            throw new \Exception("the class $type is not found");
        }

        return $block;
    }

    /**
     * @param \SimpleXMLElement $node
     * @return \Blocks\BlockAbstract
     * @throws \Exception
     */
    private function _createBlock($node)
    {
        $cls = null;

        if (!$node) return;

        if (isset($node['type']) && isset($node['name'])) {

            $block  = $this->createBlock($node['type'], $node['name']);

            if (isset($node['template']) && (string)$node['template'] != "") {
                $block->setTemplate((string)$node['template']);
            }

        } else {
            throw new \Exception("block type or name can't be undefined");
        }

        return $block;
    }

}