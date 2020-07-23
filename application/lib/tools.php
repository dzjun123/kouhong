<?php
/*changelog
last modified date: 2017/06/23 7:22
2017/06/23 [数组函数] 添加函数isFieldOfMapSetHasSameElement|isArrayHasSameElement
2017/06/09 [PHP模版函数] isArrayMemberThenChecked() 如果是成员，则checked
2017/06/06 [数组函数] buildMap() 从关联数组集合中返回一组映射
2017/01/10 [表单函数] parseFormFieldsWithConnector() 将表单提交的数据，组织成层次分明的数组
2017/01/09 [修复函数] floatCut()
2016/12/29 [调试函数]修改echoln(),isRunInCLI()
2016/12/28 [日期函数]增加函数getDateNDaysAround(),获取给定日期前后、前、后N天的范围(可选'时间戳'或'ISO8601格式')
2016/12/28 [调试函数]增加函数echoln() echo 的同时进行换行
2016/12/28 [系统操作]增加函数isRunInCLI() 判断是否在命令行中运行
2016/12/22 [文件操作]增加函数saveToConf() 将二维数组生成php形式的配置文件
2016/10/15 [系统操作]引入函数isWindows()和isLinux()，检测服务器操作系统
2016/09/29 [数据安全]引入外部函数 unicodeEncode unicodeDecode
2016/09/28  reArrangeAssocArylist() 增加：如果待重排的元素是关联数组，重排以后，在第二层保留最外层的键值
2016/09/13  handleForZtree() 为配合zTree插件
2016/09/10  groupByFieldEx implodeEx buildCondiBatchUpdateSql
2016/04/07 19:22 改善turnIndexAryToOptions()函数，增加_indexAsVal，_givenVal属性，增加选中功能

2016/04/01 14:11 添加batchUnset, unset关联数组中的指定元素，为节省流量，减少传输
2016/01/28 为isAllUtf8Cn添加 \x{f900}-\x{fa2d} 字符集
2016/01/18 为groupByField() 添加第三个参数
*/

/**
 * @desc 判断给定的映射集中，指定字段下的值是否有重复
 * @param $_mapSet
 * @param $_field
 * @return bool
 */
function isFieldOfMapSetHasSameElement( $_mapSet, $_field ){

    $targetArray = array_column( $_mapSet, $_field );
    return isArrayHasSameElement( $targetArray );
}

/**
 * @desc 给定的数组中是否有相同的元素
 * @param array $_ary
 * @return bool true=有|false=无
 */
function isArrayHasSameElement( Array $_ary ){

    $stat = array_count_values( $_ary );
    $keys = array_keys( $stat );
    return ( count( $keys ) != array_sum( $stat ) );
}

/**
 * @desc 如果给定的值是数组的某个元素，则输出"checked"
 * @module PHP模版相关函数
 * @param $_srcAry
 * @param $_givenVal
 * @return string
 */
function isArrayMemberThenChecked( $_srcAry, $_givenVal  ){
    return in_array( $_givenVal, $_srcAry )? "checked" : "" ;
}

/**
 * @desc [数组函数]使用关联数组集合构建键值对映射
 * @param $_assocAry 关联数组集合
 * @param $_keyField 其值将作为键的字段
 * @param $_valField 其值将作为值的字段
 * @return array
 */
function buildMap( $_assocAry, $_keyField, $_valField ){
    $retAry = [];
    foreach( $_assocAry as $item ){
        $retAry[$item[ $_keyField ]] = $item[$_valField];
    }
    return $retAry;
}

/**
 * HTTP延迟跳转
 * @param $_sec
 * @param $_url
 * @param $_promptHTML
 */
function httpRefresh( $_sec, $_url, $_promptHTML ){
    header("Refresh:{$_sec};url={$_url}");
    exit( $_promptHTML );
}

/**
 * @desc 输出,并根据脚本运行环境换行,考虑windows下的cli编码
 * @author hehm
 * @param  [...] 接收可变参数
 * @date 2016/12/27 20:50
 * @apply
 */
function echoln(){
    $args = func_get_args();
    $args[] = isRunInCLI() ?  ( isWindows() ? "\r\n" : "\n" ) : "<br />" ;

    $txt = implode( $args, '' );
    echo ( isWindows() ? iconv('utf-8', 'gbk', $txt) : $txt );
}

/**
 * @desc 判断PHP脚本是否运行在CLI中
 * @author hehm
 * @date 2016/12/27 20:46
 * @apply
 * @return bool true=是|false=否
 */
function isRunInCLI(){
    return ( php_sapi_name() == "cli" );
}

/**
 * @desc
 * @author hehm
 * @param $_saveRealPath 配置文件保存路径
 * @param $_confName 配置名称
 * @param $_stateNameMap 待转换为配置数据的二维数组
 * @return bool
 */
function saveToConf( $_saveRealPath, $_confName, Array $_stateNameMap ){

    $header = "<?php\r\n".
        "return [\r\n".
        "   '{$_confName}' => [\r\n";
    $footer = " ] ];";

    $keyValueTmpl = "       '%s' => '%s', ";
    $keyValueStr = "";
    foreach( $_stateNameMap as $key => $val ){
        $keyValueStr .= sprintf( $keyValueTmpl, $key, $val )."\r\n";
    }

    $content = $header.$keyValueStr.$footer;
    $bytes = file_put_contents( $_saveRealPath, $content );

    return ( $bytes > 0 ) ? true : false ;
}

function isWindows(){
    return (PHP_SHLIB_SUFFIX == 'dll') ? true : false ;
}

function isLinux(){
    return (PHP_SHLIB_SUFFIX == 'os') ? true : false ;
}

//将内容进行UNICODE编码
function unicodeEncode($name){
    $name = iconv('UTF-8', 'UCS-2', $name);
    $len = strlen($name);
    $str = '';
    for ($i = 0; $i < $len - 1; $i = $i + 2){
        $c = $name[$i];
        $c2 = $name[$i + 1];
        if (ord($c) > 0) {
            // 两个字节的文字
            $str .= '\u'.base_convert(ord($c), 10, 16).base_convert(ord($c2), 10, 16);
        }
        else{
            $str .= $c2;
        }
    }
    return $str;
}

// 将UNICODE编码后的内容进行解码
function unicodeDecode($name){
    // 转换编码，将Unicode编码转换成可以浏览的utf-8编码
    $pattern = '/([\w]+)|(\\\u([\w]{4}))/i';
    preg_match_all($pattern, $name, $matches);
    if (!empty($matches)){
        $name = '';
        for ($j = 0; $j < count($matches[0]); $j++){
            $str = $matches[0][$j];
            if (strpos($str, '\\u') === 0){
                $code = base_convert(substr($str, 2, 2), 16, 10);
                $code2 = base_convert(substr($str, 4), 16, 10);
                $c = chr($code).chr($code2);
                $c = iconv('UCS-2', 'UTF-8', $c);
                $name .= $c;
            }
            else{
                $name .= $str;
            }
        }
    }
    return $name;
}

/**
 * @desc 构建批量条件更新的sql语句
 * @author hehm
 * @date 2016/09/10
 * @apply
 * @param $_tbName 表名
 * @param $_condiField 用于查询条件的字段(在where中使用)
 * @param $_newValSet 形如:
 *      ["fieldVal1"=>['num'=>'3','isDel'=>0],"fieldVal2"=>['num'=>'2','isDel'=>1]]
 * @return string 假如 _condiField="orderNo"
 *       核心语句:  set num=case orderNum when 'fieldVal1' then 3 when 'fieldVal2' then 2, ...
 */
function buildCondiBatchUpdateSql( $_tbName, $_condiField, $_newValSet ){

    $fieldValAry = array_keys( $_newValSet );
    $sqlTmpl = "update {$_tbName} set ".
        " %s ".
        " where {$_condiField} in (%s)";

    $updtFields = array_keys( current( $_newValSet ) );  //待更新字段

    $setComp = [];
    foreach( $updtFields as $field ){
        $str = " {$field}=case {$_condiField} ";

        foreach( $_newValSet as $condiFieldVal => $newVals ){
            $str .= " when '{$condiFieldVal}' then {$newVals[$field]} ";
        }
        $str .= "end";
        $setComp[] = $str;
    }//foreach
    $setStr = implode(",", $setComp);

    $sql = sprintf( $sqlTmpl, $setStr, implodeEx(",", $fieldValAry, function($v){ return "'".$v."'"; }) );
    return $sql;
}

/**
 * @desc 连接$_ary中的元素
 * @author hehm
 * @date 2016/09/10
 * @apply
 * @param $_glue
 * @param $_ary
 * @param $_callback 用于预处理$_ary中每一个元素的回调, $_callback($v){...} $v将为$_ary中的元素
 * @return string
 */
function implodeEx($_glue, $_ary, $_callback){
    foreach( $_ary as &$val ){
        $val = call_user_func($_callback, $val);
    }
    return implode( $_glue, $_ary );
}

/**
 * @desc 统计一个关联数组中，给定字段为某值的元素个数
 * @author hehm
 * @date 2016/09/10
 * @apply
 * @param $_assocAry
 * @param $_field
 * @param $_val
 * @return int
 */
function countByFieldVal( $_assocAry, $_field, $_val ){
    $count = 0;
    foreach( $_assocAry as $item ){
        if( $item[$_field] == $_val ) $count++;
    }

    return $count;
}

/**
 * @desc 获取真实的访问ip
 * @author hehm
 * @date 2016/08/18
 * @apply
 * @return mixed
 */
function getTrueIP(){
    return $_SERVER['REMOTE_ADDR'];
}//func

/**
 * @desc
 * @author hehm
 * @date 2016/7/18
 * @apply 用于比较时间，例如信用卡有效期是否过期
 * @param $_year
 * @param $_month
 * @return int
 */
function getLastDayTimestampOfYearMonth($_year, $_month){

    $months = ["","January","February","March","April","May","June","July","August","September","October",
        "November", "December"];
    $dtStr = "last day of {$months[$_month]} {$_year}";

    return strtotime( $dtStr );

}//func

/**
 * @desc
 * @author hehm
 * @date 2016/7/18
 * @apply 用于比较时间
 * @param $_year
 * @param $_month
 * @return int
 */
function getFirstDayTimestampOfYearMonth($_year, $_month){

    $months = ["","January","February","March","April","May","June","July","August","September","October",
        "November", "December"];
    $dtStr = "first day of {$months[$_month]} {$_year}";

    return strtotime( $dtStr );

}//func

