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
	 * @param text 快捷回复语句
	 * @return code 200->成功
	 */
	public function setQuickReplyText(){
        $data = input('put.');
        $data['company_id'] = $this->company_id;
        $data['uid'] = $this->uid;
        
        return \think\Loader::model('CommonLogic','logic\v1\message')->setQuickReplyText($data);
	}

    /**
     * 获取快捷回复语句
     * 请求类型 get
     * 请求参数  type 1个人 2公共
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"},"body":[{"quick_reply_id":1,"quick_reply_text":"测试快捷回复语句","uid":6454,"company_id":"51454009d703c86c91353f61011ecf2f"}]}
	 * API_URL_本地: http://localhost:91/api/v1/message/Common/getQuickReplyList
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/message/Common/getQuickReplyList
	 * @return code 200->成功
	 */
	public function getQuickReplyList(){
        $type = input('get.type',1);

        return \think\Loader::model('CommonLogic','logic\v1\message')->getQuickReplyList($this->company_id,$this->uid,$type);
    }
    
    /**
     * 删除快捷回复语句
     * 请求类型 post
     * 传入JSON格式: {"quick_reply_id":"1"}
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"},"body":[]}
	 * API_URL_本地: http://localhost:91/api/v1/message/Common/delQuickReply
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/message/Common/delQuickReply
     * @param quick_reply_id 删除的语句id
	 * @return code 200->成功
	 */
	public function delQuickReply(){
        $data = input('put.');

        return \think\Loader::model('CommonLogic','logic\v1\message')->getQuickReplyList($this->company_id,$this->uid,$data['quick_reply_id']);
	}

    /**
     * 强制发送会话消息
	 * 请求类型：post
	 * 传入JSON格式: {"session_id":"4f56b7fd6021401b5b476c4e4eab9200","message":"测试","type":"1"}
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"},"body":null}
	 * API_URL_本地: http://localhost:91/api/v1/message/Common/forcedSendMessage
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/message/Common/forcedSendMessage
     * @param session_id 会话id
     * @param message 消息内容
     * @param type 消息类型 1文字 2图片 3声音 4视频 6图文信息素材
     * @param resources_id 资源id
     * @param media_id 素材id
	 * @return code 200->成功
	 */
	public function forcedSendMessage(){
        $data = input('put.');
        $data['company_id'] = $this->company_id;
        
        if(empty($data['uid'])){
            $data['uid'] = $this->uid;
        }

        return \think\Loader::model('CommonLogic','logic\v1\message')->forcedSendMessage($data);
    }

    /**
     * 接入排队中会话
     * 请求类型 post
     * 传入JSON格式: {"session_id":"f5013b20d77c15ab0ae9bb1c5a52370b"}
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"},"body":[]}
	 * API_URL_本地: http://localhost:91/api/v1/message/Common/accessQueuingSession
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/message/Common/accessQueuingSession
     * @param session_id 会话id
	 * @return code 200->成功
	 */
	public function accessQueuingSession(){
        $data = input('put.');

        return \think\Loader::model('CommonLogic','logic\v1\message')->accessQueuingSession($this->company_id,$this->uid,$data['session_id']);
    }
    
    /**
     * 获取会话微信用户基本信息
     * 请求类型 post
     * 传入JSON格式: {"openid":"oZ8DFwf1NoKWyq-yO1_DKZ5FDaoE","appid":"wx88c6052d06eaaf7d"}
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"},"body":{"user_info":{"wx_user_id":"ed112ace4826075039fb174fe5576e61","nickname":"Junwen","portrait":"http:\/\/wx.qlogo.cn\/mmopen\/YCOL3hU8ffVqHh18vG77VtZ9KpWGX7PFunR9cO0oTbS1SqmS8DBUJxwvI4ZZnY2mQqEb020ULBv1SBcibYeI3ey1BEa0dbSHB\/0","gender":1,"city":"惠州","province":"广东","language":"zh_CN","country":"中国","groupid":"0","subscribe_time":"2017-12-12 14:08:26","openid":"oZ8DFwf1NoKWyq-yO1_DKZ5FDaoE","add_time":"2017-11-27 11:24:54","appid":"wx88c6052d06eaaf7d","company_id":"51454009d703c86c91353f61011ecf2f","tagid_list":null,"unionid":null,"is_sync":1,"desc":"","subscribe":1,"update_time":"2017-12-13 23:37:24","qrcode_id":"9c01fc4903da18b16fa166f181b93b62","customer_info_id":"2357fc7de3f269187e6d7c442970a049","lng":114.410263,"lat":23.056023,"precision":30,"get_into_count":11},"position_locus":[{"geographical_position_id":"04525bca8d3c802a9b49db0d60219d79","appid":"wx88c6052d06eaaf7d","openid":"oZ8DFwf1NoKWyq-yO1_DKZ5FDaoE","lng":114.410126,"lat":23.056034,"precision":30,"establish_time":"2017-12-14 10:22:18","company_id":"51454009d703c86c91353f61011ecf2f"},{"geographical_position_id":"3e25dea312942f93ce974e21f74d4b6c","appid":"wx88c6052d06eaaf7d","openid":"oZ8DFwf1NoKWyq-yO1_DKZ5FDaoE","lng":114.410965,"lat":23.055668,"precision":30,"establish_time":"2017-12-14 10:00:18","company_id":"51454009d703c86c91353f61011ecf2f"}],"session_frequency":63,"invitation_frequency":0}}
	 * API_URL_本地: http://localhost:91/api/v1/message/Common/getWxUserInfo
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/message/Common/getWxUserInfo
     * @param openid 微信用户openid
     * @param appid 微信用户appid
	 * @return code 200->成功
	 */
	public function getWxUserInfo(){
        $data = input('put.');

        return \think\Loader::model('CommonLogic','logic\v1\message')->getWxUserInfo($this->company_id,$data['openid'],$data['appid']);
    }
    
    /**
     * 创建微信用户会话
     * 请求类型 post
	 * 传入JSON格式: {"openid":"openid...","appid":"appid..."}
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"},"body":{"session_id":"sessionid..."}}
	 * API_URL_本地: http://localhost:91/api/v1/message/Common/createWxUserSession
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/message/Common/createWxUserSession
     * @param openid 客户微信openid
     * @param appid 客户微信appid
	 * @return code 200->成功
	 */
	public function createWxUserSession(){
        $data = input('put.');
        $data['company_id'] = $this->company_id;
        $data['uid'] = $this->uid;
        
        return \think\Loader::model('CommonLogic','logic\v1\message')->createWxUserSession($data);
    }
}