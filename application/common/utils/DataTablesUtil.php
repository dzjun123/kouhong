<?php
/**
 * Created by PhpStorm.
 * User: mr.lee
 * Date: 2018/7/18
 * Time: 下午8:39
 */

namespace app\common\utils;


class DataTablesUtil
{
//datatable参数下标常量定义
    const ID_DISPLAY_START = 'iDisplayStart'; //开始行
    const ID_DISPLAY_LENGTH = 'iDisplayLength'; //每页显示行数
    const S_ECHO = 'sEcho'; //每页的唯一标识，必须在结果集中传回
    const ORDER_PREFIX = 'mDataProp_'; //列名前缀
    const ISORTCOL_0 = 'iSortCol_0'; //排列下标
    const SSORTDIR_0 = 'sSortDir_0'; //排列方式

    //自定义查询条件key
    const CRITERIA = 'criteria';//和页面中aoData中的查询条件key要一致

    //封装查询条件下标常量定义
    const ORDER = 'order';
    const LIMIT = 'limit';

    /**
     * 根据$aoData参数，封装查询条件（筛选_criteria、排序_order、分页_limit）
     * */
    public static function getQueryPageProperty($aoData){
        //json参数转化为数组
        $arr = json_decode($aoData,true);

        //将数据整理为key=>value的关联模式数组
        $dataArr = array();
        for ($i=0;$i<count($arr);$i++){
            $dataArr[$arr[$i]['name']] = $arr[$i]['value'];
        }

        //设置返回的数据
        $iSortCol_0 = $dataArr[DataTablesUtil::ISORTCOL_0];
        $sSortDir_0 = $dataArr[DataTablesUtil::SSORTDIR_0];
        $orderClumn = $dataArr[DataTablesUtil::ORDER_PREFIX.$iSortCol_0];

        $pageArr[DataTablesUtil::S_ECHO] = $dataArr[DataTablesUtil::S_ECHO];
        if(empty($orderClumn)){
            $pageArr[DataTablesUtil::ORDER] = '';
        }else{
            $pageArr[DataTablesUtil::ORDER] = $orderClumn.' '.$sSortDir_0.' ';
        }
        $pageArr[DataTablesUtil::LIMIT] = $dataArr[DataTablesUtil::ID_DISPLAY_START].','.$dataArr[DataTablesUtil::ID_DISPLAY_LENGTH];
        $pageArr[DataTablesUtil::CRITERIA] = $dataArr[DataTablesUtil::CRITERIA];

        return $pageArr;
    }

    /**
     * 返回dataTable所需要的json格式数据
     * @param $sEcho
     * @param $count
     * @param $data 查询结果集（数组）
     * */
    public static function getJsonPage($sEcho,$count,array $data){
        $json = '{"sEcho":'.$sEcho.',"iTotalRecords":'.$count
            .',"iTotalDisplayRecords":'.$count
            .',"aaData":'.json_encode($data).'}';
        return $json;
    }

}