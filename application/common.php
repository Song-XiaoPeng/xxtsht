<?php
//api返回数据方法
function msg($code,$msg,$arr = ''){
    $result['meta']['code'] = $code;
    $result['meta']['msg'] = $msg;
    $result['body'] = $arr != '' ? $arr : null;
    return $result;
}