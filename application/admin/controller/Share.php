<?php
namespace app\admin\controller;

use app\common\model\RougeGame;
use app\common\model\RougeGoods;
use app\common\model\RougeShare;
use app\common\model\RougeShareLog;
use app\common\model\RougeSystem;
use app\common\model\RougeSystemWx;
use app\common\model\RougeUser;
use app\common\utils\DataTablesUtil;
use think\Request;
use think\Session;

class Share extends Base
{
    //基础设置
    public function setshare(Request $request){
        $model = new RougeShare();
        $init = Session::get();
        if ($request->isAjax()){
            $param = $request->post();
            $data = $param;
            $map['uniacid'] = $init['uniacid'];
            $isone = $model->where($map)->find();
            if ($isone){
                $res = $model->allowField(true)->save($data,$map);
            }else{
                $data['uniacid'] = $init['uniacid'];
                $res = $model->allowField(true)->save($data);
            }
            if ($res){
                $rarr['statu'] = 0 ;
                $rarr['message'] = 'success' ;
            }else{
                $rarr['statu'] = 1 ;
                $rarr['message'] = 'error' ;
            }
            return json($rarr);
        }else{
            $sql['uniacid'] = $init['uniacid'];
            $info = $model->where($sql)->find();
            $this->assign('info',$info);
            return $this->fetch();
        }
    }

    //公告设置
    public function sharelist(Request $request){
        if ($request->isAjax()) {
            $param = $request->post();
            $aoData = $request->param('aoData');
            $queryArr = DataTablesUtil::getQueryPageProperty($aoData);
            $criteria = $queryArr[DataTablesUtil::CRITERIA];
            if ($criteria) {
                $RougeUser = new RougeUser();
                if (!empty($criteria['nickname'])) {
                    $map['id|nickname'] = $criteria['nickname'];
                    $Uid = $RougeUser->where($map)->value('openid');
                    $sql['a.openid'] = $Uid;
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
            $seuser = Session::get();
            $model = new RougeShareLog();
            $sql['a.uniacid'] = $seuser['uniacid'];
            $list = $model->alias('a')->join('wn_rouge_user b', 'a.openid=b.openid', 'left')
                ->field('b.nickname,a.price,a.create_time')
                ->limit($queryArr[DataTablesUtil::LIMIT])
                ->where($sql)->order('a.id desc')->select();
            $lists = [];
            foreach ($list as $k=>$v){
                $lists[$k] = $v;
                $lists[$k]['nickname'] =  urldecode($v['nickname'] )  ;
//                $lists[$k]['map'] = $sql;
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



}
