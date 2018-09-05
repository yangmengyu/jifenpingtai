<?php

return array (
  'autoload' => false,
  'hooks' => 
  array (
    'leesignhook' => 
    array (
      0 => 'leesign',
    ),
  ),
  'route' => 
  array (
    '/example$' => 'example/index/index',
    '/example/d/[:name]' => 'example/demo/index',
    '/example/d1/[:name]' => 'example/demo/demo1',
    '/example/d2/[:name]' => 'example/demo/demo2',
    '/leesign$' => 'leesign/index/index',
  ),
);