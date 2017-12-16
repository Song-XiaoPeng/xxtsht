<?php
namespace app\api\common;
use think\Db;
use EasyWeChat\Foundation\Application;

class Common {
    //获取商户公众号或小程序授权信息
    public static function getRefreshToken($appid,$company_id = ''){
        $map['appid'] = $appid;
        if($company_id){
            $map['company_id'] = $company_id;
        }

        $openweixin_authinfo_res = Db::name('openweixin_authinfo')->where($map)->cache(true,30)->find();
        if(!$openweixin_authinfo_res){
            return msg(3001,'appid不存在');
        }

        $time = time();

        $days = round(($time - $openweixin_authinfo_res['refresh_time'])/3600/24);
        if($days >= 3){
            try {
                $app = new Application(wxOptions());
                $openPlatform = $app->open_platform;
                $auth_res = $openPlatform->getAuthorizerInfo($appid);
            } catch (\Exception $e) {
                return msg(3003,$e->getMessage());
            }
            
            $refresh_token = $auth_res['authorization_info']['authorizer_refresh_token'];

            Db::name('openweixin_authinfo')->where($map)->update(['refresh_time'=>$time,'refresh_token'=>$refresh_token]);
        }else{
            $refresh_token = $openweixin_authinfo_res['refresh_token'];
        }
    
        return msg(200,'success',['refresh_token'=>$refresh_token]);
    }

    /**
     * 增加会话消息记录
     * @param appid 公众号或小程序appid
     * @param openid 用户微信openid
     * @param session_id 会话id
     * @param customer_service_id 客服id
     * @param type 消息类型 0其他 1文本 2图片 3语音 4视频 5坐标 6图文素材 7链接
     * @param opercode 操作码 1客服发送信息 2客服接收消息
     * @param data_obj 数据对象
	 * @return code 200->成功
	 */
    public static function addMessagge($appid,$openid,$session_id,$customer_service_id,$uid,$type,$opercode,$data_obj){
        $time = date('Y-m-d H:i:s');

        switch($type){
            case '1':
                $arr['text'] = emoji_encode($data_obj['text']);
                break;
            case '2':
                $arr['file_url'] = $data_obj['file_url'];
                $arr['media_id'] = empty($data_obj['media_id']) == true ? '' : $data_obj['media_id'];
                $arr['resources_id'] = empty($data_obj['resources_id']) == true ? '' : $data_obj['resources_id'];
                break;
            case '3':
                $arr['media_id'] = empty($data_obj['media_id']) == true ? '' : $data_obj['media_id'];
                $arr['resources_id'] = empty($data_obj['resources_id']) == true ? '' : $data_obj['resources_id'];
                $arr['file_url'] = empty($data_obj['file_url']) == true ? '' : $data_obj['file_url'];
                break;
            case '4':
                $arr['media_id'] = empty($data_obj['media_id']) == true ? '' : $data_obj['media_id'];
                $arr['resources_id'] = empty($data_obj['resources_id']) == true ? '' : $data_obj['resources_id'];
                $arr['file_url'] = empty($data_obj['file_url']) == true ? '' : $data_obj['file_url'];
                break;
            case '5':
                $arr['lng'] = $data_obj['lng'];
                $arr['lat'] = $data_obj['lat'];
                $arr['map_scale'] = $data_obj['map_scale'];
                $arr['map_label'] = $data_obj['map_label'];
                $arr['map_img'] = $data_obj['map_img'];
                break;
            case '6':
                $arr['media_id'] = $data_obj['media_id'];
                break;
            case '7':
                $arr['text'] = $data_obj['text'];
                $arr['page_title'] = $data_obj['page_title'];
                $arr['page_desc'] = $data_obj['page_desc'];
                break;
            default:
                return false;
        }

        $company_id = Db::name('openweixin_authinfo')->where(['appid'=>$appid])->cache(true,60)->value('company_id');

        $insert_arr = [
            'message_id' => md5(uniqid()),
            'appid' => $appid,
            'company_id' => $company_id,
            'customer_service_id' => $customer_service_id,
            'customer_wx_openid' => $openid,
            'add_time' => $time,
            'opercode' => $opercode,
            'message_type' => $type,
            'session_id' => $session_id,
            'uid' => $uid,
        ];

        $add_data = array_merge($arr,$insert_arr);

        if($opercode == 1){
            $insert_res = Db::name('message_data')
            ->partition(['customer_wx_openid'=>$openid], "customer_wx_openid", ['type'=>'md5','num'=>10])
            ->insert($add_data);

            Db::name('message_session')
            ->partition(['session_id'=>$session_id], 'session_id', ['type'=>'md5','num'=>config('separate')['message_session']])
            ->where(['session_id'=>$session_id])
            ->update(['send_time'=>$time]);
        }else if($opercode == 2){
            $insert_res = Db::name('message_data')
            ->partition(['customer_wx_openid'=>$openid], "customer_wx_openid", ['type'=>'md5','num'=>10])
            ->insert($add_data);

            Db::name('message_session')
            ->partition(['session_id'=>$session_id], 'session_id', ['type'=>'md5','num'=>config('separate')['message_session']])
            ->where(['session_id'=>$session_id])
            ->update(['receive_message_time'=>$time]);

            $redis = self::createRedis();
            $redis->select(1);
            $redis->zAdd($uid,time(),json_encode($add_data));
        }else{
            return false;
        }

        if($insert_res){
            return true;
        }else{
            return false;
        }
    }

    /**
     * 记录微信用户与公众号交互时间
     * @param appid 公众号或小程序appid
     * @param openid 用户微信openid
	 * @return code 200->成功
	 */
    public static function setWxUserLastTime($appid,$openid){
        $company_id = Db::name('openweixin_authinfo')->where(['appid'=>$appid])->cache(true,60)->value('company_id');
        if(empty($company_id)){
            return false;
        }

        $time = date('Y-m-d H:i:s');

        $update_res = Db::name('wx_user')
        ->partition(['company_id'=>$company_id], "company_id", ['type'=>'md5','num'=>config('separate')['wx_user']])
        ->where(['appid'=>$appid,'openid'=>$openid])
        ->update(['last_time'=>$time]);

        if($update_res !== false){
            return true;
        }else{
            return false;
        }
    }

    //创建redis连接
    public static function createRedis(){
        $redis = new \Predis\Client([
            'host' => config('redis_host'),
            'port' => config('redis_port'),
            'password' => config('redis_password'),
        ]);

        return $redis;
    }
}