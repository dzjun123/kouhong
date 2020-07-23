<?php

namespace app\admin\controller;

use app\common\model\RougeAchi;
use app\common\model\RougeAdvert;
use app\common\model\RougeDistr;
use app\common\model\RougeGame;
use app\common\model\RougeGetmoney;
use app\common\model\RougeGoods;
use app\common\model\RougeRule;
use app\common\model\RougeSystemWx;
use app\common\model\RougeUser;
use app\common\utils\DataTablesUtil;
use think\Request;
use think\Session;
use Weixinpay\Company;
use Weixinpay\Wxtk;

class Distr extends Base
{
    //分销设置
    public function setdistr(Request $request)
    {
        $model = new RougeDistr();
        $init = Session::get();
        if ($request->isAjax()) {
            $param = $request->post();
            $data = $param;
            $map['uniacid'] = $init['uniacid'];
            $isone = $model->where($map)->find();
            if ($isone) {
                $res = $model->allowField(true)->save($data, $map);
            } else {
                $data['uniacid'] = $init['uniacid'];
                $res = $model->allowField(true)->save($data);
            }
            if ($res) {
                $rarr['statu'] = 0;
                $rarr['message'] = 'success';
            } else {
                $rarr['statu'] = 9001;
                $rarr['message'] = 'success';
            }
            return json($rarr);
        } else {
            $sql['uniacid'] = $init['uniacid'];
            $info = $model->where($sql)->find();

            $this->assign('info', $info);
            return $this->fetch();
        }
    }

    //分销用户
    public function distruserlist(Request $request)
    {
        if ($request->isAjax()) {
            $param = $request->post();
            $aoData = $request->param('aoData');
            $queryArr = DataTablesUtil::getQueryPageProperty($aoData);
            $criteria = $queryArr[DataTablesUtil::CRITERIA];
            if ($criteria) {

                if (!empty($criteria['nickname'])){
                    $map['nickname|id'] = $criteria['nickname'];
                }

                if (!empty($criteria['starttime'])) {
                    $start_time = strtotime($criteria['starttime']);
                } else {
                    $start_time = 0;
                }
                if (!empty($criteria['endtime'])) {
                    $end_time = strtotime($criteria['endtime'])+86399;
                } else {
                    $end_time = time();
                }
            //    $map['a.create_time'] = array(array('egt', $start_time), array('elt', $end_time), "and");
            }
            $model = new RougeUser();
            $seuser = Session::get();

            $map['uniacid'] = $seuser['uniacid'];
            $map['level'] = 1;
            $list = $model
//
//            ->alias('a')
//            ->join('wn_rouge_user b', 'a.openid=b.top_openid', 'left')
//            ->join('wn_rouge_achi c', 'a.openid=c.top_openid', 'left')
//            ->field('a.id,a.openid,a.top_openid,a.nickname,a.header_url,a.phone,a.create_time,count(b.id) as ren_num,sum(c.get_price) as achi_price')
                ->where($map)->order('id desc')->limit($queryArr[DataTablesUtil::LIMIT])->select();
            $lists = [];
            foreach ($list as $k=>$v){
                $lists[$k] = $v;
                $lists[$k]['ren_num']=getdistr($v['openid'],'ren_num').'人';
                $lists[$k]['achi_num']='￥'.getdistr($v['openid'],'achi_num');
                $lists[$k]['distr_url'] = url('member/memberlist',array('top_openid'=>$v['openid']));        //推广记录
                $lists[$k]['achi_url'] = url('distr/distrachilist',array('top_openid'=>$v['openid']));       //推广绩效
                $lists[$k]['nickname'] =  urldecode($v['nickname'] )  ;
            }
            $count = $model->where($map)->alias('a')->count('id');
            $json = DataTablesUtil::getJsonPage($queryArr[DataTablesUtil::S_ECHO], $count, $lists);
            return json_decode($json);

        }else{
            $param = $request->param();
            if (!isset($param['openid'])){$param['openid']='';}
            if (!isset($param['top_openid'])){$param['top_openid']='';}
            $this->assign('param',$param);
            return $this->fetch();
        }



    }

