<?php
//api返回数据方法
function msg($code,$msg,$arr = ''){
    $result['meta']['code'] = $code;
    $result['meta']['msg'] = $msg;
    $result['body'] = $arr != '' ? $arr : null;
    return $result;
}

//组合apiurl
function combinationApiUrl($url){
    return config('auth_server_url').$url;
}

//微信授权参数
function wxOptions(){
    return  [
        'open_platform' => [
            'app_id' => config('app_id'),
            'secret' => config('app_secret'),
            'token' => config('wx_msg_token'),
            'aes_key' => config('wx_aes_key')
        ]
    ];
}