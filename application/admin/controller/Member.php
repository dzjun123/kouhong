<?php
namespace app\admin\controller;

use app\common\controller\QrcodeMake;
use app\common\model\RougeOrder;
use app\common\model\RougePaylog;
use app\common\model\RougeUser;
use app\common\model\RougeUserConsume;
use app\common\model\RougeUserGameLog;
use app\common\model\RougeUserPrize;
use app\common\utils\DataTablesUtil;
use think\Request;
use think\Session;

class Member extends Base
{
    //用户列表
    public function memberlist(Request $request){
        if ($request->isAjax()) {
            $param = $request->post();
            $aoData = $request->param('aoData'); 
            $queryArr = DataTablesUtil::getQueryPageProperty($aoData);
            $criteria = $queryArr[DataTablesUtil::CRITERIA];
            if ($criteria) {
                if (!empty($criteria['top_openid'])){
                    $sql['top_openid'] = $criteria['top_openid'];
                }
                if (!empty($criteria['nickname'])){
                    $sql['nickname|id|phone'] =  urlencode(trim($criteria['nickname'])) ;
                }

                if ($criteria['level'] === '0') {
                    $sql['level'] = 0;
                }else{
                    if (!empty($criteria['level'])) {
                        $sql['level'] = $criteria['level'];
                    }
                }
                if (!empty($criteria['starttime'])) {
                    $start_time = strtotime($criteria['starttime']);
                } else {
                    $start_time = 0;
                }
                if (!empty($criteria['endtime'])) {
                    $end_time = strtotime($criteria['endtime']) + 86399;
                } else {
                    $end_time = time();
                }
                $sql['create_time'] = array(array('egt', $start_time), array('elt', $end_time), "and");
            }
            $seuser = Session::get();
            $model = new RougeUser();
            $RougePaylog = new RougePaylog();
            $RougeGameLog = new RougeUserGameLog();
            $RougeOrder = new RougeOrder();
            $RougePrize = new RougeUserPrize();
            $sql['uniacid'] = $seuser['uniacid'];
            $list = $model->where($sql)->order('id desc')->limit($queryArr[DataTablesUtil::LIMIT])->select();
            $lists = [];
            foreach ($list as $k=>$v){
                $lists[$k] = $v;
                $lists[$k]['nickname'] =  urldecode($v['nickname'])   ;
                $lists[$k]['level'] = $v['level'] == 1?'推广员':'普通会员';
                $lists[$k]['paylog_url'] = url('paylog/payloglist',array('openid'=>$v['openid']));      //支付列表
                $lists[$k]['game_url'] = url('member/gamelist',array('openid'=>$v['openid']));          //游戏记录
                $lists[$k]['prize_url'] = url('member/prizelist',array('openid'=>$v['openid']));        //奖品记录
                $lists[$k]['orders_url'] = url('orders/orderlist',array('openid'=>$v['openid']));       //订单记录
                $lists[$k]['edir_url'] = url('member/edituserdistr',array('id'=>$v['id']));       //订单记录
                $lists[$k]['status'] =$v['statu']==0?"正常用户":"黑名单用户";


                $lists[$k]['reg_price'] = '￥'.$RougePaylog->where(['openid'=>$v['openid'],'uniacid'=>$seuser['uniacid'],'pay_statu'=>1])->sum('price');      //充值金额
                $lists[$k]['game_num'] = $RougeGameLog->where(['openid'=>$v['openid'],'uniacid'=>$seuser['uniacid']])->count('id');      //游戏数量
                $lists[$k]['order_num'] = $RougeOrder->where(['openid'=>$v['openid'],'uniacid'=>$seuser['uniacid']])->count('id');       //兑换数量
                $lists[$k]['prize_num'] = $RougePrize->where(['openid'=>$v['openid'],'uniacid'=>$seuser['uniacid']])->count('id');       //奖品数量
//                $lists[$k]['results'] = $v['results'] == 1?'失败':'成功';
//                $lists[$k]['addtime'] = date('Y-m-d h:i:s',$v['addtime']);
            }
            $count = $model->where($sql)->count('id');
            $json = DataTablesUtil::getJsonPage($queryArr[DataTablesUtil::S_ECHO], $count, $lists);
            return json_decode($json);

        }else{
            $param = $request->param();
            if (!isset($param['openid'])){$param['openid']='';}
            if (!isset($param['top_openid'])){$param['top_openid']='';}
            $this->assign('param',$param);
            return $this->fetch();
        }


    }

