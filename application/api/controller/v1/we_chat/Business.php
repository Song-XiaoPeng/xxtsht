<?php
namespace app\api\controller\v1\we_chat;
use \think\Log;
use think\Config;

//微信前台业务处理微信数据回调对接方法
class Business {
    //微信授权事件处理
    public function authCallback(){
        return \think\Loader::model('BusinessModel','logic\v1\we_chat')->authCallback();
    }

    //微信公众号消息与事件处理
    public function messageEvent(){
        return 'success';
    }

    //获取第三方公众号授权链接
    public function getAuthUrl(){
        $company_id = input('get.company_id');

        return \think\Loader::model('BusinessModel','logic\v1\we_chat')->getAuthUrl($company_id);
    }

    //授权成功跳转页面
    public function authCallbackPage(){
        $data = input('get.');

        return \think\Loader::model('BusinessModel','logic\v1\we_chat')->authCallbackPage($data);
    }
}