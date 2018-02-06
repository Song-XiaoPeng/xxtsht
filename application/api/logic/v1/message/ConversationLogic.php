<?php
namespace app\api\logic\v1\message;
use think\Model;
use think\Db;
use app\api\common\Common;

class ConversationLogic extends Model {
    /**
     * 拉取客服当前等待中会话中相关会话数据
     * @param company_id 商户id
     * @param uid 客服uid
     */
    public function getSessionList($company_id,$uid){
        $message_session = Db::name('message_session')
        ->partition('', '', ['type' => 'md5', 'num' => config('separate')['message_session']])
        ->where([
            'company_id' => $company_id,
            'uid' => $uid,
            'state' => ['in',[0,1,2]]
        ])
        ->select();

        foreach($message_session as $k=>$v){
            $message_session[$k]['session_frequency'] = 0;

            $message_session[$k]['invitation_frequency'] = 0;

            $message_session[$k]['app_name'] = Db::name('openweixin_authinfo')
            ->where(['appid'=>$v['appid']])
            ->cache(true,3600)
            ->value('nick_name');
        }

        return msg(200,'success',$message_session);
    }
}