    //分销绩效
    public function distrachilist(Request $request)
    {
        if ($request->isAjax()) {
            $param = $request->post();
            $aoData = $request->param('aoData');
            $queryArr = DataTablesUtil::getQueryPageProperty($aoData);
            $criteria = $queryArr[DataTablesUtil::CRITERIA];
            if ($criteria) {
                $RougeUser = new RougeUser();
                if (!empty($criteria['nickname'])) {
                    $maps['id|nickname|phone'] = $criteria['nickname'];
                    $Uid = $RougeUser->where($maps)->value('openid');
                    $map['a.top_openid'] = $Uid;
                }elseif (!empty($criteria['top_openid'])){
                    $map['a.top_openid'] = $criteria['top_openid'];
                }

                if (!empty($criteria['starttime'])) {
                    $start_time = strtotime($criteria['starttime']);
                } else {
                    $start_time = 0;
                }
                if (!empty($criteria['endtime'])) {
                    $end_time = strtotime($criteria['endtime'])+86399;
                } else {
                    $end_time = time();
                }
                $map['a.create_time'] = array(array('egt', $start_time), array('elt', $end_time), "and");
            }
            $model = new RougeAchi();
            $seuser = Session::get();

            $map['a.uniacid'] = $seuser['uniacid'];
            $list = $model
                ->alias('a')
                ->join('wn_rouge_user b', 'a.openid=b.openid', 'left')
                ->join('wn_rouge_user c', 'a.top_openid=c.openid', 'left')
                ->field('a.id,a.orderid,a.openid,a.top_openid,a.create_time,a.re_ratio,a.price,a.get_price,b.nickname,c.nickname as top_nickname')
                ->where($map)->order('a.id desc')->limit($queryArr[DataTablesUtil::LIMIT])->select();
            $lists = [];
            foreach ($list as $k=>$v){
                $lists[$k] = $v;
                $lists[$k]['nickname'] =  urldecode($v['nickname'] )  ;
                 $lists[$k]['top_nickname'] =  urldecode($v['top_nickname'] )  ;
                
            }
            $count = $model->alias('a')->where($map)->count('id');
            $json = DataTablesUtil::getJsonPage($queryArr[DataTablesUtil::S_ECHO], $count, $lists);
            return json_decode($json);

        }else{
            $param = $request->param();
            if (!isset($param['openid'])){$param['openid']='';}
            if (!isset($param['top_openid'])){$param['top_openid']='';}
            $this->assign('param',$param);
            return $this->fetch();
        }

    }

    //分销提现列表
    public function distrgetmoneylist(Request $request)
    {
        if ($request->isAjax()) {
            $param = $request->post();
            $aoData = $request->param('aoData');
            $queryArr = DataTablesUtil::getQueryPageProperty($aoData);
            $criteria = $queryArr[DataTablesUtil::CRITERIA];
            if ($criteria) {
                $RougeUser = new RougeUser();
                if (!empty($criteria['nickname'])) {
                    $maps['id|nickname|phone'] = $criteria['nickname'];
                    $Uid = $RougeUser->where($maps)->value('openid');
                    $map['a.openid'] = $Uid;
                }
                if (!empty($criteria['top_openid'])){
                    $map['a.openid'] = $criteria['top_openid'];
                }
                if (!empty($criteria['starttime'])) {
                    $start_time = strtotime($criteria['starttime']);
                } else {
                    $start_time = 0;
                }
                if (!empty($criteria['endtime'])) {
                    $end_time = strtotime($criteria['endtime'])+86399;
                } else {
                    $end_time = time();
                }
                $map['a.create_time'] = array(array('egt', $start_time), array('elt', $end_time), "and");
            }
            $model = new RougeGetmoney();
            $seuser = Session::get();
            $map['a.uniacid'] = $seuser['uniacid'];
            $list =  $model->alias('a')
                ->join('wn_rouge_user b', 'a.openid=b.openid', 'left')
                ->field('a.id,b.nickname,a.price,a.statu,a.create_time')
                ->where($map)->order('a.id desc')->limit($queryArr[DataTablesUtil::LIMIT])->select();
            $lists = [];
            foreach ($list as $k=>$v){
                $lists[$k] = $v;
                $lists[$k]['is_statu'] = $v['statu'] ;
                $lists[$k]['statu'] = getmoneystatu($v['statu']) ;
                $lists[$k]['nickname'] =  urldecode($v['nickname'] )  ;
            }
            $count = $model->alias('a')->where($map)->count('id');
            $json = DataTablesUtil::getJsonPage($queryArr[DataTablesUtil::S_ECHO], $count, $lists);
            return json_decode($json);

        }else{
            $param = $request->param();
            if (!isset($param['openid'])){$param['openid']='';}
            if (!isset($param['top_openid'])){$param['top_openid']='';}
            $this->assign('param',$param);
            return $this->fetch();
        }

    }

