<?php
namespace app\mobile\controller;

use app\common\model\RougeGoods;
use app\common\model\RougeRule;
use app\common\model\RougeSystem;
use think\Controller;
use think\Request;
use think\Session;
use Wechat\WechatApi;

class Index extends Base
{
    /**
     * 测试模板
     */
     public function aa() {
         var_dump("http://".$_SERVER['SERVER_NAME']);
    }
    
    //首页
    public function index(Request $request)
    {
        if ($request->isAjax()) {
            $model = new RougeGoods();

            $map['uniacid'] = Session::get('uniacid');
            $list = $model->where($map)->select();
            if ($list) {
                $rarr['code'] = 0;
                $rarr['data'] = $list;
                $rarr['map'] = $map;
                $rarr['message'] = 'success';
            } else {
                $rarr['code'] = 9001;
                $rarr['data'] = $list;
                $rarr['map'] = $map;
                $rarr['message'] = 'success';
            }
            return json($rarr);
        } else {
            $url = $request->url(true);


            $this->init($url);


            $userinfo['openid'] = '';
            $userinfo = $this->getuserinfo();

            $this->assign('user',$userinfo);
            return $this->fetch('index/index');
        }
    }

    //获取充值列表
    public function getrecharge(Request $request)
    {
        if ($request->isAjax()) {
            $model = new RougeRule();
            $userinfo = $this->getuserinfo();
            $map['uniacid'] = $userinfo['uniacid'];
            $list = $model->where($map)->select();
            if ($list) {
                $rarr['code'] = 0;
                $rarr['data'] = $list;
                $rarr['map'] = $map;
                $rarr['message'] = 'success';
            } else {
                $rarr['code'] = 9001;
                $rarr['data'] = $list;
                $rarr['map'] = $map;
                $rarr['message'] = 'success';
            }
            return json($rarr);
        }
    }



    //首页详情
    public function getindex(Request $request){
        if ($request->isAjax()) {
            $model = new RougeSystem();
            $userinfo = $this->getuserinfo();
            $map['uniacid'] = $userinfo['uniacid'];
            $list = $model->where($map)->find();
            if ($list) {

                $rarr['code'] = 0;
                $rarr['data'] = $list;
                $rarr['message'] = 'success';
            } else {
                $rarr['code'] = 9001;
                $rarr['data'] = $list;
                $rarr['message'] = 'success';
            }
            return json($rarr);
        } else {
            $url = $request->url(true);
            $this->init($url);
            return $this->fetch();
        }
    }
    
    /**
     * 清空session
     */
    public function clearSession() {
        // 初始化session.
          $se = Session::get();
          var_dump($se); 
           
          Session::destroy(); //销毁所有session
          $se = Session::get();
          var_dump($se);
    }


}
