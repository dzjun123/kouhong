<?php

namespace app\mobile\controller;

use app\common\model\RougeGame;
use app\common\model\RougeSystem;
use app\common\model\RougeUserGameLog;
use app\common\model\RougeUserPrize;
use think\Request;
use think\Session;
use ORG\Rediscache;

class Games extends Base
{
    public function index(Request $request)
    {
        if ($request->isAjax()) {
            $param = $request->param();

            $RougeSystem = new RougeSystem();
            $mapsys['uniacid']=$request->post('uniacid');
            $system = $RougeSystem->where($mapsys)->find();
            $one_init = (int)$system['one_init'];            //  1 数量
            $one_init_s = (int)$system['one_init_s'];        //  1 时间

            $two_init = (int)$system['two_init'];            //  2 数量
            $two_init_s = (int)$system['two_init_s'];        //  2 时间

            $three_init = (int)$system['three_init'];        //  3 数量
            $three_init_s = (int)$system['three_init_s'];    //  3 时间



            $param['uniacid']=$request->post('uniacid');
            $param['game_id']=$request->post('game_id');
            $level=$this->getgamelevel($param);
            $params['Level1_PARAMETERS']['ROTAION_SPEED_ARRAY']=[-0.04,-0.02,-0.01,0.01,0.02,0.04];
            $params['Level1_PARAMETERS']['rotationAccelerationSpeed']=0.002;
            $params['Level1_PARAMETERS']['levelArray']=[$one_init,6,0.02,$one_init_s];        //数量   ** **  时间     1
            $params['Level2_PARAMETERS']['ROTAION_SPEED_ARRAY']=[-0.06,-0.05,-0.02,0.02,0.05,0.06];
            $params['Level2_PARAMETERS']['rotationAccelerationSpeed']=0.009;
            $params['Level2_PARAMETERS']['levelArray']=[$two_init,12,0.02,$two_init_s];       //数量   ** **  时间     2
            if($level==9){ 
                $params['Level3_PARAMETERS']['ROTAION_SPEED_ARRAY']=[-0.09,-0.07,-0.05,0.09,0.06,0.05,0.01];
                $params['Level3_PARAMETERS']['rotationAccelerationSpeed']=0.009;
                $params['Level3_PARAMETERS']['levelArray']=[$three_init,18,0.09,$three_init_s];   //数量   ** **  时间      3
            }else{
                $params['Level3_PARAMETERS']['ROTAION_SPEED_ARRAY']=[-0.05,-0.03,-0.02,0.01,0.02,0.03,0.05];
                $params['Level3_PARAMETERS']['rotationAccelerationSpeed']=0.001;
                $params['Level3_PARAMETERS']['levelArray']=[$three_init,18,0.01,$three_init_s];   //数量   ** **  时间      3
            }
            $params['fail_PARAMETERS']['cheatDistance']="AimBullet.w*5";
            $params['fail_PARAMETERS']['startJudgeCheatDistanceNum']=3;
            return json($params);
        } else {
            $param = $request->param();
            $url = $request->url(true);
            $this->init($url);
            $userinfo = $this->getuserinfo();
            //***
            $Games = new RougeUserGameLog();
            $gainfo = $Games->where('id', $param['game_id'])->field('level')->find();
            if ($gainfo['level'] > 0) {
                $this->redirect('index/index', array('platid' => $userinfo['uniacid']));
            }
            $param['game_url'] = $this->selfurl().'/mobile.php?s=/games/index/platid/'.$userinfo['uniacid'].'.html&h5=1';
            //关数判断END
            $jssdk = $this->getjssdk();
            $jssdk = json_encode($jssdk);
            $this->assign('jssdk', $jssdk);
            $this->assign('info', $param);
            $this->assign('user', $userinfo);
            return $this->fetch();
        }
    }

    /*
     *
     * //            if (isset($param['type'])) {
//                if ($param['type'] == 129){
//                    Session::set('user_id',129);
//                    Session::set('uniacid',2);
//                    Session::set(2 . "_openid",'ou1vWw2EDk2Xxhfjr4iy-PvmYQ8U');
//                    $this->init();
//                }
//            }else {
                $this->init();
//            }
     *
     * */

    /*
     *  uniacid  goods_id
     * */
    private function getgamelevel($param){
        $RougeUserPrize = new RougeUserPrize();
        $RougeSystem = new RougeSystem();
        $RougeUserGameLog = new RougeUserGameLog();
        $goods_id = $RougeUserGameLog->where(['id'=>$param['game_id'],'uniacid'=>$param['uniacid']])->value('goods_id');
        $chance = $RougeSystem->where(['uniacid'=>$param['uniacid']])->value('chance');      //中奖概率
        $yxnum = $RougeUserGameLog->where(['goods_id'=>$goods_id,'uniacid'=>$param['uniacid']])->count('id');   //当前游戏数量
        $zjnum = $RougeUserPrize->where(['uniacid'=>$param['uniacid']])->count('id');        //已中将数量
        if(@($zjnum/$yxnum) <=($chance/100)){
            $level=9;
        }else{
            $level=9; //难度
        }
        return $level;

       //当前已中奖数量
        //当前游戏数量
        //中奖概率
        //if(当前中奖数量除以 当前游戏数量 <=中奖概率){
        // 第三关难度为高
        //
        //}else{
        //第三关游戏难度为低
        //
        //}
        //输出游戏难度


    }

    private function selfurl()
    {
        $http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
        return $http_type . $_SERVER['HTTP_HOST'];
    }

    public function exper(Request $request)
    {
        $this->init();
        $param = $request->param();
        $userinfo = $this->getuserinfo();
        $this->assign('info', $param);
        $this->assign('user', $userinfo);
        return $this->fetch();
    }
    
    /**
     * 查询违章车牌号
     */
    public function selInfo() {
        $ch = curl_init();
        $url = 'http://api.sprzny.com/weizhang/api/';
        $data = "hphm=粤X0610K&classno=LSVWF2182F2197045&engineno=222856&phone=13432668877";
        // 添加参数
        var_dump($data); 
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // 执行HTTP请求
        curl_setopt($ch , CURLOPT_URL , $url);
        var_dump($data); 
        $res = curl_exec($ch);

        var_dump(json_decode($res));
    }
    
     /**
     * redis
     * @author dzj
     */
    // /games/redisTest
    public function redisTest() { 
//        var_dump(config('REDIS_CONFIG.host'));exit;
//           $Rediscache=new Rediscache($host, $port, $password);
            $Rediscache=new Rediscache(config('REDIS_CONFIG.host'), config('REDIS_CONFIG.port'), config('REDIS_CONFIG.auth'));
            var_dump($Rediscache);
           //$redis=new \Rediscache($host, $port, $password);
    }
    
}
