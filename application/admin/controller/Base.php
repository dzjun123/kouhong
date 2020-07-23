<?php
namespace app\admin\controller;

use app\common\model\RougeSystemWx;
use think\Controller;
use think\Session;

class Base extends Controller
{
    public function _initialize()
    {
        $seuser = Session::get();
        if(empty($seuser['userid']) && empty($seuser['username']) && empty($seuser['uniacid'])){
            $this->redirect(url('Login/index'));
        }
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
