<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
function getorderstatu($type){

  switch ($type){
      case "1":
          $re_data="未发货";
          break;
      case "2":
          $re_data="已发货";
          break;
      case "3":
          $re_data="已发货";
          break;
      default :
          $re_data="未知";
          break;
  }
  return $re_data;

}

function gameres($id){
    $res = $id == 1?'失败':'成功';
    return $res;
}

function getuserlevel($type){
    switch ($type){
        case "0":
            $re_data="普通用户";
            break;
        case "1":
            $re_data="推广用户";
            break;
        default :
            $re_data="未知";
            break;
    }
    return $re_data;

}


//支付   0 / 1
function zerostatu($type){
    switch ($type){
        case "0":
            $re_data="未成功";
            break;
        case "1":
            $re_data="成功";
            break;
        default:
            $re_data="未知";
            break;
    }
    return $re_data;
}

//提现状态
function getmoneystatu($type){
    switch ($type){
        case "1":
            $re_data="未处理";
            break;
        case "2":
            $re_data="已通过";
            break;
        case "3":
            $re_data="已拒绝";
            break;
        default:
            $re_data="未知";
            break;
    }
    return $re_data;
}

//分销用户统计
function getdistr($openid,$type){
    switch ($type){
        case 'ren_num':        //推广人数
            $RougeUser = new \app\common\model\RougeUser();
            $map['top_openid'] = $openid;
            $res = $RougeUser->where($map)->count('id');
            break;
        case 'achi_num':        //推广绩效
            $RougeAchi = new \app\common\model\RougeAchi();
            $map['top_openid'] = $openid;
            $res = $RougeAchi->where($map)->sum('get_price');
            break;
        default :
            $res = 0;
            break;
    }
    return $res;
}