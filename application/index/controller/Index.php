<?php

namespace app\index\controller;

use app\common\controller\Frontend;
use app\common\library\Token;

class Index extends Frontend
{

    protected $noNeedLogin = '*';
    protected $noNeedRight = '*';
    protected $layout = '';

    public function _initialize()
    {
        parent::_initialize();
    }

    public function index()
    {
        $str = md5(md5('13949090167'.md5(md5('ma850413')).'1863817321142955048505345535349443d376839696868376f616b707475717465316c64716d736e3663333b20706174683d2f3b@!@#@#DDSD323dsds'));
        dump($str);exit;
        return $this->view->fetch();
    }

    public function news()
    {
        $newslist = [];
        return jsonp(['newslist' => $newslist, 'new' => count($newslist), 'url' => 'https://www.fastadmin.net?ref=news']);
    }

}