/**
 * @desc 将关联数组集合，以给定的某个字段的值进行排序
 * @author hehm
 * @date 2016/05/05 2:47
 * @apply 用于以 field in (val1, val2, val3)取出数据，但最终的结果集并未以相应字段的 val1、val2、val3进行排序的情况
 * @param $_valueStr 字段值的集合，通常以','分割
 * @param $_assocArySet 关联属组记录
 * @param $_field 表示_valueStr中的每一项是哪个字段的值
 * @param string=',' $_delimiter _valueStr中的分隔符
 */
function sortBySpecifiedValuesOrder( $_valueStr, $_assocArySet, $_field, $_delimiter = ',' ){

    $retData = array();
    $valueSet = explode($_delimiter, $_valueStr);
    $list = groupByField($_assocArySet, 'sn');

    foreach($valueSet as $sn){
        $retData[] = $list[$sn][0];
    }

    return $retData;
}//func

/**
 * @desc 检测给定的关联数组，是否有字段为空('')
 * @author hehm
 * @date 2016/05/04 17:18
 * @apply 后端数据验证
 * @param $_assocAry 待检测的数组
 * @return bool 如果给定的数组本身为空 或 存在值为空的字段,那么返回true
 */
function isAssocExistsFeildsEmpty( $_assocAry ){

    if( empty($_assocAry) ){
        return true;
    }

    $hasFieldEmpty = false;
    foreach( $_assocAry as $val ){
        if( "" == $val ){
            $hasFieldEmpty = true;
            break;
        }
    }

    return $hasFieldEmpty;
}//func

/**
 * @desc 给定的'字符串单元'是否在摸个字符串中( 排除了'1'在'12,32'中的情况 )
 * @author hehm
 * @date 2016/04/28
 * @apply
 * @param $_haystack
 * @param $_needle
 * @param string="" $_delimeter
 * @return bool
 */
function isPartOfCertainStr($_haystack, $_needle, $_delimeter = ""){

    $_haystack = $_delimeter.$_haystack.$_delimeter;
    $_needle = $_delimeter.$_needle.$_delimeter;

    $pos = strpos($_haystack, $_needle);

    return (false === $pos) ? false : true;

}//func

/**
 * @desc 按照给定的字段的值快速'降序'排序一个关联数组集合
 * @author hehm
 * @date 2016/04/28 10:37
 * @apply
 * @param $_ary
 * @param $_field
 * @return array
 */
function quickRsortByField( $_ary, $_field ){

    if( empty($_ary) ){
        return $_ary;
    }
    if( 1 == ($len = count($_ary)) ){
        return $_ary[0];
    }

    $lAry = array();
    $rAry = array();
    $firstItem = $_ary[0];

    for($i=1; $i<$len; $i++ ){
        if( $firstItem[$_field] < $_ary[$i][$_field] ){
            $lAry[] = $_ary[$i];
        }else{
            $rAry[] = $_ary[$i];
        }
    }//for

    //merge
    $mergeAry = array();
    if( !empty( $lAry ) ){
        $lAry  = ( count($lAry) > 1 ) ? quickRSortByField( $lAry, $_field ) : $lAry;
        foreach( $lAry as $item )	array_push($mergeAry, $item);
    }
    array_push( $mergeAry, $firstItem );
    if( !empty( $rAry ) ){
        $rAry = ( count($rAry) >1 ) ? quickRSortByField( $rAry, $_field ) : $rAry ;
        foreach( $rAry as $item )  array_push($mergeAry, $item);
    }

    return $mergeAry;
}//func

/**
 * @desc 按给定'字段'的值快速升序排序一个关联数组结合
 * @author hehm
 * @date 2016/04/28 10:35
 * @apply
 * @param $_ary 待排序的关联数组集合
 * @param $_field 待排序字段
 * @return array
 */
function quickSortByField( $_ary, $_field ){

    if( empty($_ary) ){
        return $_ary;
    }
    if( 1 == ($len = count($_ary)) ){
        return $_ary[0];
    }

    $lAry = array();
    $rAry = array();
    $firstItem = $_ary[0];

    for($i=1; $i<$len; $i++ ){
        if( $firstItem[$_field] > $_ary[$i][$_field] ){
            $lAry[] = $_ary[$i];
        }else{
            $rAry[] = $_ary[$i];
        }
    }//for

    //merge
    $mergeAry = array();
    if( !empty( $lAry ) ){
        $lAry  = ( count($lAry) > 1 ) ? quickSortByField( $lAry, $_field ) : $lAry;
        foreach( $lAry as $item )	array_push($mergeAry, $item);
    }
    array_push( $mergeAry, $firstItem );
    if( !empty( $rAry ) ){
        $rAry = ( count($rAry) >1 ) ? quickSortByField( $rAry, $_field ) : $rAry ;
        foreach( $rAry as $item )  array_push($mergeAry, $item);
    }

    return $mergeAry;
}//func

/**
 * @desc 按数组进行手动分页
 * @author hehm
 * @date
 * @apply 适用于需要按照业务逻辑计算某个字段，然后需要用此字段进行排序的情况
 * @param $_list 待分页的数据，以数组形式存放
 * @param $_page 待获取的页数
 * @param $_numPerPage 每页数量
 * @return array
 */
function pagingManually($_list, $_page, $_numPerPage){

    return array_slice( $_list, ($_page-1)*$_numPerPage, $_numPerPage );
}//func

/**
 * @desc 在指定的'关联数组'中，删除给定的键值数组所对应的元素
 * @author hehm
 * @date 2016/04/01 14:14
 * @apply 响应数据时，减少流量
 * @param $_assocAry 待处理的关联数组
 * @param $_fields 待删除的'键'，组成的数组
 */
function batchUnset(&$_assocAry, $_fields){

    foreach( $_fields as $field ){
        if( isset($_assocAry[$field]) ){
            unset($_assocAry[$field]);
        }
    }//foreach
}//func

/**
 * @desc 将关联数组列表(其中元素最好为同构)，按字段，分别组织成索引数组
 * @author hehm
 * @date 2016/03/24 10:28
 * @apply 为了前端的特殊显示要求，必须要处理从数据库获取的数据
 * @param $_aryList 待处理的数据
 * @return array
 * case1 例如  [ ['name'=>'lily', 'age'=>21], ['name'=>'lucy', 'age'=>22] ]
 * 处理后为 [ 'name'=>['lily','lucy'],'age'=>[21,22] ]
 * case2 例如 [ 'No01' => ['name'=>'lily','age'=>21], 'No02' => ['name'=>'lucy','age'=>22] ]
 * 处理后为 [ 'name'=>['No01'=>'lily','No02'=>'lucy'], 'age'=>['No01'=>21,'No02'=>22] ]
 */
function reArrangeAssocArylist($_aryList){

    $retAry = array();
    foreach($_aryList as $key => $item){
        foreach( $item as $field => $val ){
            if( is_numeric($key) ){
                $retAry[$field][] = $val;
            }
            else{
                $retAry[$field][$key] = $val;
            }
        }
    }//foreach

    return $retAry;
}//func

/**
 * @desc 格式化信用卡号码
 * @date 2016/03/17 11:10
 * @param $_no 信用卡号码
 * @param int=5 $_partSize 每一组长度
 * @param string='-' $_seperator 每一组分隔符
 * @return string
 */
function creditCardNoFormat($_no, $_partSize = 4,$_seperator = "-"){

    $parts = str_split($_no, $_partSize);
    return implode($parts, $_seperator);
}//func

/**
 * @desc 获得给定时间之前的N天，返回其时间戳或者iso8601格式的日期
 * @date 2016/03/11 11:47
 * @param $_dtISO8601 给定的日期
 * @param $_daysBefore 指定日期之前的N天
 * @param string $_type 返回类型,如果不是'iso8601'，则以时间戳形式返回
 * @return bool|int|string
 */
function getPrevNDate( $_dtISO8601, $_daysBefore, $_type="iso8601" ){

    $timestamp = strtotime($_dtISO8601." 00:00:00 -{$_daysBefore} day");

    return ("iso8601" == $_type) ? date("Y-m-d", $timestamp)  : $timestamp ;
}//func

/**
 * @desc 获取给定ISO8601日期 前后、前、后的日期范围数据
 * @author hehm
 * @param $_dtFromISO8601
 * @param $_day 前后N天
 * @param string $_range ["both"|"before"|"after"]
 * @param string $_format ["ymd"|"ts"]
 * @return array
 */
function getDateNDaysAround( $_dtFromISO8601, $_day, $_range = "both", $_format = "ymd"){

    $ts = strtotime( $_dtFromISO8601 );
    $beforeTs = $ts - 86400*$_day;
    $afterTs = $ts + 86400*$_day;

    $retAry = [];
    $startTs = ( "both" == $_range ) ? $beforeTs : ( ("before" == $_range)  ? $beforeTs : $ts ) ;
    $endTs = ( "both" == $_range ) ? $afterTs: ( ("after" == $_range) ? $afterTs: $ts  ) ;

    if( "ymd" == $_format ){
        for( $curTs = $startTs; $curTs <= $endTs; $curTs += 86400 ) $retAry[] = date( "Y-m-d", $curTs );
    }
    else{
        for( $curTs = $startTs; $curTs <= $endTs; $curTs += 86400 ) $retAry[] = $curTs;
    }
    return $retAry;
}//func


/**
 * @desc 将给定的ISO8601的日期范围转换成单个是ISO8601日期数组(只包含Ymd部分)
 * @date 2016/03/10 16:00
 * @param $_dtFromISO8601 起始日期
 * @param $_dtToISO8601 结束日期
 * @param $_daysDrop 从后丢弃的天数
 * @return array
 */
function turnISO8601DtRangeToYmdAry($_dtFromISO8601, $_dtToISO8601, $_daysDrop = 0){

    $ymdAry = array();

    while( $_dtFromISO8601 <= $_dtToISO8601 ){

        $ymdAry[] = $_dtFromISO8601;
        $tmpTimestamp = strtotime($_dtFromISO8601." 00:00:00");

        $_dtFromISO8601 = date("Y-m-d", $tmpTimestamp + 86400);    //1天后
    }//while

    for($len = 0; $len < $_daysDrop; $len++){
        array_pop( $ymdAry );
    }
    return $ymdAry;
}//func

