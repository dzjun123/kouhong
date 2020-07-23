<?php
namespace app\system\controller;

use app\common\model\RougePlat;
use think\Request;
use think\Session;

class Plat extends Base
{
    //列表
    public function platlist(){
        $model = new RougePlat();
        $list = $model->select();
        $this->assign('list',$list);
        return $this->fetch();
    }

    //添加
    public function platadd(Request $request){
        if ($request->isAjax()){
            $model = new RougePlat();
            $param = $request->post();
            $data = $param;
            $data['create_time'] = time();
            $res = $model->allowField(true)->save($data);
            if ($res){
                $rarr['statu'] = 0 ;
                $rarr['url'] = url('goods/goodslist');
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
    public function platedit(Request $request){
        $model = new RougePlat();
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
                $rarr['url'] = url('goods/goodslist');
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
    public function platinfo(Request $request){
        $model = new RougePlat();
        $seuser = Session::get();
        $param = $request->param();
        $sql['uniacid'] = $seuser['uniacid'];
        $sql['id']=$param['id'];
        $info =$model->where($sql)->find();
        return $this->fetch();
    }

    //删除
    public function platdel(Request $request){
        $model = new RougePlat();
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
