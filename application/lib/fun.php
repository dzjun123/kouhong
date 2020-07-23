<?php
function daddslashes($string, $force = 0, $strip = FALSE) {
    if ($force || !get_magic_quotes_gpc()) {
        if (is_array($string)) {
            foreach ($string as $key => $val) {
                $string[$key] = daddslashes($val, $force, $strip);
            }
        } else {
            $string = addslashes($strip ? stripslashes($string) : $string);
        }
    }
    return $string;
}


//去斜杠
function slashes($string) {
    if (is_array($string)) {
        foreach ($string as $key => $val) {
            $string[$key] = slashes($val);
        }
    } else {
        $string = trim(stripslashes($string));
    }
    return $string;
}

function redis() {
    global $config;
    require_once(HH_ROOT.'/lib/Rediscache.php');
    return new Rediscache($config['REDIS_HOST'], $config['REDIS_PORT'], $config['REDIS_PWD'], $config['REDIS_DB']);
}

//初始化
function init() {
	//自动加载类
    spl_autoload_register(function ($class) {
    	if(file_exists(HH_ROOT . "/control/$class.php") && !class_exists($class)) {
	    	include_once(HH_ROOT . "/control/$class.php");
    	} else if (file_exists(HH_ROOT . "/model/$class.php") && !class_exists($class)) {
    		include_once(HH_ROOT . "/model/$class.php");
    	} else if (file_exists(HH_ROOT . "/lib/$class.php") && !class_exists($class)) {
    		include_once(HH_ROOT . "/lib/$class.php");
    	}
	});
}

function checkEmail($email) {
    $pregEmail = "/^[0-9a-z][a-z0-9\._-]{1,}@[a-z0-9-]{1,}[a-z0-9]\.[a-z\.]{1,}[a-z]$/i";
    return preg_match($pregEmail, $email);
}

//计算中文字符长度
function utf8_strlen($string = null) {
	preg_match_all("/./us", $string, $match);
	return count($match[0]);
}

/**
 * 获取客户端IP地址
 * @param integer $type 返回类型 0 返回IP地址 1 返回IPV4地址数字
 * @return mixed
 */
function get_client_ip($type = 0) {
    $type = $type ? 1 : 0;
    static $ip = NULL;
    if ($ip !== NULL)
        return $ip[$type];
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $pos = array_search('unknown', $arr);
        if (false !== $pos)
            unset($arr[$pos]);
        $ip = trim($arr[0]);
    }elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (isset($_SERVER['REMOTE_ADDR'])) {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    // IP地址合法验证
    $long = sprintf("%u", ip2long($ip));
    $ip = $long ? array($ip, $long) : array('127.0.0.1', 0);
    return $ip[$type];
}

/**
 * 生成随机数
 */
function randKey($length) {
    $str = 'abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randString = '';
    $len = strlen($str) - 1;
    for ($i = 0; $i < $length; $i++) {
        $num = mt_rand(0, $len);
        $randString .= $str[$num];
    }
    return $randString;
}

/**
 *
 * @param type $url   
 * @param type $data  //参数
 * @param type $input //是否开启php输入流,默认不开启
 * @return type 
 */
function curl_post($url,$data,$input = 0) {
	//'http://192.168.21.222/682public_api/api/'
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	if($input) {
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Content-Type: application/json',
			'Content-Length: ' . strlen($data))
		);
	}
	$result = curl_exec($ch);
	curl_close($ch);
	return $result;
}

function checkargs($param,$defVal = "",$_trim = true) {
	if(is_array($param)) {
		foreach($param as $k => $v) {
			$args[$k] = checkargs($v);
		}
	}else {
        $args = !utf8_strlen($param)?'':$param;
		if($_trim) {
            $args = strip_tags($args);
			$args = trim($args);
		}
	}
	return $args;
}

/**
 * 生成($num * 2)位随机码
 */
function ssl_random($num = 4){
	 $bytes = openssl_random_pseudo_bytes($num);
	 $hex   = bin2hex($bytes);
	 return $hex;
}

/**
 * 验证整数
 * @param int $num 整数
 */
function checkint($num) {
	if(empty($num)) {
		 return 0;
	}
	if(preg_match("/^\d*$/",$num)) {
		return $num;
	}else {
		return 0;
	}
}
/**
 *  curl
 */
function https_get($url) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);	// 要求结果为字符串且输出到屏幕上
	curl_setopt($ch, CURLOPT_HEADER, 0); // 不要http header 加快效率
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
	curl_setopt($ch, CURLOPT_TIMEOUT, 250);
	curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,1);
	curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
	$output = curl_exec($ch);
	curl_close($ch);
//    echo $output;exit;
	$output = json_decode($output,true);
	return $output;

}


//检查手机号格式
function check_phone($phone) {
    if(preg_match('/^[1][34578]\d{9}$/', $phone)) {
        return true;
    }else {
        return false;
    }
}

//密码加密
function mt_password($password, $salt) {
    return hash('sha256',md5($password).$salt);
}

//检查密码格式
function check_passwd($passwd) {
	//是否纯数字
    if (preg_match("/^[0-9]{6,12}$/i", $passwd)) {
        return false;
    }
	//是否纯字母
    if (preg_match("/^[a-zA-Z]{6,12}$/i", $passwd)) {
        return false;
    }
	//是否6-12位数字、字母和非空格符号
    if (!preg_match("/^\S{6,12}$/i", $passwd)) {
        return false;
    }
    return true;
}

//检查数字格式
function check_is_num($num, $type) {
    switch ($type) {
        //整数
        case 'int':
            return preg_match('/^-?\d+$/i', $num);
            break;
        //整数/小数
        case 'double':
            return preg_match('/^-?\d+(\.\d+)?$/i', $num);
            break;
        //正整数
        case 'posInt':
            return preg_match('/\d+$/i', $num);
            break;
        //正整数/小数
        case 'posDouble':
            return preg_match('/\d+(\.\d+)?$/i', $num);
            break;
    }
}