/**
 * @desc 将给定的ISO8601的日期范围转换成时间戳(时间为00:00:00)
 * @param $_dtFromISO8601 起始天数
 * @param $_dtToISO8601 结束天数
 * @param $_daysDrop 丢弃后面的_daysDrop天
 * @return array
 */
function turnISO8601DtRangeToTimestampAry($_dtFromISO8601, $_dtToISO8601, $_daysDrop){

    $timestampAry = array();

    while( $_dtFromISO8601 <= $_dtToISO8601 ){
        $tmpTimestamp = strtotime($_dtFromISO8601." 00:00:00");
        $timestampAry[] = $tmpTimestamp;

        $_dtFromISO8601 = date("Y-m-d", $tmpTimestamp + 86400);    //1天后
    }//while

    for($len = 0; $len < $_daysDrop; $len++){
        array_pop($timestampAry);
    }
    return $timestampAry;
}//func

/**
 * @desc 将给定的时间戳范围，转换成时间戳数组(其中的元素以Y-m-d 00:00:00计时)
 * @date 2016/03/15 20:56
 * @param $_sTimestamp 起始时间戳
 * @param $_eTimestamp 结束时间戳
 * @param $_daysDrop 丢弃的天数
 * @return array
 */
function turnTimestampRangeToDaytimestampAry($_sTimestamp, $_eTimestamp, $_daysDrop){

    $timestampAry = array();
    $_sTimestamp = strtotime(date("Y-m-d", $_sTimestamp));
    $_eTimestamp = strtotime(date("Y-m-d", $_eTimestamp));

    while( $_sTimestamp <= $_eTimestamp ){
        $timestampAry[] = $_sTimestamp;
        $_sTimestamp += 86400;
    }

    for($len = 0; $len < $_daysDrop; $len++){
        array_pop($timestampAry);
    }
    return $timestampAry;
}//func

/**
 * @desc 如果给定的值与目标值相等，那么则输出disabled="disabled"
 * @date 2016/01/29 15:51
 * @apply 表单模版函数
 * @param $_targetVal
 * @param $_givenVal
 */
function eqThenDisabled($_targetVal, $_givenVal){
    if( $_targetVal == $_givenVal ){
        return 'disabled="disabled"';
    }
    return '';
}

/**
 * @desc 如果给定的值与目标值相等，那么则输出display:none;
 * @date 2017/05/23
 * @apply 表单模版函数
 * @param $_targetVal
 * @param $_givenVal
 */
function eqThenHidden( $_targetVal, $_givenVal ){
    if( $_targetVal == $_givenVal ){
        return "display:none";
    }
    return "";
}

/**
 * @desc 如果给定的值与目标值相等，那么则输出readonly="readonly"
 * @date 2016/03/02 20:27
 * @apply 表单模版函数
 * @param $_targetVal
 * @param $_givenVal
 */
function eqThenReadonly($_targetVal, $_givenVal){
    if( $_targetVal == $_givenVal ){
        return 'readonly="readonly"';
    }
    return '';
}

/**
 * @description 将给定的关联数组集合，生成<option>字符串集合
 * @date 2016/01/28 14:50
 * @mdate 2016/04/07
 * @param $_ary 给定的数据集合
 * @param $_valField 关联数组元素中作为value的字段
 * @param $_textField 关联数组元素中作为<option>中间的文本字段
 * @param $_givenVal {string=""} 如果给定的$_valField字段下的值与$_givenVal的相等，则选中对应option
 * @return string
 */
function turnAssocAryToOptions( $_ary, $_valField, $_textField, $_givenVal = "" ){
    $optionHtml = "";
    foreach( $_ary as $item ){

        $selected = ( $item[$_valField] == $_givenVal )? 'selected="selected"' : "" ;
        $optionHtml .= '<option '.$selected.' value="'.$item[$_valField].'">'.$item[$_textField].'</option>';
    }
    return $optionHtml;
}

/**
 * @description 将给定的索引数组的非空值，生成<option>字符串集合
 * @date 2016/01/28 15:03
 * @mdate 2016/04/07 19:18
 * @param $_ary 对应的索引数组
 * @param $_indexAsVal {bool=true} 是否将数组的索引作为option的value属性(如果不是，则以数组元素作为option的value属性)
 * @param $_givenVal {string=""} 如果某个<option>的值和给定的这个值相等，此option添加selected属性
 * @return string
 */
function turnIndexAryToOptions( $_ary, $_indexAsVal = true, $_givenVal = "" ){

    $optionHtml = "";
    foreach( $_ary as $index => $val ){

        if( "" == $val ){   //防止索引数组空元素
            continue;
        }

        if(  false === $_indexAsVal ){
            $selected = ( $val == $_givenVal ) ? 'selected="selected"' : "" ;
            $optionHtml .= '<option '.$selected.' value="'.$val.'">'.$val.'</option>';
        }
        else{
            $selected = ( $index == $_givenVal ) ? 'selected="selected"' : "" ;
            $optionHtml .= '<option '.$selected.' value="'.$index.'">'.$val.'</option>';
        }
    }//foreach

    return $optionHtml;
}//func

//2016/01/28 11:55
//验证给定实数是否大于等于0
function isRealGteZero( $_str ){
    if( isReal($_str) && $_str >= 0 ){
        return true;
    }
    return false;
}

//2016/01/28 11:54
//验证给定实数是否大于0
function isRealGtZero( $_str ){
    if( isReal($_str) && $_str > 0 ){
        return true;
    }
    return false;
}

//2016/01/28 11:53
//验证给定整数是否大于等于0
function isIntGteZero( $_str ){
    if( isIntegerValid($_str) && $_str > 0 ){
        return true;
    }
    return false;
}

//2016/01/28 11:52
//验证给定整数是否大于0
function isIntGtZero( $_str ){
    if( isIntegerValid($_str) && $_str > 0 ){
        return true;
    }
    return false;
}

//2016/01/22 19:55
//使用姓氏和名字构建全名
function buildHumanFullname( $_fname, $_lname ){
    $pattern = '/^[a-zA-Z ]+$/';
    if( preg_match( $pattern, $_fname ) > 0 ){  //英文
        return "{$_fname} {$_lname}";
    }else{  //中文
        return "{$_lname}{$_fname}";
    }
}//func

//2016/01/22 18:54
//是否是台湾的邮递区号
function isTWZipCode( $_str ){
    $pattern = '/^\d{3}$/';
    return ( preg_match( $pattern, $_str ) > 0 )? true : false ;
}

//2016/02/22 09:15
//是否只包含"下划线、字母和数字"
function isUnderscoreAndAlphaAndNum( $_str ){
    $pattern = '/^[_a-zA-Z0-9]+$/';
    return ( preg_match( $pattern, $_str ) > 0 )? true : false ;
}


//2016/01/22 15:35
//是否是有效的信用卡安全号码
function isCreditSafetyCode( $_str ){
    $pattern = '/^\d{3}$/';
    return ( preg_match( $pattern, $_str ) > 0 )? true : false ;
}

//2016/01/22 11:07
//数组函数-目标字段数组(_targetField)，如果其中的元素在给定的关联数组(givenAssoc)中不存在，那么在
//关联数组中添加此项，并将值设置为val
//@apply: 适用于没有选中某复选框，但是也要为期设置值的场景
function notExistsThenSet( $_targetFields, $_givenAssoc, $_val ){
    foreach($_targetFields as $field){
        if( !isset($_givenAssoc[$field]) ){
            $_givenAssoc[$field] = $_val;
        }
    }//foreach
    return $_givenAssoc;
}


//2016/01/20 10:56 如果相等，则输出给定字符串，否则输出_defaultStr(默认为空串)
//apply 根据条件判定是否输出css 类名(sidebar 选中)
function eqThenPrintStr( $_targetVal, $_givenVal, $_strToPrint, $_defaultStr = "" ){
    if( $_targetVal == $_givenVal ){
        return $_strToPrint;
    }
    return $_defaultStr;
}

//2016/07.25 15:52 如果给定的值(字符串，数值)，存在与目标数组中，则输出给定字符串，否则输出_defaultStr(默认为空串)
//apply 根据条件判定是否输出css 类名(sidebar 选中)
function inThenPrintStr( $_targetValAry, $_givenVal, $_strToPrint, $_defaultStr = "" ){
    return in_array( $_givenVal, $_targetValAry ) ? $_strToPrint : $_defaultStr ;
}

//2016/04/28 21:27 如果不想等，则输出给定字符串，否则输入_defaultStr(默认为空串)
//apply 根据条件判定是否输出css 类名(sidebar 选中)
function neqThenPrintStr( $_targetVal ,$_givenVal, $_strToPrint, $_defaultStr = "" ){
    if( $_targetVal != $_givenVal ){
        return $_strToPrint;
    }
    return $_defaultStr;
}

//2016/01/19 16:18 如果小于则选中
function ltThenChecked( $_targetVal, $_givenVal ){
    if( $_givenVal < $_targetVal ){
        return 'checked="checked"';
    }
    return "";
}

//2016/01/19 16:18 如果大于则选中
function gtThenChecked( $_targetVal, $_givenVal ){
    if( $_givenVal > $_targetVal ){
        return 'checked="checked"';
    }
    return "";
}//func

//2016/01/19  15:59
//简单计算两个iso8601时间之间相隔的月数(包含此两个月在内)
function calcIso8601MonthGap($_dateStr1, $_dateStr2){
    //年月化处理
    $tmp = str_split($_dateStr1,7);
    $dateStr1 = $tmp[0];
    $tmp = str_split($_dateStr2,7);
    $dateStr2 = $tmp[0];

    if( $dateStr1 == $dateStr2 ){
        return 1;
    }

    //否则计算
    ($dateStr1 > $dateStr2) && swap($dateStr1, $dateStr2);      //保证$dateStr1为较小的那个
    $counter = 1;
    do{
        $dateStr1 = date("Y-m", strtotime($dateStr1." +1 month"));
        $counter++;
    }while($dateStr1 < $dateStr2);

    return $counter;
}//func

//2016/01/19 15:34
//将从数据库中获取的关联数组形式的记录，中的每个记录的某个字段对应的值提取出来，由分隔符链接
function extractValsByField( $_assocArySet, $_field, $_separator = ";" ){
    $fieldVals = array();
    foreach( $_assocArySet as $item ){
        $fieldVals[] = $item[$_field];
    }

    return implode($fieldVals, $_separator);
}//func

