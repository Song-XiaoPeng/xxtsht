<?php
namespace app\api\logic\v1\we_chat;
use think\Model;
use think\Db;
use EasyWeChat\Foundation\Application;
use EasyWeChat\OpenPlatform\Guard;
use think\Log;
use app\api\common\Common;

class BusinessLogic extends Model {
    private $default_message = '';

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
        $openid = $data['openid'];

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
                $returnMessage = $this->clickEvent($appid,$openid,$message);
                break;
            case 'text':
                $returnMessage = $this->textEvent($appid,$openid,$message['Content']);
                break;
            case 'image':
                $returnMessage = $this->imgEvent($appid,$openid,$message);
                break;
            case 'voice':
                $returnMessage = $this->voiceEvent($appid,$openid,$message);
                break;
            case 'video':
                $returnMessage = $this->videoEvent($appid,$openid,$message);
                break;
            case 'location':
                $returnMessage = $this->locationEvent($appid,$openid,$message);
                break;
            case 'link':
                $returnMessage = $this->linkEvent($appid,$openid,$message['Url']);
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
     * 视频消息处理
     * @param appid 公众号或小程序appid
     * @param openid 用户openid
     * @param message_arr 消息数据
	 * @return code 200->成功
	 */
    private function videoEvent($appid,$openid,$message_arr){
        //判断是否存在客服会话
        $session_res = $this->getSession($appid,$openid);
        if($session_res){
            $add_res = Common::addMessagge(
                $appid,
                $openid,
                $session_res['session_id'],
                $session_res['customer_service_id'],
                $session_res['uid'],
                4,
                2,
                ['media_id'=>$message_arr['MediaId']]
            );

            if($add_res){
                return '';
            }else{
                return '系统繁忙请稍候重试!';
            }
        }
    }

    /**
     * 语音消息处理
     * @param appid 公众号或小程序appid
     * @param openid 用户openid
     * @param message_arr 消息数据
	 * @return code 200->成功
	 */
    private function voiceEvent($appid,$openid,$message_arr){
        //判断是否存在客服会话
        $session_res = $this->getSession($appid,$openid);
        if($session_res){
            $add_res = Common::addMessagge(
                $appid,
                $openid,
                $session_res['session_id'],
                $session_res['customer_service_id'],
                $session_res['uid'],
                3,
                2,
                ['media_id'=>$message_arr['MediaId']]
            );

            if($add_res){
                return '';
            }else{
                return '系统繁忙请稍候重试!';
            }
        }
    }

