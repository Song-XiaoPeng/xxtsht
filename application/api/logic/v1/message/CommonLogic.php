<?php
namespace app\api\logic\v1\message;
use think\Model;
use think\Db;
use app\api\common\Common;
use app\api\logic\v1\we_chat\BusinessLogic;
use EasyWeChat\Foundation\Application;

class CommonLogic extends Model {
    /**
     * 设置快捷回复语句
     * @param quick_reply_id 存在则是编辑
	 * @param text 快捷回复语句
	 * @return code 200->成功
	 */
    public function setQuickReplyText($data){
        $quick_reply_id = empty($data['quick_reply_id']) == true ? false : $data['quick_reply_id'];
        $reply_group_id = empty($data['reply_group_id']) == true ? -1 : $data['reply_group_id'];
        $text = $data['text'];
        $uid = $data['uid'];
        $company_id = $data['company_id'];

        if($quick_reply_id){
            $update_res = Db::name('quick_reply')
            ->where([
                'quick_reply_id' => $quick_reply_id,
                'company_id' => $company_id,
                'uid' => $uid,
                'type' => 1,
            ])
            ->update([
                'quick_reply_text' => $text,
                'reply_group_id' => $reply_group_id
            ]);

            if($update_res !== false){
                return msg(200,'success');
            }else{
                return msg(3002,'更新数据失败');
            }
        }

        $insert_res = Db::name('quick_reply')
        ->insert([
            'quick_reply_text' => $text,
            'company_id' => $company_id,
            'reply_group_id' => $reply_group_id,
            'uid' => $uid,
            'type' => 1,
        ]);

        if($insert_res){
            return msg(200,'success');
        }else{
            return msg(3001,'更新数据失败');
        }
    }

    /**
     * 获取快捷回复语句
     * @param quick_reply_id 存在则是编辑
	 * @param text 快捷回复语句
	 * @return code 200->成功
	 */
    public function getQuickReplyList($data){
        $company_id = $data['company_id'];
        $uid = $data['uid'];
        $reply_group_id = empty($data['reply_group_id']) == true ? -1 : $data['reply_group_id'];
        $type = empty($data['type']) == true ? 1 : $data['type'];

        $list = Db::name('quick_reply')->where(['company_id'=>$company_id,'uid'=>$uid,'type'=>$type,'reply_group_id'=>$reply_group_id])->select();
    
        foreach($list as $k=>$v){
            $list[$k]['group_name'] = Db::name('quick_reply_group')->where(['company_id'=>$company_id,'reply_group_id'=>$v['reply_group_id']])->value('group_name');
        }

        return msg(200,'success',$list);
    }

    /**
     * 删除快捷回复语句
     * @param quick_reply_id 语句id
	 * @return code 200->成功
	 */
    public function delQuickReply($company_id,$uid,$quick_reply_id){
        $del_res = Db::name('quick_reply')->where(['quick_reply_id'=>$quick_reply_id,'uid'=>$uid,'company_id'=>$company_id,'type'=>1])->delete();

        if($del_res){
            return msg(200,'success');
        }else{
            return msg(3001,'删除失败');
        }
    }

