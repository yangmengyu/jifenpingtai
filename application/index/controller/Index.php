<?php

namespace app\index\controller;

use app\admin\model\Withdraw;
use app\common\controller\Frontend;
use app\common\library\Token;
use app\common\model\Order;

class Index extends Frontend
{

    protected $noNeedLogin = '';
    protected $noNeedRight = '*';
    protected $layout = 'default';

    public function _initialize()
    {
        parent::_initialize();
    }

    public function index()
    {

        $user_id = $this->auth->id;
        $child_userid = \app\common\model\User::getChildsId('',$user_id);
        $data['child_count'] = count($child_userid);
        $child_userid[] = $user_id;
        $data['order_count'] = Order::whereIn('user_id',$child_userid)->count();
        $data['totalamount'] = Order::whereIn('user_id',$child_userid)->where('status',1)->sum('return_amount');
        $firstDate = date('Y-m-d 00:00:00', time());
        $lastDate = date('Y-m-d H:i:s', strtotime("$firstDate + 1 day")-1);
        $startTime = strtotime($firstDate);
        $lastTime = strtotime($lastDate);
        $data['today_order_count'] = Order::where('createtime','between',[$startTime,$lastTime])->whereIn('user_id',$child_userid)->count();
        $data['user'] = \app\common\model\User::get($user_id);
        $data['news'] = \app\common\model\News::order('createtime','desc')->paginate(5);
        $this->view->assign('data',$data);
        $this->view->assign('title','首页');
        return $this->view->fetch();
    }



}
