<?php
namespace app\mobile\controller;

use app\common\model\RougeSystem;
use app\common\model\RougePv;
use think\Request;
use think\Session;

class Login extends Base
{
    public function index(Request $request)
    {
        if ($request->isAjax()){
            $model = new RougeSystem();
            $param = $request->post();
//            $init = Session::get();
            $map['uniacid'] = $param['uniacid'];
            $list = $model->where($map)->field('loading_url')->find();
            if ($list){
                $rarr['code'] = 0;
                $rarr['message'] = 'success';
                $rarr['data'] = $list;
            }else{
                $rarr['code'] = 9001;
                $rarr['message'] = 'error';
                $rarr['data'] = $list;
            }
            return json($rarr);
        }else {
            $url = $request->url(true);
            $this->init($url);
             
            $user['openid'] = '';
            $user = $this->getuserinfo();
            if (!$user){
                $user['platid'] = $request->param('platid');
                $user['uniacid'] = $user['platid'] ;
            }
            $this->assign('user',$user);
            //END
            $param = $request->param();
            if (isset($param['outer'])){
                $info['outer'] = $param['outer'];
            }else{
                $info['outer'] = '';
            }
            $this->assign('info',$info);
            
            //记录pv,uv
            if ($user){ 
                $rouge_pv_model=new RougePv();
                $data['uniacid']=$request->param('platid');
                $data['user_id']=$user['id'];
                $data['ip']=  get_client_ip(); 
                $data['openid']=$user['openid'];
                $data['create_time']=time();
                $data['create_date']=  date('Y-m-d'); 
                $rouge_pv_model->allowField(true)->save($data);
            }
            return $this->fetch();
        }
    }

    //获取code
    public function getuserauth(Request $request){

    }

    public function loguot(){
        Session::clear();
    }
}
