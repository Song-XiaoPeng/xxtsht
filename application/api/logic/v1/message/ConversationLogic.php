<?php
namespace app\api\logic\v1\message;
use think\Model;
use think\Db;
use app\api\common\Common;

class ConversationLogic extends Model {
    /**
     * 拉取客服当前会话
     * @param company_id 商户id
     * @param uid 客服uid
     */
    public function getSessionList($company_id,$uid){
        $message_session = Db::name('message_session')
        ->partition('', '', ['type' => 'md5', 'num' => config('separate')['message_session']])
        ->where([
            'company_id' => $company_id,
            'uid' => $uid
        ])
        ->select();

        
    }
}