<?php

namespace app\common\model;

use think\Model;

class Scoreproduct extends Model
{
    // 表名
    protected $name = 'scoreproduct';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    
    // 追加属性
    protected $append = [
        'name_text'
    ];
    

    
    public function getNameList()
    {
        return ['unicom_maidelong' => __('Name unicom_maidelong'),'unicom_woerma' => __('Name unicom_woerma'),'mobile_maidelong' => __('Name mobile_maidelong'),'mobile_woerma' => __('Name mobile_woerma'),'mobile_tmall' => __('Name mobile_tmall')];
    }     


    public function getNameTextAttr($value, $data)
    {        
        $value = $value ? $value : (isset($data['name']) ? $data['name'] : '');
        $list = $this->getNameList();
        return isset($list[$value]) ? $list[$value] : '';
    }




}
