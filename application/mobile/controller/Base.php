<?php

namespace app\mobile\controller;

use app\common\model\RougeSystem;
use app\common\model\RougeSystemWx;
use app\common\model\RougeUser;
use think\Controller;
use think\Session;
use Wechat\WechatApi;

class Base extends Controller
{ 
    
    public function init($url = '')
    {
          
        $param = request()->param();
        if (!isset($param['platid'])) {
            $this->error('请刷新页面后重试');
            exit();
        }
        //电脑端检测，禁止电脑端访问
        if(!$this->isMobile()){
            //跳转移动端页面
            //echo '提示:请用手机访问'; 
            //exit; 
        } 
        
        $plat = $param['platid'];
  Session::set($plat.'_openid','oSvf_0lw9jIH4-C2W6YOQBiHoe7g');  //测试的
        $openid = Session::get($plat . "_openid");
        $option = $this->option($plat);
        $WxObj = new WechatApi($option);
        $model = new RougeUser();
        if (!$openid) {
            $userinfo = $WxObj->getOauthAccessToken();
            //获取授权为空时向微信发送请求
            if (!$userinfo) {
                $request = request();
                if (!$url) {
                    $url = $request->url(true);
                }
                $callback = $url;
                $url = $WxObj->getOauthRedirect($callback, '', 'snsapi_userinfo');
                header("Location:$url");
                exit();
            } else {
                $openid=$userinfo['openid'];
                Session::set($plat."_openid",$openid);

                $user = $model->where('openid', $openid)->find();
                if (!$user) {
                    $users = $WxObj->getOauthUserinfo($userinfo['access_token'], $openid);
                    if (isset($param['outer'])) {
                        $data['top_openid'] = $param['outer'];
                        $data['type'] = 1;
                    }
                    $data['openid'] = $users['openid'];
                    $data['nickname'] =  urlencode($users['nickname']) ;
                    $data['sex'] = $users['sex'];
                    $data['header_url'] = $users['headimgurl'];
                    $data['wx_info'] =urlencode(json_encode($users, JSON_UNESCAPED_UNICODE)) ;
                    $data['uniacid'] = $plat;

                    $data['create_time'] = time();
                    //默认推广
                    $data['level']=1;
                    //$request->domain().'/mobile.php/login/index/outer/' . $param['openid'].'/platid/'.Session::get('uniacid');
                    $openid= $users['openid'];
                    
                    $data['distr_url']="http://".$_SERVER['SERVER_NAME']."/mobile.php/login/index/outer/$openid/platid/$plat";
                    //$data['distr_img_url']="http://kouhong.easyke.wang/qrcode/_401.png";
                    //error_log(time().":base:".  json_encode($data));
                    $model->allowField(true)->save($data);
                    $users['id']=$model->id;
                    $users['uniacid']=$plat;
                    
                    //更新
                    $id=$model->id;
                    $data2['distr_img_url']="http://".$_SERVER['SERVER_NAME']."/qrcode/_$id.png";
                    $model->allowField(true)->save($data2, ['id' => $id]);
                    
                }else{
                    $users['id']=$user['id'];
                    $users['uniacid']=$user['uniacid'];
                }
                $users = $model->where('openid', $openid)->find();
                Session::set('user_id', $users['id']);
                Session::set('uniacid', $users['uniacid']);
                $system = new RougeSystem();
                $map['uniacid'] = $users['uniacid'];
                $sysinfo = $system->where($map)->field('title,uniacid')->find();
                $this->assign('sysinfo', $sysinfo);
                //END
                $jssdkconfig = $this->getjssdk();      //
                $jssdksharedata = $this->getsharedata($sysinfo);    //
                $jssdkconfig = json_encode($jssdkconfig);
                $this->assign('jssdkconfig', $jssdkconfig);
                $this->assign('jssdksharedata', $jssdksharedata);
                //获取信息授权END
            }
        }else{
            $users = $model->where('openid', $openid)->find();
            Session::set('user_id', $users['id']);
            Session::set('uniacid', $users['uniacid']);
            $system = new RougeSystem();
            $map['uniacid'] = $users['uniacid'];
            $sysinfo = $system->where($map)->field('title,uniacid')->find();
            $this->assign('sysinfo', $sysinfo);
            //END
            $jssdkconfig = $this->getjssdk();      //
            $jssdksharedata = $this->getsharedata($sysinfo);    //
            $jssdkconfig = json_encode($jssdkconfig);
            $this->assign('jssdkconfig', $jssdkconfig);
            $this->assign('jssdksharedata', $jssdksharedata);
        }
    }

    public function getuserinfo()
    {
        $RougeUser = new RougeUser();
        $se = Session::get();
        $map['uniacid'] = $se['uniacid'];
        $map['id'] = $se['user_id'];
        $user = $RougeUser->where($map)->find();
        if($user){
            $user['nickname']=  urldecode( $user['nickname']);
        }
        return $user;
    }

    public function getopacid()
    {

    }


    public function getorderno()
    {
        $order = date('YmdHis') . rand(10000, 99999);
        return $order;
    }

    //获取订单
    public function getorder2($sh = 666)
    {
        $order = "H" . $sh . "-" . date('YmdHis') . rand(10000, 99999);
        return $order;
    }


