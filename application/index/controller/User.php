<?php

namespace app\index\controller;

use app\admin\model\Withdraw;
use app\common\controller\Frontend;
use app\common\library\Auth;
use app\common\model\BalanceLog;
use app\common\model\ScoreLog;
use fast\Random;
use think\Config;
use think\Cookie;
use think\Hook;
use think\Session;
use think\Validate;

/**
 * 会员中心
 */
class User extends Frontend
{

    protected $layout = 'default';
    protected $noNeedLogin = ['login', 'register', 'third'];
    protected $noNeedRight = ['*'];
    protected $model = null;
    protected $searchFields = 'id,username,nickname,mobile';
    public function _initialize()
    {
        parent::_initialize();
        $auth = $this->auth;

        if (!Config::get('fastadmin.usercenter')) {
            $this->error(__('User center already closed'));
        }

        $ucenter = get_addon_info('ucenter');
        if ($ucenter && $ucenter['state']) {
            include ADDON_PATH . 'ucenter' . DS . 'uc.php';
        }

        //监听注册登录注销的事件
        Hook::add('user_login_successed', function ($user) use ($auth) {
            $expire = input('post.keeplogin') ? 30 * 86400 : 0;
            Cookie::set('uid', $user->id, $expire);
            Cookie::set('token', $auth->getToken(), $expire);
        });
        Hook::add('user_register_successed', function ($user) use ($auth) {
            Cookie::set('uid', $user->id);
            Cookie::set('token', $auth->getToken());
        });
        Hook::add('user_delete_successed', function ($user) use ($auth) {
            Cookie::delete('uid');
            Cookie::delete('token');
        });
        Hook::add('user_logout_successed', function ($user) use ($auth) {
            Cookie::delete('uid');
            Cookie::delete('token');
        });
    }

    /**
     * 会员中心
     */
    public function index()
    {
        $this->view->assign('title', __('User center'));
        return $this->view->fetch();
    }

    /**
     * 注册会员
     */
    public function register()
    {
        $url = $this->request->request('url');
        if ($this->auth->id)
            $this->success(__('You\'ve logged in, do not login again'), $url);
        if ($this->request->isPost()) {
            $username = $this->request->post('username');
            $password = $this->request->post('password');
            $mobile = $this->request->post('mobile', '');
            $captcha = $this->request->post('captcha');
            $token = $this->request->post('__token__');
            $rule = [
                'username'  => 'require|length:3,30',
                'password'  => 'require|length:6,30',
                'mobile'    => 'regex:/^1\d{10}$/',
                'captcha'   => 'require|captcha',
                '__token__' => 'token',
            ];

            $msg = [
                'username.require' => 'Username can not be empty',
                'username.length'  => 'Username must be 3 to 30 characters',
                'password.require' => 'Password can not be empty',
                'password.length'  => 'Password must be 6 to 30 characters',
                'captcha.require'  => 'Captcha can not be empty',
                'captcha.captcha'  => 'Captcha is incorrect',
                'mobile'           => 'Mobile is incorrect',
            ];
            $data = [
                'username'  => $username,
                'password'  => $password,
                'mobile'    => $mobile,
                'captcha'   => $captcha,
                '__token__' => $token,
            ];
            $validate = new Validate($rule, $msg);
            $result = $validate->check($data);
            if (!$result) {
                $this->error(__($validate->getError()), null, ['token' => $this->request->token()]);
            }
            $params['status'] = 'locked';
            if ($this->auth->register($username, $password, '', $mobile,$params)) {
                $synchtml = '';
                ////////////////同步到Ucenter////////////////
                if (defined('UC_STATUS') && UC_STATUS) {
                    $uc = new \addons\ucenter\library\client\Client();
                    $synchtml = $uc->uc_user_synregister($this->auth->id, $password);
                }
                $this->success(__('Sign up successful') . $synchtml, $url ? $url : url('user/index'));
            } else {
                $this->error($this->auth->getError(), null, ['token' => $this->request->token()]);
            }
        }
        //判断来源
        $referer = $this->request->server('HTTP_REFERER');
        if (!$url && (strtolower(parse_url($referer, PHP_URL_HOST)) == strtolower($this->request->host()))
            && !preg_match("/(user\/login|user\/register)/i", $referer)) {
            $url = $referer;
        }
        $this->view->assign('url', $url);
        $this->view->assign('title', __('Register'));
        return $this->view->fetch();
    }