//2016/07/20 和>PHP5.5 中的array_column一致
//将从数据库中获取的关联数组形式的记录，中的每个记录的某个字段对应的值提取出来，由分隔符链接
//$_encap 为包裹符
function extractAryByField($_assocArySet, $_field, $_encap = ""){
    $fieldVals = array();
    foreach( $_assocArySet as $item ){
        $fieldVals[] = $_encap.$item[$_field].$_encap;
    }

    return $fieldVals;
}//func


//2016/01/19  11:45
//给定一串iso8601格式的字符串列表，处理并生成 array( "Y-m"=>dayArray, ... ) 的形式
//apply:配合前端日期选择器等插件
function statIso8601DatestrList( $_datestrList, $_separator = ";" ){

    $retVal = array();
    $dateStrAry = explode($_separator, $_datestrList);  //数组化
    sort( $dateStrAry );

    foreach($dateStrAry as $dateStr){
        list($ym, $day)= str_split($dateStr, 7);
        $day = ltrim($day,'-');

        $retVal[$ym][] = $day;
    }

    return $retVal;
}//func

//2016/01/19 11:02
//计算两个时间戳之间的间隔天数(d)、小时数(h)、分钟数(m)、秒数(s)
function calcDateInterval( $_timestamp1, $_timestamp2 ){
    $retVal = array();
    $interval = abs($_timestamp1 - $_timestamp2);

    //天数
    $retVal['d'] = floor($interval / 86400);
    $remainSec = $interval % 86400;

    //小时
    $retVal['h'] = floor($remainSec / 3600);
    $remainSec = $interval % 3600;

    //分钟、秒
    $retVal['m'] = floor($remainSec / 60);
    $retVal['s']= $interval % 60;

    return $retVal;
}//func


//2016/01/18 10:21
function getLastMonthDayByIso8601($_ymStr){
    return date("d", strtotime( $_ymStr." last day of this month" ));
}//func

/***
 * 2016/01/18 09:53
 * @param $_monthNum
 * @param $_isLeapYear
 * @return mixed
 */
function getLastMonthDay($_monthNum, $_isLeapYear){
    $_monthNum = intval($_monthNum);
    $ary = array(
        array(31,29,31,30,31,30,31,31,30,31,30,31),
        array(31,28,31,30,31,30,31,31,30,31,30,31)
    );

    return $ary[$_isLeapYear][$_monthNum-1];
}//func


/**
 * 将给定值与目标值项比较，以决定是否输出checked="checked"字符串
 * @date 2016/01/12 11:26
 * @apply 表单显示处理函数(主要用于input[type=radio])
 *
 * @param $_targetVal {String} 待比较的字符串
 * @param $_givenVal  {String} 给定的字符串
 * @return string {String} 如果相等，则返回选中标志，否则返回空字符串
 */
function eqThenChecked( $_targetVal, $_givenVal){
    if( $_targetVal == $_givenVal ){
        return 'checked="checked"';
    }
    return "";
}//func

/**
 * @description 将给定值与目标值相比较，以决定是否输出空
 * @date 2016/01/12 11:21
 * @apply 表单显示处理函数
 * @relative eqThenPrint
 *
 * @param $_targtVal {String} 待比较的字符串
 * @param $_givenVal {String} 给定字符串
 * @return string    {String}  如果相等，则返回空字符串，否则返回给定的字符串
 */
function eqThenPrintEmpty( $_targetVal, $_givenVal ){
    if( $_targetVal == $_givenVal ){
        return "";
    }
    return $_givenVal;
}//func

#### todo
/**
 * @description 向后截取路径的后面第n截，以获取相对路径
 * @date 2016/01/05
 *
 * @param $_path 路径字符串
 * @param $_sectionNum 待截取的段数
 * @return 返回所截取的相对路径
 */
function truncatePathFromEnd( $_path, $_targetNum ){
    $separator = '/';
    $_path = trim( str_replace('//', $separator, $_path), $separator) ;

    $sectionSet = explode($separator, $_path);
    $sectionNum = count( $sectionSet );

    if( $sectionNum < $_targetNum ){
        return $_path;
    }

    //否则，丢弃一部分
    $throwNum = $sectionNum - $_targetNum;

    for($i = 0; $i < $throwNum; $i++){
        array_shift( $sectionSet );
    }

    return implode($separator, $sectionSet);
}//func

/**
 * @description 将文件从临时文件夹，移动到指定文件夹，并以指定层次的子目录结构保存
 * @date 2016/01/05 15:03
 * @mdate 2016/03/16 09:50 考虑到linux环境，将目录路径的统一处理由trim改为rtrim
 *
 * @param $_tempPath
 * @param $_saveAbsDir  保存目录的绝对路径
 * @param array $_childDir
 * @return 成功则返回图片保存的绝对路径|失败则返回false(系统繁忙)
 */
function moveFileTo( $_tempAbsPath,$_saveAbsDir,$_childDir = array('Y','m','d') ){

    $_tempAbsPath = rtrim( str_replace('\\', '/', $_tempAbsPath), '/');
    $_saveAbsDir = rtrim( str_replace('\\', '/', $_saveAbsDir), '/');    //保存文件的绝对路径

    $fileFullName = getFullname( $_tempAbsPath );
    $retVal = false;

    if( empty( $_childDir ) ){
        $savePath = $_saveAbsDir.'/'.$fileFullName;
        $retVal = @rename( $_tempAbsPath, $savePath );
    }
    else{      //按子文件夹
        $curTime = time();
        $saveDir = $_saveAbsDir;

        foreach($_childDir as $subDir){
            $saveDir .= '/'.date("{$subDir}", $curTime);
            if( file_exists($saveDir) ){
                continue;
            }
            @mkdir( $saveDir );
        }//foreach

        $savePath = $saveDir.'/'.$fileFullName;
        $retVal = @rename( $_tempAbsPath, $savePath );
    }//else

    return $retVal ? $savePath : false;
}//moveFileTo

/**
 * @desc (文件上传)将文件从临时文件夹，移动到指定文件夹，并以指定层次的子目录结构保存
 * @notes 用于其他数据验证出错时，用户再次提交表单(防止文件移动之后就没有了)
 * @author hehm
 * @date 2016/08/23
 * @apply
 * @param $_tempAbsPath
 * @param $_saveAbsDir
 * @param array $_childDir
 * @return bool|string
 */
function copyFileTo($_tempAbsPath,$_saveAbsDir,$_childDir = array('Y','m','d')){
    $_tempAbsPath = rtrim( str_replace('\\', '/', $_tempAbsPath), '/');
    $_saveAbsDir = rtrim( str_replace('\\', '/', $_saveAbsDir), '/');    //保存文件的绝对路径

    $fileFullName = getFullname( $_tempAbsPath );
    $retVal = false;

    if( empty( $_childDir ) ){
        $savePath = $_saveAbsDir.'/'.$fileFullName;
        $retVal = @copy( $_tempAbsPath, $savePath );
    }
    else{      //按子文件夹
        $curTime = time();
        $saveDir = $_saveAbsDir;

        foreach($_childDir as $subDir){
            $saveDir .= '/'.date("{$subDir}", $curTime);
            if( file_exists($saveDir) ){
                continue;
            }
            @mkdir( $saveDir );
        }//foreach

        $savePath = $saveDir.'/'.$fileFullName;
        $retVal = @copy( $_tempAbsPath, $savePath );
    }//else

    return $retVal ? $savePath : false;
}//method

/**
 * 检测字符串是否全部是utf8中文字编码(且不能为空)
 * @date 2015/12/22 09:56
 * @mtime 2016/01/28 10:47
 * @param $_str 待检测字符串
 * @return bool true :  如果是, false : 如果不是
 */
function isAllUtf8Cn( $_str ){
    $pattern = '/^[\x{4E00}-\x{9FA5}\x{F900}-\x{FA2D}]+$/u';
    return ( preg_match( $pattern, $_str ) > 0 )? true : false ;
}

/**
 * 检测字符串是否是台湾座机或传真区号
 * @date 2015/12/23 14:08
 * @param $_str 待检测字符串
 * @return bool true :  如果是, false : 如果不是
 */
function isTWPhoneOrFaxAreaCode( $_str ){
    $pattern = '/^0\d{1,2}$/';
    return ( preg_match( $pattern, $_str ) > 0 )? true : false ;
}

/**
 * 检测字符串是否是台湾座机或传真号码
 * @date 2015/12/23 14:09
 * @param $_str 待检测字符串
 * @return bool true :  如果是, false : 如果不是
 */
function isTWPhoneOrFaxNumber( $_str ){
    $pattern = '/^\d{6,8}$/';
    return ( preg_match( $pattern, $_str ) > 0 )? true : false ;
}

/**
 * 检测字符串是否是台湾移动电话号码
 * @date 2015/12/23 14:09
 * @param $_str 待检测字符串
 * @return bool true :  如果是, false : 如果不是
 */
function isTWMobilePhone( $_str ){
    $pattern = '/^09\d{8}$/';
    return ( preg_match( $pattern, $_str ) > 0 )? true : false ;
}

/**
 * 检测字符串是否全部是由下划线和字母组成(包含空格)(且不能为空)
 * @date 2015/12/22 10:09
 * @param $_str 待检测字符串
 * @return bool true :  如果是, false : 如果不是
 */
function isAllUnderscoreAndAlpha( $_str ){
    $pattern = '/^[_ a-zA-Z]+$/';
    return ( preg_match( $pattern, $_str ) > 0 )? true : false ;
}

/***
 * @description 检测字符串是否是合法的英文标识符(字母，下划线，连字符，空格，数字)
 * @date 2015/01/28 10:58
 * @param $_str 待检测字符串
 * @return bool true :  如果是, false : 如果不是
 */
function isAllUtf8En( $_str ){
    $pattern = '/^[_ 0-9a-zA-Z\-]+$/';
    return ( preg_match( $pattern, $_str ) > 0 )? true : false ;
}

######## end to be extended ####

/**
 * 为给定的字符串添加随机字符串
 * 随机字符串由time()+6位的随机字母/数字串组成
 *
 * @date 2015/12/14 14:03
 * @apply 防止缓存时，在url后增加的随机字符串，例如_str部分通常为picurl,_prefix为'?_='
 */
function appendRandStr( $_str = "", $_prefix = "" ){
    return $_str.$_prefix.time().getRandStr(6);
}

