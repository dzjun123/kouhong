<?php
namespace app\admin\controller;

use app\common\model\RougeGoods;
use app\common\model\RougeOrder;
use app\common\model\RougeUser;
use app\common\utils\DataTablesUtil;
use think\Request;
use think\Session;

class Orders extends Base
{
    //兑换列表
    public function orderlist(Request $request){
        if ($request->isAjax()) {
            $param = $request->param();
            $aoData = $request->param('aoData');
            $queryArr = DataTablesUtil::getQueryPageProperty($aoData);
            $criteria = $queryArr[DataTablesUtil::CRITERIA];
            if ($criteria || $param) {
                $RougeUser = new RougeUser();
                if (!empty($criteria['nickname'])) {
                    $map['id|nickname|phone'] = $criteria['nickname'];
                    $Uid = $RougeUser->where($map)->value('openid');
                    $sql['a.openid'] = $Uid;
                }

                if (!empty($criteria['openid'])) {
                    $sql['a.openid'] = $criteria['openid'];
                }
                if (!empty($criteria['statu'])) {
                    $sql['a.statu'] = $criteria['statu'];
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
            $model = new RougeOrder();
            $seuser = Session::get();
            $sql['a.uniacid'] = $seuser['uniacid'];
            $list = $model->alias('a')
                ->join('wn_rouge_goods b', 'a.goods_id=b.id', 'left')
                ->join('wn_rouge_user c', 'a.openid=c.openid', 'left')
                ->field('b.img_url,b.brand,b.title,b.ori_price,a.id,a.uniacid,a.user_id,a.goods_id,a.orderid,a.statu,a.create_time,a.phone,a.address,a.name,a.put_time,a.courier_no,c.nickname')
                ->order('a.id desc')
                ->limit($queryArr[DataTablesUtil::LIMIT])
                ->where($sql)->select();
            $lists = [];
            foreach ($list as $k => $v) {
                $lists[$k] = $v;
                $lists[$k]['map'] = $sql;
                $lists[$k]['statu'] = $v['statu'] == 1?'未发货':'已发货';
                $lists[$k]['put_time'] = $v['put_time'] == ''?'暂无':date('Y-m-d h:i:s',$v['put_time']);
                $lists[$k]['nickname'] =  urldecode($v['nickname']) ;
            }
            $count = $model->alias('a')->where($sql)->count('id');
            $json = DataTablesUtil::getJsonPage($queryArr[DataTablesUtil::S_ECHO], $count, $lists);
            return json_decode($json);
        }else {
            $param = $request->param();
            if (!isset($param['openid'])){$param['openid']='';}
            $this->assign('param',$param);
            return $this->fetch();
        }
    }

    //兑换发货
    public function orderedit(Request $request){
        $model = new RougeOrder();
        if ($request->isAjax()){
            $seuser = Session::get();
            $param = $request->post();
            $data = $param;
            $sql['uniacid'] = $seuser['uniacid'];
            $sql['id'] = $param['id'];
            $res = $model->allowField(true)->save($data,$sql);
            if ($res){
                $rarr['statu'] = 0 ;
                $rarr['message'] = 'success' ;
            }else{
                $rarr['statu'] = 9001 ;
                $rarr['message'] = 'success' ;
            }
            return json($rarr);
        }else{
            $seuser = Session::get();
            $param = $request->param();
            $rouGoods = new RougeGoods();
            $sql['uniacid'] = $seuser['uniacid'];
            $sql['id']=$param['id'];

            $info =$model->where($sql)->find();
            $goods = $rouGoods->where(['id'=>$info['goods_id']])->find();
            $info['title'] = $goods['title'];
            $info['img_url'] = $goods['img_url'];
            $this->assign('info',$info);
            return $this->fetch();
        }
    }



    //删除
    public function orderdel(Request $request){
        $model = new RougeOrder();
        $seuser = Session::get();
        $param = $request->param();
        $sql['uniacid'] = $seuser['uniacid'];
        $sql['id']=$param['id'];
        $res = $model->where($sql)->delete();
        if ($res){
            $rarr['statu'] = 0 ;
            $rarr['message'] = 'success' ;
        }else{
            $rarr['statu'] = 9001 ;
            $rarr['message'] = 'success' ;
        }
        return json($rarr);
    }
}
