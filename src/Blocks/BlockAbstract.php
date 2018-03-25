<?php

namespace App\Blocks;

use App;
use App\Model\core\AbstractModel;
use App\Model\core\Layout;

abstract class BlockAbstract extends AbstractModel
{
    protected $_name = '';
    protected $_title = '';
    protected $_buttons = array();
    protected $_children = array();
    protected $_template = false;
    protected $_layout = null;

    /**
     * @param BlockAbstract $block
     * @param string $name
     * @return bool
     * @throws \Exception
     */
    public function addChild($block, $name = false)
    {
        if ($block && $name) {
            $this->_children[$name] = $block;
        } else {
            throw new \Exception("Can't add child with a empty name");
        }
        return true;
    }

    /**
     * @param string $name
     * @return BlockAbstract|null
     */
    public function getChild($name)
    {
        if (isset($this->_children[$name])) {
            return $this->_children[$name];
        }
        return null;
    }

    /**
     * @param string $name
     * @return BlockAbstract
     */
    public function removeChild($name)
    {
        if (isset($this->_children[$name])) {
            unset($this->_children[$name]);
        }

        return $this;
    }

    /**
     * @param Layout $layout
     * @return BlockAbstract $this
     */
    public function setLayout($layout)
    {
        $this->_layout = $layout;

        $this->_prepareLayout();

        return $this;
    }

    /**
     * @return Layout
     */
    public function getLayout()
    {
        return $this->_layout;
    }

    /**
     * Get child blocks
     *
     * @return array
     */
    public function getChildren()
    {
        return $this->_children;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->_name = (string)$name;
    }

    /**
     * @return bool
     */
    public function getName()
    {
        return $this->_name;
    }

    public function setTitle($title)
    {
        $this->_title = $title;
    }

    public function getTitle()
    {
        return $this->_title;
    }

    public function setButton($cls, $title, $modal=null)
    {
        $this->_buttons[$cls] = array('name' => $cls, 'title' => $title, 'cls' => $cls, 'modal' => $modal);
    }

    public function getButtons()
    {
        return $this->_buttons;
    }

    public function getButton($cls)
    {
        return isset($this->_buttons[$cls]) ? $this->_buttons[$cls] : null;
    }

    public function getApplicationTitle()
    {
        $config = $this->getConfig();
        if (isset($config['general']['title'])) {
            return $config['general']['title'];
        } else {
            return '';
        }
    }

    /**
     * @param string $template
     * @return $this
     */
    public function setTemplate($template)
    {
        $this->_template = $template;

        return $this;
    }

    /**
     * @return string
     */
    public function getTemplate()
    {
        return $this->_template;
    }

    public function getUrl($url = '/')
    {
        return App::getBaseUrl() . '/' . $url;
    }

    public function getSkinUrl($url = '/')
    {
        $config = $this->getConfig();
        return App::getBaseUrl() . '/' . $config['path']['skin'] . '/' . $url;
    }

    public function getConfig()
    {
        return App::getConfig();
    }

    public function getCredentials()
    {
        return App::getModel('security/Login')->getSession();
    }

    /**
     * @param string $name
     * @param bool $renderChildren
     * @return null
     */
    public function getChildHtml($name = '', $renderChildren = false)
    {
        $html = '';
        if ($block = $this->getChild($name)) {
            if (($children = $block->getChildren()) && $renderChildren) {
                $htmlArr = array();
                foreach ($children as $child) {
                    $htmlArr[] = $child->toHtml();
                }
                $html = implode('', $htmlArr);
            } else {
                $html = $block->toHtml();
            }
        }
        return $html;
    }

    public function toHtml()
    {
        $html = '';
        if ($path = $this->getTemplate()) {
            $config = $this->getConfig();
            $templateDir = $config['path']['template'] . '/';
            $templatePath = $templateDir . $path;
            if (file_exists($templatePath)) {
                ob_start();
                include $templatePath;
                $html = ob_get_contents();
                ob_get_clean();
            }
        }
        return $html;
    }

    protected function _prepareLayout()
    {
        return $this;
    }

}