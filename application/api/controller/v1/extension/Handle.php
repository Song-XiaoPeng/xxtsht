<?php
namespace app\api\controller\v1\extension;
use app\api\common\Auth;

//推广相关操作api
class Handle extends Auth{
    /**
     * 创建推广二维码
     * 请求类型 post
	 * 传入JSON格式: {"type":"2","appid":"wx88c6052d06eaaf7d","activity_name":"测试推广","qrcode_group_id":"12","invalid_day":"2","label":["集团用户","新服务"],"customer_service_id":"12","customer_service_group_id"::""}
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"},"body":{"qrcode_id":"a8ec8596f9fbad831595718dd618eaf6","qrcode_url":"https:\/\/mp.weixin.qq.com\/cgi-bin\/showqrcode?ticket=gQG-8DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyYzAyZVJSRDBmNTExMDAwMHcwN0gAAgRyXxpaAwQAAAAA"}}
	 * API_URL_本地: http://localhost:91/api/v1/extension/Handle/createQrcode
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/extension/Handle/createQrcode
     * @param appid 公众号appid
     * @param type 二维码类型 1永久二维码 2临时二维码
     * @param activity_name 活动名称或渠道名称
     * @param qrcode_group_id 二维码分组id 活动分组id 或渠道分组id
     * @param invalid_day 有效天数 单位 日 临时二维码
     * @param label 关注自动打标签
     * @param customer_service_id 关注的用户专属客服id
     * @param customer_service_group_id 关注的用户专属客服分组id
     * @return code 200->成功
	 */
    public function createQrcode () {
        $data = input('put.');
        $data['uid'] = $this->uid;
        $data['company_id'] = $this->company_id;
        
        return \think\Loader::model('ExtensionModel','logic\v1\extension')->createQrcode($data);
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
        
        return \think\Loader::model('ExtensionModel','logic\v1\extension')->addQrcodeGroup($data);
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
        
        return \think\Loader::model('ExtensionModel','logic\v1\extension')->delQrcodeGroup($data);
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
        
        return \think\Loader::model('ExtensionModel','logic\v1\extension')->editQrcodeGroupName($data);
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
        
        return \think\Loader::model('ExtensionModel','logic\v1\extension')->getQrcodeGroupList($data);
    }
}