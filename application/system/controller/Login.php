<?php
namespace app\system\controller;

use app\common\model\RougeLoginUser;
use app\common\model\RougeUserConsume;
use think\Controller;
use think\Request;
use think\response\Json;
use think\Session;
use think\Validate;

class Login extends Controller
{
    public function index(Request $request){

        if ($request->isAjax()){
            $model = new RougeLoginUser();
            $data = $request->post();
            $validate = new Validate([
                'username|用户名' => 'require',
                'password|密码' => 'require',
//                'code|验证码' => 'require|captcha'
            ]);
            if (!$validate->check($data)) {
                $rarr['code'] = 9002 ;
                $rarr['message'] = $validate->getError();
            }else {
                $res = $model->where('username', $data['username'])->find();
                if (md5($data['password']) == $res['password']) {
                    Session::set('userid', $res['id']);
                    Session::set('level', 'leader');
                    Session::set('username', $res['username']);
                    $rarr['code'] = 0 ;
                    $rarr['url'] = url('index/index');
                    $rarr['message'] = 'success' ;
                } else {
                    $rarr['code'] = 9001 ;
                    $rarr['message'] = '登陆失败，用户名或密码错误！' ;
                }
            }
            return json($rarr);
        }else{

            return $this->fetch();

        }

    }
}
