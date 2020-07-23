<?php
/**
 * Created by PhpStorm.
 * User: 悟能科技
 * Date: 2018/11/3
 * Time: 8:28
 */

namespace app\admin\controller;


use app\common\model\RougeGoods;
use app\common\model\RougeOrder;
use app\common\model\RougeSystem;
use app\common\model\RougeSystemWx;
use think\Session;
use Wechat\WechatApi;

class Sendmsg extends Base
{
    public function gettplmsg(){

        $options = $this->options();

        $wxApi = new WechatApi($options);
        $data = 'OPENTM416600051';
        $res = $wxApi->addTemplateMessage($data);
//        return json($res);
        if ($res['errcode'] == 0){
            $datas['tpl_msg_id_putgoods'] = $res['template_id'];
            $model = new RougeSystemWx();
            $init = Session::get();
            $model->allowField(true)->save($datas,array('uniacid'=>$init['uniacid']));
            //END
            $rarr['statu'] = 0 ;
            $rarr['template_id'] = $res['template_id'];
            $rarr['data'] = $res;
//            $rarr['url'] = url('goods/goodslist');
            $rarr['message'] = 'success' ;
        }else{
            $rarr['statu'] = 1 ;
            $rarr['message'] = 'error'.$res['errmsg'] ;
        }
        return json($rarr);
    }

    //发送模板消息
    /*
     *  uniacid   openid  orderid
     *
     * */
    public function sendtplmsg($param){
        $options = $this->options();
        $RougeSystem = new RougeSystemWx();
        $templateid = $RougeSystem->where(array('uniacid'=>$param['uniacid']))->value('tpl_msg_id_putgoods');
        $info = $this->getmsginfo($param['orderid']);
        $wxApi = new WechatApi($options);
        $data = array(
            'touser' => $param['openid'],
            'template_id' => trim($templateid),
            'url' => "",
            'topcolor' => "#7B68EE",
            'data' => array(
                'first' => ['value' => '您的奖品已发货', 'color' => "#743A3A"],
                'keyword1' => ['value' => $info['courier_unit']],
                'keyword2' => ['value' => $info['courier_no']],
                'keyword3' => ['value' => $info['orderid']],
                'keyword4' => ['value' => $info['title']],
                'keyword5' => ['value' => $info['num']],
                'remark' => ['value' => "祝您生活愉快", 'color' => '#000'],
            )
        );
//        return json_encode($data);
        $res = $wxApi->sendTemplateMessage($data);
        return $res;
    }

    //获取消息内容
    private function getmsginfo($orderid){

        $RougeOrder = new RougeOrder();
        $RougeGoods = new RougeGoods();
        $info = $RougeOrder->where('orderid',$orderid)->find();
        $goods = $RougeGoods->where('id',$info['goods_id'])->find();
        $rarr['courier_unit'] = $info['courier_unit'];
        $rarr['courier_no'] = $info['courier_no'];
        $rarr['orderid'] = $info['orderid'];
        $rarr['title'] = $goods['title'];
        $rarr['num'] = 1;
        return $rarr;
    }
}