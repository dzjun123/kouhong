<?php
/**
 * Created by PhpStorm.
 * User: 悟能科技
 * Date: 2018/11/3
 * Time: 9:22
 */

namespace app\mobile\controller;


use app\common\model\RougePaylog;
use app\common\model\RougeSystemWx;
use think\Request;
use think\Session;
use Wechat\WechatApi;

class Pay extends Base
{
    
    public function test() {
        $notify_url = 'http://' . $_SERVER ['SERVER_NAME'].'/wx_inter.php/authdzj';
        echo $notify_url;
    }

    //  orderid  uniacid   openid  total_fee
    public function pay(Request $request){
        if ($request->isAjax()) {
            $request = request();
            $param = $request->post();
            if ($param['total_fee'] > 0){
                //END
                $options = $this->options();
                $weObj = new WechatApi($options);
                $user = $this->getuserinfo();
                //END
                $data['uniacid'] = $param['uniacid'];
                $data['orderid'] = $param['orderid'];
                $data['openid'] = $param['openid'];
                $data['price'] = $param['total_fee'];
                $data['pay_fs'] = 0;
                $data['user_id'] = $user['id'];
                $data['total_fee'] = $param['total_fee'];
                $data['create_time'] = time();
                $RougePaylog = new RougePaylog();
                $RougePaylog->allowField(true)->save($data);
                //END
                $WxConfig = new RougeSystemWx();
                $map['uniacid'] = $param['uniacid'];
                $coninfo = $WxConfig->where($map)->find();
                $datas['mch_id'] = $coninfo['mch_id'];
                $datas['sub_mch_id'] = '';
                $datas['body'] = '充值';
                $datas['out_trade_no'] = $param['orderid'];
                $datas['total_fee'] = $param['total_fee'] * 100;
                $datas['spbill_create_ip'] = $request->ip();
                $notify_url = 'http://' . $_SERVER ['SERVER_NAME'].'/wx_inter.php/authdzj';
                error_log(time()."::".$notify_url);
//                return json($notify_url);
                $datas['trade_type'] = 'NATIVE';
                //END
                $appid = $coninfo['appid'];
                $sub_appid = '';
                $mch_id = $coninfo['mch_id'];
                $sub_mch_id = '';
                $body = '充值';
                $out_trade_no = $param['orderid'];
                $total_fee = $datas['total_fee'];
                $sub_openid = $param['openid'];
                $partnerkey = $options['partnerkey'];
                //END
                $spbill_create_ip = $_SERVER["REMOTE_ADDR"];
                $nonce_str = $weObj->generateNonceStr();
                $pay_xml = $this->createPackageXml($appid, '', $mch_id, '', $nonce_str, $body, $out_trade_no, $total_fee, $spbill_create_ip, $notify_url, $sub_openid, $sub_openid,$partnerkey);
             
                $pay_xml = $this->get_pay_id($pay_xml);


//                return json($pay_xml);
                if ($pay_xml['return_code'] == 'SUCCESS') {
                    $prepay_id = $pay_xml['prepay_id'];
                    $jsApiObj["appId"] = $appid;
                    $timeStamp = time();
                    $jsApiObj["timeStamp"] = "$timeStamp";
                    $jsApiObj["nonceStr"] = $nonce_str;
                    $jsApiObj["package"] = "prepay_id=" . $prepay_id;
                    $jsApiObj["signType"] = "MD5";
                    $jsApiObj["paySign"] = $this->getPaySignature($jsApiObj,$partnerkey);
                    //状态码
                    $jsApiObj['code'] = 0;
                } else {
                    $jsApiObj = ['code' => 9002, 'message' => '支付失败！'];
                }
                //jssdk
                $url = "https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
                $jssdk = $weObj->getJsSign($url);
            } else {
                $jsApiObj = ['code' => 9001, 'message' => '请输入正确的充值金额！'];
            }
            return json($jsApiObj);
        }
    }


    //
    //微信支付

