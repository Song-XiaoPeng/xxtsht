<?php
namespace app\api\logic\v1\message;
use think\Model;
use think\Db;
use app\api\common\Common;

class CommonLogic extends Model {
    /**
     * 设置快捷回复语句
     * @param quick_reply_id 存在则是编辑
	 * @param text 快捷回复语句
	 * @return code 200->成功
	 */
    public function setQuickReplyText($data){
        $quick_reply_id = empty($data['quick_reply_id']) == true ? false : $data['quick_reply_id'];
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
                'quick_reply_text' => $text
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
    public function getQuickReplyList($company_id,$uid,$type = 1){
        $list = Db::name('quick_reply')->where(['company_id'=>$company_id,'uid'=>$uid,'type'=>$type])->select();
    
        return msg(200,'success',$list);
    }

    /**
     * 删除快捷回复语句
     * @param quick_reply_id 语句id
	 * @return code 200->成功
	 */
    public function delQuickReply($company_id,$uid,$quick_reply_id){
        $del_res = Db::name('quick_reply')->where(['quick_reply_id'=>$quick_reply_id,'uid'=>$uid,'company_id'=>$company_id])->delete();

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

        $session_list = $redis->sMembers($company_id);

        foreach($session_list as $v){
            $val = json_decode($v,true);
        
            if($val['session_id'] == $session_id){
                $customer_service_id = Db::name('customer_service')->where(['appid'=>$val['appid'],'uid'=>$uid])->cache(true,60)->value('customer_service_id');
                if(!$customer_service_id){
                    return msg(3003,'未获取到客服基础信息');
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
                ->update(['customer_service_uid'=>$uid]);

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

        $createSession = $this->createSession($appid,$openid,'user',$uid,true);
        if($createSession['meta']['code'] == 200){
            return msg(200,'success',['session_id'=>$createSession['body']['session_id']]);
        }else{
            return $createSession;
        }
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
                $label_id = Db::name('label_tag')->where(['tag_id'=>$v])->cache(true,60)->value('label_id');
                if($label_id){
                    $label_res = Db::name('label')->where(['label_id'=>$label_id])->find();
    
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
}