<?php
namespace app\api\controller\v1\we_chat;

//微信前台业务处理微信数据回调对接方法
class Business {
    public function authCallback(){
        echo 123333;
    }

    public function messageEvent(){
        echo 12339933;
    }
}