######### 数值操作扩展 #########
/**
 *检测两个实数区间是否相交
 *
 *@date 2014/12/11 15:20
 *@scenario 碰撞检测; 业务要求能够自定义分时间段设置价格策略时
 *
 *@param array _interval1 第一个区间[0]表示区间的开始, [1]表示区间的结束
 *@param array _interval2 第二个区间[0]表示区间的开始, [1]表示区间的结束
 *@return boolean 相交返回true, 否则返回 false
 **/
function areIntervalsOverlapped( $_interval1, $_interval2 ){
    if( $_interval1[1] < $_interval2[0] || $_interval2[1] < $_interval1[0]  ){
        return false;
    }
    return true;
}//func

/**
 * @desc 按精度裁剪浮点数，不进行四舍五入，不够精度长则补充0
 * @author hehm
 * @date 2016/12/19 19:48
 * @apply
 * @param $_val 待修剪的值
 * @param $_precision 精度
 * @return string
 */
function floatCut( $_val, $_precision ){

    $dotPosition = strpos( $_val, '.' );
    if( $_precision < 0 || false === $dotPosition ){
        return $_val;
    }
    //补充0
    $_val .= str_repeat( '0', $_precision );

    return substr( $_val, 0, $dotPosition + 1 + $_precision );
}//func

#########  无限分类操作 #########
/**
 *垂直层次 溯祖函数
 *
 *@date 2015/10/08 09:11
 *@dependence 依赖于vHirarchify(), $_vList通过调用vHirarchify函数返回的
 *
 *@param array _vList  垂直结构的分类列表
 *@param int _id	待追溯其祖先的 节点的id值
 *@param array _fields 返回的节点(节点二维数组)中，包含哪些字段
 *@param array _cateInfo  定义实际字段，以适合表结构不同的情况
 *                              id表示给定_vList节点中作为id的字段
 *								 childs表示,如果_vList节点含有子元素，其子元素的键名
 *@return array 从根到指定节点名称的数组
 **/
function  vTraceRoot( $_vList, $_id, $_fields = array('name'), $_cateInfo = array('id' => 'id', 'childs'=>'childs')  ){

    $pathToRoot = array();		//待返回的数组
    list($id, $childs) = array( $_cateInfo['id'], $_cateInfo['childs'] );		//设置变量
    $_fields =  empty($_fields)  ? array('name')  : $_fields;		//默认显示的字段

    foreach( $_vList as $vList ){
        if( $vList[$id] == $_id ){		//如果找到指定节点
            $tmp = array();
            foreach( $_fields as $field ){
                $tmp[$field] = $vList[$field];
            }
            array_unshift( $pathToRoot, $tmp );
            return $pathToRoot;
        }

        //如果当前节点还有子节点
        if( $vList[$childs] ){
            $pathToRoot = vTraceRoot( $vList[$childs], $_id, $_fields, $_cateInfo );
            if( !empty($pathToRoot) ){			//如果属于一条脉络上的
                $tmp = array();
                foreach( $_fields as $field ){
                    $tmp[$field] = $vList[$field];
                }
                array_unshift( $pathToRoot, $tmp );
                return $pathToRoot;
            }
        }//if
    }//foreach

    return $pathToRoot;
}//vTraceRoot

/**
 *水平层次 溯祖函数
 *@2015/10/08 09:11
 *@dependence 依赖于hHirarchify(), $_hList通过调用hHirarchify函数返回的
 *
 *@param array _hList 水平结构的分类列表
 *@param int _id	待追溯其祖先的 节点的id值
 *@param array _fields 返回的节点(节点二维数组)中，包含哪些字段
 *@param array _cateInfo  定义实际字段，以适合表结构不同的情况
 *                              id表示给定_vList节点中作为id的字段
 *								 childs表示,如果_vList节点含有子元素，其子元素的键名
 *@return array 从根到指定节点名称的数组
 **/
function hTraceRoot( $_hList, $_id, $_fields = array( 'name' ), $_cateInfo = array('id' => 'id', 'pid'=>'pid')  ){

    $pathToRoot = array();		//待返回数组
    list($id, $pid) = array( $_cateInfo['id'], $_cateInfo['pid']);
    $_fields =  empty($_fields)  ? array('name')  : $_fields;		//默认显示的字段

    foreach( $_hList as $item ){
        if( $item[$id] != $_id ) continue;

        $tmp = array();
        foreach( $_fields as $field ){			//复制所需要的字段
            $tmp[$field] = $item[$field];
        }
        array_unshift($pathToRoot, $tmp);
        if( 0 == $item[$pid] ){		//如果当前元素已经是Root
            break;
        }else{
            $pathToRoot = array_merge( hTraceRoot( $_hList, $item[$pid], $_fields, $_cateInfo ), $pathToRoot);
        }
    }//foreach

    return $pathToRoot;
}//hTraceRoot

/**
 *通过水平层次的分类列表, 来获取某顶级节点id下的指定层级范围内的所有后代节点id
 *
 *@date2015/10/07
 *@dependence 依赖于hHirarchify(), $_hList通过调用hHirarchify函数返回的
 *@notes 要想此函数正常工作，必须使得_level, _cateInfo与上一次调用hHirarchify()是设置的参数一致
 *@scenario 随机获取某个顶级类下面所有叶子分类下的商品
 *@TODO     函数还不够完善
 *						精确控制获得子孙的层次
 *
 *@param array _hList 水平层次的分类数组
 *@param int _id 待获取其后代节点id集的元素节点id
 *@param int _level 如果后代节点所处的层次<=_level, 才返回
 * @param array _cateInfo 自定义id, pid, level对应的字段名(因为用户的数据表字段设置各不相同)
 *                        但是只要依据此三个字段，就可以实现通用情况)
 *@return array 返回找到的子节点中_cateinfo['id']字段(即作为id的字段)的值的集合
 */
function hGetDescendantIds($_hList, $_id, $_level = 0,
                           $_cateInfo = array('id'=>'id', 'pid'=>'pid', 'level'=>'level') ){

    list( $id, $pid, $level ) = array( $_cateInfo['id'], $_cateInfo['pid'], $_cateInfo['level'] );
    $retAry = array();

    foreach( $_hList as $item ){
        if( $item[$pid] == $_id ){
            if( $item[$level] >= $_level ){		//限制保留的层级
                $retAry[] = $item[$id];
            }
            $retAry = array_merge( $retAry, hGetDescendantIds( $_hList, $item[$id], $_level, $_cateInfo) );
        }
    }//foreach

    return $retAry;
}//hGetDescendantIds

/** 层次化某个列表
 *  垂直层次化(Vertically Hirarchify), 层次体现于结构，便于循环嵌套显示
 *  子层次可以通过节点的childs键来访问
 *
 * @author lucifer-v.
 * @date 2015/08/28
 * @changelog
 *		2015/10/06 调整_excludeIds与_cateInfo位置;改变$id, $pid, $level变量赋值方式
 *		2015/10/05 添加参数_excludeIds
 * @todo 针对于对象的扩展
 *
 * @param array _list 待进行层次化的某个分类列表
 * @param int   _pid  从父id为0的元素开始构造分类层次
 * @param int   _level 父类id为_pid的元素所在的层次数
 * @param array _exclueIds 如果id在此数组中，那么此元素不纳入分类，null表示不排除
 * @param array _cateInfo 自定义id, pid, level对应的字段名(因为用户的数据表字段设置各不相同)
 *                        但是只要依据此三个字段，就可以实现通用无限分类，并且凸显层次)
 *                        keyField表示用作每个节点键值的字段(使用节点本身的某一个键值)
 * @return array 一个具有垂直结构的分类结构
 **/
function vHirarchify($_list, $_pid = 0,$_level = 0, $_excludeIds = array(),
                     $_cateInfo = array("id"=>"id","pid"=>"pid", "level"=>"level","keyField"=>"") ){

    $retList = array();		 //待返回数组
    list($id,$pid,$level)	= array( $_cateInfo['id'], $_cateInfo['pid'], $_cateInfo['level'] );
    if( null == $_excludeIds ){ $_excludeIds = array(); }

    foreach($_list as $item){    //寻找父元素为指定pid的
        if( in_array( $item[$id], $_excludeIds) )	continue;		//跳过被排除的id

        if( $item[$pid] == $_pid ){
            $item[$level] = $_level;
            if( "" != $_cateInfo['keyField'] ){
                $retList[$item[$_cateInfo['keyField']]] = $item;
            }else{
                $retList[] = $item;
            }
        }
    }//foreach

    foreach( $retList as &$item ){  //获取孩子节点
        $item['childs'] = vHirarchify($_list, $item[$id], $_level + 1, $_excludeIds, $_cateInfo);
    }//foreach

    return $retList;
}//vHirarchify


/** 层次化某个列表
 *  水平层次化(Horizontally Hirarchify),使用level代表层次信息,适合下拉列表中显示
 *
 * @author lucifer-v.
 * @date 2015/08/28
 * @changelog
 *		2015/10/06 调整_excludeIds与_cateInfo位置;改变$id, $pid, $level变量赋值方式
 *		2015/10/05 添加参数_excludeIds
 * @todo 针对于对象的扩展
 *
 * @param array _list 待进行层次化的某个分类列表
 * @param int   _pid  从父id为0的元素开始构造分类层次
 * @param int   _level 父类id为_pid的元素所在的层次数
 * @param array _exclueIds 如果id在此数组中，那么此元素不纳入分类，null表示不排除
 * @param array _cateInfo 自定义id, pid, level对应的字段名(因为用户的数据表字段设置各不相同)
 *                        但是只要依据此三个字段，就可以实现无限分类，并且凸显层次)
 * @return array 一个具有水平结构的分类结构
 **/
function hHirarchify( $_list, $_pid = 0, $_level = 0, $_excludeIds = array(),
                      $_cateInfo = array("id"=>"id","pid"=>"pid", "level"=>"level")  ){

    $retList = array();  //待返回列表
    list($id, $pid, $level) = array( $_cateInfo['id'], $_cateInfo['pid'], $_cateInfo['level'] );		//局部变量赋值
    if( null === $_excludeIds ){	 $_excludeIds = array();	 }

    foreach( $_list as $item ){
        if( in_array( $item[$id], $_excludeIds) )	continue;		//跳过被排除的id

        if( $item[$pid] == $_pid ){  //查找子元素
            $item[$level] = $_level;
            $retList[] = $item;
            //查找子元素的子元素
            $retList = array_merge($retList , hHirarchify( $_list, $item[$id], $_level + 1, $_excludeIds, $_cateInfo ) );
        }
    }//for

    return $retList;
}//hHirarchify

