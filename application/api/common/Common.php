<?php

namespace app\api\common;
use think\Db;
use EasyWeChat\Foundation\Application;
use EasyWeChat\Message\Material;
use EasyWeChat\Message\Image;
use think\Log;
use EasyWeChat\Message\Text;

class Common
{
    //获取商户公众号或小程序授权信息
    public static function getRefreshToken($appid, $company_id = '')
    {
        $map['appid'] = $appid;
        if ($company_id) {
            $map['company_id'] = $company_id;
        }

        $openweixin_authinfo_res = Db::name('openweixin_authinfo')->where($map)->cache(true, 30)->find();
        if (!$openweixin_authinfo_res) {
            return msg(3001, 'appid不存在');
        }

        $time = time();

        $days = round(($time - $openweixin_authinfo_res['refresh_time']) / 3600 / 24);
        if ($days >= 3) {
            try {
                $app = new Application(wxOptions());
                $openPlatform = $app->open_platform;
                $auth_res = $openPlatform->getAuthorizerInfo($appid);
            } catch (\Exception $e) {
                return msg(3003, $e->getMessage());
            }

            $refresh_token = $auth_res['authorization_info']['authorizer_refresh_token'];

            Db::name('openweixin_authinfo')->where($map)->update(['refresh_time' => $time, 'refresh_token' => $refresh_token]);
        } else {
            $refresh_token = $openweixin_authinfo_res['refresh_token'];
        }

        return msg(200, 'success', ['refresh_token' => $refresh_token]);
    }

