<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/26
 * Time: 11:15
 */

namespace app\common\model;
use think\Db;

class HttpCurl {
    function callInterfaceCommon($URL,$data=null,$type='POST',$headers="",$data_type='json')
    {
        $ch = curl_init();
        //判断ssl连接方式
        if (stripos($URL, 'https://') !== false) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_SSLVERSION, 1);
        }
        $connttime = 300; //连接等待时间500毫秒
        $timeout = 15000;//超时时间15秒

        $querystring = "";
        if (is_array($data)) {
            // Change data in to postable data
            foreach ($data as $key => $val) {
                if (is_array($val)) {
                    foreach ($val as $val2) {
                        $querystring .= urlencode($key) . '=' . urlencode($val2) . '&';
                    }
                } else {
                    $querystring .= urlencode($key) . '=' . urlencode($val) . '&';
                }
            }
            $querystring = substr($querystring, 0, -1); // Eliminate unnecessary &
        } else {
            $querystring = $data;
        }

        // echo $querystring;
        curl_setopt($ch, CURLOPT_URL, $URL); //发贴地址
        //设置HEADER头部信息
//        if($headers!=""){
//            curl_setopt ($ch, CURLOPT_HTTPHEADER, $headers);
//        }else {
//            curl_setopt ($ch, CURLOPT_HTTPHEADER, array('Content-type: text/json'));
//        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);//反馈信息
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1); //http 1.1版本

        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, $connttime);//连接等待时间
        curl_setopt($ch, CURLOPT_TIMEOUT_MS, $timeout);//超时时间

        switch ($type) {
            case "GET" :
                curl_setopt($ch, CURLOPT_HTTPGET, true);
                break;
            case "POST":
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $querystring);
                break;
            case "PUT" :
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
                curl_setopt($ch, CURLOPT_POSTFIELDS, $querystring);
                break;
            case "DELETE":
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
                curl_setopt($ch, CURLOPT_POSTFIELDS, $querystring);
                break;
        }
        $file_contents = curl_exec($ch);//获得返回值
        // echo time().'<br>';
        $status = curl_getinfo($ch);
        //dump($status);
        curl_close($ch);
        if ($data_type == "json" || $data_type == "JSON") {
            return json_encode($file_contents);
        } else {
            return $file_contents;
        }
    }

    public function MD5($str){
        return strtolower(md5(md5($str)));
    }
    /// <summary>
    /// 获取当前时间戳
    /// </summary>
    /// <returns></returns>
    public function Timestamp()
    {
        //$now = time();
        //$old = strtotime('1970-01-01 00:00:00');
        //var_dump('nowtime:'.$now.'old:'.$old);
        //return $now - $old;

        list($t1, $t2) = explode(' ', microtime());
        return (float)sprintf('%.0f',(floatval($t1)+floatval($t2))*1000);
    }

    // 手机号码归属地(返回: 如 广东移动)
    public function get_mobile_area($mobilephone){
        $data = Db::name('mobile_area')->where('mobile',$mobilephone)->find();
        if($data){
            return $data['area'];
        }else{
            $url = "http://apis.juhe.cn/mobile/get?phone=".$mobilephone."&key=e7222f3a58119bb52a8fe4d866945cbc";
            $content = \GuzzleHttp\json_decode(file_get_contents($url));
            if($content->resultcode ==200){
                Db::name('mobile_area')->insert([
                    'mobile'=>$mobilephone,
                    'area'=>$content->result->province.$content->result->company
                ]);
                return $content->result->province.$content->result->company;
            }else{
                return '';
            }
        }

    }
    // 转换字符串编码为 UTF8
    public function conv2utf8($text){
        return mb_convert_encoding($text,'UTF-8','ASCII,GB2312,GB18030,GBK,UTF-8');
    }
    //获取验证码
    public function getSms($MerId,$mobile,$Merkey,$Smstype=NULL){

        $url = "http://120.55.161.115:2222/WemFile/wem_setsms";
        $data['MerId'] = $MerId;
        $data['Phone'] = $mobile;
        $key = $this->MD5($Merkey);
        $SignSource = $this->MD5($mobile.$key.$MerId.'@!@#@#DDSD323dsds');
        $data['SignSource'] = $SignSource;
        $result = $this->callInterfaceCommon($url,$data,'POST','',FALSE);
        return $result;
    }

    //发起上报
    public function shangbao($MerId,$mobile,$MerKey,$smscode,$LoginKey){
        $url = "http://120.55.161.115:2222/WemFile/wem_setsms";
        $data['MerId'] = $MerId;
        $data['Phone'] = $mobile;
        $key = $this->MD5($MerKey);
        $data['SmsCode'] = $smscode;
        $data['LoginKey'] = $LoginKey;
        $SignSource = $this->MD5($mobile.$key.$data['MerId'].$data['SmsCode'].$LoginKey.'@!@#@#DDSD323dsds');
        $data['SignSource'] = $SignSource;
        $result = $this->callInterfaceCommon($url,$data,'POST','',FALSE);
        return $result;
    }


}