    //分销提现操作
    public function distrgetmoney(Request $request)
    {
        $model = new RougeGetmoney();
        if ($request->isAjax()) {
            $seuser = Session::get();
            $param = $request->post();
            $RougeUser = new RougeUser();
            $getuser = $model->where('id',$param['id'])->find();
            $map['openid'] = $getuser['openid'];
            if ($param['statu'] == 2) {
                $res = $this->is_hb_fk($getuser['uniacid'],$param['id']);
                if ($res['return_code'] == 'SUCCESS' && $res['result_code'] == 'SUCCESS') {
                    //通过后增加可提现金额
                    $RougeUser->where($map)->setInc('y_bonus', $getuser['price']);
                    $rarr['statu'] = 0;
                    $rarr['message'] = 'success';
                    $rarr['url'] = url('distr/distrgetmoneylist');
                } else {
                    $rarr['statu'] = 1;
                    $rarr['url'] = url('distr/distrgetmoneylist');
                    $rarr['message'] = $res['result_msg'];
                }
            }else{
                $model->save(['statu'=>3],['id'=>$param['id']]);
                //拒绝后增加可提现金额
                $RougeUser->where($map)->setInc('bonus', $getuser['price']);
                $rarr['statu'] = 0;
                $rarr['url'] = url('distr/distrgetmoneylist');
                $rarr['message'] = 'success';
            }
            return json($rarr);
        } else {
            $seuser = Session::get();
            $param = $request->param();
            $sql['uniacid'] = $seuser['uniacid'];
            $sql['id'] = $param['id'];
            $info = $model->where($sql)->find();
            $this->assign('info', $info);
            return $this->fetch();
        }
    }

