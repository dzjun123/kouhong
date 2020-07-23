<?php
namespace app\admin\controller;

use app\common\model\RougeAchi;
use app\common\model\RougeGetmoney;
use app\common\model\RougeLoginUser;
use app\common\model\RougePaylog;
use app\common\model\RougeUser;
use app\common\model\RougeUserPrize;
use think\Controller;
use think\Request;
use think\Session;

class Index extends Base
{
    public function index(Request $request)
    {
        if ($request->isAjax()){
            $param = $request->post();
            if ($param){

                if (!empty($param['starttime'])){
                    $starttime = strtotime($param['starttime']);
                }else{
                    $starttime = strtotime(date("Y-m-d",time())." 0:0:0");
                }
                if (!empty($param['endtime'])){
                    $endtime = strtotime($param['endtime']) + 86399;
                }else{
                    $endtime = strtotime(date("Y-m-d",time())." 23:59:59");
                }
            }else{

                $starttime = strtotime(date("Y-m-d",time())." 0:0:0");
                $endtime = strtotime(date("Y-m-d",time())." 23:59:59");
            }

            $seuser = Session::get();
            $RougeUser = new RougeUser();
            $RougeUserPrize = new RougeUserPrize();
            $RougePaylog = new RougePaylog();
            $RougeGetmoney = new RougeGetmoney();
            $sql['create_time'] = array(array('egt', $starttime), array('elt', $endtime));
            $sql['uniacid'] = $seuser['uniacid'];
            $info['prize'] = $RougeUserPrize->where($sql)->count('id');         //总中奖数
            $info['user'] = $RougeUser->where($sql)->count('id');               //总用户数
            //用户统计END
            $sql['create_time'] = array(array('egt', $starttime), array('elt', $endtime));
            $sql['pay_statu'] = 1;
            $info['pay_num'] = $RougePaylog->where($sql)->count('id');          //总支付笔数
            $info['pay_price'] = $RougePaylog->where($sql)->sum('price');       //总支付金额
            //支付统计END
            $sql1['uniacid'] = $seuser['uniacid'];
            $sql1['level'] = 1;
            $info['ren_num'] = $RougeUser->where($sql1)->count('id');               //分销总人数
            //分销用户END
            $RougeAchi = new RougeAchi();
            $sql2['create_time'] = array(array('egt', $starttime), array('elt', $endtime));
            $sql2['uniacid'] = $seuser['uniacid'];
            $info['achi_price'] = $RougeAchi->where($sql2)->sum('get_price');    //总绩效

            $sql3['uniacid'] = $seuser['uniacid'];
            $sql3['create_time'] = array(array('egt', $starttime), array('elt', $endtime));
            $sql3['statu'] = 2;
            $info['y_get_achi_price'] = $RougeGetmoney->where($sql3)->sum('price');    //提现金额

            $info['get_achi_price'] =  $info['achi_price'] - $info['y_get_achi_price'];       //未提现金额
//        var_dump($RougeUser->getLastSql());

            //分销统计END
            if ($info){
                $data['statu'] = 0;
                $data['data'] = $info;
            }else{
                $data['statu'] = 1;
                $data['data'] = $info;
            }
            return json($data);

        }else {
            $seuser = Session::get();
            $RougeUser = new RougeUser();
            $RougeUserPrize = new RougeUserPrize();
            $RougePaylog = new RougePaylog();
            $sql['uniacid'] = $seuser['uniacid'];
            $info['prize'] = $RougeUserPrize->where($sql)->count('id');
            $info['user'] = $RougeUser->where($sql)->count('id');
            //END
            $sql['pay_statu'] = 1;
            $info['pay_num'] = $RougePaylog->where($sql)->count('id');
            $info['pay_price'] = $RougePaylog->where($sql)->sum('price');
            //END
            $RougeAchi = new RougeAchi();
            $sql1['uniacid'] = $seuser['uniacid'];
            $info['achi_price'] = $RougeAchi->where($sql1)->sum('get_price');    //总绩效
//        var_dump($RougeAchi->getLastSql());
            $sql1['level'] = 1;
            $info['ren_num'] = $RougeUser->where($sql1)->count('id');     //分销总人数
            $info['get_achi_price'] = $RougeUser->where($sql1)->sum('bonus');    //未提现金额
//        var_dump($RougeUser->getLastSql());
            $info['y_get_achi_price'] = $RougeUser->where($sql1)->sum('y_bonus');    //提现金额
            $this->assign('info', $info);
            return $this->fetch();
        }
    }

    //修改密码
    public function editinfo(Request $request){
        $RougeLogin = new RougeLoginUser();
        if ($request->isAjax()){
            $param = $request->post();
            $seuser = Session::get();
            $sql['uniacid'] = $seuser['uniacid'];
            $sql['id'] = $seuser['userid'];
            $user = $RougeLogin->where($sql)->find();
            if (MD5($param['old_password']) == $user['password']) {
                $data['password'] = $param['password'];
                $res = $RougeLogin->save($data,$sql);
                if ($res) {
                    $rarr['statu'] = 0;
                    $rarr['url'] = url('login/logout');;
                    $rarr['message'] = '修改成功';
                } else {
                    $rarr['statu'] = 9001;
                    $rarr['message'] = '修改失败';
                }
            }else{
                $rarr['statu'] = 9002;
                $rarr['message'] = '请输入正确的原密码';
            }
            return json($rarr);
        }else{

            return $this->fetch();
        }
    }
}
