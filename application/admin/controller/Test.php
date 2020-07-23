<?php
/**
 * Created by PhpStorm.
 * User: 悟能科技
 * Date: 2018/11/3
 * Time: 15:50
 */

namespace app\admin\controller;


use app\common\controller\QrcodeMake;
use app\common\model\RougeAchi;
use app\common\model\RougePaylog;
use app\common\model\RougeSystem;
use app\common\model\RougeUser;
use app\common\model\RougeUserGameLog;
use app\common\model\RougeUserPrize;
use think\Session;

class Test extends Base
{

    public function get(){
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
//        var_dump($RougePaylog->getLastSql());
        $info['pay_price'] = $RougePaylog->where($sql)->sum('price');
        return json($info);



        //卡券列表
        $cardslist = $CardGeneral->alias('a')
//                    ->join('wn_card_consume b', 'a.card_id=b.card_id', 'left')
//                    ->join('wn_card_user_get c', 'a.card_id=c.card_id', 'left')
            ->where($map)
            ->order('a.create_time desc')
            ->field('a.id,a.mid,a.card_type,a.card_id,a.create_time,a.title,a.stocks,a.getsume as get_sume,a.consume as con_sume,a.least_cost,a.reduce_cost')
            ->limit(($param['pageNumber'] - 1) * $param['pageSize'], $param['pageSize'])
            ->select();
//                return json($cardslist);
        $list = [];
        foreach ($cardslist as $k => $v) {
            $maplist['card_id'] = $v['card_id'];
            $list[$k] = $v;
            $list[$k]['sub_title'] = $v['card_type'] == 'CASH' ? '满' . $v['least_cost'] . '减' . $v['reduce_cost'] . '代金券' : $v['title'];;
        }
        //统计
        $re_param["total"] = $CardGeneral->alias('a')->where($map)->count();
        //获取的记录
        $re_param["rows"] = $list;
        return json($re_param);

//        $res = url('index/blog/read','id=5&name=thinkphp');
//        var_dump($res);
//        $QrCode = new QrcodeMake();
//        $RougeUser = new RougeUser();
//        $user = $RougeUser->where('id',1)->find();
//        $qr = $QrCode->getcodeurl($user['id'],$user['distr_url']);
//        var_dump($qr);

//        $seuser = Session::get();
//        $model = new RougeUser();
//        $map['a.uniacid'] = $seuser['uniacid'];
//        $map['a.level'] = 1;
////        $map['b.top_openid'] = ['neq',''];
//        $list = $model->alias('a')
////            ->join('wn_rouge_user b', 'a.openid=b.top_openid', 'left')count(b.id) as ren_num,
//            ->join('wn_rouge_achi c', 'a.openid=c.top_openid', 'left')
//            ->field('a.id,a.openid,a.top_openid,a.nickname,a.header_url,a.phone,a.create_time,sum(c.get_price) as achi_price')->where($map)->select();
////        $list = $model->getLastSql();
//        return json($list);
    }

    public function send(){
//        $data['uniacid'] = 1;
//        $data['openid'] = 'oJ1uY1O4OTqbbjQpoSIaXFC268h4';
//        $data['orderid'] = '2018110615050995087';
//
//        $SendMsg = new Sendmsg();
//        $res = $SendMsg->sendtplmsg($data);
//        var_dump($res);
        $model = new RougeAchi();
        $map['a.uniacid'] = 1;
        $list = $model
            ->alias('a')
            ->join('wn_rouge_user b', 'a.openid=b.openid', 'left')
            ->join('wn_rouge_user c', 'a.top_openid=c.openid', 'left')
            ->field('a.id,a.orderid,a.openid,a.top_openid,a.create_time,a.re_ratio,a.price,a.get_price,b.nickname,c.nickname as top_nickname')
            ->where($map)->order('a.id desc')->limit(10,10)->select();
        var_dump($model->getLastSql());
    }

    public function test(){
        $param['game_id'] = 42;
        $param['uniacid'] = 1;
        $RougeUserPrize = new RougeUserPrize();
        $RougeSystem = new RougeSystem();
        $RougeUserGameLog = new RougeUserGameLog();
        $goods_id = $RougeUserGameLog->where(['id'=>$param['game_id'],'uniacid'=>$param['uniacid']])->value('goods_id');
        $chance = $RougeSystem->where(['uniacid'=>$param['uniacid']])->value('chance');      //中奖概率
        $yxnum = $RougeUserGameLog->where(['goods_id'=>$goods_id,'uniacid'=>$param['uniacid']])->count('id');   //当前游戏数量
        $zjnum = $RougeUserPrize->where(['uniacid'=>$param['uniacid']])->count('id');        //已中将数量
        var_dump($chance);
        var_dump($yxnum);
        var_dump($zjnum);
//        var_dump($RougeSystem->getLastSql());
////        var_dump($RougeUserGameLog->getLastSql());
////        var_dump($RougeUserPrize->getLastSql());
    }

}