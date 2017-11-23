<?php
namespace app\api\common;
use think\Db;

class Common {
    //获取商户公众号或小程序授权信息
    public static function getRefreshToken($appid,$company_id = ''){
        $map['appid'] = $appid;
        if($company_id){
            $map['company_id'] = $company_id;
        }

        $refresh_token = Db::name('openweixin_authinfo')->where($map)->cache(true,120)->value('refresh_token');
        if(!$refresh_token){
            return msg(3001,'appid不存在');
        }
    
        return msg(200,'success',['refresh_token'=>$refresh_token]);
    }

    /**
     * 增加会话消息记录
     * @param appid 公众号或小程序appid
     * @param openid 用户微信openid
     * @param session_id 会话id
     * @param customer_service_id 客服id
     * @param type 消息类型 0其他 1文本 2图片 3语音 4视频 5坐标 6链接
     * @param data_obj 数据对象
	 * @return code 200->成功
	 */
    public static function addMessagge($appid,$openid,$session_id,$customer_service_id,$uid,$type,$data_obj){
        $time = date('Y-m-d H:i:s');

        switch($type){
            case '1':
                $arr['text'] = emoji_encode($data_obj['text']);
                break;
            case '2':
                $arr['file_url'] = $data_obj['file_url'];
                break;
            case '3':
                $arr['file_url'] = $data_obj['file_url'];
                break;
            case '4':
                $arr['file_url'] = $data_obj['file_url'];
                break;
            case '5':
                $arr['lng'] = $data_obj['lng'];
                $arr['lat'] = $data_obj['lat'];
                break;
            case '6':
                $arr['text'] = $data_obj['text'];
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
            'opercode' => 2,
            'message_type' => $type,
            'session_id' => $session_id,
            'uid' => $uid,
        ];

        $add_data = array_merge($arr,$insert_arr);

        $insert_res = Db::name('message_data')->insert($add_data);
        if($insert_res){
            return true;
        }else{
            return false;
        }
    }
}