//配合zTree插件
function handleForZtree( &$_v ){

    foreach( $_v as &$item ){
        if( 0 != count( $item['childs'] ) ){
            $item['children'] = handleForZtree( $item['childs'] );
        }else{
            unset( $item['childs'] );
        }
        batchUnset( $item, ['id','parentId','level'] );
        $item['open'] = false;
    }
    return $_v;
}//func

/***
 * @description 验证给定的字符串是否是是实数
 *			区别系统函数real(), 只能作用于数值型
 *			可以验证 'a.b', '.b', 'ab'等形式的数值字符串
 * @date 2015/12/29
 *
 * @param $_str 待验证字符串
 * @return bool
 */
function isReal( $_str ){
    $pattern = '/^\-?(\d+|\d+\.\d+|\.\d+)$/';
    return ( preg_match( $pattern, $_str ) > 0 )? true : false ;
}//func

/**
 *@description 检测字符串中是否含有utf8编码的中文
 *
 *@date 2015/10/09 11:03
 *@mdate 2016/01/04 22:28 by lucifer-v.
 *
 *@param string _str 待检测的字符串
 *@return boolean true :  如果含有
 *				 false : 如果不含有
 */
function isIncludeUtf8Cn( $_str ){

    $pattern = '/[\x{4e00}-\x{9fa5}\x{F900}-\x{FA2D}]/u';
    return ( preg_match( $pattern, $_str ) > 0 )? true : false ;
}//func

/**
 *验证给定的定点数是否合法
 *验证规则：总长度(不包括点号)不超过_totalLen, 小数位数不超过_decLen即视为合法
 *@author lucifer-v.
 *@date2015/10/05 17:37
 *@lastmodify 2015/11/01 12:39
 *
 *@param string _decimal 待验证其合法性的定点数
 *@param int _totalLen 允许的定点数总长度(不包括小数点)
 *@param int _decLen 允许的定点数小数部分最大长度
 *@return true / false
 */
function isDecimalValid( $_decimal, $_totalLen, $_decLen ){
    //参数长度不合法则返回false
    if( $_totalLen <= $_decLen ) return false;
    //如果含有除数字和.以外的字符，返回false
    if( preg_match( '/[^0-9.]/', $_decimal ) ) return false;

    $hasDot = (false === strpos($_decimal, '.')) ? false: true ;		//是否是小数

    $pattern = '/^\d{1,'.$_totalLen.'}$/';		//如果是整数的情况
    if(  $hasDot ){		//如果含有小数
        $decComps = explode('.', $_decimal);
        $facLen = min($_decLen, strlen($decComps[1]));		//实际的小数位数
        if( 0 == $facLen){		//考虑到实时输入是会出现'12.'的情况
            return false;
        }
        $pattern = '/^\d{1,'.($_totalLen - $facLen).'}\.\d{1,'.$facLen.'}$/';
    }

    return ( preg_match($pattern, $_decimal) > 0 ) ? true : false;
}//isDecimalValid

/**
 *整数是否合法 (验证4*2=8种类型的整数)
 *只支持到4294967295, 亦即mysql的int unsigned类型，
 *这对于一般的业务已经完全够用
 *@author lucifer-v.
 *@date 2015/10/05 18:58
 *
 *@param int _int 待检测的整数
 *@param int _type 整数类型("tinyint|smallint|mediumint|int")
 *@param boolen _unsigned 有符号false, 无符号true
 *@reutrn 合法 返回true, 非法返回false
 */
function isIntegerValid( $_int, $_type = 'int', $_unsigned = false ){

    $pattern = '/^\-?\d+$/';
    if( preg_match($pattern, $_int) == 0 ){	//如果形式上不是整数
        return false;
    }

    //从精度上判断
    if( $_unsigned ) $min = 0;
    $type = strtolower($_type . $_unsigned);		//整数类型

    switch( strval($_type . $_unsigned) ){
        case 'tinyint' :	 { $min = -128;	$max = 127; break; }
        case 'tinyint1' : { $max = 255; break; }
        case 'smallint' : { $min = -32768; $max = 32768; break; }
        case 'smallint1' : { $max = 65535; break; }
        case 'mediumint' : { $min = -8388608; $max = 8388607; break; }
        case 'mediumint1' : { $max = 16777215; break; }
        case 'int' : { $min = -2147483648; $max = 2147483647; break; }
        case 'int1' : { $max = 4294967295; break; }
    }//switch

    return ( $min <= $_int && $_int <= $max ) ? true : false;
}//isIntegerValid

/**
 *Email是否合法
 *
 *@author lucifer-v.
 *@date 2015/09/02 14:42
 *@lastmdate 2015/10/07 17:26
 *@param string email电子邮件
 *@return int  true=合法; false=不合法
 */
function isEmailValid( $_email ){

    $pattern = '/^[\-\w]+@\w+(\.\w+)+$/';

    return ( preg_match($pattern, $_email) > 0 ) ? true : false ;
}//func

/**
 *邮政编码是否合法
 *
 *@author lucifer-v.
 *@date 2015/09/02 14:42
 *@lastmdate 2015/10/07 17:26
 *@param string _qqnum qq号码
 *@return int  true=合法; false=不合法
 */
function isZipcodeValid( $_zipcode ){

    $pattern = '/^[1-9]\d{5}$/';
    return ( preg_match($pattern, $_zipcode) > 0) ? true : false ;
}

/**
 * @desc 是否全部是整数
 *
 * @author lucifer-v.
 * @date 2016/04/05 01:44
 * @apply 粗略地验证电话号码
 * @param string _str 待验证数据
 * @return int  true=合法; false=不合法
 */
function isAllInt( $_str ){

    $pattern = '/^\d+$/';
    return ( preg_match($pattern, $_str) > 0) ? true : false ;
}

/**
 *验证QQ号码是否合法
 *
 *@author lucifer-v.
 *@date 2015/10/04 23:47
 *@lastmdate 2015/10/07 17:26
 *@param string _qqnum qq号码
 *@return int  1=合法; 0=不合法
 */
function isQQValid( $_qqnum ){

    $pattern = '/^[1-9]\d{5,}$/';
    return ( preg_match($pattern, $_qqnum) > 0) ? true : false ;
}//func

/**
 *验证手机号码是否合法
 *
 *@author lucifer-v.
 *@date 2015/09/02 14:42
 *@lastmdate 2015/10/07 17:26
 *@param string _phonenum 手机号码
 *@return  int true=合法; false=不合法
 */
function isMobilephoneValid( $_phonenum ){
    $pattern = '/^1\d{10}$/';
    return ( preg_match($pattern, $_phonenum) > 0 ) ? true : false ;
}

/**
 * @desc 验证URL是否合法
 * @param $_url
 * @return bool
 */
function isHttpUrlValid( $_url ){
    $pattern = '/^(https?):\/\/[\w\-]+(\.[\w\-]+)+([\w\-\.,@?^=%&:\/~\+#]*[\w\-\@?^=%&\/~\+#])?$/';
    return ( preg_match($pattern, $_url) > 0 ) ? true : false ;
}

######### 日期/时间扩展 ##########
/***
 * @description 为个位数的小时和分钟，左侧补零
 * @date 2015/12/28 16:23
 *
 * @param $_mOrh 分钟(minute)或小时(hour)
 * @return string
 */
function timePadZero( $_mOrh ){
    $_mOrh = intVal( trim($_mOrh) );
    if( $_mOrh < 10 ){
        return '0' . $_mOrh;
    }
    return $_mOrh;
}//func

/***
 *得到时间戳，包含毫秒(但是毫秒以小数形式出现)
 *
 *@date  2015/10/28
 *@apply 用于测试代码执行时间
 *
 *@return float 当前时间戳
 */
function getMicrotime(){
    list($microSec, $sec) = explode(' ', microtime());
    return $sec + $microSec;
}//getMicrotime

/***
 *得到时间戳(精确到0.1毫秒)
 *
 *@date 2015/11/16
 *
 *@return int 当前时间戳
 */
function getIntMicrotime(){
    list($microSec, $sec) = explode(' ', microtime());
    return ($sec + $microSec)*10000;
}//getIntMicrotime

/**
 *根据时间戳得到中文的星期
 *
 *@date 2015/09/02 14:42
 *@param int _timeStamp 时间戳
 *@return string 星期N
 */
function getCnWeekName( $_timeStamp ){
    $_timeStamp = $_timeStamp ?: time();
    switch(date('N' , $_timeStamp) ){
        case '1': return '星期一';
        case '2': return '星期二';
        case '3': return '星期三';
        case '4': return '星期四';
        case '5': return '星期五';
        case '6': return '星期六';
        case '7': return '星期日';
    }
}//func

/**
 *将'YYYYMMDD'格式的年月字符串格式化为'YYYY*MM*DD'格式的
 *其中的'*'表示分隔符
 *
 *@date 2015/10/09 19:02
 *
 *@param string _datestr 待格式化的字符串
 *@param string _delim 格式分隔符
 *@return string 格式化以后的字符串
 */
function datestrf( $_datestr, $_delim = '/'){

    if( preg_match('/\D/', $_datestr) > 0 ){		//明显包含非数值字符时
        return false;
    }

    $_datestr = trim( $_datestr );							//去掉括号
    $pattern = '/^(\d{4})(\d{2})(\d{2})$/';	//标准匹配模式
    $len = strlen($_datestr);

    if( 6 == $len ){		//如果省去了世纪，年份>69 则19XX，否则20XX
        $pattern = '/^(\d{2})(\d{2})(\d{2})$/';
        preg_match( $pattern, $_datestr, $match );
        $year = ( $match[1] > 69 ) ?  '19'.$match[1] : '20'.$match[1] ;

        return implode( $_delim, array( $year, $match[2], $match[3] ) );
    }

    //长度不是6
    if( preg_match($pattern, $_datestr, $match) > 0){
        return implode( $_delim, array( $match[1], $match[2], $match[3] ) );
    }
    return false;
}//datestrf

/**
 * @description 得到一个日期ISO601日期列表中的'最大日期'和'最小日期'字符串
 * @date 2016/01/17 14:30
 * @apply 求出能够囊括所有给定的日历散列值的最小日期范围
 *
 * @param _dataList {string} 日期集合字符串
 * @param _separator {string=";"} 日期集合字符串中单个日期字符串的分隔符
 * @return {Assoc Ary} retVal['min'] 最小日期字符串
 *					   retVal['max'] 最大日期字符串
 */
