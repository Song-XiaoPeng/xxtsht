<?php
namespace app\api\controller\v1\extension;
use app\api\common\Auth;

//推广相关操作api
class Handle extends Auth{
    /**
     * 创建或编辑推广二维码
     * 请求类型 post
	 * 传入JSON格式: {"type":"2","appid":"wx88c6052d06eaaf7d","activity_name":"测试推广","qrcode_group_id":"12","invalid_day":"2","label":["集团用户","新服务"],"customer_service_id":"12","customer_service_group_id"::""}
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"},"body":{"qrcode_id":"a8ec8596f9fbad831595718dd618eaf6","qrcode_url":"https:\/\/mp.weixin.qq.com\/cgi-bin\/showqrcode?ticket=gQG-8DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyYzAyZVJSRDBmNTExMDAwMHcwN0gAAgRyXxpaAwQAAAAA"}}
	 * API_URL_本地: http://localhost:91/api/v1/extension/Handle/createQrcode
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/extension/Handle/createQrcode
     * @param appid 公众号appid (编辑无法修改)
     * @param qrcode_id 二维码id (修改时传入)
     * @param type 二维码类型 1永久二维码 2临时二维码 (编辑无法修改)
     * @param activity_name 活动名称或渠道名称
     * @param qrcode_group_id 二维码分组id 活动分组id 或渠道分组id
     * @param invalid_day 有效天数 单位 日 临时二维码 (编辑无法修改)
     * @param label 关注自动打标签
     * @param customer_service_id 关注的用户专属客服id
     * @param customer_service_group_id 关注的用户专属客服分组id
     * @param reception_type 接待类型 1指定客服 2指定客服分组 3不指定
     * @param reply_type 自动回复类型 -1不回复 1文本内容 2图片 3微信图文信息
     * @param media_id 回复微信媒体id
     * @param resources_id 回复资源id
     * @param reply_text 回复文本内容
     * @return code 200->成功
	 */
    public function createQrcode () {
        $data = input('put.');
        $data['uid'] = $this->uid;
        $data['company_id'] = $this->company_id;
        
        return \think\Loader::model('ExtensionLogic','logic\v1\extension')->createQrcode($data);
    }

    /**
     * 创建推广二维码分组
     * 请求类型 post
	 * 传入JSON格式: {"qrcode_group_name":"张三","group_type":"1"}
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"},"body":{"qrcode_group_id":"2"}}
	 * API_URL_本地: http://localhost:91/api/v1/extension/Handle/addQrcodeGroup
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/extension/Handle/addQrcodeGroup
	 * @param qrcode_group_name 分组名称
	 * @param group_type 分组类型 1渠道 2限时推广
	 * @return code 200->成功
	 */
    public function addQrcodeGroup () {
        $data = input('put.');
        $data['uid'] = $this->uid;
        $data['company_id'] = $this->company_id;
        
        return \think\Loader::model('ExtensionLogic','logic\v1\extension')->addQrcodeGroup($data);
    }

    /**
     * 删除推广二维码分组
     * 请求类型 post
	 * 传入JSON格式: {"qrcode_group_id":"12"}
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"}}
	 * API_URL_本地: http://localhost:91/api/v1/extension/Handle/delQrcodeGroup
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/extension/Handle/delQrcodeGroup
	 * @param qrcode_group_id 分组id
	 * @return code 200->成功
	 */
    public function delQrcodeGroup () {
        $data = input('put.');
        $data['uid'] = $this->uid;
        $data['company_id'] = $this->company_id;
        
        return \think\Loader::model('ExtensionLogic','logic\v1\extension')->delQrcodeGroup($data);
    }

    /**
     * 编辑推广二维码分组名称
     * 请求类型 post
	 * 传入JSON格式: {"qrcode_group_id":"12","qrcode_group_name":"测试"}
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"}}
	 * API_URL_本地: http://localhost:91/api/v1/extension/Handle/editQrcodeGroupName
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/extension/Handle/editQrcodeGroupName
	 * @param qrcode_group_id 分组id
	 * @param qrcode_group_name 分组名称
	 * @return code 200->成功
	 */
    public function editQrcodeGroupName(){
        $data = input('put.');
        $data['company_id'] = $this->company_id;
        
        return \think\Loader::model('ExtensionLogic','logic\v1\extension')->editQrcodeGroupName($data);
    }

