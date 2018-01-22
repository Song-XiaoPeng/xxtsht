<?php
namespace app\api\controller\v1\tmplmsg;
use app\api\common\Auth;

class Message extends Auth{
	/**
     * 获取所有消息模板列表
     * 请求类型 post
	 * 请求JSON格式: {"appid":"wx4a14a2375e93cb7b"}
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

	/**
     * 添加消息模板
     * 请求类型 post
	 * 请求JSON格式: {"appid":"wx4a14a2375e93cb7b","short_id":"TM00015"}
	 * 返回JSON格式: 
	 * API_URL_本地: http://localhost:91/api/v1/tmplmsg/Message/addTemplate
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/tmplmsg/Message/addTemplate
     * @param appid 公众号appid
     * @param short_id 添加的模板ID
	 * @return code 200->成功
	 */
	public function addTemplate(){
		$data = input('put.');
		$data['company_id'] = $this->company_id;

		return \think\Loader::model('MessageLogic','logic\v1\tmplmsg')->addTemplate($data);
	}

	/**
     * 删除消息模板
     * 请求类型 post
	 * 请求JSON格式: {"appid":"wx4a14a2375e93cb7b","template_id":"BQrJpIHncCAk3BBDuLSAL_2VhWLd1eWLiKkkHiLWRYc"}
	 * 返回JSON格式: 
	 * API_URL_本地: http://localhost:91/api/v1/tmplmsg/Message/delTemplate
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/tmplmsg/Message/delTemplate
     * @param appid 公众号appid
     * @param template_id 删除的模板ID
	 * @return code 200->成功
	 */
	public function delTemplate(){
		$data = input('put.');
		$data['company_id'] = $this->company_id;

		return \think\Loader::model('MessageLogic','logic\v1\tmplmsg')->delTemplate($data);
	}
}