    /**
     * 接入排队中会话
     * @param company_id 商户company_id
     * @param uid 客服uid
     * @param session_id 排队中session_id
	 * @return code 200->成功
	 */
    public function accessQueuingSession($company_id,$uid,$session_id){
        $redis = Common::createRedis();
        $redis->select(2); 

        $time = date('Y-m-d H:i:s');

        $session_list = $redis->sMembers($company_id);

        foreach($session_list as $v){
            $val = json_decode($v,true);
        
            if($val['session_id'] == $session_id){
                //判断会话是否来自小程序
                $auth_info = Db::name('openweixin_authinfo')->where(['company_id'=>$company_id,'appid'=>$val['appid']])->cache(true,60)->find();
                if(!$auth_info){
                    return msg(3004,'公众号或小程序已解绑会话无法接入');
                }

                if($auth_info['type'] == 1){
                    $customer_service_id = Db::name('customer_service')->where(['appid'=>$val['appid'],'uid'=>$uid])->cache(true,60)->value('customer_service_id');
                    if(!$customer_service_id){
                        return msg(3005,'未获取到客服基础信息');
                    }
                }else{
                    $customer_service_id = Db::name('customer_service')->where(['company_id'=>$company_id,'uid'=>$uid])->cache(true,60)->value('customer_service_id');
                    if(!$customer_service_id){
                        return msg(3006,'未获取到客服基础信息');
                    }
                }

                $update_res = Db::name('message_session')
                ->partition(['session_id'=>$session_id], 'session_id', ['type'=>'md5','num'=>config('separate')['message_session']])
                ->where(['session_id'=>$session_id,'state'=>3])
                ->update([
                    'customer_service_id' => $customer_service_id,
                    'uid' => $uid,
                    'state' => 1,
                ]);

                Db::name('wx_user')
                ->partition(['company_id'=>$company_id], "company_id", ['type'=>'md5','num'=>config('separate')['wx_user']])
                ->where(['appid'=>$val['appid'],'openid'=>$val['customer_wx_openid']])
                ->update(['customer_service_uid'=>$uid,'is_clue'=>-1,'last_time'=>$time]);

                if($update_res){
                    $redis->SREM($company_id, $v);

                    //插入会话消息
                    Db::name('message_data')
                    ->partition(['customer_wx_openid'=>$val['customer_wx_openid']], "customer_wx_openid", ['type'=>'md5','num'=>config('separate')['message_data']])
                    ->where(['session_id'=>$session_id,'company_id'=>$company_id])
                    ->update(['customer_service_id'=>$customer_service_id,'uid'=>$uid]);
 
                    $message_list = Db::name('message_data')
                    ->partition(['customer_wx_openid'=>$val['customer_wx_openid']], "customer_wx_openid", ['type'=>'md5','num'=>config('separate')['message_data']])
                    ->where(['session_id'=>$session_id,'company_id'=>$company_id])
                    ->select();

                    foreach($message_list as $add_data){
                        $redis->select(1);
                        $redis->zAdd($uid,time(),json_encode($add_data));
                    }

                    return msg(200, 'success');
                }else{
                    return msg(3001, '已被其他客服接入');
                }
            }
        }

        return msg(3002, '接入失败');
    }

    /**
     * 创建微信用户会话
     * @param company_id 商户company_id
     * @param openid 客户微信openid
     * @param appid 客户微信appid
     * @param uid 登录账号uid
	 * @return code 200->成功
	 */
    public function createWxUserSession($data){
        $company_id = $data['company_id'];
        $openid = $data['openid'];
        $appid = $data['appid'];
        $uid = $data['uid'];

        $customer_service_res = Db::name('customer_service')
        ->where(['appid'=>$appid,'uid'=>$uid,'company_id'=>$company_id])
        ->find();

        if(!$customer_service_res){
            return msg(3001,'未设置客服账号信息');
        }

        $wx_user_res = Db::name('wx_user')
        ->partition([], "", ['type'=>'md5','num'=>config('separate')['wx_user']])
        ->where(['appid'=>$appid,'openid'=>$openid,'company_id'=>$company_id])
        ->find();

        if($wx_user_res['customer_service_uid'] != $uid && $wx_user_res['customer_service_uid'] != -1){
            $name = Db::name('customer_service')->where(['company_id'=>$company_id,'appid'=>$appid,'uid'=>$wx_user_res['customer_service_uid']])->cache(true,60)->value('name');

            return msg(3002,'此客户是'.$name.'的专属客户不得接入');
        }

        $BusinessLogic = new BusinessLogic();
        $createSession = $BusinessLogic->createSession($appid,$openid,'user',$uid,true,false);
        if($createSession['meta']['code'] == 200){
            Db::name('wx_user')
            ->partition(['company_id'=>$company_id], "company_id", ['type'=>'md5','num'=>config('separate')['wx_user']])
            ->where(['wx_user_id'=>$wx_user_res['wx_user_id'],'company_id'=>$company_id])
            ->setInc('active_count');

            Db::name('wx_user')
            ->partition(['company_id'=>$company_id], "company_id", ['type'=>'md5','num'=>config('separate')['wx_user']])
            ->where(['wx_user_id'=>$wx_user_res['wx_user_id'],'company_id'=>$company_id])
            ->update(['active_time'=>date('Y-m-d H:i:s'),'customer_service_uid'=>$uid]);

            return msg(200,'success',['session_id'=>$createSession['body']['session_id'],'insert_data'=>$createSession['body']['insert_data']]);
        }else{
            return $createSession;
        }
    }

