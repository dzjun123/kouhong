<?php
namespace app\admin\controller;

use app\common\model\RougeGame;
use app\common\model\RougeGoods;
use think\Request;
use think\Session;

class Games extends Base
{
    //关卡列表
    public function gameslist(){
        $model = new RougeGame();
        $seuser = Session::get();
        $sql['uniacid'] = $seuser['uniacid'];
        $list = $model->where($sql)->select();
        $this->assign('list',$list);
        return $this->fetch();
    }

    //添加
    public function gamesadd(Request $request){
        if ($request->isAjax()){
            $model = new RougeGame();
            $seuser = Session::get();
            $param = $request->post();
            $data = $param;
            $data['uniacid'] = $seuser['uniacid'];
            $res = $model->allowField(true)->save($data);
            if ($res){
                $rarr['statu'] = 0 ;
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
    public function gamesdit(Request $request){
        $model = new RougeGame();
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
            $sql['uniacid'] = $seuser['uniacid'];
            $sql['id']=$param['id'];
            $info =$model->where($sql)->find();
            $this->assign('info',$info);
            return $this->fetch();
        }
    }

    //详情
    public function gamesinfo(Request $request){
        $model = new RougeGame();
        $seuser = Session::get();
        $param = $request->param();
        $sql['uniacid'] = $seuser['uniacid'];
        $sql['id']=$param['id'];
        $info =$model->where($sql)->find();
        $this->assign('info',$info);
        return $this->fetch();
    }

    //删除
    public function gamesdel(Request $request){
        $model = new RougeGame();
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
