<?php
/**
 * Created by PhpStorm.
 * User: 悟能科技
 * Date: 2018/11/3
 * Time: 9:20
 */

namespace app\mobile\controller;


use app\common\controller\QrcodeMake;
use app\common\model\RougeAchi;
use app\common\model\RougeGetmoney;
use app\common\model\RougeUser;
use think\Request;

class Marke extends Base
{
    //分享
    public function index(Request $request){
        $url = $request->url(true);
        $this->init($url);
        $userinfo = $this->getuserinfo(); 
        $this->assign('user',$userinfo);
        return $this->fetch();
    }

    //二维码
    public function share(Request $request){
        $url = $request->url(true);
        $this->init($url);
        $userinfo = $this->getuserinfo();
        //此处缺少是否为推广员判断
        if ($userinfo['level'] == 1) {
            $qrcode=new QrcodeMake();
//            var_dump($userinfo['id']);
//            var_dump($userinfo['distr_url']);exit;
            $qr=$qrcode->getcode($userinfo['id'],$userinfo['distr_url']);  //distr_url 这个是什么意思 header_url
            $this->assign('qrcode',$qr);
            $this->assign('user',$userinfo);
            return $this->fetch();
        }else{
            $this->error('您不是推广员,无法访问该页面');
        }
    }

    //单独二维码
    public function shareqrcode(Request $request){
        $url = $request->url(true);
        $this->init($url);
        $userinfo = $this->getuserinfo();
        if ($userinfo['level'] == 1) {
//        此处缺少是否为推广员判断
            $QrCode = new QrcodeMake();
            $qr = $QrCode->getcodeurl($userinfo['id'], $userinfo['distr_url']);
            //先注释掉
//            file_put_contents('/www/wwwroot/h5yiszhcom/share.txt', '2:' . json_encode($qr) . PHP_EOL, FILE_APPEND);
            
             $this->assign('qrcode',$qr);
            $this->assign('user',$userinfo);
            return $this->fetch();
        }else{ 

//            $qr = '';
       $this->error('您不是推广员,无法访问该页面');
        }

//        $qr = '';
       
    }

    //提现
    public function getmoney(Request $request){
        if ($request->isAjax()){
            $param = $request->post();
            $model = new RougeGetmoney();
            $RougeUser = new RougeUser();
            $userinfo = $this->getuserinfo();
            if ($userinfo['bonus'] >= 1 && $userinfo['bonus'] >= $param['price'] && $param['price'] >= 1) {
                $data['uniacid'] = $userinfo['uniacid'];
                $data['openid'] = $param['openid'];
                $data['user_id'] = $param['user_id'];
                $data['price'] = $param['price'];
                $data['create_time'] = time();
                $data['orderid']="H888".date('YmdHis').rand(1000,9999);
                $res = $model->allowField(true)->save($data);
                if ($res) {
                    $map['openid'] = $param['openid'];
                    $map['id'] = $param['user_id'];
                    $RougeUser->where($map)->setDec('bonus', $param['price']);
                    $rarr['code'] = 0;
                    $rarr['message'] = '申请成功';
                } else {
                    $rarr['code'] = 9001;
                    $rarr['message'] = '申请失败';
                }
            }else{
                $rarr['code'] = 9002;
                $rarr['message'] = '请输入正确提现金额';
            }
            return json($rarr);
        }else {
            $url = $request->url(true);
            $this->init($url);
            $userinfo = $this->getuserinfo();
            $this->assign('user', $userinfo);
            return $this->fetch();
        }
    }

    //提现明细
    public function getmoneylist(Request $request)
    {
        $model = new RougeGetmoney();
        if ($request->isAjax()) {
            $param = $request->post();
            $init = $this->getuserinfo();
            $map['uniacid'] = $init['uniacid'];
            $map['openid'] = $init['openid'];;
            $count = $model->where($map)->count('id');
            $page_num = ceil($count / 5);
            $list2 = [];
            if($page_num > (int)$param['page'] || $page_num == (int)$param['page']) {
                $list = $model->where($map)->paginate(5);

                foreach ($list as $k=>$v) {
                    $list2[$k] = $v;
                    $list2[$k]['status'] = getmoneystatu($v['statu']);
                }
            }
            if ($list2){
                $rarr['code'] = 0 ;
                $rarr['data'] = $list2 ;
                $rarr['message'] = 'success' ;
                $rarr['count'] = $count;
                $rarr['page_num'] = $page_num;
                $rarr['page'] = $param['page'];
            }else{
                if ($param['page'] < 2) {
                    $rarr['code'] = 9001;
                    $rarr['data'] = $list2;
                    $rarr['message'] = 'error';
                    $rarr['count'] = $count;
                    $rarr['page_num'] = $page_num;
                    $rarr['page'] = $param['page'];
                }else{
                    $rarr['code'] = 0;
                    $rarr['data'] = $list2;
                    $rarr['message'] = 'success';
                    $rarr['count'] = $count;
                    $rarr['page_num'] = $page_num;
                    $rarr['page'] = $param['page'];
                }

            }
            return json($rarr);
        }else{
            $url = $request->url(true);
            $this->init($url);

            $userinfo = $this->getuserinfo();
            $map['openid'] = $userinfo['openid'];
            $list = $model->where($map)->select();
            $this->assign('list', $list);
            $this->assign('user', $userinfo);
            return $this->fetch();
        }
    }

    //推荐的人
    public function shareuserlist(Request $request){
        $model = new RougeUser();
        if ($request->isAjax()) {
            $param = $request->post();
            $init = $this->getuserinfo();
            $map['uniacid'] = $init['uniacid'];
            $map['top_openid'] = $init['openid'];
            $count = $model->where($map)->count('id');
            $page_num = ceil($count / 5);
            $list2 = [];
            if($page_num > (int)$param['page'] || $page_num == (int)$param['page']) {
                $list = $model->where($map)->field('header_url,nickname,create_time,balance')->paginate(5);
                
                foreach ($list as $k=>$v) {
                    $v['nickname']=  urldecode($v['nickname']) ; //urldecode一下
                    $list2[$k] = $v;
                }
            }
            if ($list2){
                $rarr['code'] = 0 ;
                $rarr['data'] = $list2 ;
                $rarr['message'] = 'success' ;
                $rarr['count'] = $count;
                $rarr['page_num'] = $page_num;
                $rarr['page'] = $param['page'];
            }else{
                if ($param['page'] < 2) {
                    $rarr['code'] = 9001;
                    $rarr['data'] = $list2;
                    $rarr['message'] = 'error';
                    $rarr['count'] = $count;
                    $rarr['page_num'] = $page_num;
                    $rarr['page'] = $param['page'];
                }else{
                    $rarr['code'] = 0;
                    $rarr['data'] = $list2;
                    $rarr['message'] = 'success';
                    $rarr['count'] = $count;
                    $rarr['page_num'] = $page_num;
                    $rarr['page'] = $param['page'];
                }
            }
            return json($rarr);
        }else {
            $url = $request->url(true);
            $this->init($url);
            $userinfo = $this->getuserinfo();
            $map['top_openid'] = $userinfo['openid'];
            $list = $model->where($map)->select();
            $this->assign('list', $list);
            $this->assign('user', $userinfo);
            return $this->fetch();
        }
    }

    //进入
    public function enteruser(Request $request){
        $url = $request->url(true);
        $this->init($url);
        $userinfo = $this->getuserinfo();
        $this->assign('user',$userinfo);
        return $this->fetch();
    }


}