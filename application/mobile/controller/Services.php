<?php
/**
 * Created by PhpStorm.
 * User: 悟能科技
 * Date: 2018/11/2
 * Time: 14:47
 */

namespace app\mobile\controller;


use think\Controller;

class Services extends Base
{
    public function index(){
        $this->init();
        return $this->fetch();
    }
    
   public function index2(){
        $this->init();
        return $this->fetch();
    }

}