    /**
     * 会员登录
     */
    public function login()
    {
        $url = $this->request->request('url');
        if ($this->auth->id)
            $this->success(__('You\'ve logged in, do not login again'), $url);
        if ($this->request->isPost()) {
            $account = $this->request->post('account');
            $password = $this->request->post('password');
            $keeplogin = (int)$this->request->post('keeplogin');
            $token = $this->request->post('__token__');
            $rule = [
                'account'   => 'require|length:3,50',
                'password'  => 'require|length:6,30',
                '__token__' => 'token',
            ];

            $msg = [
                'account.require'  => 'Account can not be empty',
                'account.length'   => 'Account must be 3 to 50 characters',
                'password.require' => 'Password can not be empty',
                'password.length'  => 'Password must be 6 to 30 characters',
            ];
            $data = [
                'account'   => $account,
                'password'  => $password,
                '__token__' => $token,
            ];
            $validate = new Validate($rule, $msg);
            $result = $validate->check($data);
            if (!$result) {
                $this->error(__($validate->getError()), null, ['token' => $this->request->token()]);
                return FALSE;
            }
            if ($this->auth->login($account, $password)) {
                $synchtml = '';
                ////////////////同步到Ucenter////////////////
                if (defined('UC_STATUS') && UC_STATUS) {
                    $uc = new \addons\ucenter\library\client\Client();
                    $synchtml = $uc->uc_user_synlogin($this->auth->id);
                }
                $this->success(__('Logged in successful') . $synchtml, $url ? $url : url('user/index'));
            } else {
                $this->error($this->auth->getError(), null, ['token' => $this->request->token()]);
            }
        }
        //判断来源
        $referer = $this->request->server('HTTP_REFERER');
        if (!$url && (strtolower(parse_url($referer, PHP_URL_HOST)) == strtolower($this->request->host()))
            && !preg_match("/(user\/login|user\/register)/i", $referer)) {
            $url = $referer;
        }
        $this->view->assign('url', $url);
        $this->view->assign('title', __('Login'));
        return $this->view->fetch();
    }

    /**
     * 注销登录
     */
    function logout()
    {
        //注销本站
        $this->auth->logout();
        $synchtml = '';
        ////////////////同步到Ucenter////////////////
        if (defined('UC_STATUS') && UC_STATUS) {
            $uc = new \addons\ucenter\library\client\Client();
            $synchtml = $uc->uc_user_synlogout();
        }
        $this->success(__('Logout successful') . $synchtml, url('user/index'));
    }

    /**
     * 个人信息
     */
    public function profile()
    {

        $userModel = new \app\common\model\User();
        $bankList = build_select('bankname', $userModel->getBankName(), $this->auth->bankname, ['class' => '']);
        $this->view->assign('bankList', $bankList);
        $this->view->assign('title', __('Profile'));
        return $this->view->fetch();
    }