    /**
     * 获取推广二维码分组list
     * 请求类型 post
	 * 传入JSON格式: {"group_type":"1"}
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"},"body":[{"qrcode_group_id":1,"qrcode_group_name":"张三"},{"qrcode_group_id":2,"qrcode_group_name":"张三"}]}
	 * API_URL_本地: http://localhost:91/api/v1/extension/Handle/getQrcodeGroupList
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/extension/Handle/getQrcodeGroupList
	 * @param group_type 分组类型 1渠道 2限时推广
	 * @return code 200->成功
	 */
    public function getQrcodeGroupList(){
        $data = input('put.');
        $data['company_id'] = $this->company_id;
        
        return \think\Loader::model('ExtensionLogic','logic\v1\extension')->getQrcodeGroupList($data);
    }

    /**
     * 删除推广二维码
     * 请求类型 post
	 * 传入JSON格式: {"qrcode_id":"1e30180bc266c2a762850f8dc48c4d1b"}
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"},"body":[{"qrcode_group_id":1,"qrcode_group_name":"张三"},{"qrcode_group_id":2,"qrcode_group_name":"张三"}]}
	 * API_URL_本地: http://localhost:91/api/v1/extension/Handle/delQrcod
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/extension/Handle/delQrcod
	 * @param qrcode_id 删除的二维码id
	 * @return code 200->成功
	 */
    public function delQrcod(){
        $data = input('put.');
        $data['company_id'] = $this->company_id;
        $data['uid'] = $this->uid;
        
        return \think\Loader::model('ExtensionLogic','logic\v1\extension')->delQrcod($data);
    }

    /**
     * 获取推广二维码list
     * 请求类型 post
	 * 传入JSON格式: {"page":1,"type":"1"}
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"},"body":{"data_list":[{"qrcode_id":"a8ec8596f9fbad831595718dd618eaf6","company_id":"51454009d703c86c91353f61011ecf2f","appid":"wx88c6052d06eaaf7d","type":1,"invalid_time":"0000-00-00 00:00:00","activity_name":"测试推广","create_time":"2017-11-26 14:30:10","qrcode_url":"https:\/\/mp.weixin.qq.com\/cgi-bin\/showqrcode?ticket=gQG-8DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyYzAyZVJSRDBmNTExMDAwMHcwN0gAAgRyXxpaAwQAAAAA","label":["集团用户","新服务"],"attention":0,"canel_attention":0,"create_uid":6454,"customer_service_id":4,"customer_service_group_id":"","qrcode_group_id":2,"is_del":-1,"nick_name":"网鱼服务营销平台","qrcode_group_name":"测试123123123","attention_num":0,"create_user_name":"地方","create_user_group_name":"技师"}],"page_data":{"count":1,"rows_num":16,"page":1}}}
	 * API_URL_本地: http://localhost:91/api/v1/extension/Handle/getQrcodList
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/extension/Handle/getQrcodList
	 * @param page 分页参数默认1
	 * @param type 类型 1渠道 2限时推广
	 * @return code 200->成功
	 */
    public function getQrcodList(){
        $data = input('put.');
        $data['company_id'] = $this->company_id;
        
        return \think\Loader::model('ExtensionLogic','logic\v1\extension')->getQrcodList($data);
    }

