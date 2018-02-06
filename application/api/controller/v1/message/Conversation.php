<?php
namespace app\api\controller\v1\message;
use app\api\common\Auth;

class Conversation extends Auth{
	/**
     * 拉取客服当前等待中会话中相关会话数据
     * 请求类型 get
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"},"body":[{"session_id":"43892018c4f2b21712b66c0804c11376","customer_service_id":151,"customer_wx_openid":"oZ8DFwf1NoKWyq-yO1_DKZ5FDaoE","add_time":"2018-02-05 17:04:26","state":1,"uid":8105,"appid":"wx88c6052d06eaaf7d","company_id":"51454009d703c86c91353f61011ecf2f","previous_customer_service_id":null,"customer_wx_nickname":"Junwen","customer_wx_portrait":"http:\/\/wx.qlogo.cn\/mmopen\/YCOL3hU8ffVqHh18vG77VtZ9KpWGX7PFunR9cO0oTbS1SqmS8DBUJ4EZibAeFc36BKmQkIfrdJQQJSFBo5oKRp4yKO0Lg3Az0\/132","wx_user_id":"b0f5ee144ffdad208d9c5a6f8b54dd8d","receive_message_time":"2018-02-05 18:22:43","send_time":"2018-02-05 17:46:53","close_explain":null,"session_frequency":0,"invitation_frequency":0,"app_name":"网鱼服务营销平台"},{"session_id":"1ac1c104697c7ff03ed0ab29821fa29c","customer_service_id":150,"customer_wx_openid":"oF_-jjmYyxKMsnmN-z0mRWgsLeQI","add_time":"2018-02-05 17:54:48","state":1,"uid":8105,"appid":"wxe30d2c612847beeb","company_id":"51454009d703c86c91353f61011ecf2f","previous_customer_service_id":null,"customer_wx_nickname":"Junwen","customer_wx_portrait":"http:\/\/wx.qlogo.cn\/mmopen\/jJtbwFuzNwCX0UtgFMYZj1HDCv1qpzHqOZoU81iaEStqJtgXicuMNdrs1CFpNBmn4d9j2pejibGiczXKLHtmiaicR2R9XEUzic7CDfv\/132","wx_user_id":"9f9b0fdd0f35d6ff8c693f6df6bd76cc","receive_message_time":"2018-02-06 09:33:48","send_time":"2018-02-06 09:32:48","close_explain":null,"session_frequency":0,"invitation_frequency":0,"app_name":"利亚方舟影楼管理软件"}]}
	 * API_URL_本地: http://localhost:91/api/v1/message/Conversation/getSessionList
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/message/Conversation/getSessionList
	 * @return code 200->成功
	 */
	public function getSessionList(){
		return \think\Loader::model('ConversationLogic','logic\v1\message')->getSessionList($this->company_id,$this->uid);
	}
}