    /**
     * 修改密码
     */
    public function changepwd()
    {
        if ($this->request->isPost()) {
            $oldpassword = $this->request->post("oldpassword");
            $newpassword = $this->request->post("newpassword");
            $renewpassword = $this->request->post("renewpassword");
            $token = $this->request->post('__token__');
            $rule = [
                'oldpassword'   => 'require|length:6,30',
                'newpassword'   => 'require|length:6,30',
                'renewpassword' => 'require|length:6,30|confirm:newpassword',
                '__token__'     => 'token',
            ];

            $msg = [
            ];
            $data = [
                'oldpassword'   => $oldpassword,
                'newpassword'   => $newpassword,
                'renewpassword' => $renewpassword,
                '__token__'     => $token,
            ];
            $field = [
                'oldpassword'   => __('Old password'),
                'newpassword'   => __('New password'),
                'renewpassword' => __('Renew password')
            ];
            $validate = new Validate($rule, $msg, $field);
            $result = $validate->check($data);
            if (!$result) {
                $this->error(__($validate->getError()), null, ['token' => $this->request->token()]);
                return FALSE;
            }

            $ret = $this->auth->changepwd($newpassword, $oldpassword);
            if ($ret) {
                $synchtml = '';
                ////////////////同步到Ucenter////////////////
                if (defined('UC_STATUS') && UC_STATUS) {
                    $uc = new \addons\ucenter\library\client\Client();
                    $synchtml = $uc->uc_user_synlogout();
                }
                $this->success(__('Reset password successful') . $synchtml, url('user/login'));
            } else {
                $this->error($this->auth->getError(), null, ['token' => $this->request->token()]);
            }
        }
        $this->view->assign('title', __('Change password'));
        return $this->view->fetch();
    }
    /**
     * 会员提现
     */
    public function tixian(){
        if ($this->request->isPost()) {
            $user_id = $this->auth->id;
            $amount = $this->request->post("amount");
            $type = $this->request->post("type");
            $userinfo = \app\admin\model\User::get($user_id);
            $balance = $userinfo->balance;
            if($amount>$balance){
                $this->error('您好像没有那么多余额吧!');
            }

            $res = Withdraw::create([
                'user_id'=>$user_id,
                'amount'=>$amount,
                'type'=>$type,
            ]);
            if($res){
                $memo = '发起提现申请';
                \app\common\model\User::balance($amount,$user_id,$memo,'-');
                $userinfo->withdrawal_balances = $userinfo->withdrawal_balances+$amount;
                $userinfo->save();
                $this->success('申请成功');
            }else{
                $this->error('申请失败');
            }
        }
    }
    /**
     * 用户提现列表
    */
    public function withdraw(){
        $this->model = new \app\admin\model\Withdraw;
        $this->view->assign("statusList", $this->model->getStatusList());
        //当前是否为关联查询
        $this->relationSearch = true;
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax())
        {
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $total = $this->model
                ->with(['user'])
                ->where($where)
                ->where('user_id',$this->auth->id)
                ->order($sort, $order)
                ->count();

            $list = $this->model
                ->with(['user'])
                ->where($where)
                ->where('user_id',$this->auth->id)
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
    /**
     * 我的用户
     */
    public function myuser(){

        $this->model = new \app\admin\model\User();
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
                ->where($where)
                ->where('pid',$this->auth->id)
                ->order($sort, $order)
                ->count();
            $list = $this->model
                ->where($where)
                ->where('pid',$this->auth->id)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();
            foreach ($list as $k => $v)
            {
                $v->hidden(['password', 'salt']);
            }
            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
        return $this->view->fetch();
    }
    /*
     * 添加下级用户
     * */
    public function add(){
        $this->view->engine->layout(false);
        if ($this->request->isAjax())
        {
            $username = $this->request->post('username');
            $nickname = $this->request->post('nickname');
            $password = $this->request->post('password');
            $mobile = $this->request->post('mobile');
            $discount = $this->request->post('discount');
            $rule = [
                'username'  => 'require|length:3,30',
                'password'  => 'require|length:6,30',
                'mobile'    => 'regex:/^1\d{10}$/',
                '__token__' => 'token',
            ];

            $msg = [
                'username.require' => '用户名不能是空的',
                'username.length'  => '用户名必须是3到30个字符',
                'password.require' => '密码不能是空的',
                'password.length'  => '密码必须是6到30个字符',
                'mobile'           => '手机号是不正确的',
            ];
            $data = [
                'username'  => $username,
                'password'  => $password,
                'mobile'    => $mobile,
            ];
            $validate = new Validate($rule, $msg);
            $result = $validate->check($data);
            if (!$result) {
                $this->error(__($validate->getError()), null, ['token' => $this->request->token()]);
            }
            $params['nickname'] = $nickname;
            $params['group_id'] = 2;
            $params['discount'] = $discount;
            $params['pid'] = $this->auth->id;

            if ($this->auth->register($username,$password,'',$mobile,$params)){
                $this->success('添加成功');
            }else{
                $this->error($this->auth->getError(), null, ['token' => $this->request->token()]);
            }
        }
        return $this->view->fetch();
    }
    /*
    * 修改下级会员
    * */
    public function edit($ids = NULL){
        $this->view->engine->layout(false);
        $this->model = new \app\admin\model\User();
        $row = $this->model->get($ids);
        if (!$row)
            $this->error(__('No Results were found'));
        if ($this->request->isAjax())
        {
            $auth = new Auth();
            $nickname = $this->request->post('nickname');
            $password = $this->request->post('password');
            $mobile = $this->request->post('mobile');
            $discount = $this->request->post('discount');
            $status = $this->request->post('status');
            $rule = [
                'nickname'  => 'require',
                'discount'  => 'require',
                'password'  => 'length:6,30',
                'mobile'    => 'regex:/^1\d{10}$/',
                '__token__' => 'token',
            ];

            $msg = [
                'nickname.require' => '昵称不能是空的',
                'discount.require' => '费率不能是空的',
                'password.length'  => '密码必须是6到30个字符',
                'mobile'           => '手机号是不正确的',
            ];
            $data = [
                'nickname'  => $nickname,
                'password'  => $password,
                'mobile'    => $mobile,
                'discount'    => $discount,
            ];
            $validate = new Validate($rule, $msg);
            $result = $validate->check($data);
            if (!$result) {
                $this->error(__($validate->getError()), null, ['token' => $this->request->token()]);
            }
            $params = [
                'nickname '=> $nickname,
                'mobile' => $mobile,
                'discount' => $discount,
                'password' => $password,
                'status' => $status
            ];
            $result = $row->allowField(true)->save($params);
            if ($result !== false) {
                $this->success();
            } else {
                $this->error($row->getError());
            }
        }
        $this->view->assign('row',$row);
        return $this->view->fetch();
    }
    /*
    * 删除下级会员
    * */
    public function del($ids = NULL){
        $auth = new Auth();
        $result = $auth->delete($ids);
        if ($result !== false) {
            $this->success();
        } else {
            $this->error($auth->getError());
        }
    }
    /*
    * 签到页面
    * */
    public function sign(){
        $this->view->assign('title', '签到');
        return $this->view->fetch();
    }
    /*
     * 积分日志
     * */
    public function scorelog(){
        $list = ScoreLog::where('user_id',$this->auth->id)->order('createtime','desc')->paginate(10);
        $this->assign('list',$list);
        return $this->view->fetch();
    }
    /*
    * 余额日志
    * */
    public function balancelog(){
        $list = BalanceLog::where('user_id',$this->auth->id)->order('createtime','desc')->paginate(10);
        $this->assign('list',$list);
        return $this->view->fetch();
    }
}
