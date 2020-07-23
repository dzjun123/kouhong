<?php
namespace app\mobile\controller;

use app\common\model\RougeGoods;
use app\common\model\RougeShare;
use app\common\model\RougeShareLog;
use app\common\model\RougeSystem;
use app\common\model\RougeUser;
use app\common\model\RougeUserConsume;
use app\common\model\RougeUserGameLog;
use app\common\model\RougeUserPrize;
use think\Request;
use think\Session;

class Member extends Base
{
    //我的
    public function index(Request $request)
    {
        if ($request->isAjax()){
            $model = new RougeUser();
            $init = Session::get();
            $map['uniacid'] = $init['uniacid'];
            $map['id'] = $init['user_id'];
            $list = $model->where($map)->find();
            $RougeUserPrize = new RougeUserPrize();
            $mab['uniacid'] = $init['uniacid'];
            $mab['user_id'] = $init['user_id'];
            $mab['statu'] = 1;
            $list['rouge'] = $RougeUserPrize->where($mab)->count('id');
            if ($list){
                $rarr['code'] = 0;
                $rarr['message'] = 'success';
                $rarr['data'] = $list;
            }else{
                $rarr['code'] = 9001;
                $rarr['message'] = 'error';
                $rarr['data'] = $list;
            }
            return json($rarr);
        }else {
            $url = $request->url(true);
            $this->init($url);
            $RougeUserPrize = new RougeUserPrize();
            $userinfo = $this->getuserinfo();
            $mab['uniacid'] = $userinfo['uniacid'];
            $mab['user_id'] = $userinfo['id'];
            $mab['statu'] = 1;
            $userinfo['rouge'] = $RougeUserPrize->where($mab)->count('id');
//            $jssdk = $this->getjssdk();
//            $jssdk = json_encode($jssdk);
//            $this->assign('jssdk',$jssdk);
            //var_dump($userinfo);exit;
            $this->assign('user',$userinfo);
            return $this->fetch();
        }
    }

    //我的口红
    public function myrouge(Request $request){
        if ($request->isAjax()) {
            $model = new RougeUserPrize();
            $init = Session::get();

            $map['a.uniacid'] = $init['uniacid'];
            $map['a.user_id'] = $init['user_id'];
            $map['a.statu'] = 1;
            $list = $model->alias('a')->join('wn_rouge_goods b', 'a.goods_id=b.id', 'left')->where($map)->field('b.img_url,b.brand,b.title,b.ori_price,a.id,a.uniacid,a.user_id,a.goods_id,a.orderid,a.create_time')->select();
            if ($list){
                $rarr['code'] = 0 ;
                $rarr['data'] = $list ;
                $rarr['message'] = 'success' ;
            }else{
                $rarr['code'] = 9001 ;
                $rarr['data'] = $list ;
                $rarr['message'] = 'success' ;
            }
            return json($rarr);
        }else {
            $url = $request->url(true);
            $this->init($url);
            $userinfo = $this->getuserinfo();
            $this->assign('user',$userinfo);
            return $this->fetch('index/index');
        }
    }