    //用户奖品列表
    public function prizelist(Request $request){
        if ($request->isAjax()) {
            $param = $request->param();
            $aoData = $request->param('aoData');
            $queryArr = DataTablesUtil::getQueryPageProperty($aoData);
            $criteria = $queryArr[DataTablesUtil::CRITERIA];
            if ($criteria || $param) {
                $RougeUser = new RougeUser();
                if (!empty($criteria['nickname'])) {
                    $map['id|nickname'] = trim($criteria['nickname']);
                    $Uid = $RougeUser->where($map)->value('id');
                    $sql['a.user_id'] = $Uid;
                }
                if (!empty($criteria['openid'])) {
                    $sql['a.openid'] = $criteria['openid'];
                }
                if (!empty($criteria['level'])) {
                    $sql['a.statu'] = $criteria['level'];
                }
                if (!empty($criteria['starttime'])) {
                    $start_time = strtotime($criteria['starttime']);
                } else {
                    $start_time = 0;
                }
                if (!empty($criteria['endtime'])) {
                    $end_time = strtotime($criteria['endtime']) + 86399;
                } else {
                    $end_time = time();
                }
                $sql['a.create_time'] = array(array('egt', $start_time), array('elt', $end_time), "and");
            }
            $model = new RougeUserPrize();
            $seuser = Session::get();
            $sql['a.uniacid'] = $seuser['uniacid'];
            $list = $model->alias('a')
                ->join('wn_rouge_user c', 'a.openid=c.openid', 'left')
                ->field('a.title,a.img_url,c.nickname,a.create_time,a.statu')
                ->limit($queryArr[DataTablesUtil::LIMIT])
                ->order('a.id desc')
                ->where($sql)->select();
            $lists = [];
            foreach ($list as $k => $v) {
                $lists[$k] = $v;
                $lists[$k]['statu'] = $this->getstatuprize($v['statu']);
                $lists[$k]['nickname'] =  urldecode($v['nickname']) ;
            }
            $count = $model->alias('a')->where($sql)->order('id desc')->count('id');
            $json = DataTablesUtil::getJsonPage($queryArr[DataTablesUtil::S_ECHO], $count, $lists);
            return json_decode($json);
        }else {
            $param = $request->param();
            if (!isset($param['openid'])){$param['openid']='';}
            $this->assign('param',$param);
            return $this->fetch();
        }
    }

    private function getstatuprize($id){
        switch ($id){
            case "1":
                $res = '未兑换';
                break;
            case "2":
                $res = '已提交';
                break;
            case "3":
                $res = '已兑换';
                break;
            default :
                $res = '未知';
        }
        return $res;
    }

    //用户游戏记录
    /*
     *
     * ->limit(($param['pageNumber'] - 1) * $param['pageSize'], $param['pageSize'])->
     * */
    public function gamelist(Request $request){
        if ($request->isAjax()) {
            $param = $request->post();
            $aoData = $request->param('aoData');
            $queryArr = DataTablesUtil::getQueryPageProperty($aoData);
            $criteria = $queryArr[DataTablesUtil::CRITERIA];
            if ($criteria) {
                $RougeUser = new RougeUser();
                if (!empty($criteria['nickname'])) {
                    $map['id|nickname'] = trim($criteria['nickname']);
                    $Uid = $RougeUser->where($map)->value('id');
                    $sql['a.user_id'] = $Uid;
                }
                if (!empty($criteria['level'])) {
                    $sql['a.results'] = $criteria['level'];
                }
                if (!empty($criteria['openid'])) {
                    $sql['a.openid'] = $criteria['openid'];
                }
                if (!empty($criteria['starttime'])) {
                    $start_time = strtotime($criteria['starttime']);
                } else {
                    $start_time = 0;
                }
                if (!empty($criteria['endtime'])) {
                    $end_time = strtotime($criteria['endtime']) + 86399;
                } else {
                    $end_time = time();
                }
                $sql['a.addtime'] = array(array('egt', $start_time), array('elt', $end_time), "and");
            }
            $seuser = Session::get();
            $model = new RougeUserGameLog();
            $sql['a.uniacid'] = $seuser['uniacid'];
            $list = $model->alias('a')
                ->join('wn_rouge_goods b', 'a.goods_id=b.id', 'left')
                ->join('wn_rouge_user c', 'a.openid=c.openid', 'left')
                ->field('a.id,b.title,b.img_url,a.level,a.results,c.nickname,a.create_time,a.addtime')
                ->limit($queryArr[DataTablesUtil::LIMIT])
                ->where($sql)->order('a.id desc')->select();
            $lists = [];
            foreach ($list as $k=>$v){
                $lists[$k] = $v;
                $lists[$k]['results'] = $v['results'] == 2?'成功':'失败';
                $lists[$k]['addtime'] = date('Y-m-d h:i:s',$v['addtime']);
                $lists[$k]['nickname'] =  urldecode($v['nickname']) ;
            }
            $count = $model->alias('a')->where($sql)->count('id');
            $json = DataTablesUtil::getJsonPage($queryArr[DataTablesUtil::S_ECHO], $count, $lists);
            return json_decode($json);

        }else{
            $param = $request->param();
            if (!isset($param['openid'])){$param['openid']='';}
            if (!isset($param['top_openid'])){$param['top_openid']='';}
            $this->assign('param',$param);
            return $this->fetch();
        }

    }

