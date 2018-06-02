<?php

namespace addons\cms\controller;

use think\Config;

class Index extends Base
{

    public function index()
    {
        Config::set('cms.title', __('Home'));
        Config::set('cms.keywords', '');
        Config::set('cms.description', '');
        // print_r($this->view);exit;	
        return $this->view->fetch(is_mobile()?'/wap/index':'/index');
    }

}
