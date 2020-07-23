<?php
namespace app\admin\controller;

use app\common\model\RougePlat;
use app\common\model\RougeSystem;
use app\common\model\RougeSystemWx;
use app\common\model\RougeUser;
use app\common\model\RougeLoginUser;
use think\Request;
use think\Session;

class Plat extends Base
{
    public function _initialize()
    {
        $seuser = Session::get();
        if ($seuser['userlevel'] !== 1 && $seuser['username'] !== 'admin'){
            $this->redirect('index/index');
        }
    }

    //列表
    public function platlist(){
        $model = new RougeSystem();
        $list = $model->alias('a')->join('wn_rouge_login_user b', 'a.uniacid=b.uniacid', 'left')->
        field('a.id,a.title,a.create_time,a.statu,b.username,a.uniacid')->select();
        $this->assign('list',$list);
        return $this->fetch();
    }

    //添加
    public function platadd(Request $request){
        if ($request->isAjax()){
            $RougeSystem = new RougeSystem();
            $RouUser = new RougeLoginUser();
            $param = $request->post();
            $datas['username'] = $param['username'];
            $map['username'] = $param['username'];
            $only = $RouUser->where($map)->find();
            $maps['title'] = $param['title'];
            $onlys = $RougeSystem->where($maps)->find();
            if (!$onlys) {
                if (!$only) {
                    $datas['password'] = $param['password'];
                    $datas['create_time'] = time();
                    $conten = $RouUser->allowField(true)->save($datas);
                    //END
                    if ($conten) {
                        $data['title'] = $param['title'];
                        $data['create_time'] = time();
                        $res = $RougeSystem->allowField(true)->save($data);
                        //END
                        $datas1['uniacid'] = $RougeSystem->id;
                        $RouUser->allowField(true)->save($datas1, ['username' => $param['username']]);
                        $RougeSystem->allowField(true)->save($datas1, ['id' => $datas1['uniacid']]);
                        $this->addplat($datas1);
                        if ($res) {
                            $rarr['statu'] = 0;
                            $rarr['url'] = url('plat/platlist');
                            $rarr['message'] = 'success';
                        } else {
                            $rarr['statu'] = 9001;
                            $rarr['message'] = 'success';
                        }
                    } else {
                        $rarr['statu'] = 9002;
                        $rarr['message'] = $RouUser->getError();
                    }
                } else {
                    $rarr['statu'] = 9003;
                    $rarr['message'] = '此账户已存在，请更改后再试';
                }
            } else {
                $rarr['statu'] = 9004;
                $rarr['message'] = '此平台名称已存在，请更改后再试';
            }

            return json($rarr);
        }else{
            return $this->fetch();
        }
    }

    //添加平台名称   微信记录
    private function addplat($param){
        $model = new RougeSystemWx();
        $data['uniacid'] = $param['uniacid'];
        $data['create_time'] = time();
        $model->allowField(true)->save($data);
    }

    //修改
    public function platedit(Request $request){
        $model = new RougeSystem();
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
                $rarr['url'] = url('plat/platlist');
            }else{
                $rarr['statu'] = 9001 ;
                $rarr['message'] = 'success' ;
            }
            return json($rarr);
        }else{
//            $seuser = Session::get();
            $param = $request->param();
            $sql['a.id']=$param['id'];
            $info =$model->alias('a')->join('wn_rouge_login_user b', 'a.uniacid=b.uniacid', 'left')->
            field('a.id,a.title,a.create_time,a.statu,b.username,b.password')->find();
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
        if ($request->isAjax()) {
            $RougeSystem = new RougeSystem();
            $RougeSystemWx = new RougeSystemWx();
            $RougeLoginUser = new RougeLoginUser();
            $param = $request->post();
            $sql['uniacid'] = $param['uniacid'];
            $res = $RougeSystem->where($sql)->delete();
            $RougeSystemWx->where($sql)->delete();
            $RougeLoginUser->where($sql)->delete();
            if ($res) {
                $rarr['statu'] = 0;
                $rarr['message'] = 'success';
            } else {
                $rarr['statu'] = 9001;
                $rarr['message'] = 'success';
            }
            return json($rarr);
        }
    }
}
