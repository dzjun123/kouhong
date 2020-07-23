<?php
namespace app\admin\controller;

use app\common\model\RougeGame;
use app\common\model\RougeGoods;
use app\common\model\RougeRule;
use think\Request;
use think\Session;

class Rule extends Base
{
    //充值列表
    public function rulelist(){
        $model = new RougeRule();
        $seuser = Session::get();
        $sql['uniacid'] = $seuser['uniacid'];
        $list = $model->where($sql)->select();
        $this->assign('list',$list);
        return $this->fetch();
    }

    //添加
    public function ruleadd(Request $request){
        if ($request->isAjax()){
            $model = new RougeRule();
            $seuser = Session::get();
            $param = $request->post();
            $data = $param;
            $data['uniacid'] = $seuser['uniacid'];
            $data['create_time'] = time();
            $res = $model->allowField(true)->save($data);
            if ($res){
                $rarr['statu'] = 0 ;
                $rarr['url'] = url('rule/rulelist') ;
                $rarr['message'] = 'success' ;
            }else{
                $rarr['statu'] = 9001 ;
                $rarr['message'] = 'success' ;
            }
            return json($rarr);
        }else{
            return $this->fetch();
        }
    }

    //修改
    public function ruleedit(Request $request){
        $model = new RougeRule();
        if ($request->isAjax()){
            $seuser = Session::get();
            $param = $request->post();
            $data = $param;
            $sql['uniacid'] = $seuser['uniacid'];
            $sql['id'] = $param['id'];
            $res = $model->allowField(true)->save($data,$sql);
            if ($res){
                $rarr['statu'] = 0 ;
                $rarr['url'] = url('rule/rulelist') ;
                $rarr['message'] = 'success' ;
            }else{
                $rarr['statu'] = 9001 ;
                $rarr['message'] = 'success' ;
            }
            return json($rarr);
        }else{
            $seuser = Session::get();
            $param = $request->param();
            $sql['uniacid'] = $seuser['uniacid'];
            $sql['id']=$param['id'];
            $info =$model->where($sql)->find();
            $this->assign('info',$info);
            return $this->fetch();
        }
    }

    //详情
    public function ruleinfo(Request $request){
        $model = new RougeRule();
        $seuser = Session::get();
        $param = $request->param();
        $sql['uniacid'] = $seuser['uniacid'];
        $sql['id']=$param['id'];
        $info =$model->where($sql)->find();
        $this->assign('info',$info);
        return $this->fetch();
    }

    //删除
    public function ruledel(Request $request){
        $model = new RougeRule();
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
