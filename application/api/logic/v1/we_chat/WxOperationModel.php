<?php
namespace app\api\logic\v1\we_chat;
use think\Model;
use think\Db;
use EasyWeChat\Foundation\Application;
use think\Config;

//微信后台操作业务类
class WxOperationModel extends Model {
    public function assemblingConfing($app_id,$secret){
        $options = Config::get('wechat');
        $options['app_id'] = $app_id;
        $options['secret'] = $secret;

        return $options;
    }

    public function test(){
        $app = new Application(
            $this->assemblingConfing('wx4ab51a2534acaffb','cd93cb0e1bb06382c21fd89f9d5eccc7')
        );

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
        $response->send();
        
    
        
        exit;
        $openPlatform = $app->open_platform;
        $openPlatform->pre_auth->getCode();
        
        dump($app);
    }
}