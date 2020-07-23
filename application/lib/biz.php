<?php

/**
 * 创建一个32位的会话token
 * @param $_key
 */
function jcToken( $_key = "" ){
    $seed = mt_rand( 100000, 999999 );
    return md5(  $_key.time().$seed );
}