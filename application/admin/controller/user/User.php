<?php

namespace app\admin\controller\user;

use app\common\controller\Backend;
use app\common\library\Auth;
use think\Validate;

/**
 * 会员管理
 *
 * @icon fa fa-user
 */
class User extends Backend
{

    protected $relationSearch = true;


    /**
     * @var \app\admin\model\User
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('User');
    }

    /**
     * 查看
     */
    public function index()
    {
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
                    ->with('group')
                    ->where($where)
                    ->order($sort, $order)
                    ->count();
            $list = $this->model
                    ->with('group')
                    ->where($where)
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

    /**
     * 编辑
     */
    public function edit($ids = NULL)
    {
        $row = $this->model->get($ids);
        if (!$row)
            $this->error(__('No Results were found'));
        $this->view->assign('groupList', build_select('row[group_id]', \app\admin\model\UserGroup::column('id,name'), $row['group_id'], ['class' => 'form-control selectpicker']));
        return parent::edit($ids);
    }
    /**
     * 添加
     */
    public function add(){
        if ($this->request->isPost()) {
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
            $params['group_id'] = 1;
            $params['discount'] = $discount;
            $this->auth = new Auth();
            if ($this->auth->register($username,$password,'',$mobile,$params)){
                $this->success('添加成功');
            }else{
                $this->error($this->auth->getError(), null, ['token' => $this->request->token()]);
            }
        }
        return $this->view->fetch();
    }
}