    /**
     * 文本消息处理
     * @param appid 公众号或小程序appid
     * @param openid 用户openid
     * @param key_word 关键词
	 * @return code 200->成功
	 */
    private function textEvent($appid,$openid,$key_word){
        //判断是否存在客服会话
        $session_res = $this->getSession($appid,$openid);
        if($session_res){
            if(Common::addMessagge($appid,$openid,$session_res['session_id'],$session_res['customer_service_id'],$session_res['uid'],1,2,['text'=>$key_word])){
                return '';
            }else{
                return '系统繁忙请稍候重试!';
            }
        }

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
     * 位置消息处理
     * @param appid 公众号或小程序appid
     * @param openid 用户openid
     * @param message_arr 消息内容
	 * @return code 200->成功
	 */
    private function locationEvent($appid,$openid,$message_arr){
        //判断是否存在客服会话
        $session_res = $this->getSession($appid,$openid);
        if($session_res){
            $add_res = Common::addMessagge(
                $appid,
                $openid,
                $session_res['session_id'],
                $session_res['customer_service_id'],
                $session_res['uid'],
                5,
                2,
                ['map_scale'=>$message_arr['Scale'],'map_label'=>$message_arr['Label'],'map_img'=>'','lng'=>$message_arr['Location_Y'],'lat'=>$message_arr['Location_X']]
            );

            if($add_res){
                return '';
            }else{
                return '系统繁忙请稍候重试!';
            }
        }
    }

    /**
     * 链接消息处理
     * @param appid 公众号或小程序appid
     * @param openid 用户openid
     * @param key_word 关键词
	 * @return code 200->成功
	 */
    private function linkEvent($appid,$openid,$key_word){
        //判断是否存在客服会话
        $session_res = $this->getSession($appid,$openid);
        if($session_res){
            $client = new \GuzzleHttp\Client();
            $request_res = $client->request('GET', $key_word, [
                'timeout' => 3,
            ]);
            $html = $request_res->getBody();

            if(!empty($html)){
                $page_title = get_title($html);
            }else{
                $page_title = '无法获取标题';
            }

            $add_res = Common::addMessagge(
                $appid,
                $openid,
                $session_res['session_id'],
                $session_res['customer_service_id'],
                $session_res['uid'],
                6,
                2,
                ['text'=>$key_word,'page_title'=>$page_title,'page_desc'=>'暂无页面描述']
            );

            if($add_res){
                return '';
            }else{
                return '系统繁忙请稍候重试!';
            }
        }
    }

    /**
     * 图片消息处理
     * @param appid 公众号或小程序appid
     * @param openid 用户openid
     * @param message_arr 消息数据
	 * @return code 200->成功
	 */
    private function imgEvent($appid,$openid,$message_arr){
        //判断是否存在客服会话
        $session_res = $this->getSession($appid,$openid);
        if(!$session_res){
            return '';
        }

        $add_res = Common::addMessagge(
            $appid,
            $openid,
            $session_res['session_id'],
            $session_res['customer_service_id'],
            $session_res['uid'],
            2,
            2,
            ['file_url'=>$message_arr['PicUrl'],'media_id'=>$message_arr['MediaId']]
        );

        if($add_res){
            return '';
        }else{
            return '系统繁忙请稍候重试!';
        }
    }

    /**
     * 获取用户会话
     * @param appid 公众号或小程序appid
     * @param openid 用户微信openid
	 * @return code 200->成功
	 */
    private function getSession($appid,$openid){
        $res = Db::name('message_session')
        ->partition('', '', ['type'=>'md5','num'=>config('separate')['message_session']])
        ->where(['appid'=>$appid,'customer_wx_openid'=>$openid,'state'=>1])
        ->find();

        if($res){
            return [
                'session_id'=>$res['session_id'],
                'uid'=>$res['uid'],
                'customer_service_id'=>$res['customer_service_id']
            ];
        }else{
            return false;
        }
    }

    /**
     * 事件消息处理
     * @param appid 公众号或小程序appid
     * @param openid 用户微信openid
     * @param message 消息对象
	 * @return code 200->成功
	 */
    private function clickEvent($appid,$openid,$message){
        if(!empty($message['EventKey'])){
            $event_arr = explode('_',$message['EventKey']);
            switch($event_arr[0]){
                case 'kf':
                    $id = empty($event_arr[2]) == true ? '' :  $event_arr[2];

                    return $this->createSession($appid,$openid,$event_arr[1],$id);
                    break;
                case 'qrscene':
                    return $this->qrcodeEvent($appid,$openid,$event_arr[1]);
                    break;
                default:
                    return $this->default_message;
                    break;
            }
        }else{
            return '';
        }
    }

    /**
     * 二维码来源用户处理
     * @param appid 公众号或小程序appid
     * @param openid 用户微信openid
     * @param qrcode_id 二维码id
	 * @return code 200->成功
	 */
    private function qrcodeEvent($appid,$openid,$qrcode_id){
        $info = $this->addWxUserInfo($appid,$openid,$qrcode_id);

        if(!$info['is_update']){
            Db::name('extension_qrcode')->where(['qrcode_id'=>$qrcode_id])->setInc('attention');
        }

        $qrcode_res = Db::name('extension_qrcode')->where(['qrcode_id'=>$qrcode_id,'is_del'=>-1])->cache(true,60)->find();
        if(!$qrcode_res){
            return '欢迎关注！';
        }

        if(!empty($qrcode_res['customer_service_id'])){
            $this->createSession($appid,$openid,'user',$qrcode_res['customer_service_id']);
        }

        if(!empty($qrcode_res['customer_service_group_id'])){
            $this->createSession($appid,$openid,'group',$qrcode_res['customer_service_group_id']);
        }

        return '欢迎关注！';
    }

    /**
     * 创建客服会话
     * @param appid 公众号或小程序appid
     * @param openid 用户微信openid
     * @param type 分配类型 user->指定到具体的客服 group->指定到具体的客服分组
     * @param id 分配的客服id或客服分组id
	 * @return code 200->成功
	 */
    private function createSession($appid,$openid,$type,$id = ''){
        $company_id = Db::name('openweixin_authinfo')->where(['appid'=>$appid])->cache(true,120)->value('company_id');
        if(empty($company_id)){
            return '公众号未绑定第三方平台';
        }

        //匹配是否存在待接入或排队中会话数据
        try {
            $redis = Common::createRedis();
            $redis->select(0);

            $line_session_list = $redis->sMembers($company_id);
            foreach($line_session_list as $v){
                $session_data = json_decode($v,true);

                if($session_data['customer_wx_openid'] == $openid && $session_data['appid'] == $appid){
                    if(empty($session_data['customer_service_id'])){
                        return '正在为您分配客服';
                    }

                    $customer_service_name = Db::name('customer_service')->where(['customer_service_id'=>$session_data['customer_service_id']])->value('name');
                    
                    return '正在为您接入客服'.$customer_service_name.'请稍等！';
                }
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }

        //匹配是否存在正在会话中数据
        $session_res = Db::name('message_session')
        ->partition('', '', ['type'=>'md5','num'=>config('separate')['message_session']])
        ->where(['appid'=>$appid,'customer_wx_openid'=>$openid,'state'=>array('in',[0,1,3])])
        ->find();
        if($session_res){
            if($session_res['state'] == 0 || $session_res['state'] == 1){
                $customer_service_name = Db::name('customer_service')->where(['customer_service_id'=>$session_res['customer_service_id']])->value('name');
            }

            switch($session_res['state']){
                case 0:
                    return '正在为您接入客服'.$customer_service_name.'请稍等！';
                    break;
                case 1:
                    return '客服'.$customer_service_name.'正在为您服务！';
                    break;
                case 3:
                    return '正在为您分配客服，请稍等！';
                    break;
            }
        }

        switch($type){
            case 'user':
                $customer_service_res = Db::name('customer_service')->where(['appid'=>$appid,'state'=>1,'uid'=>$id])->find();
                if(empty($customer_service_res)){
                    return '暂无可分配的客服！';
                }
                break;

            case 'group':
                $list = Db::name('customer_service')->where(['appid'=>$appid,'state'=>1,'user_group_id'=>$id])->select();
                if(empty($list)){
                    return '暂无可分配的客服！';    
                }

                $customer_service_res = array_rand($list);
                break;

            case 'other':
                return '暂未开通';
                break;

            default:
                return $this->default_message;
        }

        $customer_service_id = $customer_service_res['customer_service_id'];
        $customer_service_uid = $customer_service_res['uid'];
        $company_id = $customer_service_res['company_id'];
        $customer_service_name = $customer_service_res['name'];

        $wx_info = $this->addWxUserInfo($appid,$openid,'',$customer_service_uid);
        if(empty($wx_info)){
            return '系统繁忙';
        }

        try{
            $insert_data = [
                'session_id' => md5(uniqid()),
                'customer_service_id' => $customer_service_id,
                'customer_wx_openid' => $openid,
                'add_time' => date('Y-m-d H:i:s'),
                'uid' => $customer_service_uid,
                'appid' => $appid,
                'company_id' => $company_id,
                'customer_wx_nickname' => $wx_info['nickname'],
                'customer_wx_portrait' => $wx_info['headimgurl'],
                'state' => 0,
                'wx_user_id' => $wx_info['wx_user_id']
            ];

            $add_res = $redis->sAdd($company_id, json_encode($insert_data));

            if($add_res){
                return '正在为您接入客服'.$customer_service_name.'请稍等！';
            }else{
                return '系统繁忙';
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * 添加微信用户信息
     * @param appid 公众号或小程序appid
     * @param openid 用户微信openid
     * @param qrcode_id 二维码id
	 * @return code 200->成功
	 */
    private function addWxUserInfo($appid,$openid,$qrcode_id = '',$uid = ''){
        $time = date('Y-m-d H:i:s');

        $authinfo_res = Db::name('openweixin_authinfo')->where(['appid'=>$appid])->cache(true,60)->find();
        if(empty($authinfo_res)){
            return;
        }

        $company_id = $authinfo_res['company_id'];

        try{
            $token_info = Common::getRefreshToken($appid,$company_id);
            if($token_info['meta']['code'] == 200){
                $refresh_token = $token_info['body']['refresh_token'];
            }else{
                return;
            }

            $app = new Application(wxOptions());
            $openPlatform = $app->open_platform;
            
            $userService = $openPlatform->createAuthorizerApplication($appid,$refresh_token)->user;
            $wx_info = $userService->get($openid);
        }catch (\Exception $e) {
            Log::record($e->getMessage());
            return;
        }

        $wx_user_res = Db::name('wx_user')
        ->partition(['company_id'=>$company_id], "company_id", ['type'=>'md5','num'=>config('separate')['wx_user']])
        ->where(['appid'=>$appid,'openid'=>$openid])
        ->find();

        if(!$wx_user_res['customer_info_id']){
            $customer_info_id = md5(uniqid());

            Db::name('customer_info')
            ->insert([
                'customer_info_id' => $customer_info_id,
                'company_id' => $company_id,
                'add_time' => $time,
                'uid' => $uid,
            ]);
        }else{
            $customer_info_id = $wx_user_res['customer_info_id'];
        }

        if($wx_user_res){
            $update_map = [
                'nickname' => $wx_info['nickname'],
                'portrait' => $wx_info['headimgurl'],
                'gender' => $wx_info['sex'],
                'city' => $wx_info['city'],
                'province' => $wx_info['province'],
                'language' => $wx_info['language'],
                'country' => $wx_info['country'],
                'groupid' => $wx_info['groupid'],
                'subscribe_time' => date("Y-m-d H:i:s",$wx_info['subscribe_time']),
                'desc' => $wx_info['remark'],
                'company_id' => $company_id,
                'tagid_list' => $wx_info['tagid_list'],
                'unionid' => $wx_info['unionid'],
                'is_sync' => 1,
                'subscribe' => $wx_info['subscribe'],
                'update_time' => $time,
                'customer_info_id' => $customer_info_id,
            ];

            if(!empty($qrcode_id)){
                $update_map['qrcode_id'] = $qrcode_id;
            }

            Db::name('wx_user')
            ->partition(['company_id'=>$company_id], "company_id", ['type'=>'md5','num'=>config('separate')['wx_user']])
            ->where(['appid'=>$appid,'openid'=>$openid])
            ->update($update_map);

            $wx_info['is_update'] = true;
            $wx_info['wx_user_id'] = $wx_user_res['wx_user_id'];

            return $wx_info;
        }

        $wx_user_id = md5(uniqid());

        $wx_user_count = Db::name('wx_user')
        ->partition(['company_id'=>$company_id], "company_id", ['type'=>'md5','num'=>config('separate')['wx_user']])
        ->insert([
            'wx_user_id' => $wx_user_id,
            'nickname' => $wx_info['nickname'],
            'portrait' => $wx_info['headimgurl'],
            'gender' => $wx_info['sex'],
            'city' => $wx_info['city'],
            'province' => $wx_info['province'],
            'language' => $wx_info['language'],
            'country' => $wx_info['country'],
            'groupid' => $wx_info['groupid'],
            'subscribe_time' => date("Y-m-d H:i:s",$wx_info['subscribe_time']),
            'openid' => $openid,
            'add_time' => $time,
            'appid' => $appid,
            'desc' => $wx_info['remark'],
            'company_id' => $company_id,
            'tagid_list' => $wx_info['tagid_list'],
            'unionid' => $wx_info['unionid'],
            'is_sync' => 1,
            'subscribe' => $wx_info['subscribe'],
            'update_time' => $time,
            'qrcode_id' => $qrcode_id,
            'customer_info_id' => $customer_info_id,
        ]);

        $wx_info['is_update'] = false;
        $wx_info['wx_user_id'] = $wx_user_id;
        
        return $wx_info;
    }

    /**
     * 获取素材内容
     * @param company_id 商户company_id
     * @param appid 公众号或小程序appid
     * @param media_id 素材id
     * @param type 素材类型 1图片 2视频 3语音
	 * @return code 200->成功
	 */
    public function getMaterial($appid,$company_id,$media_id,$type){
        switch($type){
            case 1:
                $content_type = 'image/png';
                break;
            case 2:
                $content_type = 'video/mp4';
                break;
            case 3:
                $content_type = 'audio/mp3';
                break; 
            default:
                return msg(3003,'type参数错误');
        }

        $token_info = Common::getRefreshToken($appid,$company_id);
        if($token_info['meta']['code'] == 200){
            $refresh_token = $token_info['body']['refresh_token'];
        }else{
            return $token_info;
        }

        try{
            $app = new Application(wxOptions());
            $openPlatform = $app->open_platform;
            $temporary = $openPlatform->createAuthorizerApplication($appid,$refresh_token)->material_temporary;
            
            $content = $temporary->getStream($media_id);

            //语音amr转mp3
            if($type == 3){
                $audio_name = md5(uniqid());
                if(!file_put_contents('../uploads/source_material/'.$audio_name.'.amr', $content, true)){
                    return msg(3005,'file_error');
                }

                $amr_file = "../uploads/source_material/$audio_name.amr";

                $mp3_file = "../uploads/source_material/$audio_name.mp3";

                $command = "/usr/local/bin/ffmpeg -i $amr_file $mp3_file";

                exec($command, $log, $status);

                if(!file_exists($mp3_file)){
                    return msg(3004,'file_error');
                }

                $PSize = filesize($mp3_file);
                $picture_data = fread(fopen($mp3_file, "r"), $PSize);

                unlink($amr_file);
                unlink($mp3_file);

                return response($picture_data)->contentType($content_type);
            }

            return response($content)->contentType($content_type);
        }catch (\Exception $e) {
            return msg(3001,$e->getMessage());
        }
    }

    /**
     * 获取上传的图片资源流数据
     * @param resources_id 资源id
	 * @return code 200->成功
	 */
    public function getImg($resources_id){
        $res = Db::name('resources')->where([
            'resources_id'=>$resources_id
        ])->find();
        if(!$res){
            header("Content-Type:image/png");
            $im = imagecreate(300, 300);
            $black = imagecolorallocate($im, 100, 100, 100);
            $white = imagecolorallocate($im, 255, 255, 255);
            imagettftext($im, 18, 0, 105, 100, $white, "../uploads/static/fonts/hwxh.ttf", "Error 003");
            imagettftext($im, 14, 0, 65, 150, $white, "../uploads/static/fonts/hwxh.ttf", "图片不存在或已过期！");
            imagepng($im);
            exit();
        }

        $file_ize = filesize('..'.$res['resources_route']);
        $picture_data = fread(fopen('..'.$res['resources_route'], "r"), $file_ize);

        return response($picture_data)->contentType($res['mime_type']);
    }

    /**
     * 获取上传的资源流数据
     * @param resources_id 资源id
	 * @return code 200->成功
	 */
    public function getFile($resources_id){
        $res = Db::name('resources')->where([
            'resources_id'=>$resources_id
        ])->find();
        if(!$res){
            return msg(3001,'not_file');
        }

        $file_ize = filesize('..'.$res['resources_route']);
        $picture_data = fread(fopen('..'.$res['resources_route'], "r"), $file_ize);

        return response($picture_data)->contentType($res['mime_type']);
    }

    //获取微信外链图片
    public function getWxUrlImg($url){
        $im = imagecreate(300, 300);
        $black = imagecolorallocate($im, 100, 100, 100);
        $white = imagecolorallocate($im, 255, 255, 255);

        if (!$url) {
            header("Content-Type:image/png");
            imagettftext($im, 18, 0, 105, 100, $white, "../uploads/static/fonts/hwxh.ttf", "Error 001");
            imagettftext($im, 14, 0, 10, 150, $white, "../uploads/static/fonts/hwxh.ttf", "请在参数中输入图片的绝对地址！");
            imagepng($im);
            exit();
        }
        @$imgString = urlOpen($url);
        if ($imgString == "") {
            header("Content-Type:image/png");
            imagettftext($im, 18, 0, 105, 100, $white, "../uploads/static/fonts/hwxh.ttf", "Error 002");
            imagettftext($im, 14, 0, 10, 150, $white, "../uploads/static/fonts/hwxh.ttf", "加载远程图片失败地址无法访问！");
            imagepng($im);
            exit();
        }

        return response($imgString)->contentType('image/png');
    }
}