    /**
     * 增加会话消息记录
     * @param appid 公众号或小程序appid
     * @param openid 用户微信openid
     * @param session_id 会话id
     * @param customer_service_id 客服id
     * @param type 消息类型 0其他 1文本 2图片 3语音 4视频 5坐标 6图文素材 7链接
     * @param opercode 操作码 1客服发送信息 2客服接收消息 3管理员监控插话 4群聊消息
     * @param data_obj 数据对象
     * @return code 200->成功
     */
    public static function addMessagge($appid, $openid, $session_id, $customer_service_id, $uid, $type, $opercode, $data_obj)
    {
        $time = date('Y-m-d H:i:s');

        switch ($type) {
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

        $company_id = Db::name('openweixin_authinfo')->where(['appid' => $appid])->cache(true, 3600)->value('company_id');

        $message_id = md5(uniqid());

        $insert_arr = [
            'message_id' => $message_id,
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

        $add_data = array_merge($arr, $insert_arr);

        $insert_res = Db::name('message_data')
            ->partition(['customer_wx_openid' => $openid], "customer_wx_openid", ['type' => 'md5', 'num' => config('separate')['message_data']])
            ->insert($add_data);

        if ($opercode == 1 || $opercode == 3) {
            Db::name('message_session')
                ->partition(['session_id' => $session_id], 'session_id', ['type' => 'md5', 'num' => config('separate')['message_session']])
                ->where(['session_id' => $session_id])
                ->update(['send_time' => $time]);

            if ($opercode == 3) {
                $redis = self::createRedis();
                $redis->select(config('redis_business')['message']);
                $redis->zAdd($uid, time(), json_encode($add_data));
            }
        } else if ($opercode == 2) { //客服接收消息
            Db::name('message_session')
                ->partition(['session_id' => $session_id], 'session_id', ['type' => 'md5', 'num' => config('separate')['message_session']])
                ->where(['session_id' => $session_id])
                ->update(['receive_message_time' => $time]);

            if (empty($customer_service_id) == false && empty($uid) == false) {
                $redis = self::createRedis();
                $redis->select(config('redis_business')['message']);
                $redis->zAdd($uid, time(), json_encode($add_data));
            }
        } else if ($opercode == 4) {//群聊
            $redis = self::createRedis();
            $redis->select(config('redis_business')['message']);
            //查找群聊的所有uid
            $uids = Db::name('message_session_group')->where('session_id', $session_id)->column('uid');
            //查找uid对应的微信头像
            $avator = Db::name('user_portrait')->where('uid', $uid)->value('resources_id');
            if ($uids) {
                foreach ($uids as $v) {
                    $add_data['customer_service_avator'] = empty($avator) ? "" : "http://kf.lyfz.net/api/v1/we_chat/Business/getImg?resources_id=" . $avator;
                    $redis->zAdd($v, time(), json_encode($add_data));
                }
            }
        } else {
            return msg(3001, 'opercode参数错误');
        }

        if ($insert_res) {
            return msg(200, 'success', ['message_id' => $message_id]);
        } else {
            return msg(3002, '插入数据失败');
        }
    }

    /**
     * 记录微信用户与公众号交互时间
     * @param appid 公众号或小程序appid
     * @param openid 用户微信openid
     * @return code 200->成功
     */
    public static function setWxUserLastTime($appid, $openid)
    {
        $company_id = Db::name('openweixin_authinfo')->where(['appid' => $appid])->cache(true, 60)->value('company_id');
        if (empty($company_id)) {
            return false;
        }

        $time = date('Y-m-d H:i:s');

        $update_res = Db::name('wx_user')
            ->partition(['company_id' => $company_id], "company_id", ['type' => 'md5', 'num' => config('separate')['wx_user']])
            ->where(['appid' => $appid, 'openid' => $openid])
            ->update(['last_time' => $time]);

        if ($update_res !== false) {
            return true;
        } else {
            return false;
        }
    }

    //创建redis连接
    public static function createRedis()
    {
        $redis = new \Predis\Client([
            'host' => config('redis_host'),
            'port' => config('redis_port'),
            'password' => config('redis_password'),
        ]);

        return $redis;
    }

    /**
     * 获取下属账号uid
     * @param company_id 商户company_id
     * @param uid 登录账号uid
     * @return code 200->成功
     */
    public static function getAscriptionUidList($company_id, $uid, $user_type)
    {
        $data['company_id'] = $company_id;
        $data['uid'] = $uid;

        $res = \think\Loader::model('FrameworkLogic', 'logic\v1\user')->getSubordinateList($data);
        if ($res['meta']['code'] != 200) {
            return $res;
        }

        $arr = $res['body'];

        $uid_list = [];

        foreach ($arr as $k => $v) {
            array_push($uid_list, $v['uid']);
        }

        array_push($uid_list, $uid);

        return msg(200, 'success', $uid_list);
    }

    /**
     * 发送微信消息
     * @param appid 公众号appid
     * @param openid 消息接收openid
     * @param type 消息类型 1文字 2图片 3图文
     * @param message_data 消息数据
     * @return code 200->成功
     */
    public static function sendWxMessage($data){
        $appid = $data['appid'];
        $openid = $data['openid'];
        $type = $data['type'];
        $message_data = $data['message_data'];

        $authinfo = Db::name('openweixin_authinfo')->where(['appid'=>$appid])->cache(true, 360)->find();
        if (!$authinfo) {
            return msg(3001, 'appid不存在');
        }

        try {
            $token_info = Common::getRefreshToken($appid, $authinfo['company_id']);
            if ($token_info['meta']['code'] == 200) {
                $refresh_token = $token_info['body']['refresh_token'];
            } else {
                return $token_info;
            }
    
            $app = new Application(wxOptions());
            $openPlatform = $app->open_platform;
            $temporary = $openPlatform->createAuthorizerApplication($appid, $refresh_token)->material_temporary;

            switch($type){
                case 1:
                    $message = new Text(['content' => $message_data['content']]);
                    break;
                case 2:
                    $cache_key = $appid.$message_data['resources_id'].'_img_media';

                    if (empty(cache($cache_key))) {
                        $resources_res = Db::name('resources')->where(['resources_id' => $message_data['resources_id']])->find();
                        if (!$resources_res) {
                            return msg(3003, '资源不存在');
                        }
    
                        $upload_res = $temporary->uploadImage('..' . $resources_res['resources_route']);

                        cache($cache_key, $upload_res, 21600);
                    }else{
                        $upload_res = cache($cache_key);
                    }

                    $message = new Image(['media_id' => $upload_res['media_id']]);
                    break;
                case 3:
                    $message = new Material('mpnews', $message_data['media_id']);
                    break;
                default:
                    return msg(3002,'不支持的消息类型');
            }
    
            $staff = $openPlatform->createAuthorizerApplication($appid, $refresh_token)->staff;
            $staff->message($message)->to($openid)->send();
        } catch (\Exception $e) {
            return msg(3008, $e->getMessage());
        }

        return msg(200, 'success');
    }

}