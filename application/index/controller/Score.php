<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/7
 * Time: 11:39
 */

namespace app\index\controller;
use app\common\controller\Frontend;
use app\common\model\Category;
use app\common\model\HttpCurl;
use app\common\model\Scoreproduct;
use think\Log;

class Score extends Frontend
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
     * 积分兑换首页
     * */
    public function index()
    {
        $categorys =  Category::getCategoryArray();
        $this->view->assign('categorys',$categorys);
        $this->view->assign('title', '积分兑换');
        return $this->view->fetch();
    }
    /*
     * 兑换页面渲染
     * */
    public function duihuan($channel)
    {
        switch ($channel)
        {
            case 'woerma':
                $this->view->assign('title', '移动积分沃尔玛兑换');

                break;
            case 'maidelong':
                $this->view->assign('title', '移动积分麦德龙兑换');
                break;
        }
        $lists = Scoreproduct::where('name',$channel)->order('score','asc')->select();
        $this->view->assign('lists',$lists);
        $this->view->assign('channel',$channel);
        return $this->view->fetch('duihuan');
    }
    /*
     * 兑换逻辑
     * */
    public function add()
    {
        $mobile = $this->request->request('mobile');
        $smscode = $this->request->request('smscode');
        $LoginKey = $this->request->request('LoginKey');
        $smstype = $this->request->request('smstype');
        $dosubmit = $this->request->request('dosubmit');
        $channel = $this->request->request('channel');
        $HttpCurl = new HttpCurl();
        $data['MerId'] = '18638173211';
        $data['Phone'] = $mobile;
        $key = $HttpCurl->MD5('ma850413');
        if($dosubmit){
            $data['SmsCode'] = $smscode;
            $data['LoginKey'] = $LoginKey;
            $SignSource = $HttpCurl->MD5($mobile.$key.$data['MerId'].$data['SmsCode'].$LoginKey.'@!@#@#DDSD323dsds');
            $data['SignSource'] = $SignSource;
            $url = "http://120.55.161.115:2222/WemFile/wem_setsms";
            $result = $HttpCurl->callInterfaceCommon($url,$data,'POST','',FALSE);
        }else{
            $SignSource = $HttpCurl->MD5($mobile.$key.$data['MerId'].'@!@#@#DDSD323dsds');
            $data['Smstype'] =  $smstype;
            $data['SignSource'] = $SignSource;
            $url = "http://120.55.161.115:2222/WemFile/wem_getsms";
            $result = $HttpCurl->callInterfaceCommon($url,$data,'POST','',FALSE);
        }
        Log::write('('.$mobile.'-'.date('Y-m-d H:i:s',time()).')，通道为：'.$channel.'：'.$result);
        $result = \GuzzleHttp\json_decode($result);
        if($result->ErrorCode === '000'){
            if($result->ErrorMsg == 'GetSmsSuccess'){
                $this->success('获取验证码成功','',['LoginKey'=>$result->Data]);
            }else{
                $orders = explode(',',$result->Data);
            }
        }else{
            $this->error($result->ErrorMsg);
        }
    }

}