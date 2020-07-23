<?php
/**
 * Created by PhpStorm.
 * User: 悟能科技
 * Date: 2018/11/2
 * Time: 14:01
 */

namespace app\admin\controller;


use app\common\model\RougeGoods;
use think\Request;
use think\Session;

class Goods extends Base
{

    //列表
    public function goodslist(Request $request){

            $param = $request->param();
            if ($param) {
                if (!empty($param['title'])){
                    $sql['title'] = trim($param['title']);
                }
            }
            $model = new RougeGoods();
            $seuser = Session::get();
            $sql['uniacid'] = $seuser['uniacid'];
            $list = $model->where($sql)->paginate('10', false, ['query' => request()->param()]);
            $this->assign('list', $list);
            return $this->fetch();

    }

    //添加
    public function goodsadd(Request $request){
        if ($request->isAjax()){
            $model = new RougeGoods();
            $seuser = Session::get();
            $param = $request->post();
            $data = $param;
            $data['uniacid'] = $seuser['uniacid'];
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
    public function goodsedit(Request $request){
        $model = new RougeGoods();
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
    public function goodsinfo(Request $request){
        $model = new RougeGoods();
        $seuser = Session::get();
        $param = $request->param();
        $sql['uniacid'] = $seuser['uniacid'];
        $sql['id']=$param['id'];
        $info =$model->where($sql)->find();
        return $this->fetch();
    }

    //删除
    public function goodsdel(Request $request){
        $model = new RougeGoods();
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