    /**
     * 发放红包
     * @param type $id
     * @return string
     */
    private function sendhb($id=9)
    {
        $RougeGetmoney = new RougeGetmoney();
        $map['id'] = $id;
        $getinfo = $RougeGetmoney->where($map)->find();
        if ($getinfo['statu'] == 1) {
            $RougeSystemWx = new RougeSystemWx();
            $mab['uniacid'] = $getinfo['uniacid'];
            $wxinfo = $RougeSystemWx->where($mab)->find();
            $weObj = new Wxtk();
            $notice = $weObj->generateNonceStr();
            $weObj->setParameter('nonce_str', $notice);
            $weObj->setParameter('mch_billno', $getinfo['orderid']);
            $weObj->setParameter('mch_id', $wxinfo['mch_id']);
            $weObj->setParameter('wxappid', $wxinfo['appid']);
            $weObj->setParameter('send_name', $wxinfo['title']);
            $weObj->setParameter('re_openid',  $getinfo['openid']);
            $weObj->setParameter('total_amount', $getinfo['price']*100);
            $weObj->setParameter('total_num', 1);
            $weObj->setParameter('wishing', $wxinfo['wishing']);
            $weObj->setParameter('client_ip', $_SERVER['REMOTE_ADDR']);
            $weObj->setParameter('act_name', $wxinfo['title']);
            $weObj->setParameter('remark', $wxinfo['wishing']);
            $sss = $weObj->create_refund_xml($wxinfo['partnerkey']);
            $url = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/sendredpack';
            $responseXml = $weObj->curl_post_ssl($url, $sss, $wxinfo['apiclient_cert'], $wxinfo['apiclient_key']);
            $responseObj = simplexml_load_string($responseXml, 'SimpleXMLElement', LIBXML_NOCDATA);
            $responseArr = ( array )$responseObj;
            if ($responseArr) {
                if ($responseArr['return_code'] == 'SUCCESS' && $responseArr['result_code'] == "SUCCESS") {
                    if ($responseArr['return_msg'] == '发放成功') {
                        $datas['statu']=2;
                        $datas['wxinfo']=json_encode($responseArr,JSON_UNESCAPED_UNICODE);
                        $RougeGetmoney->save($datas,$map);
                        $rarr['return_code'] = "SUCCESS";
                        $rarr['result_code'] = "SUCCESS";
                        $rarr['return_msg'] = $responseArr['return_msg'];
                        $rarr['result_msg'] = $responseArr['return_msg'];
                    } else {
                        $rarr['return_code'] = "SUCCESS";
                        $rarr['result_code'] = "FAIL";
                        $rarr['return_msg'] = $responseArr['return_msg'];
                        $rarr['result_msg'] = $responseArr['return_msg'];
                    }
                } else {
                    if (isset($responseArr['err_code_des'])) {
                        $rarr['return_code'] = "SUCCESS";
                        $rarr['result_code'] = "FAIL";
                        $rarr['return_msg'] = $responseArr['err_code_des'];
                        $rarr['result_msg'] = $responseArr['err_code_des'];

                    } elseif (isset($responseArr['return_msg'])) {
                        $rarr['return_code'] = "SUCCESS";
                        $rarr['result_code'] = "FAIL";
                        $rarr['return_msg'] = $responseArr['return_msg'];
                        $rarr['result_msg'] = $responseArr['return_msg'];
                    } else {
                        $rarr['return_code'] = "SUCCESS";
                        $rarr['result_code'] = "FAIL";
                        $rarr['return_msg'] = "未知错误";
                        $rarr['result_msg'] = "未知错误";
                    }
                }
            } else {
                $rarr['return_code'] = "SUCCESS";
                $rarr['result_code'] = "FAIL";
                $rarr['return_msg'] = "证书参数有误";
                $rarr['result_msg'] = "证书参数有误";
            }
        }else{
            $rarr['return_code'] = "SUCCESS";
            $rarr['result_code'] = "FAIL";
            $rarr['return_msg'] = "订单状态异常";
            $rarr['result_msg'] = "订单状态异常";
        }
        return $rarr;
    }

