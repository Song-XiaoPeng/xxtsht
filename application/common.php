<?php
//api返回数据方法
function msg($code,$msg,$arr = ''){
    $result['meta']['code'] = $code;
    $result['meta']['message'] = $msg;
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

//emoji表情转义
function emoji_encode($nickname){
    $strEncode = '';
    $length = mb_strlen($nickname,'utf-8');
    for ($i=0; $i < $length; $i++) {
        $_tmpStr = mb_substr($nickname,$i,1,'utf-8');
        if(strlen($_tmpStr) >= 4){
            $strEncode .= '[[EMOJI:'.rawurlencode($_tmpStr).']]';
        }else{
            $strEncode .= $_tmpStr;
        }
    }
    return $strEncode;
}

//emoji表情反转义
function emoji_decode($str){
    $strDecode = preg_replace_callback('|\[\[EMOJI:(.*?)\]\]|', function($matches){	
        return rawurldecode($matches[1]);
    }, $str);

    return $strDecode;
}