    /**
     * 会话转接
     * @param company_id 商户company_id
     * @param session_id 会话id
     * @param uid 登录账号uid
     * @param uransfer_uid 转接的客服uid
	 * @return code 200->成功
	 */
    public function sessionTransfer($data){
        $company_id = $data['company_id'];
        $session_id = $data['session_id'];
        $uid = $data['uid'];
        $uransfer_uid = $data['uransfer_uid'];

        //判断是否转接给自身
        if($uid == $uransfer_uid){
            return msg(3006,'不允许转接到原账号');
        }

        //获取会话数据
        $session_data = Db::name('message_session')
        ->partition('', '', ['type'=>'md5','num'=>config('separate')['message_session']])
        ->where(['company_id'=>$company_id,'session_id'=>$session_id])
        ->find();
        if(empty($session_data)){
            return msg(3001,'会话不存在');
        }

        //判断客服账号是否存在
        $user_res = Db::name('user')->where(['company_id'=>$company_id,'uid'=>$uransfer_uid])->find();
        if(empty($user_res)){
            return msg(3002,'用户不存在');
        }
        
        //结束原会话
        Db::name('message_session')
        ->partition(['session_id' => $session_id], 'session_id', ['type'=>'md5','num'=>config('separate')['message_session']])
        ->where(['company_id'=>$company_id,'session_id'=>$session_id])
        ->update([
            'state' => -1,
            'close_explain' => '会话转接'
        ]);

        //变更专属客服
        Db::name('wx_user')
        ->partition(['company_id'=>$company_id], "company_id", ['type'=>'md5','num'=>config('separate')['wx_user']])
        ->where(['wx_user_id'=>$session_data['wx_user_id']])
        ->update([
            'customer_service_uid' => $uransfer_uid
        ]);

        //创建新会话
        return \think\Loader::model('BusinessLogic','logic\v1\we_chat')
        ->createSession(
            $session_data['appid'],
            $session_data['customer_wx_openid'],
            'user',
            $uransfer_uid,
            true
        );
    }

    /**
     * 获取会话微信用户基本信息
     * @param company_id 商户company_id
     * @param openid 客户微信openid
     * @param appid 客户微信appid
	 * @return code 200->成功
	 */
    public function getWxUserInfo($company_id,$openid,$appid){
        $user_info = Db::name('wx_user')
        ->partition([], '', ['type'=>'md5','num'=>config('separate')['wx_user']])
        ->where(['company_id'=>$company_id,'appid'=>$appid,'openid'=>$openid])
        ->find();

        $position_locus = Db::name('geographical_position')
        ->where(['company_id'=>$company_id,'appid'=>$appid,'openid'=>$openid])
        ->limit(20)
        ->cache(true,60)
        ->select();

        $session_frequency = Db::name('message_session')
        ->partition([], '', ['type'=>'md5','num'=>config('separate')['message_session']])
        ->where(['customer_wx_openid'=>$openid,'appid'=>$appid,'company_id'=>$company_id])
        ->cache(true,60)
        ->count();
        
        $invitation_frequency = 0;

        $label_arr = [];

        $tagid_list = json_decode($user_info['tagid_list'],true);
        if(!empty($tagid_list)){
            foreach($tagid_list as $v){
                $label_id = Db::name('label_tag')->where(['tag_id'=>$v,'company_id'=>$company_id])->cache(true,60)->value('label_id');
                if($label_id){
                    $label_res = Db::name('label')->where(['label_id'=>$label_id,'company_id'=>$company_id])->cache(true,60)->find();
    
                    array_push($label_arr,$label_res);
                }
            }
        }

        return msg(200,'success',[
            'user_info' => $user_info,
            'position_locus' => $position_locus,
            'session_frequency' => $session_frequency,
            'invitation_frequency' => $invitation_frequency,
            'label' => $label_arr
        ]);
    }

