<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/8
 * Time: 15:31
 */

namespace app\index\controller;
use app\common\controller\Frontend;


class News extends Frontend
{
    protected $layout = 'default';
    protected $noNeedLogin = [''];
    protected $noNeedRight = ['*'];
    protected $model = null;
    public function _initialize()
    {
        parent::_initialize();
    }
    /*
     * 公告详情
     * */
    public function show($id)
    {
        $data = \app\common\model\News::get($id);
        $news = \app\common\model\News::order('createtime','desc')->limit(0,10)->select();
        $this->assign('data',$data);
        $this->assign('news',$news);
        return $this->view->fetch();
    }
}