<?php
/**
 * Created by PhpStorm.
 * User: 悟能科技
 * Date: 2018/11/2
 * Time: 8:51
 */

namespace app\mobile\controller;


use think\Request;

class Notime extends Base
{
    public function index(Request $request)
    {
        $url = $request->url(true);
        $this->init($url);
        $userinfo = $this->getuserinfo();
        $this->assign('user',$userinfo);
        return $this->fetch();
    }
}