    /**
     * 企业付款
     * @param type $id
     * @return string
     */
    private function sendfk($id = 9){
        $RougeGetmoney = new RougeGetmoney();
        $map['id'] = $id;
        $getinfo = $RougeGetmoney->where($map)->find();
        $RougeSystemWx = new RougeSystemWx();
        $Syswxinfo = $RougeSystemWx->where('uniacid',$id)->find();
        $mch_appid = $Syswxinfo['appid'];
        $mchid = $Syswxinfo['mch_id'];
        $partener_trade_no = 'H888'.date('YmdHis').rand(1000,9999);
        $openid = $getinfo['openid'];
        $desc = $Syswxinfo['wishing'];
        $partnerkey = $Syswxinfo['partnerkey'];
        $apiclient_cert = $Syswxinfo['apiclient_cert'];
        $apiclient_key = $Syswxinfo['apiclient_key'];
        if ($getinfo['statu'] == 1) {
//            $weObj = new Company();
            $weObj = new Wxtk();
            $notice = $weObj->generateNonceStr();
            $weObj->setParameter('mch_appid', $mch_appid);//商户appid
            $weObj->setParameter('mchid', $mchid);//商户号
            $weObj->setParameter('nonce_str', $notice);//随机字符串
            $weObj->setParameter('partner_trade_no',  $partener_trade_no);//订单号
            $weObj->setParameter('openid',$openid );//用户openID
            $weObj->setParameter('check_name', "NO_CHECK");//校验姓名（默认不校验）
            $weObj->setParameter('amount', $getinfo['price']*100);//付款金额
            $weObj->setParameter('desc',$desc);//付款备注
            $weObj->setParameter('spbill_create_ip', $_SERVER['REMOTE_ADDR']);//发起IP
            $sss = $weObj->create_refund_xml($partnerkey);//签名
            $url = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers';
            $responseXml = $weObj->curl_post_ssl($url, $sss, $apiclient_cert, $apiclient_key);
            $responseObj = simplexml_load_string($responseXml, 'SimpleXMLElement', LIBXML_NOCDATA);
            $responseArr = ( array )$responseObj;
//            var_dump($responseArr);
            error_log('e-'.$id.':'.time()."QYFK:".json_encode($responseArr));
//            file_put_contents('/www/wwwroot/h5yiszhcom/sendfk.txt', 'e-'.$id.':' . json_encode($responseArr) . PHP_EOL, FILE_APPEND);
            if ($responseArr) {
                if ($responseArr['return_code'] == 'SUCCESS' && $responseArr['result_code'] == "SUCCESS") {
                    if ($responseArr['return_msg'] == '发放成功') {
                        $datas['statu']=2;
                        $datas['wxinfo']=json_encode($responseArr,JSON_UNESCAPED_UNICODE);
                        $RougeGetmoney->save($datas,$map);
                        $rarr['return_code'] = "SUCCESS";
                        $rarr['result_code'] = "SUCCESS";
                        $rarr['return_msg'] = $responseArr['return_msg'];
                        $rarr['result_msg'] = $responseArr['return_msg'];
                    } else {
                        $rarr['return_code'] = "SUCCESS";
                        $rarr['result_code'] = "FAIL";
                        $rarr['return_msg'] = $responseArr['return_msg'];
                        $rarr['result_msg'] = $responseArr['return_msg'];
                    }
                } else {
                    if (isset($responseArr['err_code_des'])) {
                        $rarr['return_code'] = "SUCCESS";
                        $rarr['result_code'] = "FAIL";
                        $rarr['return_msg'] = $responseArr['err_code_des'];
                        $rarr['result_msg'] = $responseArr['err_code_des'];

                    } elseif (isset($responseArr['return_msg'])) {
                        $rarr['return_code'] = "SUCCESS";
                        $rarr['result_code'] = "FAIL";
                        $rarr['return_msg'] = $responseArr['return_msg'];
                        $rarr['result_msg'] = $responseArr['return_msg'];
                    } else {
                        $rarr['return_code'] = "SUCCESS";
                        $rarr['result_code'] = "FAIL";
                        $rarr['return_msg'] = "未知错误";
                        $rarr['result_msg'] = "未知错误";
                    }
                }
            } else {
                $rarr['return_code'] = "SUCCESS";
                $rarr['result_code'] = "FAIL";
                $rarr['return_msg'] = "证书参数有误";
                $rarr['result_msg'] = "证书参数有误";
            }
//            $rarr = $responseArr;
        }else{
            $rarr['return_code'] = "SUCCESS";
            $rarr['result_code'] = "FAIL";
            $rarr['return_msg'] = "订单状态异常";
            $rarr['result_msg'] = "订单状态异常";
        }
        error_log('e-'.$id.':'.time()."QYFK:".json_encode($rarr));
        //file_put_contents('/www/wwwroot/h5yiszhcom/sendfk-r.txt', 'e-'.$id.':' . json_encode($rarr) . PHP_EOL, FILE_APPEND);
        return $rarr;
    }