    /**
     * 添加编辑红包活动
     * 请求类型 post
	 * 传入JSON格式: {"activity_name":"红包大活动","activity_id":"","number":"10","amount":"0.01","amount_start":"","amount_end":"","amount_type":1,"start_time":"2017-12-21 14:00:00","end_time":"2017-12-31 14:00:00","is_follow":1,"qrcode_id":"68140c52cfac5c743fa35228051111cc","appid":"wxe30d2c612847beeb","is_share":-1,"share_url":"http://wxyx.lyfz.net","share_cover":"http://wxyx.lyfz.net/Public/mobile/images/default_portrait.jpg","amount_upper_limit":"20","is_open":1}
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"},"body":{"activity_id":"7f4b56cb0596d8ffdabd623b02da13d3"}}
	 * API_URL_本地: http://localhost:91/api/v1/extension/Handle/addRedEnvelopes
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/extension/Handle/addRedEnvelopes
	 * @param activity_name 红包活动名称
	 * @param activity_id 活动id 存在则编辑
	 * @param number 红包数量
	 * @param amount 红包金额
	 * @param amount_start 随机金额开始
	 * @param amount_end 随机金额结束
	 * @param amount_type 派发金额方式1固定金额 2随机金额
	 * @param start_time 活动开始时间
	 * @param end_time 活动结束时间
	 * @param is_follow 是否强制关注 1是 -1否
	 * @param qrcode_id 强制关注二维码
	 * @param appid 运营公众号appid
	 * @param is_share 是否强制分享 1是 -1否
	 * @param share_url 分享链接
	 * @param share_cover 分享封面资源id
	 * @param amount_upper_limit 派发金额上限
	 * @param is_open 是否开启 1是 -1否
	 * @return code 200->成功
	 */
    public function addRedEnvelopes(){
        $data = input('put.');
        $data['company_id'] = $this->company_id;

        return \think\Loader::model('ExtensionLogic','logic\v1\extension')->addRedEnvelopes($data);
    }

    /**
     * 删除红包活动
     * 请求类型 post
	 * 传入JSON格式: {"activity_id":"activity_id.."}
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"},"body":null}
	 * API_URL_本地: http://localhost:91/api/v1/extension/Handle/delRedEnvelopes
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/extension/Handle/delRedEnvelopes
	 * @param activity_id 活动id
	 * @return code 200->成功
	 */
    public function delRedEnvelopes(){
        $data = input('put.');
        $data['company_id'] = $this->company_id;
        
        return \think\Loader::model('ExtensionLogic','logic\v1\extension')->delRedEnvelopes($this->company_id,$data['activity_id']);
    }

    /**
     * 查看红包二维码列表
     * 请求类型 post
	 * 传入JSON格式: {"activity_id":"activity_id..","page":"1"}
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"},"body":{"data_list":[{"red_envelopes_id":"2zFWytVd6YuBO21F06ync0V2A0E7U0Re","appid":"wxe30d2c612847beeb","activity_id":"e4807ceb9777e859f7eb6720bebc0995","company_id":"51454009d703c86c91353f61011ecf2f","is_receive":"否","receive_time":null,"openid":null,"receive_amount":"0.00","wx_nickname":null,"wx_portrait":null}],"page_data":{"count":10,"rows_num":16,"page":"1"}}}
	 * API_URL_本地: http://localhost:91/api/v1/extension/Handle/getRedEnvelopeList
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/extension/Handle/getRedEnvelopeList
	 * @param activity_id 活动id
	 * @return code 200->成功
	 */
    public function getRedEnvelopeList(){
        $data = input('put.');
        
        return \think\Loader::model('ExtensionLogic','logic\v1\extension')->getRedEnvelopeList($this->company_id,$data['activity_id'],$data['page'],$this->token);
    }

    /**
     * 批量生成二维码
     * 请求类型 get
	 * 返回JSON格式: 
	 * API_URL_本地: http://localhost:91/api/v1/extension/Handle/createQrcodeZip
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/extension/Handle/createQrcodeZip
	 * @param activity_id 活动id
	 * @return code 200->成功
	 */
    public function createQrcodeZip(){
        $data = input('get.');
        
        return \think\Loader::model('ExtensionLogic','logic\v1\extension')->createQrcodeZip($this->company_id,$data['activity_id'],$this->token);
    }

    public function generateRedEnvelopes(){
        $data = input('get.');
        
        return \think\Loader::model('ExtensionLogic','logic\v1\extension')->generateRedEnvelopes($this->company_id,$data['activity_id']);
    }

    /**
     * 获取红包list
     * 请求类型 post
	 * 传入JSON格式: {"page":1}
	 * 返回JSON格式: 
	 * API_URL_本地: http://localhost:91/api/v1/extension/Handle/getRedEnvelopesList
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/extension/Handle/getRedEnvelopesList
	 * @param activity_id 活动id
	 * @return code 200->成功
	 */
    public function getRedEnvelopesList(){
        $data = input('put.');
        
        return \think\Loader::model('ExtensionLogic','logic\v1\extension')->getRedEnvelopesList($this->company_id,$data['page']);
    }
}