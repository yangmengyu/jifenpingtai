<?php

namespace app\common\model;

use think\Model;

class Order extends Model
{
    // 表名
    protected $name = 'order';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    
    // 追加属性
    protected $append = [
        'channel_text',
        'status_text'
    ];
    

    
    public function getChannelList()
    {
        return ['unicom_maidelong' => __('Channel unicom_maidelong'),'unicom_woerma' => __('Channel unicom_woerma'),'mobile_maidelong' => __('Channel mobile_maidelong'),'mobile_woerma' => __('Channel mobile_woerma'),'mobile_tmall' => __('Channel mobile_tmall')];
    }     

    public function getStatusList()
    {
        return ['0' => __('Status 0'),'1' => __('Status 1'),'2' => __('Status 2')];
    }     


    public function getChannelTextAttr($value, $data)
    {        
        $value = $value ? $value : (isset($data['channel']) ? $data['channel'] : '');
        $list = $this->getChannelList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getStatusTextAttr($value, $data)
    {        
        $value = $value ? $value : (isset($data['status']) ? $data['status'] : '');
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }




    public function user()
    {
        return $this->belongsTo('User', 'user_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}
