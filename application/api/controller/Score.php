<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/9 0009
 * Time: 0:20
 */

namespace app\api\controller;
use app\common\controller\Api;
use app\common\model\HttpCurl;
use app\common\model\Order;
use think\Config;
use think\Log;

class Score extends Api
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    /**
     * 更新订单状态
     */
    public function updateStatus()
    {
        //unicom_maidelong=联通麦德龙,unicom_woerma=联通沃尔玛,mobile_maidelong=移动麦德龙,mobile_woerma=移动沃尔玛
        $channel_arr = ['unicom_maidelong','unicom_woerma','mobile_maidelong','mobile_woerma','mobile_tmall'];

        $startTime = strtotime(date('Y-m-d 00:00:00', strtotime("-1 day")));
        $endTime = time();
        $where['createtime'] = ['between', [$startTime, $endTime]];
        $where['status'] = 0;
        $where['channel'] = ['in',$channel_arr];
        $data = [];
        $successnum = 0;
        $HttpCurl = new HttpCurl();
        Order::where($where)->chunk(100,function ($items) use(&$data,&$successnum,&$HttpCurl){
            foreach ($items as $order)
            {
                $result = $HttpCurl->OrderStatus($order->order);
                if($result->result == 99){
                    $order->status = 1;
                    $order->memo = '已兑换';
                    \app\common\model\User::add_blocked_balances($order->amount,$order->user_id,'discount');
                }elseif(in_array($result->result,['3','11','12','13','14'])){
                    $order->status = 2;
                    $order->memo = $result->memo;
                }else{
                    $order->memo = $result->memo;
                }
                $order->save();
                $data[] = '订单号：'.$order->order.',状态修改为'.$order->status.'。备注：'.$order->memo;
                $successnum++;
            }
        });
        $this->success('成功更新'.$successnum.'条数据',$data);

    }

}