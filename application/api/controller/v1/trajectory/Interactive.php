<?php
namespace app\api\controller\v1\trajectory;
use app\api\common\Auth;

class Interactive extends Auth{
	/**
     * 获取用户操作事件轨迹
     * 请求类型 post
	 * 请求JSON格式: {"appid":"wxe30d2c612847beeb","openid":"oF_-jjmYyxKMsnmN-z0mRWgsLeQI","page":"1"}
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"},"body":{"data_list":[{"operation_id":1,"appid":"wxe30d2c612847beeb","openid":"oF_-jjmYyxKMsnmN-z0mRWgsLeQI","event":1,"create_time":"2018-01-19 16:41:22","company_id":"51454009d703c86c91353f61011ecf2f","desc":"点击公众号菜单->下单订购"},{"operation_id":2,"appid":"wxe30d2c612847beeb","openid":"oF_-jjmYyxKMsnmN-z0mRWgsLeQI","event":1,"create_time":"2018-01-19 16:41:55","company_id":"51454009d703c86c91353f61011ecf2f","desc":"点击公众号菜单->下单订购"},{"operation_id":3,"appid":"wxe30d2c612847beeb","openid":"oF_-jjmYyxKMsnmN-z0mRWgsLeQI","event":1,"create_time":"2018-01-19 16:42:22","company_id":"51454009d703c86c91353f61011ecf2f","desc":"点击公众号菜单->申请试用"},{"operation_id":4,"appid":"wxe30d2c612847beeb","openid":"oF_-jjmYyxKMsnmN-z0mRWgsLeQI","event":1,"create_time":"2018-01-19 16:42:31","company_id":"51454009d703c86c91353f61011ecf2f","desc":"点击公众号菜单->故障处理"},{"operation_id":5,"appid":"wxe30d2c612847beeb","openid":"oF_-jjmYyxKMsnmN-z0mRWgsLeQI","event":1,"create_time":"2018-01-19 16:42:37","company_id":"51454009d703c86c91353f61011ecf2f","desc":"点击公众号菜单->客户见证"}],"page_data":{"count":5,"rows_num":16,"page":"1"}}}
	 * API_URL_本地: http://localhost:91/api/v1/trajectory/Interactive/getEventTrajectory
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/trajectory/Interactive/getEventTrajectory
     * @param appid 公众号或小程序appid
     * @param openid 用户微信openid
     * @param page 分页参数
	 * @return code 200->成功
	 */
	public function getEventTrajectory(){
		$data = input('put.');
		$data['company_id'] = $this->company_id;

		return \think\Loader::model('InteractiveLogic','logic\v1\trajectory')->getEventTrajectory($data);
	}
}