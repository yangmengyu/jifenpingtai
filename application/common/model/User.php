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
     * 增加会员余额
     * @param int $balance    余额
     * @param int $user_id  会员ID
     * @param string $memo  备注
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