    public function getjssdk()
    {
        $url = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        $options = $this->options();
        //分享
        $weObj = new WechatApi($options);
        $jssdk = $weObj->getJsSign($url);
        $wxJsSdk = [
            'debug' => false,
            'appId' => $jssdk['appId'],
            'timestamp' => $jssdk['timestamp'],
            'nonceStr' => $jssdk['nonceStr'],
            'signature' => $jssdk['signature'],
            'jsApiList' => [
                'openAddress', 'onMenuShareTimeline', 'onMenuShareAppMessage', 'onMenuShareQQ', 'onMenuShareWeibo', 'onMenuShareQZone', 'startRecord', 'stopRecord', 'onVoiceRecordEnd', 'playVoice',
                'pauseVoice' . 'stopVoice', 'onVoicePlayEnd', 'uploadVoice', 'downloadVoice', 'chooseImage', 'previewImage', 'uploadImage', 'downloadImage', 'translateVoice', 'getNetworkType', 'openLocation',
                'getLocation', 'hideOptionMenu', 'showOptionMenu', 'hideMenuItems', 'showMenuItems', 'hideAllNonBaseMenuItem', 'showAllNonBaseMenuItem', 'closeWindow', 'scanQRCode', 'chooseWXPay', 'openProductSpecificView'
            ]
        ];
        return $wxJsSdk;
    }

    public function getsharedata($param)
    {
        $data = [
            'imgUrl'=>'http://kouhong.easyke.wang/uploads/index/20181204/e81d88c9f0c0197495682207ea561dcb.jpg',
            'title' => $param['title'],
            'title_line'=>'圣罗兰/迪奥/纪梵希/TOM FORM/MAC官网口红赢回家',
            'desc' => "圣罗兰/迪奥/纪梵希/TOM FORD/MAC官网口红送到家",//连续闯三关专柜/官网口红送到家",
            'link' => request()->domain() . '/mobile.php/login/index/platid/' . $param['uniacid']

        ];
        return $data;
    }

    //微信API参数  根据card_id获取
    /*
     * $card_id
     */
    public function option($plat)
    {
        //  Session::set('openid', null);
        $model = new RougeSystemWx();
        $mab['uniacid'] = $plat;
        $users = $model->where($mab)->field('appid,appsecret,token,encodingaeskey')->find();
        $options = array(
            'appid' => $users['appid'], // 填写高级调用功能的app id
            'appsecret' => $users['appsecret'], // 填写高级调用功能的密钥
            'token' => $users['token'], // 填写你设定的key
            'encodingaeskey' => $users['encodingaeskey'], // 填写加密用的EncodingAESKey
        );
        return $options;
    }

    //微信API参数  根据card_id获取
    /*
     * $card_id
     */
    public function options()
    {
        //  Session::set('openid', null);
        $model = new RougeSystemWx();
        $mab['uniacid'] = Session::get('uniacid');
        $users = $model->where($mab)->field('appid,appsecret,token,encodingaeskey,mch_id,partnerkey')->find();
        $options = array(
            'appid' => $users['appid'], // 填写高级调用功能的app id
            'mch_id' => $users['mch_id'], // 填写高级调用功能的app id
            'partnerkey' => $users['partnerkey'], // 填写高级调用功能的app id
            'appsecret' => $users['appsecret'], // 填写高级调用功能的密钥
            'token' => $users['token'], // 填写你设定的key
            'encodingaeskey' => $users['encodingaeskey'], // 填写加密用的EncodingAESKey
        );
        return $options;
    }
    
    
    /**
     * 检测如果是电脑端就屏蔽
     * @return boolean
     */
    function isMobile()
{ 
    // 如果有HTTP_X_WAP_PROFILE则一定是移动设备
    if (isset ($_SERVER['HTTP_X_WAP_PROFILE']))
    {
        return true;
    } 
    // 如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
    if (isset ($_SERVER['HTTP_VIA']))
    { 
        // 找不到为flase,否则为true
        return stristr($_SERVER['HTTP_VIA'], "wap") ? true : false;
    } 
    // 脑残法，判断手机发送的客户端标志,兼容性有待提高
    if (isset ($_SERVER['HTTP_USER_AGENT']))
    {
        $clientkeywords = array ('nokia',
            'sony',
            'ericsson',
            'mot',
            'samsung',
            'htc',
            'sgh',
            'lg',
            'sharp',
            'sie-',
            'philips',
            'panasonic',
            'alcatel',
            'lenovo',
            'iphone',
            'ipod',
            'blackberry',
            'meizu',
            'android',
            'netfront',
            'symbian',
            'ucweb',
            'windowsce',
            'palm',
            'operamini',
            'operamobi',
            'openwave',
            'nexusone',
            'cldc',
            'midp',
            'wap',
            'mobile'
            ); 
        // 从HTTP_USER_AGENT中查找手机浏览器的关键字
        if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT'])))
        {
            return true;
        } 
    } 
    // 协议法，因为有可能不准确，放到最后判断
    if (isset ($_SERVER['HTTP_ACCEPT']))
    { 
        // 如果只支持wml并且不支持html那一定是移动设备
        // 如果支持wml和html但是wml在html之前则是移动设备
        if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html'))))
        {
            return true;
        } 
    } 
    return false;
} 
    
}
