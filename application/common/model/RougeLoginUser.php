<?php
/**
 * Created by PhpStorm.
 * User: 悟能科技
 * Date: 2018/11/2
 * Time: 10:24
 */

namespace app\common\model;


use think\Model;
use think\Session;

class RougeLoginUser extends Model
{

    protected $insert = ['create_time'];

    protected $update = ['update_time'];

    protected function setCreateTimeAttr()
    {
        return time();
    }

    protected function setUpdateTimeAttr()
    {
        return time();
    }

    protected function setPasswordAttr($value)
    {
        return MD5($value);
    }
}