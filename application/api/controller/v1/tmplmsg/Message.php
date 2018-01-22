<?php
namespace app\api\controller\v1\tmplmsg;
use app\api\common\Auth;

class Message extends Auth{
	/**
     * 获取所有模板列表
     * 请求类型 post
	 * 请求JSON格式: {"appid":"wxe30d2c612847beeb"}
	 * 返回JSON格式: 
	 * API_URL_本地: http://localhost:91/api/v1/tmplmsg/Message/getAllTemplateList
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/tmplmsg/Message/getAllTemplateList
     * @param appid 公众号appid
	 * @return code 200->成功
	 */
	public function getAllTemplateList(){
		$data = input('put.');
		$data['company_id'] = $this->company_id;

		return \think\Loader::model('MessageLogic','logic\v1\tmplmsg')->getAllTemplateList($data);
	}
}