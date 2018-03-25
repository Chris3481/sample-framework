<?php

namespace App\Blocks\Page;

use App\Blocks\BlockAbstract;

class Header extends BlockAbstract
{

    protected $_js = array();
    protected $_css = array();


    public function addJs($js)
    {
        $key = md5($js);
        $this->_js[$key] = $js;
    }

    public function addCss($css)
    {
        $key = md5($css);
        $this->_css[$key] = $css;
    }

    public function renderJs()
    {
        $html = '';
        foreach($this->_js as $path) {
            $url = $this->getSkinUrl('js/'.$path);
            $html .= sprintf('<script type="text/javascript" src="%s"></script>', $url) . PHP_EOL;
        }

        return $html;
    }

    public function renderCss()
    {
        $html = '';
        foreach($this->_css as $path) {
            $url = $this->getSkinUrl('css/'.$path);
            $html .= sprintf('<link href="%s" rel="stylesheet" type="text/css" media="screen"/>', $url) . PHP_EOL;
        }

        return $html;
    }

    public function getPageTitle($separator = '-')
    {
        $title = $this->getApplicationTitle();
        if($root = $this->getLayout()->getBlock('root')){
            if($pageTitle = $root->getTitle()) {
                return $title . ' '.$separator.' '.$pageTitle;
            }
        }
        return $title;
    }

    public function getWrapperCls()
    {
        $handle = $this->getLayout()->getLayoutHandle();
        return str_replace('_', '-', $handle);
    }
}