    public function createPackageXml($appid, $sub_appid, $mch_id, $sub_mch_id, $nonce_str, $body, $out_trade_no, $total_fee, $spbill_create_ip, $notify_url, $openid, $sub_openid,$partnerkey)
    {
        if (strlen($sub_mch_id) > 6) {
            $arrdata = array("appid" => $appid, 'sub_appid' => $sub_appid, "mch_id" => $mch_id, "sub_mch_id" => $sub_mch_id, "nonce_str" => $nonce_str, "body" => $body, "out_trade_no" => $out_trade_no, "total_fee" => $total_fee, "spbill_create_ip" => $spbill_create_ip, "notify_url" => $notify_url, "sub_openid" => $sub_openid, 'trade_type' => 'JSAPI');
        } else {
            $arrdata = array("appid" => $appid, "mch_id" => $mch_id, "nonce_str" => $nonce_str, "body" => $body, "out_trade_no" => $out_trade_no, "total_fee" => $total_fee, "spbill_create_ip" => $spbill_create_ip, "notify_url" => $notify_url, "openid" => $openid, 'trade_type' => 'JSAPI');


        }


        ksort($arrdata);
        $paramstring = "";
        foreach ($arrdata as $key => $value) {
            if (strlen($paramstring) == 0)
                $paramstring .= $key . "=" . $value;
            else
                $paramstring .= "&" . $key . "=" . $value;
        }
        $stringSignTemp = $paramstring . "&key=" . $partnerkey;

        $signValue = strtoupper(md5($stringSignTemp));


        if (strlen($sub_mch_id) > 6) {

            $returValue = "<xml>
					   <appid>" . $appid . "</appid>
					   <sub_appid>" . $sub_appid . "</sub_appid>
					   <mch_id>" . $mch_id . "</mch_id>
                       <sub_mch_id>" . $sub_mch_id . "</sub_mch_id>  
					   <nonce_str>" . $nonce_str . "</nonce_str>
					   <sign>" . $signValue . "</sign>
					   <body>" . $body . "</body>
					   <out_trade_no>" . $out_trade_no . "</out_trade_no>
					   <total_fee>" . $total_fee . "</total_fee>
					   <spbill_create_ip>" . $spbill_create_ip . "</spbill_create_ip>
					   <notify_url>" . $notify_url . "</notify_url>
					   <trade_type>JSAPI</trade_type>
					   <sub_openid>" . $sub_openid . "</sub_openid>
					</xml>";
        } else {
            $returValue = "<xml>
					   <appid>" . $appid . "</appid>
					   <mch_id>" . $mch_id . "</mch_id>
                                       
					   <nonce_str>" . $nonce_str . "</nonce_str>
					   <sign>" . $signValue . "</sign>
					   <body>" . $body . "</body>
					   <out_trade_no>" . $out_trade_no . "</out_trade_no>
					   <total_fee>" . $total_fee . "</total_fee>
					   <spbill_create_ip>" . $spbill_create_ip . "</spbill_create_ip>
					   <notify_url>" . $notify_url . "</notify_url>
					   <trade_type>JSAPI</trade_type>
					   <openid>" . $openid . "</openid>
					</xml>";


        }
        return $returValue;
    }


    public function createOrderXml($appid, $mch_id, $sub_appid, $sub_mch_id, $out_trade_no, $nonce_str,$partnerkey)
    {
        if (strlen($sub_mch_id) > 6) {
            $arrdata = array("appid" => $appid, "mch_id" => $mch_id, "sub_appid" => $sub_appid, "sub_mch_id" => $sub_mch_id, "nonce_str" => $nonce_str, "out_trade_no" => $out_trade_no);
        } else {
            $arrdata = array("appid" => $appid, "mch_id" => $mch_id, "nonce_str" => $nonce_str, "out_trade_no" => $out_trade_no);
        }
        if (empty($sub_appid)) {
            unset($arrdata['sub_appid']);
        }
        ksort($arrdata);
        $paramstring = "";
        foreach ($arrdata as $key => $value) {
            if (strlen($paramstring) == 0)
                $paramstring .= $key . "=" . $value;
            else
                $paramstring .= "&" . $key . "=" . $value;
        }
        $stringSignTemp = $paramstring . "&key=" . $partnerkey;
        $signValue = strtoupper(md5($stringSignTemp));
        if (strlen($sub_mch_id) > 6) {
            $returValue = "<xml>
					   <appid>" . $appid . "</appid>
					   <mch_id>" . $mch_id . "</mch_id>
					   <sub_appid>" . $sub_appid . "</sub_appid>
					   <sub_mch_id>" . $sub_mch_id . "</sub_mch_id>
					   <nonce_str>" . $nonce_str . "</nonce_str>
					    <out_trade_no>" . $out_trade_no . "</out_trade_no>
					   <sign>" . $signValue . "</sign>
					   
					</xml>";
        } else {
            $returValue = "<xml>
					   <appid>" . $appid . "</appid>
					   <mch_id>" . $mch_id . "</mch_id>
                                         
					   <nonce_str>" . $nonce_str . "</nonce_str>
					    <out_trade_no>" . $out_trade_no . "</out_trade_no>
					   <sign>" . $signValue . "</sign>
					   
					</xml>";
        }
        return $returValue;
    }


