<?php
namespace app\api\controller\v1\message;
use app\api\common\Auth;

class Conversation extends Auth{
	/**
     * 拉取客服当前等待中会话中相关会话数据
     * 请求类型 get
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"},"body":null}
	 * API_URL_本地: http://localhost:91/api/v1/message/Conversation/getSessionList
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/message/Conversation/getSessionList
	 * @return code 200->成功
	 */
	public function getSessionList(){
		return \think\Loader::model('ConversationLogic','logic\v1\message')->getSessionList($this->company_id,$this->uid);
	}
}