function getTwoEndFromIso8601datelist( $_dateList, $_separator = ';'){

    $dateAry = explode( $_separator, $_dateList );
    $len  = count( $dateAry );
    if( $len < 1 ){
        return array();
    }elseif( $len == 1 ){
        return array("min" => $dateAry[0], "max" => $dateAry[0]);
    }

    sort( $dateAry );
    return array('min' => array_shift($dateAry), "max" => array_pop($dateAry));
}//func

/**
 * @description 将一些列离散的ISO8601日期字符串经可能多地使用日期范围表示
 * @date 2016/01/17 14:30
 * @apply 缩短日期范围的显示
 *
 * @param _dateList 待处理的iso8601日期字符串列表字符串
 * @param _joinStr {string="to"} 连接连续日期段的开始和末尾日期的字符串
 * @param _separator {string=";"} 参数_dateList所代表的日期列表字符串的分隔符
 * @return {Assoc Ary} 没个元素表示一个时间段(孤立日期单独作为一个元素)
 */
function parseDatelistToRangeSet( $_dateList, $_joinStr = "to", $_separator = ";" ){
    $dateAry = explode( $_separator, $_dateList );
    $dateRange = array();

    sort( $dateAry );			//升序排序
    $counter = 0;
    for($i = 0, $len = count( $dateAry ); $i < $len; $i++){
        if( 0 == $i ){		//第一个元素
            $startDate = $prevDate = $dateAry[ $i ];
            continue;
        }

        $curDate = $dateAry[ $i ];
        if( date("Y-m-d", strtotime("{$prevDate} +1 day") ) == $curDate ){		//时间是否连续
            $prevDate = $curDate;
        }else{		//时间不连续了
            if( $startDate == $prevDate ){		//如果是孤立的一天
                $dateRange[ $counter++ ]  = $startDate;
            }else{
                $dateRange[ $counter++ ] = "{$startDate} {$_joinStr} {$prevDate}";
            }
            $startDate = $prevDate = $curDate;
        }//else

        //如果最后一个日期属于一个连续时间范围内
        if( $i == $len - 1 ){
            if( $startDate == $prevDate ){		//如果是孤立的一天
                $dateRange[ $counter++ ]  = $startDate;
            }else{
                $dateRange[ $counter++ ] = "{$startDate} {$_joinStr} {$prevDate}";
            }
        }//if
    }//for

    return $dateRange;
}//func

########### 其他工具 ############
/**  交换两个变量的值 **/
function swap(&$_var1, &$_var2){
    list($_var1, $_var2) = array($_var2, $_var1);
}//func

/**
 *格式化输出变量,保留换行
 *@date 2015/10/06 09:28
 *@lastmdate  2015/10/09 10:55
 *
 *@param mixed [...] 可变参数
 */
function dumpf(  ){

    foreach( func_get_args() as $arg ){
        echo "<pre>";
        var_dump( $arg );
        echo "</pre>";
    }
}//func

/**
 * 在dumpf的基础上添加了exit;作为结尾
 * @date 2015/12/16 10:02
 * @mdate 2015/03/23 13:56
 */
function dumpfe(){
    foreach( func_get_args() as $arg ){
        echo "<pre>";
        var_dump( $arg );
        echo "</pre>";
    }
    exit;
}//func

/**
 *@description 将给定参数显示为'true'或'false'字符串
 *
 *@param {mixed} $_val 待检测的数据
 *@return 如果为真(非恒true), 返回'true',反之'false'
 */
function boolStr( $_val ){
    return ( true == $_val ) ? 'true' : 'false' ;
}

/**
 *获取文件(或资源)的文件名
 *
 *@author Lucifer-v.
 *@date  2015/10/04 23:15
 *@param string _uri 给定的文件路径
 *@return 返回文件的文件名
 */
function getFilename( $_uri ){
    return explode( '.', getFullname($_uri) )[0];
}//func

/**
 *获取文件(或资源)的扩展名(带点)
 *
 *@author Lucifer-v.
 *@date  2015/09/14 10:26
 *@param string _uri 给定的文件路径
 *@return 返回文件的扩展名(包含.)
 */
function getExtname( $_uri ){
    return strrchr( getFullname( $_uri ) , '.');
}//func


/**
 *获取文件(或URI)的全名,即最末尾的文件名+扩展名
 *
 *@author Lucifer-v.
 *@date  2015/09/14 10:15
 *@mdate 2016/01/04 22:51
 *@param string _uri 给定的文件路径
 *@return 返回文件的基名称
 */
function getFullname($_uri){

    if( null == $_uri )	return null;

    $_uri = str_replace('\\', '/', $_uri);  			//路径处理
    $_uri = trim(explode( '?', $_uri )[0], '/');

    return basename($_uri);
}//func

/**
 * 生成指定长度的随机数
 *默认情况下字符集为[数字,大小写英文字母]
 *
 *@author Lucifer-v
 *@date  2015/09/12 00:12
 *@scenario 生成salt
 *
 *@param  int _len 生成随机字符串的长度
 *@param  string _dict 自己提供字典
 *@return  string
 */
function getRandStr($_len, $_dict = null){

    $rand = "";
    //如果给定了字典
    if( null != $_dict ){
        for($i=0, $size=strlen($_dict); $i<$_len; $i++ ){
            $rand .= $_dict[mt_rand(0, $size-1)];
        }
        return $rand;
    }//if

    //默认情况
    for($i=0; $i<$_len; $i++){
        switch( mt_rand(0, 2) ){
            case 0 : $rand .= chr(rand(48, 57) );  break;
            case 1 : $rand .= chr(rand(65, 90));   break;
            case 2 : $rand .= chr(rand(97, 112)); break;
        }//switch
    }//for

    return $rand;
}//func

######## 前端/模版/表单元素/标签操作 ###########
/**
 * @desc 将表单元素中'a1-a2-a3..'形式的字段集合，解析称具有(数组)结构的数据
 * @author hehm
 * @date
 * @apply
 * @param $_keyVals 表单键值对数据
 * @param string $_connector 所提交的表单元素，'键'部分，层级之间的链接字符,如'-'
 * @return array
 * 测试数据:
    $keyVals = [
        'stu-1-name'=>"lily",
        'stu-2-age'=>"31",
        'stu-3-no'=>"NO003",
        'stu-2-name'=>"kaiwen",
        'stu-1-no'=>"NO001",
        'stu-2-no'=>"NO002",
        'stu-1-age'=>"13",
        'stu-3-name'=>"tom",
        'stu-3-age'=>"12"
    ];
 */
function parseFormFieldsWithConnector( $_keyVals, $_connector = "-" ){

    $tmp = [];
    $phpCode = "";
    $lineTmpl = "\$tmp%s='%s';";        //代码行模版

    foreach( $_keyVals as $key => $val ){
        $keyComps = explode($_connector, $key);
        $str = implodeEx( "", $keyComps, function( $ele ){ return "['{$ele}']"; } );
        $line = sprintf( $lineTmpl, $str, $val );       // $tmp['x'][...]['z'] = 'XX';
        $phpCode .= $line;
    }
    eval( $phpCode );

    return $tmp;
}//func

/***
 * 如果在字符串$_haystack中找到了子字符串$_needle，那么返回'checked="checked"'，否则返回""
 * @date 2015/12/28
 * @apply 用于模版中的[复选框]/单选框选中， 进行字符串试的匹配
 *
 * @param $_haystack
 * @param $_needle
 * @param $_deliemiter checkbox值连接符
 * @return string
 */
function hasThenChecked( $_haystack, $_needle, $_delimiter = "" ){

    $_haystack = $_delimiter . trim($_haystack, $_delimiter) . $_delimiter;
    $_needle = $_delimiter . trim($_needle, $_delimiter) . $_delimiter;

    if( false === strpos( $_haystack, $_needle ) ){
        return "";
    }
    return 'checked="checked"';
}//func

/***
 * 根据位掩码的值进行checkbox的选中，检测bitVal是否在totalVal中
 * @date 2015/12/28
 * @apply 选中checkbox，将当前checkbox的值和checkbox group所代表的总值项与
 *
 * @param $_totalVal 某个字段的各个位掩码当前值
 * @param $_bitVal 待检测的某个位掩码值
 * @return string
 */
function bitmaskEqThenChecked( $_totalVal, $_bitVal ){

    $_totalVal = intVal( $_totalVal );
    $_bitVal = intVal( $_bitVal );

    if( $_bitVal & $_totalVal ){    //如果含有此掩码
        return 'checked="checked"';
    }
    return '';
}//func

/***
 * 如果两个值相等，则返回'selected="selected"',否则返回false
 * @date 2015/12/28 16:38
 * @apply 用于前端模版，设置下拉列表选默认值时，当前值与选项值逐一比较
 *
 * @param $_optVal
 * @param $_giveVal
 */
function eqThenSelected( $_optVal, $_givenVal ){

    if( $_optVal == $_givenVal ){
        return 'selected="selected"';
    }
    return "";
}//func

/***
 * 如果给定的值为空，则显示替换字符串'_toPrint'，否则显示给定的源值
 * @date 2015/12/28 14:13
 * @apply 模版中统一化显示为空的字段。例如，某个工资字段为空，则显示'尚未录入'
 *
 * @param _str {string} 但检测的字符串
 * @param _toPrint {string} 如果待检测的字符串为空，则显示此字符串
 * @return string
 */
function emptyThenPrint( $_str, $_toPrint ){
    if('' === trim($_str)){
        return $_toPrint;
    }
    return trim($_str);
}//func

/*
 *将给定的html文本中，指定的名字为_tag(包含开始标签和闭合标签)实体化 
 *
 *@author Lucifer-v.
 *@date      2015/09/12  18:25 
 *@lastmdate 2015/10/04 22:19
 *@apply    防止XSS攻击
 *
 *@param  string  待替换的HTML字符串
 *@param string 待替换的标签名字
 *@return 替换以后的HTML字符串
 **/
function entitifyTags( $_html, $_tags ){

    foreach( $_tags as $tag ){
        //起始标签(单标签)|闭合标签
        $pattern = "/(<{$tag}[^>]*>|<\/{$tag}[^>]*)/isU";
        $_html = preg_replace_callback($pattern, function($_matches){
            return htmlspecialchars($_matches[1]);
        } ,$_html);
    };

    return $_html;
}//func

