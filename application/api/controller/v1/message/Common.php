<?php
namespace app\api\controller\v1\message;
use app\api\common\Auth;

//客户信息相关操作
class Common extends Auth{
    /**
     * 设置快捷回复语句
     * 请求类型 post
	 * 传入JSON格式: {"quick_reply_id":"","text":"测试快捷回复语句"}
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"},"body":null}
	 * API_URL_本地: http://localhost:91/api/v1/message/Common/setQuickReplyText
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/message/Common/setQuickReplyText
     * @param quick_reply_id 存在则是编辑
	 * @param text 客户微信openid
	 * @return code 200->成功
	 */
	public function setQuickReplyText(){
        $data = input('put.');
        $data['company_id'] = $this->company_id;
        $data['uid'] = $this->uid;
        
        return \think\Loader::model('CommonModel','logic\v1\message')->setQuickReplyText($data);
	}

    /**
     * 获取快捷回复语句
     * 请求类型 get
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"},"body":null}
	 * API_URL_本地: http://localhost:91/api/v1/message/Common/getQuickReplyList
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/message/Common/getQuickReplyList
	 * @return code 200->成功
	 */
	public function getQuickReplyList(){
        return \think\Loader::model('CommonModel','logic\v1\message')->getQuickReplyList($this->company_id,$this->uid);
	}
}