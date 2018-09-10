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
use app\common\model\Order;
use app\common\model\Scoreproduct;
use think\Config;
use think\Log;

class Score extends Frontend
{
    protected $layout = 'default';
    protected $noNeedLogin = [''];
    protected $noNeedRight = ['*'];
    protected $model = null;
    protected $searchFields = 'id,user.nickname,mobile';
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
    public function duihuan($channel=NULL)
    {
        $this->model = new Order();
        $this->view->assign("channelList", $this->model->getChannelList());
        $this->view->assign("statusList", $this->model->getStatusList());
        //当前是否为关联查询
        $this->relationSearch = true;
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax())
        {
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $whereto = ['channel'=>$channel];
            $ChildIds = \app\common\model\User::getChildsId('',$this->auth->id);
            $ChildIds[] = $this->auth->id;

            $total = $this->model
                ->with(['user'])
                ->where($where)
                ->where($whereto)
                ->whereIn('user_id',$ChildIds)
                ->order($sort, $order)
                ->count();

            $list = $this->model
                ->with(['user'])
                ->where($where)
                ->where($whereto)
                ->whereIn('user_id',$ChildIds)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();

            foreach ($list as $row) {

                $row->getRelation('user')->visible(['username','nickname']);
            }
            $list = collection($list)->toArray();
            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
        $cate = Category::where('nickname',$channel)->find();
        $this->view->assign('title',$cate->name);
        $lists = Scoreproduct::where('name',$channel)->order('score','asc')->select();
        $this->view->assign('lists',$lists);
        $this->view->assign('channel',$channel);
        $this->assignconfig('channel', $channel);
        return $this->view->fetch('duihuan');
    }
    /*
     * 兑换逻辑
     * */
    public function add()
    {
        $config = Config::get('site');
        $mobile = $this->request->request('mobile');
        $smscode = $this->request->request('smscode');
        $LoginKey = $this->request->request('LoginKey');
        $smstype = $this->request->request('smstype');
        $dosubmit = $this->request->request('dosubmit');
        $channel = $this->request->request('channel');
        $user_id = $this->auth->id;
        $HttpCurl = new HttpCurl();
        $data['MerId'] = $config['MerId'];
        $data['Phone'] = $mobile;
        $key = $HttpCurl->MD5($config['MerKey']);
        if($dosubmit){
            $result = $HttpCurl->shangbao($mobile,$smscode,$LoginKey);
        }else{
            $result = $HttpCurl->getSms($mobile,$smstype);
        }
        Log::write('('.$mobile.'-'.date('Y-m-d H:i:s',time()).')，通道为：'.$channel.'：'.$result);
        //$result = "{\"Success\":true,\"ErrorCode\":\"000\",\"ErrorTarget\":\"\",\"ErrorMsg\":\"提交成功\",\"Data\":\"WM090717373593452452-50.00,WM090717373652501116-50.00,\"}";
        $result = \GuzzleHttp\json_decode($result);
        if($result->ErrorCode === '000'){
            if($result->ErrorMsg == 'GetSmsSuccess'){
                $this->success('获取验证码成功','',['LoginKey'=>$result->Data]);
            }else{
                $orders = explode(',',$result->Data);
                $success = 0;
                foreach ($orders as $key=>$v) {
                    if($v !== ''){
                        $order_amount = explode('-',$v);
                        $order = $order_amount[0];
                        $amount = $order_amount[1];
                        $return_amount = sprintf("%.2f",$amount*\app\common\model\User::get_userinfo($user_id,'discount'));
                        $area = $HttpCurl->get_mobile_area($mobile);
                        Order::create([
                            'channel'=>$channel,
                            'user_id'=>$user_id,
                            'order'=>$order,
                            'mobile'=>$mobile,
                            'amount'=>$amount,
                            'return_amount'=>$return_amount,
                            'area'=>$area,
                        ]);
                        $success++;
                    }
                }
                if($success > 0){
                    $this->success('上报成功,数量为'.$success.'.');
                }else{
                    $this->error('还没有找到兑换信息,请稍后再次提交!');
                }
            }
        }else{
            $this->error($result->ErrorMsg);
        }
    }
    public function getSmsCode(){
        $mobile = $this->request->request('mobile');
        $smstype = $this->request->request('smstype');
        $HttpCurl = new HttpCurl();
        $result = $HttpCurl->getSms($mobile,$smstype);
        dump($result);exit;
        exit;
        $config = Config::get('site');
        $mobile = $this->request->request('mobile');
        $HttpCurl = new HttpCurl();
        $data['MerId'] = $config['MerId'];
        $data['Phone'] = $mobile;
        $key = $HttpCurl->MD5($config['MerKey']);
        $SignSource = $HttpCurl->MD5($mobile.$key.$data['MerId'].'@!@#@#DDSD323dsds');
        $data['SignSource'] = $SignSource;
        $data['Smstype'] =  $this->request->request('smstype');
        $url = "http://120.55.161.115:2222/WemFile/wem_getsms";

        $result = $HttpCurl->callInterfaceCommon($url,$data,'POST','',FALSE);
        dump($result);exit;

    }

}