/**
 *生成一个36个字节的GUID
 *
 *@author unknown
 *@date		 unknown
 *@mender	lucifer-v.
 *@lastmdate 2015/10/04
 *
 *@param boolean _lowercase  默认为false,如果为true，则返回大写的GUID
 *@return string 返回36字节长的GUID字符串
 */
function createGuid( $_lowercase = false ) {

    $charid = strtoupper(md5(uniqid(mt_rand(), true)));
    $hyphen = chr(45);// "-"
    $uuid = substr($charid, 0, 8).$hyphen
        .substr($charid, 8, 4).$hyphen
        .substr($charid,12, 4).$hyphen
        .substr($charid,16, 4).$hyphen
        .substr($charid,20,12);

    return $_lowercase ? strtolower($uuid) : $uuid;
}//createGuid


########### 数组 #############
/***
 * @description  将字段值的集合组合成对象的集合(每个对象包含有一副完整的字段)
 *					是 aryRearrange()的你运算
 * @data 2015/12/23 11:14
 * @param $_assocAry {2D Array}
 * @return mixed {2D Array} 每个元素是一个包含所有给定字段的'元素'
 */
function turnPartsToWhole( $_assocAry ){

    $retVal = array();
    foreach( $_assocAry as $field => $valSet ){
        foreach( $valSet as $index => $val ){
            $retVal[$index][$field] = $val;
        }
    }//foreach
    return $retVal;
}

/***
 * 将给定数组中，指定字段集(键集)对应的元素为数组的，将此数组的元素以某个分隔符连接起来
 * 如果给定的数据中没有对应字段，则在数据中添加此字段，并将值设置为""
 * @date 2015/12/28 13:03
 * @modify 2015/12/29 11:09
 * @apply ajax提交时，对表单复选框的值进行处理
 *
 * @param $_srcAssocAry {2D array} 将要处理的原数组
 * @param $_fieldAry {array} 将要处理的字段列表
 * @param string {string} $_delimiter, 如果$_delimiter显式地为null,那么将各个值当作数值相加
 * @return mixed {2D array} 经过处理的数组
 */
function multiJoinAryElements( $_srcAssocAry, $_fieldAry , $_delimiter = ";" ){

    foreach( $_fieldAry as $field ){
        if( isset($_srcAssocAry[$field]) && !is_array($_srcAssocAry[$field]) ){
            continue;
        }
        if( false === isset($_srcAssocAry[$field]) ){
            $_srcAssocAry[$field] = "";
            continue;
        }

        if( null === $_delimiter ){     //求和
            $_srcAssocAry[$field] = array_sum( $_srcAssocAry[$field] );
        }else{  //拼凑
            $_srcAssocAry[$field] = implode($_delimiter, $_srcAssocAry[$field]);
        }
    }//foreach

    return $_srcAssocAry;
}//func

/**
 *递归地对数组中的元素进行HTML实体转义
 *
 *@data 2015/10/09 11:35
 *@note 函数不可重入
 *@scenario 对$_POST和$_GET整体操作
 */
function rHtmlspecialchars( &$_ary ){

    if( !is_array($_ary) ) return;		//输入非法

    foreach( $_ary as $key => &$val ){
        if( is_array($val) ){		//如果元素是数组，递归
            rHtmlspecialchars($val);
        }else{
            $_ary[$key] = htmlspecialchars($val);
        }
    }//foreach
}//rHtmlspecialchars

/**
 *递归地删除属组元素值两边的空白
 *
 *@data 2015/10/06 22:01
 *@note 函数不可重入
 *@scenario 对$_POST和$_GET整体操作
 */
function recurTrim( &$_ary ){

    if( !is_array($_ary) ) return;		//输入非法

    foreach( $_ary as $key => &$val ){
        if( is_array($val) ){		//如果元素是数组，递归
            recurTrim($val);
        }else{
            $_ary[$key] = trim($val);
        }
    }//foreach
}//recurTrim

/**
 *从给定的数组中滤出[指定键值对]的数组元素,内部的值比较使用恒等
 *
 *@date 2015/10/08 20:33
 *@scenario 需要对某些字段的值拿出来单独处理，单独命名的情况
 *					  例如: 分离join查询出来的数据
 *@note 函数不可重入，执行成功后_ary里面是剩下的数组
 *
 *@param array &_ary
 *@param array _kv 指定需要滤出并返回的键值对，形式: $_kv =array( 'field','value' )
 *@return array 被滤出的元素集合
 **/
function filterByField( &$_ary, $_kv ){

    $retAry = array();
    $remainsAry = array();
    list($key, $val) = $_kv;

    foreach( $_ary as $index => $ele ){
        if( $ele[$key] === $val ){
            $retAry[] = $ele;
            continue;
        }
        $remainsAry[] = $ele;
    }//foreach
    $_ary = $remainsAry;

    return $retAry;
}//func

/**
 * @desc 从给定的数组列表中，滤出指定字段值(键值)组成的值的集合
 * @date 2016/02/19 16:40
 * @scenario 多次查询数据库时，从前一次获取的数据集中取出id字段集合，对此id字段再次查询
 *
 * @param $_ary 待处理的数组
 * @param $_field 待滤出其值的字段
 * @return array
 */
function filterValuesByField( $_ary, $_field ){

    $retVals = array();

    foreach( $_ary as $item ){
        $retVals[] = $item[$_field];
    }

    return $retVals;
}//func

/**
 *给定二维数组集合，依据给定[字段的实际值]
 *将元素分割为若干个分组,子分组以字段值为键
 *
 *@date 2015/10/07 10:21
 *@mdate 2016/01/18 09:31
 *@note 此函数可重入 [弃用]
 *@scenario 对学生数据进行分班操作;写接口时,为前端提供数据且按日期(ymd)分组
 *
 *@param array _ary  待分组的数组
 *@param array _field 据以分组的字段
 *@param isRemain {bool=true} 是否保留据以分组的字段
 *@return array 已分好组的二维属组
 */
function groupByField( $_ary, $_field, $_isRemain = true ){

    $retAry = array();

    foreach( $_ary as $ele ){			//执行分组操作
        $tmp = $ele[$_field];

        if( !$_isRemain ){
            unset($ele[$_field]);
        }
        $retAry[$tmp][] = $ele;

    }//foreach

    return $retAry;
}//func

//和上面的区别是: 不再多一层
function groupByFieldEx( $_ary, $_field, $_isRemain = true ){

    $retAry = array();

    foreach( $_ary as $ele ){			//执行分组操作
        $tmp = $ele[$_field];

        if( !$_isRemain ){
            unset($ele[$_field]);
        }
        $retAry[$tmp] = $ele;

    }//foreach

    return $retAry;
}//func

/**
 * 数组重排  对象数组集合-->字段数组集合
 * 将给定参数中的数组元素进行重排, 拥有相同键名的元素,以索引数组的形式
 * 组织到一起, 之前的键名作为此索引属组的键名
 * 适用于'数组'参数列表
 *
 *@date 2015/10/07 11:44
 *@lastmdate 2015/01/15 8:19
 *
 *scenario ECSHOP中收集货品库存表单数据时会用到
 *@param array [...] 可变参数
 *@return array 经过重排的数组
 **/
function aryRearrange( ){

    $retAry = array();

    foreach( func_get_args() as $arg ){
        if( empty($arg) || !is_array($arg) ) continue;			//跳过非数组元素
        foreach( $arg as $key => $val ){		//逐个处理
            $retAry[$key][] = $val;
        }
    }//foreach

    return $retAry;
}//argRearrange

/**
 * 数组重排(uncert = un certain 不确定参数)
 * 将给定参数中的数组元素进行重排, 拥有相同键名的元素,以索引数组的形式
 * 组织到一起, 之前的键名作为此索引属组的键名
 * 适用于二维数组作为参数
 *
 *@date 2015/01/15 8:19
 *
 *scenario ECSHOP中收集货品库存表单数据时会用到
 *@param array _argAry 参数数组
 *@return array 经过重排的数组
 **/
function aryRearrangeUncert( $_argAry ){

    $retAry = array();
    foreach( $_argAry as $arg  ){
        if( empty($arg) || !is_array($arg) ) continue;			//跳过非数组元素
        foreach( $arg as $key => $val ){		//逐个处理
            $retAry[$key][] = $val;
        }
    }
    return $retAry;
}//func

########### SQL语句处理 #############
/**
 *将离散且有序的字段值转换为 " field in ('val1', 'val2', 'val3') "的形式
 *获得的字符串便于组织为SQL语句中的条件语句
 *
 *@date 2015/11/17 23:50
 *
 *scenario:如下拉列表选项值为'A','B','C','D','E'，选取field在 'B'到'D'之间的
 *@param string _fieldName 字段名
 *@param array _optsAry 字段取值数组
 *@param string _optFrom 字段索引数组中的起始元素值(按数值索引升序)
 *@param string _optTo 字段索引数组中的起始元素值(按数值索引升序)
 *@return string
 *			如果 参数有误，返回""
 *			如果 from参数和to参数一致，返回 field='val'
 *			如果 from参数和to参数正常，返回 field in ('val1', 'val2', 'val3') 的条件查询语句
 */
function buildInCondiStr($_fieldName, $_optsAry, $_optFrom, $_optTo){
    if( 0 == count( $_optsAry ) || "" == $_optFrom || "" == $_optTo)	return "";

    if( $_optFrom == $_optTo)		//特殊情况，只有一个值时
        return " {$_fieldName}='{$_optFrom}' ";

    //一般情况
    $tmpAry = array();
    foreach( $_optsAry as $item ){
        //找到数组中的_optFrom项, 0==count() 是因为从找到开始的元素后就不continue了
        if( 0 == count($tmpAry) && $item != $_optFrom )	 continue;
        $tmpAry[] = "'" . $item . "'";
        if( $item == $_optTo ) break;
    }//foreach

    return " {$_fieldName} in (" . implode(',', $tmpAry)." ) ";
}//buildInCondiStr


/**
 * 构建时间数组
 * @desc 生成 0-23 时 0-59 分 用于 select 时间 option 下拉选择
 * @author zyk
 * @return array[
 *      hour => 时
 *      minute => 分
 * ]
 */
function buildHourMinuteAry(){
    $data = [];
    for( $i = 0; $i <= 23; $i++){
        $data['hour'][ sprintf('%02d',$i) ] = sprintf('%02d',$i);
    }
    for( $j = 0; $j <= 59; $j++ ){
        $data['minute'][ sprintf('%02d',$j) ] = sprintf('%02d',$j);
    }
    return $data;
}