    /**
     * 强制发送会话消息
     * @param company_id 商户company_id
     * @param uid 客服uid
     * @param session_id 会话id
     * @param message 消息内容
     * @param type 消息类型 1文字 2图片 3声音 4视频 6图文信息素材
     * @param resources_id 资源id (图片 视频 声音)
     * @param media_id 素材id (图文素材)
     * @param link_url 链接 (链接)
     * @param link_name 链接名称 (链接)
	 * @return code 200->成功
	 */
    public function forcedSendMessage($data){
        $company_id = $data['company_id'];
        $content = empty($data['message']) === true ? '' : $data['message'];
        $type = $data['type'];
        $uid = $data['uid'];
        $session_id = $data['session_id'];
        $media_id = empty($data['media_id']) == true ? '' : $data['media_id'];
        $resources_id = empty($data['resources_id']) == true ? '' : $data['resources_id'];
        $link_url = empty($data['link_url']) == true ? '' : $data['link_url'];
        $link_name = empty($data['link_name']) == true ? '' : $data['link_name'];

        $session_res = Db::name('message_session')
        ->partition('', '', ['type'=>'md5','num'=>config('separate')['message_session']])
        ->where([
            'uid' => $uid,
            'company_id' => $company_id,
            'session_id' => $session_id,
            'state' => 1,
        ])->cache(true,10)->find();

        if(empty($session_res)){
            return msg(3001,'会话不存在');
        }

        $token_info = Common::getRefreshToken($session_res['appid'],$company_id);
        if($token_info['meta']['code'] == 200){
            $refresh_token = $token_info['body']['refresh_token'];
        }else{
            return $token_info;
        }

        $app = new Application(wxOptions());
        $openPlatform = $app->open_platform;
        $broadcast  = $openPlatform->createAuthorizerApplication($session_res['appid'],$refresh_token)->broadcast;

        if($type == 2 || $type == 3 || $type == 4 || $type == 8){
            $resources_res = Db::name('resources')->where(['resources_id'=>$resources_id])->find();
            
            if(!$resources_res){return msg(3005,'资源不存在');}

            $temporary = $openPlatform->createAuthorizerApplication($session_res['appid'],$refresh_token)->material_temporary;
        }

        switch($type){
            case 1:
                try {
                    $broadcast->previewText($content, $session_res['customer_wx_openid']);
                }catch (\Exception $e) {
                    return msg(3010, $e->getMessage());
                }

                $data_obj = ['text'=>$content];
                break;
            case 2:
                $upload_res = $temporary->uploadImage('..'.$resources_res['resources_route']);

                try {
                    $broadcast->previewImage($upload_res['media_id'], $session_res['customer_wx_openid']);
                }catch (\Exception $e) {
                    return msg(3011, $e->getMessage());
                }

                $data_obj = ['file_url'=>$resources_res['resources_route'],'resources_id'=>$resources_res['resources_id']];
                break;
            case 3:
                if($resources_res['mime_type'] == 'audio/x-wav'){
                    $audio_name = md5(uniqid());
                    
                    $amr_file = '..'.$resources_res['resources_route'];
                
                    $mp3_file = "../uploads/source_material/$audio_name.mp3";
    
                    $command = "/usr/local/bin/ffmpeg -i $amr_file $mp3_file";
    
                    exec($command, $log, $status);

                    if(!file_exists($mp3_file)){
                        return msg(3004,'file_error');
                    }

                    $upload_res = $temporary->uploadVoice($mp3_file);
                    unlink($mp3_file);
                }else if($resources_res['mime_type'] == 'audio/mpeg'){
                    $upload_res = $temporary->uploadVoice('..'.$resources_res['resources_route']);
                }else{
                    return msg(3008,'File types do not support');
                }

                try {
                    $broadcast->previewVoice($upload_res['media_id'], $session_res['customer_wx_openid']);
                }catch (\Exception $e) {
                    return msg(3012, $e->getMessage());
                }

                $data_obj = ['file_url'=>$resources_res['resources_route'],'resources_id'=>$resources_res['resources_id']];
                break;
            case 4:
                $upload_res = $temporary->uploadVideo('..'.$resources_res['resources_route']);

                try {
                    $broadcast->previewVideo([$upload_res['media_id'], '视频消息', '点击查看视频消息'], $session_res['customer_wx_openid']);
                }catch (\Exception $e) {
                    return msg(3013, $e->getMessage());
                }

                $data_obj = ['file_url'=>$resources_res['resources_route'],'resources_id'=>$resources_res['resources_id']];
                break;
            case 6:
                try {
                    $broadcast->previewNews($media_id, $session_res['customer_wx_openid']);
                }catch (\Exception $e) {
                    return msg(3013, $e->getMessage());
                }

                $data_obj = ['media_id'=>$media_id];
                break;
            default:
                return msg(3006,'type参数错误');
        }

        $send_res = Common::addMessagge($session_res['appid'],$session_res['customer_wx_openid'],$session_id,$session_res['customer_service_id'],$session_res['uid'],$type,1,$data_obj);
        if($send_res['meta']['code'] == 200){
            return msg(200,'success',['message_id'=>$send_res['body']['message_id']]);
        }else{
            return $send_res;
        }
    }
}