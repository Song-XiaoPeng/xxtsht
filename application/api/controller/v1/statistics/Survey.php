<?php
namespace app\api\controller\v1\statistics;
use app\api\common\Auth;

//统计概况
class Survey extends Auth{
	/**
     * 获取首页概况信息
     * 请求类型 get
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"},"body":{"customer_service_total":{"on_line_total":1,"off_line_total":1,"total":2},"visitor_total":{"reception_total":1,"total":5},"response_time":{"pending_response":0,"average_response":0}}}
	 * API_URL_本地: http://localhost:91/api/v1/statistics/Survey/getHomeSurvey
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/statistics/Survey/getHomeSurvey
	 * @return code 200->成功
	 */
	public function getHomeSurvey(){
		return \think\Loader::model('SurveyLogic','logic\v1\statistics')->getHomeSurvey($this->company_id,$this->uid);
    }
    
	/**
     * 获取客服排名
     * 请求类型 post
	 * 传入JSON格式: {"type":"1","start_time":"","end_time":""}
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"},"body":[{"session_total":0,"first_session":0,"effective":0,"effective_percentage":0,"invalid_session":0,"session_missing":0,"own_session":0,"auto_session":0,"active_session":0,"collection":0,"collection_percentage":0,"send_message_total":0,"name":"张山","customer_service_id":6,"uid":6379},{"session_total":0,"first_session":0,"effective":0,"effective_percentage":0,"invalid_session":0,"session_missing":0,"own_session":0,"auto_session":0,"active_session":0,"collection":0,"collection_percentage":0,"send_message_total":0,"name":"张山","customer_service_id":8,"uid":6382},{"session_total":0,"first_session":0,"effective":0,"effective_percentage":0,"invalid_session":0,"session_missing":0,"own_session":0,"auto_session":0,"active_session":0,"collection":0,"collection_percentage":0,"send_message_total":0,"name":"adware","customer_service_id":7,"uid":6450},{"session_total":3,"first_session":2,"effective":5,"effective_percentage":0,"invalid_session":0,"session_missing":0,"own_session":0,"auto_session":5,"active_session":0,"collection":3,"collection_percentage":0,"send_message_total":914,"name":"张丽","customer_service_id":4,"uid":6454}]}
	 * API_URL_本地: http://localhost:91/api/v1/statistics/Survey/getCustomerServiceRanking
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/statistics/Survey/getCustomerServiceRanking
	 * @param type 1今天 2昨天 3近一周 4近一月 5自定义时间段
	 * @param start_time 开始时间
	 * @param end_time 结束时间
	 * @return code 200->成功
	 */
	public function getCustomerServiceRanking(){
		$data = input('put.');
		$data['company_id'] = $this->company_id;
		$data['uid'] = $this->uid;

		return \think\Loader::model('SurveyLogic','logic\v1\statistics')->getCustomerServiceRanking($data);
	}
}