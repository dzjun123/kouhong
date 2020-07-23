<?php
namespace app\admin\controller;

use app\common\model\RougeGame;
use app\common\model\RougeGoods;
use app\common\model\RougeSystem;
use app\common\model\RougeSystemWx;
use think\Request;
use think\Session;

class Setups extends Base
{
    //基础设置
    public function setups(Request $request){
        $model = new RougeSystemWx();
        $init = Session::get();
        if ($request->isAjax()){
            $param = $request->post();
            $data = $param;
            $map['uniacid'] = $init['uniacid'];
            $res = $model->allowField(true)->save($data,$map);
            if ($res){
                $rarr['statu'] = 0 ;
                $rarr['message'] = 'success' ;
            }else{
                $rarr['statu'] = 1 ;
                $rarr['message'] = 'success' ;
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
    public function setupsgg(Request $request){
        $model = new RougeSystem();
        $init = Session::get();
        if ($request->isAjax()){
            $param = $request->post();
            $data = $param;
            $map['uniacid'] = $init['uniacid'];
            $res = $model->allowField(true)->save($data,$map);
            if ($res){
                $rarr['statu'] = 0 ;
                $rarr['message'] = 'success' ;
            }else{
                $rarr['statu'] = 1 ;
                $rarr['message'] = 'success' ;
            }
            return json($rarr);
        }else{
            $sql['uniacid'] = $init['uniacid'];
            $info = $model->where($sql)->find();
            $this->assign('info',$info);
            return $this->fetch();
        }
    }

    //说明设置
    public function setupssm(Request $request){
        $model = new RougeSystem();
        $init = Session::get();
        if ($request->isAjax()){
            $param = $request->post();
            $data = $param;
            $map['uniacid'] = $init['uniacid'];
            $res = $model->allowField(true)->save($data,$map);
            if ($res){
                $rarr['statu'] = 0 ;
                $rarr['message'] = 'success' ;
            }else{
                $rarr['statu'] = 1 ;
                $rarr['message'] = 'success' ;
            }
            return json($rarr);
        }else{
            $sql['uniacid'] = $init['uniacid'];
            $info = $model->where($sql)->find();
            $this->assign('info',$info);
            return $this->fetch();
        }
    }

    //说明设置
    public function setupsimg(Request $request){
        $model = new RougeSystem();
        $init = Session::get();
        if ($request->isAjax()){
            $param = $request->post();
            //return json($param);
            $data = $param;
//            var_dump($data); 
            $map['uniacid'] = $init['uniacid'];
//             var_dump($map['uniacid']);exit;
            $res = $model->allowField(true)->save($data,$map);
            if ($res){
                $rarr['statu'] = 0 ;
                $rarr['message'] = 'success' ;
            }else{
                $rarr['statu'] = 1 ;
                $rarr['message'] = 'success' ;
            }
            return json($rarr);
        }else{
            $sql['uniacid'] = $init['uniacid'];
            $info = $model->where($sql)->find();
            $this->assign('info',$info);
            return $this->fetch();
        }
    }

}
