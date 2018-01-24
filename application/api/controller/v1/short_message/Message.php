<?php
namespace app\api\controller\v1\short_message;
use app\api\common\Auth;
use think\Loader;
use think\view\driver\Think;

class Message extends Auth{
	/**
     * 短信找回密码
     * 请求类型 post
	 * 请求JSON格式: {"appid":"wx4a14a2375e93cb7b"}
	 * 返回JSON格式:
	 * API_URL_本地: http://localhost:91/api/v1/tmplmsg/Message/getAllTemplateList
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/tmplmsg/Message/getAllTemplateList
     * @param appid 公众号appid
	 * @return code 200->成功
	 */
    public function sendVerifyCode()
    {
        $data = input('put.');
        $data['company_id'] = $this->company_id;

        return Loader::model('ShortMessageLogic','logic\v1\short_message')->getVerifyCode($data);
	}

	//修改密码
    public function resetPassword()
    {
        $data = input('put.');

        $data['company_id'] = $this->company_id;
        return Loader::model('ShortMessageLogic','logic\v1\short_message')->resetPassword($data);
    }
}