<?php
namespace app\api\controller\v1\message;
use app\api\common\Auth;

//客户提醒相关操作
class Remind extends Auth{
	/**
     * 增加客户提醒
     * 请求类型 post
	 * 传入JSON格式: {"remind_content":"测试","wx_user_id":"0b45c030270e41d0d873713077107ad1","remind_time":"2017-12-08 20:20:12"}
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
}