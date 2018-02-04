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
            'timeout' => 30.0
        ],
        'debug'  => false,
        'oauth' => [
            'scopes'   => ['snsapi_userinfo'],
            'callback' => '/home/Callback',
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

//获取本月开始时间结束时间
function getMonthTimeSolt(){
    $begin_time = date ( "Y-m-d H:i:s", mktime ( 0, 0, 0, date ( "m" ), 1, date ( "Y" ) ) );
    
    $end_time = date ( "Y-m-d H:i:s", mktime ( 23, 59, 59, date ( "m" ), date ( "t" ), date ( "Y" ) ) );

    return [
        'begin_time' => $begin_time,
        'end_time' => $end_time
    ];
}

//获取昨天开始时间结束时间
function getYesTerdayTimeSolt(){
    $begin_time = date ( "Y-m-d 00:00:00", strtotime("-1 day"));
    
    $end_time = date ( "Y-m-d 23:59:59");

    return [
        'begin_time' => $begin_time,
        'end_time' => $end_time
    ];
}

//获取当天开始时间结束时间
function getDayTimeSolt(){
    $begin_time = date ( "Y-m-d 00:00:00");
    
    $end_time = date ( "Y-m-d 23:59:59");

    return [
        'begin_time' => $begin_time,
        'end_time' => $end_time
    ];
}

//获取本周开始时间结束时间
function getWeekTimeSolt(){
    $sdefaultDate = date("Y-m-d");

    $first = 1;
    
    $w = date('w',strtotime($sdefaultDate));
    
    $begin_time = date('Y-m-d',strtotime("$sdefaultDate -".($w ? $w - $first : 6).' days'));
    
    $week_end = date('Y-m-d',strtotime("$begin_time +6 days"));

    return [
        'begin_time' => $begin_time,
        'end_time' => $week_end
    ];
}

//检验手机号码
function checkPhone($phone){
    if(preg_match("/^1[34578]{1}\d{9}$/",$phone)){  
        return true;
    }else{  
        return false;
    }  
}

//计算相差天数
function distanceDay($time){
    return floor((strtotime(date('YmdHis'))-strtotime($time))/86400);
}

//生成新郎短域名
function getDomainName($url){
    $url = 'http://api.t.sina.com.cn/short_url/shorten.json?source=3271760578&url_long='.$url;

    $client = new \GuzzleHttp\Client();
    $res = $client->request('GET', $url);

    $arr = json_decode($res->getBody(),true);

    return $arr[0]['url_short'];
}

/**
 * 时间差计算
 * @param Timestamp $time
 * @return String Time Elapsed
 */
function timediff($begin_time, $end_time){ 
    $begin_time = strtotime($begin_time);
    $end_time = strtotime($end_time);

    if($begin_time < $end_time){ 
        $starttime = $begin_time; 
        $endtime = $end_time; 
    }else{ 
        $starttime = $end_time; 
        $endtime = $begin_time; 
    } 

    $timediff = $endtime-$starttime; 
    $days = intval($timediff/86400); 
    $remain = $timediff%86400; 
    $hours = intval($remain/3600); 
    $remain = $remain%3600; 
    $mins = intval($remain/60); 
    $secs = $remain%60; 
    $res = array("day" => $days,"hour" => $hours,"min" => $mins,"sec" => $secs); 
    
    return $res; 
}

/**
 * 关闭无效会话中数据
 */
function differenceMinute($time){
    $tiem_arr = timediff(date('YmdHis'), $time);
    $min1 = $tiem_arr['day'] * 24 * 60;
    $min2 = $tiem_arr['hour'] * 60;
    $min3 = $tiem_arr['min'];
    return $min1 + $min2 + $min3;
}

/**
+----------------------------------------------------------
* 生成随机字符串
+----------------------------------------------------------
* @param int       $length  要生成的随机字符串长度
* @param string    $type    随机码类型：0，数字+大小写字母；1，数字；2，小写字母；3，大写字母；4，特殊字符；-1，数字+大小写字母+特殊字符
+----------------------------------------------------------
* @return string
+----------------------------------------------------------
*/
function randCode($length = 12, $type = 0) {
    $arr = array(1 => '0123456789'.date('YmdHis'), 2 => "abcdefghijklmnopqrstuvwxyz", 3 => "ABCDEFGHIJKLMNOPQRSTUVWXYZ", 4 => "~@#$%^&*(){}[]|");
    if ($type == 0) {
        array_pop($arr);
        $string = implode("", $arr);
    }else if($type == "-1") {
        $string = implode("", $arr);
    }else{
        $string = $arr[$type];
    }
    $count = strlen($string) - 1;
    $code = '';
    for($i = 0; $i < $length; $i++){
        $code .= $string[rand(0, $count)];
    }
    return $code;
}

//删除文件夹所有文件
function deleteAll($path) {
    $op = dir($path);
    while(false != ($item = $op->read())) {
        if($item == '.' || $item == '..') {
            continue;
        }
        if(is_dir($op->path.'/'.$item)) {
            deleteAll($op->path.'/'.$item);
            rmdir($op->path.'/'.$item);
        } else {
            unlink($op->path.'/'.$item);
        }
    }   

    rmdir($path);
}

//取随机小数
function randFloat($min=0, $max=1){
    return round($min + mt_rand()/mt_getrandmax() * ($max-$min), 2);
}

/**
 * 返回16位md5值
 *
 * @param string $str 字符串
 * @return string $str 返回16位的字符串
 */
function short_md5($str) {
    return substr(md5($str), 8, 16);
}

//解析url参数
function convertUrlQuery($query){
    $queryParts = explode('&', $query);
    $params = array();
    foreach ($queryParts as $param) {
        $item = explode('=', $param);
        $params[$item[0]] = $item[1];
    }
    return $params;
}

//匹配微信模板消息字段内容
function extractWxTemplate($content = ''){
    if (!preg_match_all('/{{([^<]*).DATA}}/isU', $content, $matches)) {
        return [];
    }
    $input = array();
    foreach ($matches[1] as $key => $value) {
        $input[] = $value;
    }

    return $input;
}

/**
 * 导出CSV文件
 * @param array $data        数据
 * @param array $header_data 首行数据
 * @param string $file_name  文件名称
 * @return string
 */
function exportCsv($data = [], $header_data = [], $file_name = ''){
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename='.$file_name);
    header('Cache-Control: max-age=0');
    $fp = fopen('php://output', 'a');
    if (!empty($header_data)) {
        foreach ($header_data as $key => $value) {
            $header_data[$key] = iconv('utf-8', 'gbk', $value);
        }
        fputcsv($fp, $header_data);
    }
    $num = 0;
    //每隔$limit行，刷新一下输出buffer，不要太大，也不要太小
    $limit = 100000;
    //逐行取出数据，不浪费内存
    $count = count($data);
    if ($count > 0) {
        for ($i = 0; $i < $count; $i++) {
            $num++;
            //刷新一下输出buffer，防止由于数据过多造成问题
            if ($limit == $num) {
                ob_flush();
                flush();
                $num = 0;
            }
            $row = $data[$i];
            foreach ($row as $key => $value) {
                $row[$key] = iconv('utf-8', 'gbk', $value);
            }
            fputcsv($fp, $row);
        }
    }
    fclose($fp);
}