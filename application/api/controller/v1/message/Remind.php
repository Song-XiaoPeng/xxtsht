<?php
namespace app\api\controller\v1\message;
use app\api\common\Auth;

//客户提醒相关操作
class Remind extends Auth{
	/**
     * 增加客户提醒
     * 请求类型 post
	 * 传入JSON格式: {"remind_content":"测试","wx_user_id":"0b45c030270e41d0d873713077107ad1","remind_time":"2017-12-08 20:20:12","remind_openid":"oF_-jjmYyxKMsnmN-z0mRWgsLeQI"}
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"},"body":null}
	 * API_URL_本地: http://localhost:91/api/v1/message/Remind/addRemind
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/message/Remind/addRemind
     * @param remind_content 提醒内容
	 * @param wx_user_id 提醒客户微信基础信息id
	 * @param remind_time 提醒时间
	 * @return code 200->成功
	 */
	public function addRemind(){
		$data = input('put.');
		$data['company_id'] = $this->company_id;
		$data['uid'] = $this->uid;

		return \think\Loader::model('RemindLogic','logic\v1\message')->addRemind($data);
	}
	
	/**
     * 获取客户提醒列表
     * 请求类型 post
	 * 传入JSON格式: {"wx_user_id": 12,"page":1}
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"},"body":{"data_list":[{"remind_id":1,"remind_content":"测试","company_id":"51454009d703c86c91353f61011ecf2f","uid":6454,"add_time":"2017-12-04 15:23:38","remind_time":"2017-12-08 20:20:12","wx_user_id":"0b45c030270e41d0d873713077107ad1","remind_openid":"oF_-jjmYyxKMsnmN-z0mRWgsLeQI","wx_user_info":null,"customer_info":null},{"remind_id":2,"remind_content":"测试","company_id":"51454009d703c86c91353f61011ecf2f","uid":6454,"add_time":"2017-12-04 15:33:54","remind_time":"2017-12-08 20:20:12","wx_user_id":"0b45c030270e41d0d873713077107ad1","remind_openid":"oF_-jjmYyxKMsnmN-z0mRWgsLeQI","wx_user_info":null,"customer_info":null},{"remind_id":3,"remind_content":"测试","company_id":"51454009d703c86c91353f61011ecf2f","uid":6454,"add_time":"2017-12-04 15:34:42","remind_time":"2017-12-08 20:20:12","wx_user_id":"0b45c030270e41d0d873713077107ad1","remind_openid":"oF_-jjmYyxKMsnmN-z0mRWgsLeQI","wx_user_info":null,"customer_info":null}],"page_data":{"count":3,"rows_num":16,"page":1}}}
	 * API_URL_本地: http://localhost:91/api/v1/message/Remind/getRemindList
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/message/Remind/getRemindList
	 * @param wx_user_id 提醒的客户微信基础信息id(选传)
	 * @param page 分页参数默认1
	 * @return code 200->成功
	 */
	public function getRemindList(){
		$data = input('put.');
		$data['company_id'] = $this->company_id;
		$data['uid'] = $this->uid;

		return \think\Loader::model('RemindLogic','logic\v1\message')->getRemindList($data);
	}
	
	/**
     * 删除客户提醒
     * 请求类型 post
	 * 传入JSON格式: {"remind_id":"12"}
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"},"body":null}
	 * API_URL_本地: http://localhost:91/api/v1/message/Remind/delRemind
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/message/Remind/delRemind
     * @param remind_id 删除的提醒id
	 * @return code 200->成功
	 */
	public function delRemind(){
		$data = input('put.');

		return \think\Loader::model('RemindLogic','logic\v1\message')->delRemind($data['remind_id'],$this->uid,$this->company_id);
	}

	/**
     * 修改客户提醒时间
     * 请求类型 post
	 * 传入JSON格式: {"remind_id":"1","remind_time":"2017-12-09 12:20:12"}
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"},"body":null}
	 * API_URL_本地: http://localhost:91/api/v1/message/Remind/updateRemindTime
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/message/Remind/updateRemindTime
     * @param remind_id 修改的提醒id
     * @param remind_time 提醒时间
	 * @return code 200->成功
	 */
	public function updateRemindTime(){
		$data = input('put.');

		return \think\Loader::model('RemindLogic','logic\v1\message')->updateRemindTime($data['remind_id'],$this->uid,$this->company_id,$data['remind_time']);
	}
}