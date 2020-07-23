<?php
namespace app\index\controller;

use app\common\controller\QrcodeMake;
use app\common\model\RougeGetmoney;
use app\common\model\RougeSystemWx;
use think\Controller;
use Weixinpay\Company;

class Index extends Controller
{
    public function aa() {
        echo 111;
    }
    
    public function index()
    {
//        return '<style type="text/css">*{ padding: 0; margin: 0; } .think_default_text{ padding: 4px 48px;} a{color:#2E5CD5;cursor: pointer;text-decoration: none} a:hover{text-decoration:underline; } body{ background: #fff; font-family: "Century Gothic","Microsoft yahei"; color: #333;font-size:18px} h1{ font-size: 100px; font-weight: normal; margin-bottom: 12px; } p{ line-height: 1.6em; font-size: 42px }</style><div style="padding: 24px 48px;"> <h1>:)</h1><p> ThinkPHP V5<br/><span style="font-size:30px">十年磨一剑 - 为API开发设计的高性能框架</span></p><span style="font-size:22px;">[ V5.0 版本由 <a href="http://www.qiniu.com" target="qiniu">七牛云</a> 独家赞助发布 ]</span></div><script type="text/javascript" src="https://tajs.qq.com/stats?sId=9347272" charset="UTF-8"></script><script type="text/javascript" src="https://e.topthink.com/Public/static/client.js"></script><think id="ad_bd568ce7058a1091"></think>';

    $qrcode=new QrcodeMake();
    $s=$qrcode->getcode(1,"http://h5.yiszh.com/mobile.php/login/index/outer/oJ1uY1D8Zhrogww1_ZBkLEiqqaIg/platid/1");
    echo $s;
    }
    
    
    public function sendhb($id=137)
    {
        $RougeGetmoney = new RougeGetmoney();

        $map['id'] = $id;
        $getinfo = $RougeGetmoney->where($map)->find();

        $RougeSystemWx = new RougeSystemWx();
        $Syswxinfo = $RougeSystemWx->where('uniacid',1)->find();
        $mch_appid = $Syswxinfo['appid'];
        $mchid = $Syswxinfo['mch_id'];
//        $partener_trade_no = $getinfo['orderid'];
        $partener_trade_no = 'H888'.date('YmdHis').rand(1000,9999);
//        $openid = $getinfo['openid'];
        $openid = 'oJ1uY1EXNv6fE5vDFDXYuiBLBTns';
        $desc = $Syswxinfo['wishing'];
        $partnerkey = $Syswxinfo['partnerkey'];
        $apiclient_cert = $Syswxinfo['apiclient_cert'];
        $apiclient_key = $Syswxinfo['apiclient_key'];
//        $price = 1;
//        $getinfo['statu'] = 1;
        //END
        if ($getinfo['statu'] == 1) {

//
//            $RougeSystemWx = new RougeSystemWx();
//            $mab['uniacid'] = $getinfo['uniacid'];
//            $wxinfo = $RougeSystemWx->where($mab)->find();
            $weObj = new Company();
            $notice = $weObj->generateNonceStr();
            $weObj->setParameter('mch_appid', $mch_appid);//商户appid
            $weObj->setParameter('mchid', $mchid);//商户号
            $weObj->setParameter('nonce_str', $notice);//随机字符串
            $weObj->setParameter('partner_trade_no',  $partener_trade_no);//订单号
            $weObj->setParameter('openid',$openid );//用户openID
            $weObj->setParameter('check_name', "NO_CHECK");//校验姓名（默认不校验）
            $weObj->setParameter('amount', $getinfo['price']*100);//付款金额
            $weObj->setParameter('desc',$desc);//付款备注
            $weObj->setParameter('spbill_create_ip', $_SERVER['REMOTE_ADDR']);//发起IP
            $sss = $weObj->create_refund_xml($partnerkey);//签名

            $url = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers';
            $responseXml = $weObj->curl_post_ssl($url, $sss, $apiclient_cert, $apiclient_key);
            $responseObj = simplexml_load_string($responseXml, 'SimpleXMLElement', LIBXML_NOCDATA);
            $responseArr = ( array )$responseObj;
//            var_dump($responseArr);
            if ($responseArr) {
                if ($responseArr['return_code'] == 'SUCCESS' && $responseArr['result_code'] == "SUCCESS") {
                    if ($responseArr['return_msg'] == '发放成功') {
                        $datas['statu']=2;
                        $datas['wxinfo']=json_encode($responseArr,JSON_UNESCAPED_UNICODE);
                        $RougeGetmoney->save($datas,$map);
                        $rarr['return_code'] = "SUCCESS";
                        $rarr['result_code'] = "SUCCESS";
                        $rarr['return_msg'] = $responseArr['return_msg'];
                        $rarr['result_msg'] = $responseArr['return_msg'];
                    } else {
                        $rarr['return_code'] = "SUCCESS";
                        $rarr['result_code'] = "FAIL";
                        $rarr['return_msg'] = $responseArr['return_msg'];
                        $rarr['result_msg'] = $responseArr['return_msg'];

                    }
                } else {
                    if (isset($responseArr['err_code_des'])) {
                        $rarr['return_code'] = "SUCCESS";
                        $rarr['result_code'] = "FAIL";
                        $rarr['return_msg'] = $responseArr['err_code_des'];
                        $rarr['result_msg'] = $responseArr['err_code_des'];

                    } elseif (isset($responseArr['return_msg'])) {
                        $rarr['return_code'] = "SUCCESS";
                        $rarr['result_code'] = "FAIL";
                        $rarr['return_msg'] = $responseArr['return_msg'];
                        $rarr['result_msg'] = $responseArr['return_msg'];
                    } else {
                        $rarr['return_code'] = "SUCCESS";
                        $rarr['result_code'] = "FAIL";
                        $rarr['return_msg'] = "未知错误";
                        $rarr['result_msg'] = "未知错误";
                    }
                }
            } else {
                $rarr['return_code'] = "SUCCESS";
                $rarr['result_code'] = "FAIL";
                $rarr['return_msg'] = "证书参数有误";
                $rarr['result_msg'] = "证书参数有误";
            }
            $rarr = $responseArr;
        }else{
            $rarr['return_code'] = "SUCCESS";
            $rarr['result_code'] = "FAIL";
            $rarr['return_msg'] = "订单状态异常";
            $rarr['result_msg'] = "订单状态异常";
        }
        return json($rarr);
    }
}
