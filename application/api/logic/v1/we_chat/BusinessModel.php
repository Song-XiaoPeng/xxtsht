<?php
namespace app\api\logic\v1\we_chat;
use think\Model;
use think\Db;
use EasyWeChat\Foundation\Application;
use EasyWeChat\OpenPlatform\Guard;
use think\Log;
use app\api\common\Common;

class BusinessModel extends Model {
    public $default_message = '系统未识别到您的描述，请再描述一次！';

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

    //微信公众号事件响应处理
    public function messageEvent($data){
        Log::record(json_encode($data));

        $appid = $data['appid'];

        $token_info = Common::getRefreshToken($appid);
        if($token_info['meta']['code'] == 200){
            $refresh_token = $token_info['body']['refresh_token'];
        }else{
            return $token_info;
        }

        $apc = new Application(wxOptions());
        $openPlatform = $apc->open_platform;
        $app = $openPlatform->createAuthorizerApplication($appid,$refresh_token);
        $server = $app->server;

        $message = $server->getMessage();
        switch ($message['MsgType']) {
            case 'event':
                $returnMessage = $this->clickEvent($appid,$message['EventKey']);
                break;
            case 'text':
                $returnMessage = $this->textEvent($appid,$message['Content']);
                break;
            case 'image':
                $returnMessage = '收到图片消息';
                break;
            case 'voice':
                $returnMessage = '收到语音消息';
                break;
            case 'video':
                $returnMessage = '收到视频消息';
                break;
            case 'location':
                $returnMessage = '收到坐标消息';
                break;
            case 'link':
                $returnMessage = '收到链接消息';
                break;
            default:
                $returnMessage = '您好有什么需要帮助吗？';
                break;
        }

        $server->setMessageHandler(function ($message) use ($returnMessage) {
            return $returnMessage;
        });

        $response = $server->serve();
        return $response->send();
    }

    /**
     * 文本消息处理
     * @param appid 公众号或小程序appid
     * @param key_word 关键词
	 * @return code 200->成功
	 */
    private function textEvent($appid,$key_word){
        $map['appid'] = $appid;
        $map1['pattern'] = 2;
        $map['key_word'] = array('like',"%$key_word%");
        $reply_text = Db::name('message_rule')->where($map)->where($map1)->value('reply_text');
        if(!$reply_text){
            $map2['pattern'] = 1;
            $reply_text = Db::name('message_rule')->where($map)->where($map2)->value('reply_text');
        }

        return empty($reply_text) == true ? $this->default_message : emoji_decode($reply_text);
    }

    /**
     * 菜单点击事件处理
     * @param appid 公众号或小程序appid
     * @param event_key 触发下标值
	 * @return code 200->成功
	 */
    private function clickEvent($appid,$event_key){
        $event_arr = explode('_',$event_key);
        if($event_arr[0] != 'kf'){
            return $this->default_message;
        }


        return '系统正在为您转接客服，请稍等！';
    }
}