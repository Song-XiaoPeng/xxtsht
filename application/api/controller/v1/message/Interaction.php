<?php
namespace app\api\controller\v1\message;
use app\api\common\Auth;

//消息交互相关操作
class Interaction extends Auth{
	/**
     * 获取客服历史会话列表
     * 请求类型 post
	 * 传入JSON格式: {"page":"1"}
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"},"body":{"data_list":[{"customer_wx_openid":"oZ8DFwf1NoKWyq-yO1_DKZ5FDaoE","state":-1,"uid":6454,"appid":"wx88c6052d06eaaf7d","previous_customer_service_id":null,"customer_wx_nickname":"Junwen","customer_wx_portrait":"http:\/\/wx.qlogo.cn\/mmopen\/YCOL3hU8ffVqHh18vG77VtZ9KpWGX7PFunR9cO0oTbS1SqmS8DBUJxwvI4ZZnY2mQqEb020ULBv1SBcibYeI3ey1BEa0dbSHB\/0","app_name":"网鱼服务营销平台"},{"customer_wx_openid":"oZ8DFwU5HOTs0b4g-P_skZ8wgH7g","state":-1,"uid":6454,"appid":"wx88c6052d06eaaf7d","previous_customer_service_id":null,"customer_wx_nickname":"不亦乐乎","customer_wx_portrait":"http:\/\/wx.qlogo.cn\/mmopen\/WAGxibxwib6EVzMQGErVibk1asnkzib3r2GeiclLKm5J5D5mic4VB9n4f8pWBXJ9aI0rW29CLibGUlwwqIIIF3VataBRC7OqhibY7urF\/0","app_name":"网鱼服务营销平台"}],"page_data":{"count":2,"rows_num":16,"page":"1"}}}
	 * API_URL_本地: http://localhost:91/api/v1/message/Interaction/getHistorySession
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/message/Interaction/getHistorySession
     * @param page 分页参数默认1
	 * @return code 200->成功
	 */
	public function getHistorySession(){
		$data = input('put.');
		$data['company_id'] = $this->company_id;
		$data['uid'] = $this->uid;

		return \think\Loader::model('InteractionModel','logic\v1\message')->getHistorySession($data);
    }
    
	/**
     * 获取客户历史消息记录
     * 请求类型 post
	 * 传入JSON格式: {"page":"1","customer_wx_openid":"oZ8DFwU5HOTs0b4g-P_skZ8wgH7g"}
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"},"body":{"data_list":[{"message_id":"0e19af953b298140b4e83585baee0b1b","company_id":"51454009d703c86c91353f61011ecf2f","appid":"wx88c6052d06eaaf7d","customer_service_id":4,"customer_wx_openid":"oZ8DFwU5HOTs0b4g-P_skZ8wgH7g","add_time":"2017-11-28 16:20:11","opercode":2,"text":"九分节操呢","message_type":1,"file_url":null,"lng":null,"lat":null,"is_read":-1,"session_id":"104d6db707b4e82511748971554358bb","uid":6454,"media_id":null,"page_title":null,"page_desc":null,"map_scale":null,"map_label":null,"map_img":null,"resources_id":null}],"page_data":{"count":419,"rows_num":1,"page":"1"}}}
	 * API_URL_本地: http://localhost:91/api/v1/message/Interaction/getHistoryMessage
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/message/Interaction/getHistoryMessage
     * @param page 分页参数
	 * @param customer_wx_openid 客户微信openid
	 * @return code 200->成功
	 */
	public function getHistoryMessage(){
		$data = input('put.');
		$data['company_id'] = $this->company_id;
		$data['uid'] = $this->uid;

		return \think\Loader::model('InteractionModel','logic\v1\message')->getHistoryMessage($data);
	}
}