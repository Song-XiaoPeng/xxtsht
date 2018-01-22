<?php
namespace app\api\controller\v1\tmplmsg;
use app\api\common\Auth;

class Mass extends Auth{
	/**
     * 添加群发模板消息任务
     * 请求类型 post
	 * 请求JSON格式: {"appid":"wx4a14a2375e93cb7b","template_id":"BQrJpIHncCAk3BBDuLSAL_2VhWLd1eWLiKkkHiLWRYc","template_data":"template_data...","template_url":"template_url...","type":"1"}
	 * 返回JSON格式: 
	 * API_URL_本地: http://localhost:91/api/v1/tmplmsg/Mass/addMassTmplMsg
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/tmplmsg/Mass/addMassTmplMsg
     * @param appid 公众号appid
     * @param template_id 群发的模板id
     * @param template_data 群发模板数据
     * @param template_url 模板跳转链接
     * @param type 群发类型 1全部用户 2指定分组
	 * @return code 200->成功
	 */
	public function addMassTmplMsg(){
		$data = input('put.');
		$data['company_id'] = $this->company_id;

		return \think\Loader::model('MassLogic','logic\v1\tmplmsg')->addMassTmplMsg($data);
	}
}