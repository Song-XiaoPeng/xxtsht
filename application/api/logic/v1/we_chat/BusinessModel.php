<?php
namespace app\api\logic\v1\we_chat;
use think\Model;
use think\Db;
use EasyWeChat\Foundation\Application;
use EasyWeChat\OpenPlatform\Guard;
use think\Log;

class BusinessModel extends Model {
    //微信授权事件处理
    public function authCallback(){
        Log::record('收到微信数据------'.date('YmdHis'));

        $app = new Application(wxOptions());

        $openPlatform = $app->open_platform;
        $server = $openPlatform->server;
        $server->setMessageHandler(function($event) use ($openPlatform) {
            // 事件类型常量定义在 \EasyWeChat\OpenPlatform\Guard 类里
            switch ($event->InfoType) {
                case Guard::EVENT_AUTHORIZED: // 授权成功
                    $authorizationInfo = $openPlatform->getAuthorizationInfo($event->AuthorizationCode);
                    // 保存数据库操作等...
                case Guard::EVENT_UPDATE_AUTHORIZED: // 更新授权
                    // 更新数据库操作等...
                case Guard::EVENT_UNAUTHORIZED: // 授权取消
                    // 更新数据库操作等...
            }
        });
        $response = $server->serve();

        return $response->send();
    }

    //获取第三方公众号授权链接
    public function getAuthUrl($company_id){
        if(empty($company_id)){
            return msg(3001,'缺少company_id参数');
        }

        $app = new Application(wxOptions());

        $openPlatform = $app->open_platform;
        $openPlatform->pre_auth->getCode();

        // 直接跳转
        $response = $openPlatform->pre_auth->redirect("http://kf.lyfz.net/api/v1/we_chat/Business/authCallbackPage?company_id=$company_id");

        // 获取跳转的 URL
        $url = $response->getTargetUrl();
        echo "<script>window.location.href='$url'</script>";
    }

    //授权成功跳转页面
    public function authCallbackPage($data){
        $app = new Application(wxOptions());

        $openPlatform = $app->open_platform;

        $authorization_info = $openPlatform->getAuthorizationInfo()['authorization_info'];

        $authorizer_info = $openPlatform->getAuthorizerInfo($authorization_info['authorizer_appid'])['authorizer_info'];

        $auth_info = Db::name('openweixin_authinfo')->where(['appid'=>$authorization_info['authorizer_appid']])->find();
        if($auth_info){
            if($auth_info['company_id'] != $data['company_id']){
                $company_id = $auth_info['company_id'];
                return msg(3001,"绑定失败,此公众平台或小程序已绑定company_id为:$company_id的账号,请先解绑原账号!");
            }else{
                return msg(3002,"此公众平台或小程序已绑定完成，请勿重复绑定!");
            }
        }

        Db::name('openweixin_authinfo')->insert([
            'appid' => $authorization_info['authorizer_appid'],
            'access_token' => $authorization_info['authorizer_access_token'],
            'refresh_token' => $authorization_info['authorizer_refresh_token'],
            'refresh_time' => strtotime(date('Y-m-d H:i:s')),
            'type' => 1,
            'company_id' => $data['company_id'],
            'nick_name' => $authorizer_info['nick_name'],
            'logo' => $authorizer_info['head_img'],
            'qrcode_url' => $authorizer_info['qrcode_url'],
            'principal_name' => $authorizer_info['principal_name'],
            'account_number' => $authorizer_info['user_name'],
            'alias' => $authorizer_info['alias']
        ]);

        echo '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>授权成功</title><style>.box{height:250px;text-align:center}.btn{text-align:center}#btn{background-color:#0c6;color:#fff;font-size:18px;border:0;padding:5px 20px;border-radius:5px;cursor:pointer}#btn:hover{background-color:#0c0}</style></head><body><div class="box"><img src="http://kf.lyfz.net/static/images/auth_success.png" alt=""></div><div class="btn"><button id="btn">关闭</button></div><script>const btn = document.getElementById("btn");btn.addEventListener("click", () => {window.close();
        }, false)</script></body></html>';
    }

    public function messageEvent($data){
        Log::record(json_encode($data));

        $apc = new Application(wxOptions());
        $openPlatform = $apc->open_platform;
        $app = $openPlatform->createAuthorizerApplication('wx52bf4acbefcf4653','refreshtoken@@@sxQ17aCMDUABbpCNP2WCMHUgOtMfkGz6d9JUCU3_49c');

        $server = $app->server;

        $server->setMessageHandler(function ($message) {
            // $message->FromUserName // 用户的 openid
            // $message->MsgType // 消息类型：event, text....
            return "您好！欢迎关注我!侧耳测试";
        });

        $response = $server->serve();
        return $response->send();
    }
}