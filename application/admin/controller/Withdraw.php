<?php

namespace app\admin\controller;

use app\common\controller\Backend;
use think\Config;
use app\common\model\User;
use app\admin\model\Aop\AlipayFundTransToaccountTransferRequest;
use app\admin\model\Aop\AopClient;

/**
 * 提现列管理
 *
 * @icon fa fa-circle-o
 */
class Withdraw extends Backend
{
    
    /**
     * Withdraw模型对象
     * @var \app\admin\model\Withdraw
     */
    protected $model = null;
    protected $searchFields = 'id,user.nickname,amount';

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Withdraw;
        $this->view->assign("statusList", $this->model->getStatusList());
        $this->view->assign("typeList", $this->model->getTypeList());
    }
    
    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */
    

    /**
     * 查看
     */
    public function index()
    {
        //当前是否为关联查询
        $this->relationSearch = true;
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax())
        {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField'))
            {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $total = $this->model
                    ->with(['user'])
                    ->where($where)
                    ->order($sort, $order)
                    ->count();

            $list = $this->model
                    ->with(['user'])
                    ->where($where)
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
        return $this->view->fetch();
    }
    /*
     * 提现审核逻辑
     * */
    public function shenhe($ids){
        $row = $this->model->get($ids);
        $usermodel = new User();
        $user = $usermodel->get($row->user_id);
        $config = Config::get('site');
        if($this->request->isPost()){
            $data = $this->request->post("row/a");
            $amount = $row['amount'];
            if($amount > $user['withdrawal_balances']){
                $this->error('参数有误(提现中没有那么多余额)');
            }
            if($data['type'] == 'alipay'&&$data['status']== 1){

                $aop = new AopClient();
                $aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
                $aop->appId = $config['appId'];
                $aop->rsaPrivateKey = $config['rsaPrivateKey'];
                $aop->alipayrsaPublicKey = $config['alipayrsaPublicKey'];
                $aop->apiVersion = '1.0';
                $aop->signType = 'RSA2';
                $aop->postCharset='UTF-8';
                $aop->format='json';
                $request = new AlipayFundTransToaccountTransferRequest();
                $payee_account = $user['alipay'];
                $order_id = 'alipay-'.$user['id'].'-'.time();
                /*$request->setBizContent("{" .
                    "\"out_biz_no\":\"$order_id\"," .
                    "\"payee_type\":\"ALIPAY_LOGONID\"," .
                    "\"payee_account\":\"$payee_account\"," .
                    "\"amount\":\"$amount\"" .
                    "}");
                $result = $aop->execute ( $request);

                $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
                $resultCode = $result->$responseNode->code;
                if(!empty($resultCode)&&$resultCode == 10000){
                    $update['withdrawal_balances'] = $user['withdrawal_balances']-$amount;
                    $user->save($update);
                    $row->save([
                        'status'=>1,
                        'remark'=>$data['remark'].'系统在'.date('Y-m-d H:i:s',time()).'向支付宝为：'.$payee_account.'转账'.$amount.'元。（订单号：'.$result->$responseNode->out_biz_no.'）'
                    ]);
                    $this->success('转账成功');
                } else {
                    $this->error($result->$responseNode->sub_msg);
                }*/
                $update['withdrawal_balances'] = $user['withdrawal_balances']-$amount;
                $user->save($update);
                $row->save([
                    'status'=>1,
                    'remark'=>$data['remark'].'系统在'.date('Y-m-d H:i:s',time()).'向支付宝为：'.$payee_account.'转账'.$amount.'元。'
                ]);
                $this->success('转账成功');
            }elseif($data['type'] == 'bank'&&$data['status']== 1){
                $update['withdrawal_balances'] = $user['withdrawal_balances']-$amount;
                $user->save($update);
                $row->save([
                    'status'=>1,
                    'remark'=>$data['remark'].'系统在'.date('Y-m-d H:i:s',time()).'向'.$user['bankname'].'：'.$user['bankcode'].'-（'.$user['bankusername'].'）转账'.$amount.'元。'
                ]);
                $this->success('转账成功');
            }elseif($data['status']== 2){
                $update['withdrawal_balances'] = $user['withdrawal_balances']-$amount;
                $user->save($update);
                $memo = '提现失败退回余额';
                User::balance($amount,$row->user_id,$memo);
                $row->save([
                    'status'=>2,
                    'remark'=>$data['remark']
                ]);
                $this->success('更新状态成功');
            }else{
                $this->success('未更新状态');
            }
        }
        $this->view->assign('user',$user);
        $this->view->assign('row',$row);
        return $this->view->fetch();
    }
    /*
     * 查看提现信息
     * */
    public function detail($ids){
        $row = $this->model->get($ids);
        $this->view->assign('row',$row);
        return $this->view->fetch();
    }
}
