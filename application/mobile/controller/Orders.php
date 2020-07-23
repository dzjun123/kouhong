<?php
namespace app\mobile\controller;

use app\common\model\RougeGoods;
use app\common\model\RougeOrder;
use app\common\model\RougeUser;
use app\common\model\RougeUserPrize;
use think\Request;
use think\Session;
use Wechat\WechatApi;

class Orders extends Base
{
    public function index()
    {
        return $this->fetch();
    }

    //提交订单
    public function suborder(Request $request){
        $UserPrize = new RougeUserPrize();
        if ($request->isAjax()) {
            $param = $request->post();

            $RougeOrder = new RougeOrder();
            $init = Session::get();
            $sql = array('uniacid'=>$init['uniacid'],'user_id'=>$init['user_id'],'id'=>$param['prize_id']);
            $prize = $UserPrize->where($sql)->find();
            if ($prize['statu'] == 1) {
                //END
                $data['uniacid'] = $init['uniacid'];
//            $data['from_id'] = $_GPC['form_id'];
                $data['openid'] = $param['openid'];
                $data['user_id'] = $init['user_id'];
                $data['prize_id'] = $param['prize_id'];
                $data['goods_id'] = $prize['goods_id'];
                $data['name'] = $param['name'];
                $data['phone'] = $param['phone'];
                $data['address'] = $param['address'];
                $data['create_time'] = time();
                $data['orderid'] = $this->getorderno();
                $data['info'] = json_encode($init, JSON_UNESCAPED_UNICODE);
                $res = $RougeOrder->allowField(true)->save($data);
                if ($res) {
                    $datas['statu'] = 2;
                    $datas['orderid'] = $data['orderid'];
                    $sql1 = array('uniacid' => $init['uniacid'], 'user_id' => $init['user_id'], 'id' => $param['prize_id']);
                    $UserPrize->allowField(true)->save($datas, $sql1);
                    //END
                    $datass['name'] = $param['name'];
                    $datass['phone'] = $param['phone'];
                    $datass['address'] = $param['address'];
                    $sql2 = array('uniacid' => $init['uniacid'], 'id' => $init['user_id']);
                    $RougeUser = new RougeUser();
                    $RougeUser->allowField(true)->save($datass, $sql2);
                    $rarr['code'] = 0;
                    $rarr['data']['orderid'] = $data['orderid'];
                    $rarr['message'] = 'success'; 
                    //提醒我谁中奖了口红,消息模板提醒
                    $option = $this->option(Session::get('uniacid')); 
                    $WxObj = new WechatApi($option); 
                    //获取accesstoken，发送消息模板   
                    $data=array();
                    $data['touser']="oSvf_0lw9jIH4-C2W6YOQBiHoe7g"; 
                    $data['template_id']="n8DH-FBZMAmZXKCBL1iOe0rGYEVdTGYToPEYlIolBd4"; 
                    $data['data']['first']['value']="用户中了口红,请到后台查看";
                    $data['data']['first']['color']="#F70909";

                    $data['data']['orderno']['value']=123456;
                    $data['data']['orderno']['color']="#173177";
                    
                    $data['data']['refundno']['value']="1支";
                    $data['data']['refundno']['color']="#173177";
                    
                    $data['data']['refundproduct']['value']=180;
                    $data['data']['refundproduct']['color']="#173177"; 
  
                    $data['data']['remark']['value']="";  
                    $accesstoken= $WxObj->sendTemplateMessage($data);   //获取accesstoken    
                    
                } else {
                    $rarr['code'] = 9001;
                    $rarr['data'] = '';
                    $rarr['message'] = 'error';
                }
            }else{
                $rarr['code'] = 9002;
                $rarr['data'] = '';
                $rarr['message'] = '您已提交订单';
            }
            return json($rarr);
        } else {

            $param = $request->param();
            $url = $request->url(true);
            $this->init($url);
            $user = $this->getuserinfo();
            $map['id'] = $param['prize_id'];
            $info = $UserPrize->where($map)->find();
            $this->assign('info',$info);
            $jssdk = $this->getjssdk();
            $jssdk = json_encode($jssdk);
            $this->assign('jssdk',$jssdk);
            $this->assign('user',$user);
            return $this->fetch();
        }
    }

    //订单列表
    public function orderlist(Request $request){
        if ($request->isAjax()) {
            $param = $request->post();
            $model = new RougeOrder();
            $userinfo = $this->getuserinfo();
            $map['uniacid'] = $userinfo['uniacid'];
            $map['user_id'] = $userinfo['id'];
            $count = $model->where($map)->count('id');
            $page_num = ceil($count / 5);
            if($page_num > (int)$param['page'] || $page_num == (int)$param['page']) {
                $mab['a.uniacid'] = $userinfo['uniacid'];
                $mab['a.user_id'] = $userinfo['id'];
                $list = $model->alias('a')->join('wn_rouge_goods b', 'a.goods_id=b.id', 'left')->
                field('b.img_url,b.brand,b.title,b.ori_price,a.id,a.uniacid,a.user_id,a.goods_id,a.orderid,a.create_time,a.statu')->
                where($mab)->paginate(5);
                foreach ($list as $k=>$v){
                    $list[$k] = $v;
                    $list[$k]['statu'] = $v['statu'] == 1?'未发货':'已发货';
                }
            }else{
                $list = [];
            }
            if ($list) {
                $rarr['code'] = 0;
                $rarr['data'] = $list;
                $rarr['message'] = 'success';
                $rarr['count'] = $count;
                $rarr['page_num'] = $page_num;
                $rarr['page'] = $param['page'];
            } else {
                if ($param['page'] < 2) {
                    $rarr['code'] = 9001;
                    $rarr['data'] = $list;
                    $rarr['message'] = 'error';
                    $rarr['count'] = $count;
                    $rarr['page_num'] = $page_num;
                    $rarr['page'] = $param['page'];
                }else{
                    $rarr['code'] = 0;
                    $rarr['data'] = $list;
                    $rarr['message'] = 'success';
                    $rarr['count'] = $count;
                    $rarr['page_num'] = $page_num;
                    $rarr['page'] = $param['page'];
                }
            }
            return json($rarr);
        } else {
            $url = $request->url(true);
            $this->init($url);
            $userinfo = $this->getuserinfo();
            $this->assign('user',$userinfo);
            return $this->fetch();
        }
    }

    //订单详情
    public function placeorder(Request $request){
        $url = $request->url(true);
        $this->init($url);
        $model = new RougeOrder();
        $param = $request->param();
        $userinfo = $this->getuserinfo();
        $map['a.uniacid'] = $userinfo['uniacid'];
        $map['a.user_id'] = $userinfo['id'];
        $map['a.orderid'] = $param['orderid'];
        $list = $model->alias('a')->join('wn_rouge_goods b', 'a.goods_id=b.id', 'left')->
        field('b.img_url,b.brand,b.title,b.ori_price,a.id,a.uniacid,a.user_id,a.goods_id,a.orderid,a.create_time,a.statu,a.courier_unit,a.courier_no,a.name,a.phone,a.address')->where($map)->find();
        $this->assign('info',$list);
        $this->assign('user',$userinfo);
      return $this->fetch();
    }


}

