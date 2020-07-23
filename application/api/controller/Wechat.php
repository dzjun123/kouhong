<?php
namespace app\api\controller;

use app\common\model\RougeAchi;
use app\common\model\RougeDistr;
use app\common\model\RougePaylog;
use app\common\model\RougeSystemWx;
use app\common\model\RougeUser;
use Wechat\WechatApi;

class Wechat
{
    public function __construct(){

    }
    // 接入微信
    public function index()
    {
        $postStr = file_get_contents('php://input');
        //配置微信SDK
        $param = request()->param();
        $options = $this->options($param['id']);
        $weObj = new WechatApi($options);
        //接入服务器验证
        $info = $weObj->valid();
        //获取推送类型
        $type = $weObj->getRev()->getRevType();
        switch ($type) {
            //事件
            case 'event':
                $eventype = $weObj->getRev()->getRevEvent();
                //关注事件
                if($eventype['event'] == "subscribe") {
                    $pushdata = $weObj->getRev()->getRevData();
                    exit();
                }
                //取关事件
                if($eventype['event'] == "unsubscribe") {
                    $pushdata = $weObj->getRev()->getRevData();
                    exit();
                }
                exit;
                break;

            //消息
            //文本消息
            case 'text':
                $eventype = $this->Msgreply($weObj->getRev()->getRevContent());
                if(is_array($eventype)){
                    $weObj->getRev()->news($eventype)->reply();
                }else{
                    $weObj->getRev()->text($eventype)->reply();
                }


                exit;
                break;
//			//图片消息
//          case 'image':
//
//              exit;
//              break;
//          //语音
//          case 'voice':
//
//              exit;
//              break;
//          //视频
//          case 'media_id':
//
//              exit;
//              break;
//          //短视频
//          case 'shortvideo':
//
//              exit;
//              break;
//          //地理位置
//          case 'location':
//
//              exit;
//              break;
//          //链接
//          case 'link':
//
//              exit;
//              break;
            //默认
            default:
                $weObj->getRev()->text("请用文字描述您想看的！")->reply();
        }

    }

    //写入日志
    public function authdzj2() {
        error_log('shijian.'.time());
    }
    
    public function authdzj3() {
        $postStr = file_get_contents('php://input');
        if (!empty($postStr)) {
            $msg = (array)simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            error_log(json_encode($msg)); 
        }
        error_log('dzj3_end'); 
    }
    
    //测试
    public function authdzj()
    {
        $postStr = file_get_contents('php://input');
        if (!empty($postStr)) {
            $msg = (array)simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            //error_log(json_encode($msg));exit;
            if (isset($msg['return_code'])) {
                $pay_zt = $msg['return_code'];
                if ($pay_zt == "SUCCESS") {
                    $Paylog = new RougePaylog();
                    $data['pay_statu'] = 1;
                    $id = $Paylog->where(['orderid' => $msg['out_trade_no']])->find();
                    if ($id['tz_zt'] == '0') {
                        $data['pay_time'] = time();
                        $data['pay_statu'] = 1;
                        $data['tz_zt'] = 1;
                        $data['openid'] = $msg['openid'];
                        $data['payinfo'] = json_encode($msg,JSON_UNESCAPED_UNICODE);
                        $data['real_fee'] = $msg['total_fee']/100;
                        $data['orderno'] = $msg['transaction_id'];
                        $Paylog->save($data,['orderid' => $msg['out_trade_no']]);
                        //支付记录更新完成
                        $Member = new RougeUser();
                        $map['openid'] = $id['openid'];
                        $user = $Member->where($map)->field('id,balance')->find();
                        $datas['balance'] = $data['real_fee'] + $user['balance'];
                        $Member->save($datas,$map);
                        //会员信息更新完成
//                        $Spreadrec = new Spreadrec();
//                        $ress = $Spreadrec->up_product_pay($msg['out_trade_no']);
//                        Session::set('uid',$id['uid']);
                        //推广记录更新完成

                        $this->addachi($id);
                    } elseif ($id['tz_zt'] == 1) {
                        $su = '<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>';
                        echo $su;
                    }
                }

            }
        }
    }

    //增加业绩
    /*
     * openid  orderid  price
     * */
    private function addachi($param){
        try {
            $RougeUser = new RougeUser();
            $map['openid'] = $param['openid'];
            $user = $RougeUser->where($map)->find();
            if ($user['top_openid']) {
                //是否为被推广用户
                $RougeDistr = new RougeDistr();
                $distr = $RougeDistr->where('uniacid', $user['uniacid'])->find();
                if ($distr) {
                    //是否存在分销设置
                    $RougeAchi = new RougeAchi();
                    $bili = $distr['re_ratio'] / 100;
                    $data['uniacid'] = $user['uniacid'];
                    $data['top_openid'] = $user['top_openid'];
                    $data['orderid'] = $param['orderid'];
                    $data['openid'] = $param['openid'];
                    $data['price'] = $param['price'];
                    $data['re_ratio'] = $distr['re_ratio'];
                    $data['get_price'] = $param['price'] * $bili;
                    $data['create_time'] = time();
                    $RougeAchi->allowField(true)->save($data);
                    $RougeUser->where(['openid'=>$user['top_openid']])->setInc('bonus',$data['get_price']);
                }
            }
        }catch (\Exception $e) {
            $arrc = json_encode(array('code' => '9099', 'message' => $e->getMessage()));
            error_log("wechat_pay_error:".json_encode($arrc));
            //file_put_contents('/www/wwwroot/h5yiszhcom/achi.txt', 'e:' . json_encode($arrc) . PHP_EOL, FILE_APPEND);
        }

    }

    private function options($id)
    {
        //  Session::set('openid', null);
        $model = new RougeSystemWx();
        $mab['id'] = $id;
        $users = $model->where($mab)->field('appid,appsecret,token,encodingaeskey')->find();
        $options = array(
            'appid' => $users['appid'], // 填写高级调用功能的app id
            'appsecret' => $users['appsecret'], // 填写高级调用功能的密钥
            'token' => $users['token'], // 填写你设定的key
            'encodingaeskey' => $users['encodingaeskey'], // 填写加密用的EncodingAESKey
        );
        return $options;
    }
}