    private function is_hb_fk($uniacid,$id){
        $RougeSystemWx = new RougeSystemWx();
        $sysinfo = $RougeSystemWx->where('uniacid',$uniacid)->field('is_put_mode')->find();
        if ($id == 137) {
            $res = $this->sendfk($id);
        }else{
            if ($sysinfo['is_put_mode'] == 1) {
                $res = $this->sendfk($id);
            } else {
                $res = $this->sendhb($id);
            }
        }
        return $res;
    }
    
    
    /**
     * 企业付款到银行卡
     * @param type $id
     * @return string
     */
    private function sendyhk($id = 9){
         
        $RougeSystemWx = new RougeSystemWx();
        $Syswxinfo = $RougeSystemWx->where('uniacid',$id)->find();
        //$mch_appid = $Syswxinfo['appid'];
        $mchid = $Syswxinfo['mch_id'];
        $partener_trade_no = 'H888'.date('YmdHis').rand(1000,9999);
        $openid = 'oSvf_0lw9jIH4-C2W6YOQBiHoe7g'; //写死测试
        $desc = $Syswxinfo['wishing'];
        $partnerkey = $Syswxinfo['partnerkey'];
        $apiclient_cert = $Syswxinfo['apiclient_cert'];
        $apiclient_key = $Syswxinfo['apiclient_key'];
         
//            $weObj = new Company();
            $weObj = new Wxtk();
            $notice = $weObj->generateNonceStr();
            //$weObj->setParameter('mch_appid', $mch_appid);//商户appid
            $weObj->setParameter('mchid', $mchid);//商户号
            $weObj->setParameter('nonce_str', $notice);//随机字符串
            $weObj->setParameter('sign', $weObj->get_sign());//随机字符串
            $weObj->setParameter('partner_trade_no',  $partener_trade_no);//订单号
            $weObj->setParameter('openid',$openid );//用户openID
            $weObj->setParameter('check_name', "NO_CHECK");//校验姓名（默认不校验）
            $weObj->setParameter('amount', 10);//付款金额  //写死测试
            $weObj->setParameter('desc',$desc);//付款备注
            $weObj->setParameter('spbill_create_ip', $_SERVER['REMOTE_ADDR']);//发起IP
            $sss = $weObj->create_refund_xml($partnerkey);//签名
            $url = 'https://api.mch.weixin.qq.com/mmpaysptrans/pay_bank';
            $responseXml = $weObj->curl_post_ssl($url, $sss, $apiclient_cert, $apiclient_key);
            $responseObj = simplexml_load_string($responseXml, 'SimpleXMLElement', LIBXML_NOCDATA);
            $responseArr = ( array )$responseObj;
//            var_dump($responseArr);
            error_log('e-'.$id.':'.time()."QYFK:".json_encode($responseArr));
//            file_put_contents('/www/wwwroot/h5yiszhcom/sendfk.txt', 'e-'.$id.':' . json_encode($responseArr) . PHP_EOL, FILE_APPEND);
            if ($responseArr) {
                if ($responseArr['return_code'] == 'SUCCESS' && $responseArr['result_code'] == "SUCCESS") {
                    if ($responseArr['return_msg'] == '发放成功') {
                         
                        $rarr['return_code'] = "SUCCESS";
                        $rarr['result_code'] = "SUCCESS";
                        $rarr['return_msg'] = $responseArr['return_msg'];
                        $rarr['result_msg'] = $responseArr['return_msg'];
                    } else {
                        $rarr['return_code'] = "SUCCESS";
                        $rarr['result_code'] = "FAIL";
                        $rarr['return_msg'] = $responseArr['return_msg'];
                        $rarr['result_msg'] = $responseArr['return_msg'];
                    }
                } else {
                    if (isset($responseArr['err_code_des'])) {
                        $rarr['return_code'] = "SUCCESS";
                        $rarr['result_code'] = "FAIL";
                        $rarr['return_msg'] = $responseArr['err_code_des'];
                        $rarr['result_msg'] = $responseArr['err_code_des'];

                    } elseif (isset($responseArr['return_msg'])) {
                        $rarr['return_code'] = "SUCCESS";
                        $rarr['result_code'] = "FAIL";
                        $rarr['return_msg'] = $responseArr['return_msg'];
                        $rarr['result_msg'] = $responseArr['return_msg'];
                    } else {
                        $rarr['return_code'] = "SUCCESS";
                        $rarr['result_code'] = "FAIL";
                        $rarr['return_msg'] = "未知错误";
                        $rarr['result_msg'] = "未知错误";
                    }
                }
            } else {
                $rarr['return_code'] = "SUCCESS";
                $rarr['result_code'] = "FAIL";
                $rarr['return_msg'] = "证书参数有误";
                $rarr['result_msg'] = "证书参数有误";
            }
//            $rarr = $responseArr;
         
        error_log('e-'.$id.':'.time()."QYFK:".json_encode($rarr));
        //file_put_contents('/www/wwwroot/h5yiszhcom/sendfk-r.txt', 'e-'.$id.':' . json_encode($rarr) . PHP_EOL, FILE_APPEND);
        return $rarr;
    }
    
    
}