    //改为推广员
    public function edituserdistr(Request $request){
        $RoUser = new RougeUser();
        if ($request->isAjax()){
            $param = $request->post();
//            return json($param);
            $QrCode = new QrcodeMake();
            $map['id'] = $param['id'];
            $data['level'] = $param['level'];
            $data['distr_url'] = $request->domain().'/mobile.php/login/index/outer/' . $param['openid'].'/platid/'.Session::get('uniacid');
            $data['distr_img_url'] = $QrCode->getcodeurl($param['id'],$data['distr_url']);
            $res = $RoUser->allowField(true)->save($data,$map);
            if ($res){
                $rarr['statu'] = 0 ;
                $rarr['url'] = url('member/memberlist');
                $rarr['message'] = 'success' ;
            }else{
                $rarr['statu'] = 9001 ;
                $rarr['message'] = 'success' ;
            }
            return json($rarr);
        }else{
            $param = $request->param();
            $map['id'] = $param['id'];
            $user = $RoUser->where($map)->find();
            $this->assign('info',$user);
            return $this->fetch();
        }
    }

    //用户充值
    public function recharge(Request $request){
        $RoUser = new RougeUser();
        if ($request->isAjax()){
            $param = $request->post();
            $map['id'] = $param['id'];
//            $data['balance'] = $param['balance'];
//            $data['distr_url'] = $request->domain().'/mobile.php/login/index/outer/' . $param['openid'].'/platid/'.Session::get('uniacid');
            $res = $RoUser->where($map)->setInc('balance',$param['price']);
            if ($res){
                $rarr['statu'] = 0 ;
                $rarr['url'] = url('member/memberlist');
                $rarr['message'] = 'success' ;
            }else{
                $rarr['statu'] = 9001 ;
                $rarr['message'] = 'success' ;
            }
            return json($rarr);
        }else{
            $param = $request->param();
            $map['id'] = $param['id'];
            $user = $RoUser->where($map)->find();
            $this->assign('info',$user);
            return $this->fetch();
        }
    }

    //拉黑
    public function userblack(Request $request){
        $RoUser = new RougeUser();
        if ($request->isAjax()){
            $param = $request->post();
            $map['id'] = $param['id'];
            $statu = $RoUser->where(['id'=>$param['id']])->value('statu');
            $data['statu'] = $statu == 0?1:0;
            $res = $RoUser->allowField(true)->save($data,$map);
            if ($res){
                $rarr['statu'] = 0 ;
                $rarr['url'] = url('member/memberlist');
                $rarr['message'] = 'success' ;
            }else{
                $rarr['statu'] = 9001 ;
                $rarr['message'] = 'success' ;
            }
            return json($rarr);
        }
    }

    //用户消费记录
    public function consumelist(){
        $model = new RougeUserConsume();
        $seuser = Session::get();
        $sql['uniacid'] = $seuser['uniacid'];
        $list = $model->where($sql)->select();
        $this->assign('list',$list);
        return $this->fetch();
    }
}