    //我的口红详情
    public function myrougeinfo(Request $request){
        if ($request->isAjax()) {
            $init = Session::get();
            $param = $request->post();
            $sql = array('uniacid'=>$init['uniacid'],'user_id'=>$init['user_id'],'id'=>$param['prize_id']);
            $UserPrize = new RougeUserPrize();
            $prize =$UserPrize->where($sql)->find();
            //END
            $sql1 = array('id'=>$prize['goods_id'],'uniacid'=>$init['uniacid']);
            $RougeGoods = new RougeGoods();
            $goods = $RougeGoods->where($sql1)->find();
            //END
            $sql2 = array('id'=>$prize['user_id'],'uniacid'=>$init['uniacid']);
            $RougeUser = new RougeUser();
            $user = $RougeUser->where($sql2)->find();
            //END
            if ($user['address']){
                $data['is_sh_address'] = 1;
            }else{
                $data['is_sh_address'] = 2;
            }
            $data['prize_id'] = $param['prize_id'];
            $data['uniacid'] = $init['uniacid'];
            $data['user_id'] = $init['uniacid'];
            $data['goods_id'] = $prize['goods_id'];
            $data['brand'] = $goods['brand'];
            $data['title'] = $goods['title'];
            $data['img_url'] = $goods['img_url'];
            $data['price'] = $goods['price'];
            $data['ori_price'] = $goods['ori_price'];
            $data['orderid'] = $prize['orderid'] == ''?'暂无':$prize['orderid'];
            $data['name'] = $user['name'];
            $data['phone'] = $user['phone'];
            $data['address'] = $user['address'];
            if (!$user['name'] || !$user['phone'] || !$user['address']){
                $data['sh_statu'] = 2;
            }else{
                $data['sh_statu'] = 1;
            }
            if ($data){
                $rarr['code'] = 0 ;
                $rarr['data'] = $prize ;
                $rarr['message'] = 'success' ;
            }else{
                $rarr['code'] = 9001 ;
                $rarr['data'] = $data ;
                $rarr['message'] = 'error' ;
            }
            return json($rarr);
        }else {
            $url = $request->url(true);
            $this->init($url);
            $userinfo = $this->getuserinfo();
            $this->assign('user',$userinfo);
            return $this->fetch();
        }
    }


    //消费记录
    public function consume(Request $request){
        if ($request->isAjax()) {
            $init = Session::get();
            $param = $request->post();
            $sql['uniacid'] = $init['uniacid'];
            $sql['id'] = $param['goods_id'];
            $RougeGoods = new RougeGoods();
            $goods = $RougeGoods->where($sql)->find();
            //END
            $sql1['uniacid'] = $init['uniacid'];
            $sql1['openid'] = $param['openid'];
            $rouge_user = new RougeUser();
            $user = $rouge_user->where($sql1)->find();
            //END
            if ($user['balance'] > $goods['price'] || $user['balance'] == $goods['price']) {
                $data['uniacid'] = $init['uniacid'];
                $data['openid'] = $param['openid'];
                $data['goods_id'] = $param['goods_id'];
                $data['user_id'] = $user['id'];
                $data['price'] = $goods['price'];
                $data['addtime'] = time();
                $data['create_time'] = time();
                $rouge_user_consume = new RougeUserConsume();
                $rouge_user_consume->allowField(true)->save($data);
                //END
                $datas['balance'] = $user['balance'] - $goods['price'];
                $rouge_user->allowField(true)->save($datas, $sql1);
                //END
                $datass['game_id'] = $this->usergame($data);
                $datass['product_id'] = $param['goods_id'];
                $datass['user_id'] = $user['id'];
                //END

                $rarr['code'] = 0;
                $rarr['data'] = $datass;
                $rarr['message'] = 'success';
            } else {
                $rarr['code'] = 9006;
                $rarr['data'] = ['balance' => $user['balance'], 'price' => $goods['price']];
                $rarr['message'] = 'error';

            }
            return json($rarr);
        }
    }

    //游戏记录
    private function usergame($param){
        $data['user_id']=$param['user_id'];
        $data['uniacid']=$param['uniacid'];
        $data['openid'] = $param['openid'];
        $data['goods_id'] = $param['goods_id'];
        $data['addtime'] = time();
        $data['create_time'] = time();
        $rouge_user_game_log = new RougeUserGameLog();
        $res = $rouge_user_game_log->allowField(true)->save($data);
        $game_id = $rouge_user_game_log->id;
        return $game_id;
    }



    //结束游戏
    public function endgame(Request $request){
        if ($request->isAjax()) {
            $init = Session::get();
            $param = $request->post();
            $sql['uniacid'] = $init['uniacid'];
            $sql['id'] = $param['game_id'];
            $data['level'] = $param['level'];
            if (isset($param['results'])){
                $data['results'] = $param['results'];
            }
            $rouge_user_game_log = new RougeUserGameLog();
            $res = $rouge_user_game_log->allowField(true)->save($data,$sql);
            $this->rougeadd($sql);
            if ($res) {
                $rarr['code'] = 0;
                $rarr['data'] = '';
                $rarr['message'] = 'success';
            } else {
                $rarr['code'] = 0;
                $rarr['data'] = json_encode($sql, JSON_UNESCAPED_UNICODE) . json_encode($data, JSON_UNESCAPED_UNICODE);
                $rarr['message'] = 'error';
            }
            return json($rarr);
        }
    }

