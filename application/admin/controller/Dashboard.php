<?php

namespace app\admin\controller;

use app\admin\model\User;
use app\common\controller\Backend;
use think\Config;

/**
 * 控制台
 *
 * @icon fa fa-dashboard
 * @remark 用于展示当前系统中的统计数据、统计报表及重要实时数据
 */
class Dashboard extends Backend
{

    /**
     * 查看
     */
    public function index()
    {
        $seventtime = \fast\Date::unixtime('day', -7);
        $paylist = $createlist = [];
        for ($i = 0; $i < 7; $i++)
        {
            $day = date("Y-m-d", $seventtime + ($i * 86400));
            $createlist[$day] = mt_rand(20, 200);
            $paylist[$day] = mt_rand(1, mt_rand(1, $createlist[$day]));
        }
        $hooks = config('addons.hooks');
        $uploadmode = isset($hooks['upload_config_init']) && $hooks['upload_config_init'] ? implode(',', $hooks['upload_config_init']) : 'local';
        $addonComposerCfg = ROOT_PATH . '/vendor/karsonzhang/fastadmin-addons/composer.json';
        Config::parse($addonComposerCfg, "json", "composer");
        $config = Config::get("composer");
        $addonVersion = isset($config['version']) ? $config['version'] : __('Unknown');
        $totaluser = User::count();
        $totalorder = \app\common\model\Order::where('status',1)->count();
        $totalorderamount = \app\common\model\Order::where('status',1)->sum('amount');
        //当天
        $firstDate = date('Y-m-d 00:00:00', time());
        $lastDate = date('Y-m-d H:i:s', strtotime("$firstDate + 1 day")-1);
        $startTime = strtotime($firstDate);
        $endTime = strtotime($lastDate);
        $todayorder = \app\common\model\Order::where('status',1)->where('createtime','between',[$startTime,$endTime])->count();
        $unsettleorder = \app\common\model\Order::where('status',0)->where('createtime','between',[$startTime,$endTime])->count();
        $todayorderamount = \app\common\model\Order::where('status',1)->where('createtime','between',[$startTime,$endTime])->sum('amount');
        $todayusersignup = User::where('createtime','between',[$startTime,$endTime])->count();
        $this->view->assign([
            'totaluser'        => $totaluser,
            'totalviews'       => 219390,
            'totalorder'       => $totalorder,
            'totalorderamount' => $totalorderamount,
            'todayuserlogin'   => 321,
            'todayusersignup'  => $todayusersignup,
            'todayorder'       => $todayorder,
            'unsettleorder'    => $unsettleorder,
            'sevendnu'         => '80%',
            'todayorderamount'=> $todayorderamount,
            'paylist'          => $paylist,
            'createlist'       => $createlist,
            'addonversion'       => $addonVersion,
            'uploadmode'       => $uploadmode
        ]);

        return $this->view->fetch();
    }

}
