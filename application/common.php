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
        ],
        'guzzle' => [
            'timeout' => 10.0
        ],
        'debug'  => false
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

//远程下载图片
function getImage($url,$save_dir='',$filename='',$type=0){ 
    if(trim($url)==''){ 
        return array('file_name'=>'','save_path'=>'','error'=>1); 
    } 
    if(trim($save_dir)==''){ 
        $save_dir='./'; 
    }
    if(trim($filename)==''){//保存文件名 
        $ext=strrchr($url,'.'); 
        if($ext!='.gif'&&$ext!='.jpg'&&$ext!='.png'&&$ext!='.jpeg'&&$ext!='.bmp'){ 
            return array('file_name'=>'','save_path'=>'','error'=>3); 
        } 
        $filename=time().rand(0,999999999999).$ext; 
    } 
    if(0!==strrpos($save_dir,'/')){ 
        $save_dir.='/'; 
    } 
    //创建保存目录 
    if(!file_exists($save_dir)&&!mkdir($save_dir,0777,true)){ 
        return array('file_name'=>'','save_path'=>'','error'=>5); 
    } 
    //获取远程文件所采用的方法  
    if($type){ 
        $ch=curl_init(); 
        $timeout=5; 
        curl_setopt($ch,CURLOPT_URL,$url); 
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1); 
        curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout); 
        $img=curl_exec($ch); 
        curl_close($ch); 
    }else{ 
        ob_start();  
        readfile($url); 
        $img=ob_get_contents();  
        ob_end_clean();  
    } 
    //$size=strlen($img); 
    //文件大小  
    $fp2=@fopen($save_dir.$filename,'a'); 
    fwrite($fp2,$img); 
    fclose($fp2); 
    unset($img,$url); 

    return array('file_name'=>$filename,'save_path'=>$save_dir.$filename,'error'=>0); 
}

//获取网页标题
function get_title($html){
    preg_match("/<title>(.*)<\/title>/i",$html,$title);
    return empty($title[1]) == true ? '无法获取标题' : $title[1];
}

//请求微信外链图片数据
function urlOpen($url, $data = null, $ua = ''){
    if ($ua == '') {
        $ua = 'MQQBrowser/26 Mozilla/5.0 (Linux; U; Android 2.3.7; zh-cn; MB200 Build/GRJ22; CyanogenMod-7) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1';
    } else {
        $ua = $ua;
    }
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_USERAGENT, $ua);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $content = curl_exec($ch);
    curl_close($ch);
    return $content;
}

//输出微信外链图片
function getWximg($url){
    return 'http://'.$_SERVER['HTTP_HOST'].'/api/v1/we_chat/Business/getWxUrlImg?url='.$url;
}