    /*
 * 支付结束
 *
 */
    public function r_payover()
    {
        if (request()->isAjax()) {
            $request = request();
            $options = $this->options();
            $weObj = new WechatApi($options);
            $appid = $options['appid'];
            $mch_id = $options['mch_id'];
            $out_trade_no = $request->post('orderid');
            $partnerkey = $options['partnerkey'];
            $nonce_str = $weObj->generateNonceStr();
            $pay_xml = $this->createOrderXml($appid, $mch_id, '','',$out_trade_no, $nonce_str,$partnerkey);

            $payorder_xml = $this->getPayNum($pay_xml);
//            return json($payorder_xml);
//            $orderno = $payorder_xml['transaction_id'];
            $pay_zt = $payorder_xml['trade_state'];
            if ($pay_zt == "SUCCESS") {
                $array = array('code' => 0, 'message' => "充值成功");
            } else {
                $array = array('code' => 1, 'message' => "充值失败");
            }
            return json($array);
        }
    }

    public function getPayNum($xml)
    {

        $result = $this->http_post('https://api.mch.weixin.qq.com/pay/orderquery', $xml, true);
        if ($result) {
            $json = $this->xmlToArray($result);
            return $json;
        }
        return false;

    }

    //xml转array
    public function xmlToArray($xml)
    {
        libxml_disable_entity_loader(true);
        //将XML转为array
        $array_data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $array_data;
    }

    //获取订单
    public function getorder()
    {
        $request = request();
        if ($request->isAjax()) {
            $sh = $request->post('id');
            $order = "H" . $sh . "-" . date('YmdHis') . rand(10000, 99999);
            echo $order;
        }
    }


    public function getPaySignature($arrdata,$partnerkey)
    {
        ksort($arrdata);
        $paramstring = "";
        foreach ($arrdata as $key => $value) {
            if (strlen($paramstring) == 0)
                $paramstring .= $key . "=" . $value;
            else
                $paramstring .= "&" . $key . "=" . $value;
        }

        $paramstring = $paramstring . "&key=" . $partnerkey;

        $paySign = md5($paramstring);

        return $paySign;
    }

    public function get_pay_id($xml)
    {
        $url = 'https://api.mch.weixin.qq.com/pay/unifiedorder';
        $result = $this->http_post($url, $xml, true);
        if ($result) {
            $json = $this->xmlToArray($result);
            return $json;
        }
        return false;
    }


    private function http_post($url, $param, $post_file = false)
    {
        $oCurl = curl_init();
        if (stripos($url, "https://") !== FALSE) {
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($oCurl, CURLOPT_SSLVERSION, 1); //CURL_SSLVERSION_TLSv1
        }
        if (PHP_VERSION_ID >= 50500 && class_exists('\CURLFile')) {
            $is_curlFile = true;
        } else {
            $is_curlFile = false;
            if (defined('CURLOPT_SAFE_UPLOAD')) {
                curl_setopt($oCurl, CURLOPT_SAFE_UPLOAD, false);
            }
        }
        if (is_string($param)) {
            $strPOST = $param;
        } elseif ($post_file) {
            if ($is_curlFile) {
                foreach ($param as $key => $val) {
                    if (substr($val, 0, 1) == '@') {
                        $param[$key] = new \CURLFile(realpath(substr($val, 1)));
                    }
                }
            }
            $strPOST = $param;
        } else {
            $aPOST = array();
            foreach ($param as $key => $val) {
                $aPOST[] = $key . "=" . urlencode($val);
            }
            $strPOST = join("&", $aPOST);
        }
        curl_setopt($oCurl, CURLOPT_URL, $url);
        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($oCurl, CURLOPT_POST, true);
        curl_setopt($oCurl, CURLOPT_POSTFIELDS, $strPOST);
        $sContent = curl_exec($oCurl);
        $aStatus = curl_getinfo($oCurl);
        curl_close($oCurl);
        if (intval($aStatus["http_code"]) == 200) {
            return $sContent;
        } else {
            return false;
        }
    }

    //xml转array
//
//    public function xmlToArray($xml)
//    {
//        //将XML转为array
//        $array_data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
//        return $array_data;
//    }
}