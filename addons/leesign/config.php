<?php

return array (
  0 => 
  array (
    'name' => 'enterimg',
    'title' => '入口图片',
    'type' => 'image',
    'content' => 
    array (
    ),
    'value' => '/uploads/20180410/d87b34f821a4b95a0c138bc933bd8c2f.png',
    'rule' => 'required',
    'msg' => '',
    'tip' => '连签周期',
    'ok' => '',
    'extend' => '',
  ),
  1 => 
  array (
    'name' => 'signnum',
    'title' => '签到所得积分',
    'type' => 'number',
    'content' => 
    array (
    ),
    'value' => '0',
    'rule' => 'number',
    'msg' => '积分必须为正整数',
    'tip' => '必须为正整数，可以为0',
    'ok' => '符合要求',
    'extend' => '',
  ),
  2 => 
  array (
    'name' => 'signstatus',
    'title' => '连签奖励',
    'type' => 'radio',
    'content' => 
    array (
      1 => '启用',
      0 => '关闭',
    ),
    'value' => '1',
    'rule' => 'required',
    'msg' => '',
    'tip' => '',
    'ok' => '',
    'extend' => '',
  ),
  3 => 
  array (
    'name' => 'types',
    'title' => '连签周期',
    'type' => 'array',
    'content' => 
    array (
    ),
    'value' => 
    array (
      1 => '1',
      2 => '2',
      3 => '3',
      4 => '4',
      5 => '5',
      6 => '6',
      7 => '7',
    ),
    'rule' => 'required',
    'msg' => '',
    'tip' => '连签周期',
    'ok' => '',
    'extend' => '',
  ),
  4 => 
  array (
    'name' => 'rule',
    'title' => '签到规则',
    'type' => 'text',
    'content' => 
    array (
    ),
    'value' => '	1.连续签到1天可以获得额外奖励1分哟。
	2.连续签到2天可以获得额外奖励2分哟。
	3.连续签到3天获得额外奖励3分哟。
	4.连续签到4天获得额外奖励4分哟。
        5.连续签到5天获得额外奖励5分哟。
        6.连续签到6天获得额外奖励6分哟。
        7.连续签到7天获得额外奖励7分哟。
        8.连续签到n天获得额外奖励7分哟。
',
    'rule' => 'required',
    'msg' => '请填写签到规则',
    'tip' => '签到规则（玩法）说明，可以填写很多行',
    'ok' => '',
    'extend' => '',
  ),
  5 => 
  array (
    'name' => 'domain',
    'title' => '绑定二级域名前缀',
    'type' => 'radio',
    'content' => 
    array (
    ),
    'value' => '',
    'rule' => 'required',
    'msg' => '',
    'tip' => '',
    'ok' => '',
    'extend' => '',
  ),
  6 => 
  array (
    'name' => 'rewrite',
    'title' => '伪静态',
    'type' => 'array',
    'content' => 
    array (
    ),
    'value' => 
    array (
      'index/index' => '/leesign$',
    ),
    'rule' => 'required',
    'msg' => '',
    'tip' => '',
    'ok' => '',
    'extend' => '',
  ),
);
