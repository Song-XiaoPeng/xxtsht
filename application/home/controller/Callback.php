<?php
namespace app\home\controller;
use think\Db;
use EasyWeChat\Foundation\Application;
use app\api\common\Common;
use think\Session;

class callback{
    public function index(){
        $data = input('get.');

        $auth_res = Db::name('openweixin_authinfo')->where(['appid'=>$data['appid']])->find();

        $token_info = Common::getRefreshToken($auth_res['appid'], $auth_res['company_id']);
        if ($token_info['meta']['code'] == 200) {
            $refresh_token = $token_info['body']['refresh_token'];
        } else {
            return $token_info;
        }
          
        $app = new Application(wxOptions());
        $openPlatform = $app->open_platform;
        $oauth = $openPlatform->createAuthorizerApplication($auth_res['appid'],$refresh_token)->oauth;

        $wx_user_info = $oauth->user();

        Session::set('wx_user_info', $wx_user_info->toArray());

        header('location:'. Session::get('jump_url'));
    }
}
