<?php

namespace app\common\model;

use think\Model;

/**
 * 会员模型
 */
class User Extends Model
{

    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    // 追加属性
    protected $append = [
        'url',
    ];

    /**
     * 获取个人URL
     * @param   string  $value
     * @param   array   $data
     * @return string
     */
    public function getUrlAttr($value, $data)
    {
        return "/u/" . $data['id'];
    }

    /**
     * 获取头像
     * @param   string    $value
     * @param   array     $data
     * @return string
     */
    public function getAvatarAttr($value, $data)
    {
        return $value ? $value : '/assets/img/avatar.png';
    }

    /**
     * 获取会员的组别
     */
    public function getGroupAttr($value, $data)
    {
        return UserGroup::get($data['group_id']);
    }

    /**
     * 获取验证字段数组值
     * @param   string    $value
     * @param   array     $data
     * @return  object
     */
    public function getVerificationAttr($value, $data)
    {
        $value = array_filter((array) json_decode($value, TRUE));
        $value = array_merge(['email' => 0, 'mobile' => 0], $value);
        return (object) $value;
    }

    /**
     * 设置验证字段
     * @param mixed $value
     * @return string
     */
    public function setVerificationAttr($value)
    {
        $value = is_object($value) || is_array($value) ? json_encode($value) : $value;
        return $value;
    }

    /**
     * 变更会员积分
     * @param int $score    积分
     * @param int $user_id  会员ID
     * @param string $memo  备注
     */
    public static function score($score, $user_id, $memo)
    {
        $user = self::get($user_id);
        if ($user)
        {
            $before = $user->score;
            $after = $user->score + $score;
            $level = self::nextlevel($after);
            //更新会员信息
            $user->save(['score' => $after, 'level' => $level]);
            //写入日志
            ScoreLog::create(['user_id' => $user_id, 'score' => $score, 'before' => $before, 'after' => $after, 'memo' => $memo]);
        }
    }
    /**
     * 变更会员余额
     * @param int $balance    余额
     * @param int $user_id  会员ID
     * @param string $memo  备注
     * @param string $type  加减类型
     */
    public static function balance($balance, $user_id, $memo,$type='+')
    {
        $user = self::get($user_id);
        if ($user)
        {
            $before = $user->balance;
            if($type == '+'){
                $after = $user->balance + $balance;
            }else{
                $after = $user->balance - $balance;
            }

            //更新会员信息
            $user->save(['balance' => $after]);
            //写入日志
            BalanceLog::create(['user_id' => $user_id, 'balance' => $balance, 'before' => $before, 'after' => $after, 'memo' => $memo,'type'=>$type]);
        }
    }
    /*
     * 变更会员待结算余额
     * @param int $balance  余额
     * @param int $user_id  会员ID
     * @param string $memo  备注
     * @param string $type  加减类型
     * */
    public static function blocked_balances($blocked_balances, $user_id, $memo,$type='+')
    {
        $user = self::get($user_id);
        if ($user)
        {
            $before = $user->blocked_balances;
            if($type == '+'){
                $after = $user->blocked_balances + $blocked_balances;
            }else{
                $after = $user->blocked_balances - $blocked_balances;
            }

            //更新会员信息
            $user->save(['blocked_balances' => $after]);
            //写入日志
            BlockedBalanceLog::create(['user_id' => $user_id, 'balance' => $blocked_balances, 'before' => $before, 'after' => $after, 'memo' => $memo,'type'=>$type]);
        }
    }
    /*
     * 订单增加待结算余额 上下级逻辑计算
     * @param int $blocked_balances  待结算余额
     * @param int $user_id  会员ID
     * @param string $type  对应折扣
     * */
    public static function add_blocked_balances($blocked_balances,$user_id,$type="discount"){
        $user = self::get($user_id);
        if ($user)
        {
            $user_blocked_balances = sprintf("%.2f",$blocked_balances*$user->$type);
            $mome = '做单返费'.$user_blocked_balances.'元';
            self::blocked_balances($user_blocked_balances,$user->id,$mome);
            if($user->pid !== 0){
                $parent_user = self::get($user->pid);
                if($parent_user){
                    $parent_user_blocked_balances = sprintf("%.2f",$blocked_balances*$parent_user->$type)-$user_blocked_balances;
                    $mome = '下级用户：'.$user->nickname.'做单给您的返费';
                    self::blocked_balances($parent_user_blocked_balances,$parent_user->id,$mome);
                }
            }
        }
    }
    /*
     * 获取用户信息
     * @param int $user_id  会员ID
     * @param string $field  对应字段
     * */
    public static function get_userinfo($user_id,$field = NULL){
        $user = self::get($user_id);
        if($field){
            return $user->$field;
        }else{
            return $user;
        }

    }
    /*
     * 获取下级用户id号
     * @param array $cate  传入数据
     * @param int $pid  当前id
     * */
    Static function getChildsId ($cate='', $pid) {
        if(empty($cate)){
            $cate = self::select();
        }
        $arr = array();
        foreach ($cate as $v) {
            if ($v['pid'] == $pid) {
                $arr[] = $v['id'];
                $arr = array_merge($arr, self::getChildsId($cate, $v['id']));
            }
        }
        return $arr;
    }

    /**
     * 根据积分获取等级
     * @param int $score 积分
     * @return int
     */
    public static function nextlevel($score = 0)
    {
        $lv = array(1 => 0, 2 => 30, 3 => 100, 4 => 500, 5 => 1000, 6 => 2000, 7 => 3000, 8 => 5000, 9 => 8000, 10 => 10000);
        $level = 1;
        foreach ($lv as $key => $value)
        {
            if ($score >= $value)
            {
                $level = $key;
            }
        }
        return $level;
    }

    /**
     * 获取银行数组
     */
    public function getBankName(){
        return [
            '工商银行'=>'工商银行',
            '中国银行'=>'中国银行',
            '交通银行'=>'交通银行',
            '中信银行'=>'中信银行',
            '光大银行'=>'光大银行',
            '华夏银行'=>'华夏银行',
            '民生银行'=>'民生银行',
            '广发银行'=>'广发银行',
            '平安银行'=>'平安银行',
            '招商银行'=>'招商银行',
            '兴业银行'=>'兴业银行',
            '浦发银行'=>'浦发银行',
            '北京银行'=>'北京银行',
            '农业银行'=>'农业银行',
            '建设银行'=>'建设银行',
        ];
    }

}