    //增加我的口红
    private function rougeadd($param){
        $rouge_user_game_log = new RougeUserGameLog();
        $rouge_user_prize = new RougeUserPrize();
        $game = $rouge_user_game_log->where($param)->find();
        $map['game_id'] = $game['id'];
        $prize = $rouge_user_prize->where($map)->find();
        if (!$prize) {
            if ($game['level'] == 3 && $game['results'] == 2) {
                $sql = array('id' => $game['goods_id'], 'uniacid' => $game['uniacid']);
                $rouge_goods = new RougeGoods();
                $goods = $rouge_goods->where($sql)->find();
                //END
                $data['game_id'] = $game['id'];
                $data['uniacid'] = $game['uniacid'];
                $data['openid'] = $game['openid'];
                $data['user_id'] = $game['user_id'];
                $data['goods_id'] = $game['goods_id'];
                $data['title'] = $goods['brand'] . '-' . $goods['title'];
                $data['img_url'] = $goods['img_url'];
                $data['price'] = $goods['price'];
                $data['sateu'] = 1;
                $data['ori_price'] = $goods['ori_price'];
                $data['goodsinfo'] = json_encode($goods, JSON_UNESCAPED_UNICODE);
                $data['create_time'] = time();

                $res = $rouge_user_prize->allowField(true)->save($data);
            }
        }
    }


    //分享增加金币
    public function sharebalace(Request $request){
        if ($request->isAjax()){
            $RougeUser = new RougeUser();
            $param = $request->post();
            $user = $this->getuserinfo();

            $map['openid'] = $user['openid'];
            $balance = $this->getsharebalance($user);
//            return json($balance);
            if ($balance > 0) {
                $res = $RougeUser->where($map)->setInc('balance',$balance);

                $data['openid'] = $user['openid'];
                $data['user_id'] = $user['id'];
                $data['uniacid'] = $user['uniacid'];
                $data['num'] = 1;
                $data['price'] = $balance;
                $data['create_time'] = time();
                $RougeShareLog = new RougeShareLog();
                $RougeShareLog->allowField(true)->save($data);
                if ($res) {
                    $rarr['code'] = 0;
                    $rarr['data'] = '';
                    $rarr['message'] = 'success';
                } else {
                    $rarr['code'] = 0;
                    $rarr['data'] = '';
                    $rarr['message'] = 'error';
                }
            }else{
                $rarr['code'] = 0;
                $rarr['data'] = '';
                $rarr['message'] = 'error1';
            }
            return json($rarr);
        }
    }

    //获取要增加的金币
    private function getsharebalance($param){
        $RougeShare = new RougeShare();
        $map['uniacid'] = $param['uniacid'];
        $map['statu'] = 1;
        $share = $RougeShare->where($map)->find();
        if ($share){
            $RougeShareLog = new RougeShareLog();
            $num = $share['num'];
            $start_time = mktime(0,0,0,date('m'),date('d'),date('Y'));      //开始时间
            $end_time = mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;    //结束时间
            $map['openid'] = $param['openid'];
            $map['create_time'] = array(array('egt', $start_time), array('elt', $end_time), "and");
            $res = $RougeShareLog->where($map)->count('id');
            if ($res < $num){
                $balance = $share['price'];
            }else{
                $balance = 0;
            }
        }else{
            $balance = 0;
        }
        return $balance;
    }

    //我要分享
    public function myshare(Request $request){
        $url = $request->url(true);
        $this->init($url);
        $user = $this->getuserinfo();
        $RougeShare = new RougeShare();

        $map['uniacid'] = $user['uniacid'];
        $map['statu'] = 1;
        $share = $RougeShare->where($map)->find();
        $this->assign('share',$share);
        $system = new RougeSystem();
        $map['uniacid'] = $user['uniacid'];
        $sysinfo = $system->where($map)->field('title,uniacid')->find();
        $this->assign('sysinfo',$sysinfo);
        //END

        $this->assign('user',$user);
        return $this->fetch();
    }
}
