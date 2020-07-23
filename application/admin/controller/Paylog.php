<?php
namespace app\admin\controller;

use app\common\model\RougeOrder;
use app\common\model\RougePaylog;
use app\common\model\RougeUser;
use app\common\utils\DataTablesUtil;
use think\Request;
use think\Session;

class Paylog extends Base
{
    //支付列表
    public function payloglist(Request $request){
        if ($request->isAjax()) {
            $param = $request->param();
            $aoData = $request->param('aoData');
            $queryArr = DataTablesUtil::getQueryPageProperty($aoData);
            $criteria = $queryArr[DataTablesUtil::CRITERIA];
            if ($criteria || $param) {
                $RougeUser = new RougeUser();
                if (!empty($criteria['nickname'])) {
                    $maps['id|nickname|phone'] = trim($criteria['nickname']);
                    $Uid = $RougeUser->where($maps)->value('id');
                    $map['a.user_id'] = $Uid;
                }
                if ($criteria['pay_statu'] === '0') {
                    $map['a.pay_statu'] = 0;

                }else{
                    if (!empty($criteria['pay_statu']) || $criteria['pay_statu'] === 0) {
                        $map['a.pay_statu'] = $criteria['pay_statu'];
                    }
                }
                if (!empty($criteria['openid'])) {
                    $map['a.openid'] = $criteria['openid'];
                }
                if (!empty($criteria['orderid'])) {
                    $map['a.orderid'] = $criteria['orderid'];
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
                $map['a.create_time'] = array(array('egt', $start_time), array('elt', $end_time), "and");
            }
            $model = new RougePaylog();
            $seuser = Session::get();
            $map['a.uniacid'] = $seuser['uniacid'];
            $list = $model->alias('a')
                ->join('wn_rouge_user b', 'a.openid=b.openid', 'left')
                ->field('a.id,b.nickname,a.orderid,a.pay_statu,a.pay_time,a.price,a.create_time')
                ->order('a.id desc')
                ->limit($queryArr[DataTablesUtil::LIMIT])
                ->where($map)->select();
            $lists = [];
            foreach ($list as $k => $v) {
                $lists[$k] = $v;
                $lists[$k]['pay_statu'] = $v['pay_statu'] == 0?'失败':'成功';
                $lists[$k]['nickname'] =  urldecode($v['nickname'] )  ;
            }
            $count = $model->alias('a')->where($map)->count('id');
//            $lists = $map;
            $json = DataTablesUtil::getJsonPage($queryArr[DataTablesUtil::S_ECHO], $count, $lists);
            return json_decode($json);
        }else {
            $param = $request->param();
            if (!isset($param['openid'])){$param['openid']='';}
            $this->assign('param',$param);
            return $this->fetch();
        }

    }

    //删除
    public function paylogdel(Request $request){
        $model = new RougePaylog();
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
