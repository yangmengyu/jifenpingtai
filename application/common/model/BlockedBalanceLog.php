<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/8
 * Time: 8:58
 */

namespace app\common\model;
use think\Model;


class BlockedBalanceLog Extends Model
{
    // 表名
    protected $name = 'user_blocked_balance_log';
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = '';
    // 追加属性
    protected $append = [
    ];
}