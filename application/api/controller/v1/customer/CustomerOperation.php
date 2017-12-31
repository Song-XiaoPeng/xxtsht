<?php
namespace app\api\controller\v1\customer;
use app\api\common\Auth;

//客户信息相关操作
class CustomerOperation extends Auth{
    /**
     * 设置客户信息
     * 请求类型 post
	 * 传入JSON格式: {"appid":"wx88c6052d06eaaf7d","openid":"oZ8DFwU5HOTs0b4g-P_skZ8wgH7g","real_name":"张达力","real_sex":"1","real_phone":"18316317751","contact_address":"广东省惠州市惠城区麦地岸","wx_company_id":"","wx_user_group_id":"","customer_info_id":"2f85b32c1bab69feb1e2a4b14b654bf2"}
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"},"body":null}
	 * API_URL_本地: http://localhost:91/api/v1/customer/CustomerOperation/setCustomerInfo
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/customer/CustomerOperation/setCustomerInfo
     * @param appid 客户来源appid
	 * @param openid 客户微信openid
     * @param customer_type 客户类型 0其他 1意向客户 2订单客户 3追销客户
	 * @param real_name 客户真实姓名
	 * @param real_sex 客户真实性别 0未知 1男 2女
	 * @param real_phone 客户真实联系手机
	 * @param contact_address 客户联系地址
	 * @param wx_company_id 所属公司
	 * @param wx_user_group_id 所属用户分组id
	 * @param desc 备注
	 * @param product_id 意向产品id
	 * @param customer_info_id 客户信息id (关联时选传)
	 * @return code 200->成功
	 */
    public function setCustomerInfo () {
        $data = input('put.');
        $data['company_id'] = $this->company_id;
        $data['uid'] = $this->uid;
        
        return \think\Loader::model('CustomerOperationLogic','logic\v1\customer')->setCustomerInfo($data);
	}
	
    /**
     * 获取客户信息
     * 请求类型 post
	 * 传入JSON格式: {"appid":"wx88c6052d06eaaf7d","openid":"oZ8DFwU5HOTs0b4g-P_skZ8wgH7g"}
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"},"body":null}
	 * API_URL_本地: http://localhost:91/api/v1/customer/CustomerOperation/getWxCustomerInfo
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/customer/CustomerOperation/getWxCustomerInfo
     * @param appid 客户来源appid
	 * @param openid 客户微信openid
	 * @return code 200->成功
	 */
	public function getWxCustomerInfo(){
        $data = input('put.');
        $data['company_id'] = $this->company_id;
        $data['uid'] = $this->uid;
        
        return \think\Loader::model('CustomerOperationLogic','logic\v1\customer')->getWxCustomerInfo($data);
	}

    /**
     * 获取线索数据统计
     * 请求类型 get
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"},"body":{"clue":0,"today":0,"follow_up":0,"intention":0}}
	 * API_URL_本地: http://localhost:91/api/v1/customer/CustomerOperation/getClueStatisticData
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/customer/CustomerOperation/getClueStatisticData
	 * @return code 200->成功
	 */
	public function getClueStatisticData(){     
        return \think\Loader::model('CustomerOperationLogic','logic\v1\customer')->getClueStatisticData($this->company_id, $this->uid, $this->user_type);
	}

    /**
     * 获取意向数据统计
     * 请求类型 get
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"},"body":{"total":1,"today":0,"follow_up":0,"intention":1}}
	 * API_URL_本地: http://localhost:91/api/v1/customer/CustomerOperation/getIntentionData
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/customer/CustomerOperation/getIntentionData
	 * @return code 200->成功
	 */
	public function getIntentionData(){     
        return \think\Loader::model('CustomerOperationLogic','logic\v1\customer')->getIntentionData($this->company_id, $this->uid, $this->user_type);
	}

    /**
     * 获取订单客户数据统计
     * 请求类型 get
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"},"body":{"total":1,"today":0,"follow_up":0,"intention":1}}
	 * API_URL_本地: http://localhost:91/api/v1/customer/CustomerOperation/getOrderCustomerData
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/customer/CustomerOperation/getOrderCustomerData
	 * @return code 200->成功
	 */
	public function getOrderCustomerData(){     
        return \think\Loader::model('CustomerOperationLogic','logic\v1\customer')->getOrderCustomerData($this->company_id, $this->uid, $this->user_type);
    }
    
    /**
     * 获取追销客户数据统计
     * 请求类型 get
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"},"body":{"total":1,"today":0,"follow_up":0,"intention":1}}
	 * API_URL_本地: http://localhost:91/api/v1/customer/CustomerOperation/getTrackCustomerData
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/customer/CustomerOperation/getTrackCustomerData
	 * @return code 200->成功
	 */
	public function getTrackCustomerData(){     
        return \think\Loader::model('CustomerOperationLogic','logic\v1\customer')->getTrackCustomerData($this->company_id, $this->uid, $this->user_type);
	}

    /**
     * 添加客户意向产品
     * 请求类型 post
	 * 传入JSON格式: {"product_name":"测试","product_id":"12"}
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"},"body":{"product_id":"12"}}
	 * API_URL_本地: http://localhost:91/api/v1/customer/CustomerOperation/addProduct
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/customer/CustomerOperation/addProduct
     * @param product_name 产品名称
     * @param product_id 产品id （选传存在则更新）
	 * @return code 200->成功
	 */
	public function addProduct(){
        $data = input('put.');
        $data['company_id'] = $this->company_id;
        $data['uid'] = $this->uid;
        
        return \think\Loader::model('CustomerOperationLogic','logic\v1\customer')->addProduct($data);
	}

    /**
     * 删除客户意向产品
     * 请求类型 post
	 * 传入JSON格式: {"product_id":"12"}
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"},"body":""}
	 * API_URL_本地: http://localhost:91/api/v1/customer/CustomerOperation/delProduct
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/customer/CustomerOperation/delProduct
     * @param product_id 产品id
	 * @return code 200->成功
	 */
	public function delProduct(){
        $data = input('put.');
        $data['company_id'] = $this->company_id;
        
        return \think\Loader::model('CustomerOperationLogic','logic\v1\customer')->delProduct($data);
	}

    /**
     * 模糊搜索客户意向产品List
     * 请求类型 post
	 * 传入JSON格式: {"product_name":"测试"}
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"},"body":[{"product_id":1,"product_name":"测试","company_id":"51454009d703c86c91353f61011ecf2f","is_del":-1}]}
	 * API_URL_本地: http://localhost:91/api/v1/customer/CustomerOperation/searchProduct
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/customer/CustomerOperation/searchProduct
     * @param product_name 模糊搜索名称
	 * @return code 200->成功
	 */
	public function searchProduct(){
        $data = input('put.');
        $data['company_id'] = $this->company_id;
        
        return \think\Loader::model('CustomerOperationLogic','logic\v1\customer')->searchProduct($data);
	}

    /**
     * 获取意向产品list
     * 请求类型 post
	 * 传入JSON格式: {"page":1}
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"},"body":{"data_list":[{"product_id":1,"product_name":"测试","company_id":"51454009d703c86c91353f61011ecf2f","is_del":-1}],"page_data":{"count":1,"rows_num":16,"page":1}}}
	 * API_URL_本地: http://localhost:91/api/v1/customer/CustomerOperation/getProductList
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/customer/CustomerOperation/getProductList
     * @param product_name 模糊搜索名称
	 * @return code 200->成功
	 */
	public function getProductList(){
        $data = input('put.');
        $data['company_id'] = $this->company_id;
        
        return \think\Loader::model('CustomerOperationLogic','logic\v1\customer')->getProductList($data);
	}

    /**
     * 模糊搜索获取客户信息
     * 请求类型 post
	 * 传入JSON格式: {"real_name":"张三","real_phone": 18316314485}
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"},"body":[{"customer_info_id":"2357fc7de3f269187e6d7c442970a049","real_name":"李涛二","real_sex":1,"real_phone":"13502439257","contact_address":"广东省惠州市惠城区河南岸","wx_company_id":-1,"wx_user_group_id":2,"company_id":"51454009d703c86c91353f61011ecf2f","desc":"dwadwad","birthday":"2017-12-22","wx_number":"weixin","email":"youxiang@qq.com","tel":"13233223322","uid":6454,"product_id":-1,"wx_user_group_name":"测试bvc","wx_company_name":null,"product_name":null}]}
	 * API_URL_本地: http://localhost:91/api/v1/customer/CustomerOperation/searchCustomerInfo
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/customer/CustomerOperation/searchCustomerInfo
     * @param real_name 客户姓名 (选传)
     * @param real_phone 客户手机 (选传)
	 * @return code 200->成功
	 */
	public function searchCustomerInfo(){
        $data = input('put.');
        $data['company_id'] = $this->company_id;
        
        return \think\Loader::model('CustomerOperationLogic','logic\v1\customer')->searchCustomerInfo($data);
	}

    /**
     * 获取线索客户列表
     * 请求类型 post
	 * 传入JSON格式: {"real_name":"张三","page": 1}
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"},"body":{"data_list":[{"wx_user_id":"fff2da77cb803271a3fb464d2fe5dd0b","nickname":"设计师慧婷","portrait":"http:\/\/wx.qlogo.cn\/mmopen\/3bY8zuTsQSUMrJTZjIee4diaibW1nHZXuAiaUdecsRyOPscDI4Gw64LGUEAnx7Jn6up8KdV2WhH5LlGnAvIQJ22NY1TfjKJW2BL\/0","gender":2,"city":"长治","province":"山西","language":"zh_CN","country":"中国","groupid":"0","subscribe_time":"2015-03-24 21:16:45","openid":"oF_-jjkwH-39lUqPhFoD6NIbNhBA","add_time":"2017-12-13 14:03:01","appid":"wxe30d2c612847beeb","company_id":"51454009d703c86c91353f61011ecf2f","tagid_list":null,"unionid":null,"is_sync":1,"desc":"","subscribe":1,"update_time":"2017-12-13 15:17:15","qrcode_id":null,"customer_info_id":"","app_name":"利亚方舟影楼管理软件"},{"wx_user_id":"ffea2117c9966a25a7bf90aac7f17600","nickname":"建湖摄影师阿D","portrait":"http:\/\/wx.qlogo.cn\/mmopen\/PiajxSqBRaEKBGGH9av8XCvZymibZen9hf3S4d7jr7V1xEKsFGejz5oOr08iahaMmnCjF25arLt4Q86AoEP14rj4Q\/0","gender":1,"city":"盐城","province":"江苏","language":"zh_CN","country":"中国","groupid":"0","subscribe_time":"2014-07-01 13:27:14","openid":"oF_-jjsBzT7J3rFOAM9VlZj-Skjw","add_time":"2017-12-13 14:03:01","appid":"wxe30d2c612847beeb","company_id":"51454009d703c86c91353f61011ecf2f","tagid_list":null,"unionid":null,"is_sync":1,"desc":"","subscribe":1,"update_time":"2017-12-13 15:17:15","qrcode_id":null,"customer_info_id":"","app_name":"利亚方舟影楼管理软件"}],"page_data":{"count":7192,"rows_num":2,"page":1}}}
	 * API_URL_本地: http://localhost:91/api/v1/customer/CustomerOperation/getClueCustomer
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/customer/CustomerOperation/getClueCustomer
     * @param real_name 客户姓名(选传模糊搜索)
     * @param page 分页参数 默认1
     * @param ascription 客户线索归属 1我的 2下属 3全部
     * @param type 类型 1意向池 2意向
	 * @return code 200->成功
	 */
	public function getClueCustomer(){
        $data = input('put.');
        $data['company_id'] = $this->company_id;
        $data['uid'] = $this->uid;
        $data['user_type'] = $this->user_type;
        
        return \think\Loader::model('CustomerOperationLogic','logic\v1\customer')->getClueCustomer($data);
	}

    /**
     * 获取意向客户列表
     * 请求类型 post
	 * 传入JSON格式: {"real_name":"张三","page": 1}
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"},"body":{"data_list":[{"wx_user_id":"fff2da77cb803271a3fb464d2fe5dd0b","nickname":"设计师慧婷","portrait":"http:\/\/wx.qlogo.cn\/mmopen\/3bY8zuTsQSUMrJTZjIee4diaibW1nHZXuAiaUdecsRyOPscDI4Gw64LGUEAnx7Jn6up8KdV2WhH5LlGnAvIQJ22NY1TfjKJW2BL\/0","gender":2,"city":"长治","province":"山西","language":"zh_CN","country":"中国","groupid":"0","subscribe_time":"2015-03-24 21:16:45","openid":"oF_-jjkwH-39lUqPhFoD6NIbNhBA","add_time":"2017-12-13 14:03:01","appid":"wxe30d2c612847beeb","company_id":"51454009d703c86c91353f61011ecf2f","tagid_list":null,"unionid":null,"is_sync":1,"desc":"","subscribe":1,"update_time":"2017-12-13 15:17:15","qrcode_id":null,"customer_info_id":"","app_name":"利亚方舟影楼管理软件"},{"wx_user_id":"ffea2117c9966a25a7bf90aac7f17600","nickname":"建湖摄影师阿D","portrait":"http:\/\/wx.qlogo.cn\/mmopen\/PiajxSqBRaEKBGGH9av8XCvZymibZen9hf3S4d7jr7V1xEKsFGejz5oOr08iahaMmnCjF25arLt4Q86AoEP14rj4Q\/0","gender":1,"city":"盐城","province":"江苏","language":"zh_CN","country":"中国","groupid":"0","subscribe_time":"2014-07-01 13:27:14","openid":"oF_-jjsBzT7J3rFOAM9VlZj-Skjw","add_time":"2017-12-13 14:03:01","appid":"wxe30d2c612847beeb","company_id":"51454009d703c86c91353f61011ecf2f","tagid_list":null,"unionid":null,"is_sync":1,"desc":"","subscribe":1,"update_time":"2017-12-13 15:17:15","qrcode_id":null,"customer_info_id":"","app_name":"利亚方舟影楼管理软件"}],"page_data":{"count":7192,"rows_num":2,"page":1}}}
	 * API_URL_本地: http://localhost:91/api/v1/customer/CustomerOperation/getIntentionalCustomers
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/customer/CustomerOperation/getIntentionalCustomers
     * @param real_name 微信昵称(选传模糊搜索)
     * @param page 分页参数 默认1
     * @param ascription 客户线索归属 1我的 2其他人
     * @param type 类型 1线索池客户 2线索客户
	 * @return code 200->成功
	 */
	public function getIntentionalCustomers(){
        $data = input('put.');
        $data['company_id'] = $this->company_id;
        $data['uid'] = $this->uid;
        $data['user_type'] = $this->user_type;
        
        return \think\Loader::model('CustomerOperationLogic','logic\v1\customer')->getIntentionalCustomers($data);
	}

    /**
     * 获取订单客户列表
     * 请求类型 post
	 * 传入JSON格式: {"real_name":"","page": 1,"ascription":"3"}
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"},"body":{"data_list":[{"customer_info_id":"098fbf2f0b7931ba5a80ce6165190980","real_name":"","real_sex":1,"real_phone":null,"contact_address":"","wx_company_id":-1,"wx_user_group_id":4,"company_id":"51454009d703c86c91353f61011ecf2f","desc":"","birthday":"2017-12-22","wx_number":"","email":"","tel":"","uid":7834,"product_id":-1,"customer_type":3,"add_time":"2017-12-29 14:25:56","wx_user_id":"c7b0c83efeec6ee0e04cb6a23d76d1c5","nickname":"利亚方舟曾仪","portrait":"http:\/\/wx.qlogo.cn\/mmopen\/VIaBmX56e8j7VnaMJYeloJfbtxFVxqZqeMriaicyupIsbpE6WVTU8xJUBp6fts44DS9c9mI1n9LvUQn2VD6Q4hXL7E6ue5OOdf\/0","gender":1,"city":"惠州","province":"广东","language":"zh_CN","country":"中国","groupid":"0","subscribe_time":"2017-12-29 14:25:56","openid":"oZ8DFwdcQlN5YtIl4zpz5R3PwkGU","appid":"wx88c6052d06eaaf7d","tagid_list":"[]","unionid":null,"is_sync":1,"subscribe":1,"update_time":"2017-12-29 14:26:07","qrcode_id":"","lng":114.41069,"lat":23.056143,"precision":65,"get_into_count":1,"customer_service_uid":7834,"is_clue":4,"last_time":"2017-12-29 14:26:07","set_clue_time":"2017-12-29 14:38:19","active_count":0,"active_time":"0000-00-00 00:00:00","set_intention_time":"0000-00-00 00:00:00","set_order_time":"0000-00-00 00:00:00","set_chasing_time":"0000-00-00 00:00:00","app_name":"网鱼服务营销平台","source_qrcode_name":"暂无来源二维码","product_name":null,"customer_service_name":"曾仪","wx_comapny_name":null}],"page_data":{"count":1,"rows_num":16,"page":1}}}
	 * API_URL_本地: http://localhost:91/api/v1/customer/CustomerOperation/getOrderCustomer
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/customer/CustomerOperation/getOrderCustomer
     * @param real_name 微信昵称(选传模糊搜索)
     * @param page 分页参数 默认1
     * @param ascription 客户线索归属 1我的 2其他人 3全部
	 * @return code 200->成功
	 */
	public function getOrderCustomer(){
        $data = input('put.');
        $data['company_id'] = $this->company_id;
        $data['uid'] = $this->uid;
        $data['user_type'] = $this->user_type;
        
        return \think\Loader::model('CustomerOperationLogic','logic\v1\customer')->getOrderCustomer($data);
    }
    
    /**
     * 获取追销客户列表
     * 请求类型 post
	 * 传入JSON格式: {"real_name":"","page": 1,"ascription":"3"}
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"},"body":{"data_list":[{"customer_info_id":"098fbf2f0b7931ba5a80ce6165190980","real_name":"","real_sex":1,"real_phone":null,"contact_address":"","wx_company_id":-1,"wx_user_group_id":4,"company_id":"51454009d703c86c91353f61011ecf2f","desc":"","birthday":"2017-12-22","wx_number":"","email":"","tel":"","uid":7834,"product_id":-1,"customer_type":3,"add_time":"2017-12-29 14:25:56","wx_user_id":"c7b0c83efeec6ee0e04cb6a23d76d1c5","nickname":"利亚方舟曾仪","portrait":"http:\/\/wx.qlogo.cn\/mmopen\/VIaBmX56e8j7VnaMJYeloJfbtxFVxqZqeMriaicyupIsbpE6WVTU8xJUBp6fts44DS9c9mI1n9LvUQn2VD6Q4hXL7E6ue5OOdf\/0","gender":1,"city":"惠州","province":"广东","language":"zh_CN","country":"中国","groupid":"0","subscribe_time":"2017-12-29 14:25:56","openid":"oZ8DFwdcQlN5YtIl4zpz5R3PwkGU","appid":"wx88c6052d06eaaf7d","tagid_list":"[]","unionid":null,"is_sync":1,"subscribe":1,"update_time":"2017-12-29 14:26:07","qrcode_id":"","lng":114.41069,"lat":23.056143,"precision":65,"get_into_count":1,"customer_service_uid":7834,"is_clue":5,"last_time":"2017-12-29 14:26:07","set_clue_time":"2017-12-29 14:38:19","active_count":0,"active_time":"0000-00-00 00:00:00","set_intention_time":"0000-00-00 00:00:00","set_order_time":"0000-00-00 00:00:00","set_chasing_time":"0000-00-00 00:00:00","app_name":"网鱼服务营销平台","source_qrcode_name":"暂无来源二维码","product_name":null,"customer_service_name":"曾仪","wx_comapny_name":null}],"page_data":{"count":1,"rows_num":16,"page":1}}}
	 * API_URL_本地: http://localhost:91/api/v1/customer/CustomerOperation/getTrackCustomer
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/customer/CustomerOperation/getTrackCustomer
     * @param real_name 微信昵称(选传模糊搜索)
     * @param page 分页参数 默认1
     * @param ascription 客户线索归属 1我的 2其他人 3全部
	 * @return code 200->成功
	 */
	public function getTrackCustomer(){
        $data = input('put.');
        $data['company_id'] = $this->company_id;
        $data['uid'] = $this->uid;
        $data['user_type'] = $this->user_type;
        
        return \think\Loader::model('CustomerOperationLogic','logic\v1\customer')->getTrackCustomer($data);
	}

	/**
     * 获取客户信息列表
     * 请求类型 post
	 * 传入JSON格式: {"page":"1","real_name":"","type":"0","ascription":"1"}
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"},"body":{"data_list":[{"customer_info_id":"e80de8e1b9809072d2cd6e84a6179bfa","real_name":"婉儿啊哦","real_sex":2,"real_phone":"18316317751","contact_address":"广东省惠州市惠城区麦地岸","wx_company_id":-1,"wx_user_group_id":-1,"company_id":"51454009d703c86c91353f61011ecf2f","desc":null,"birthday":null,"wx_number":null,"email":null,"tel":null,"uid":6454,"product_id":-1,"customer_type":0,"wx_user_group_name":null,"wx_company_name":null,"product_name":null},{"customer_info_id":"2357fc7de3f269187e6d7c442970a049","real_name":"李涛二","real_sex":1,"real_phone":"13502439257","contact_address":"广东省惠州市惠城区河南岸","wx_company_id":-1,"wx_user_group_id":2,"company_id":"51454009d703c86c91353f61011ecf2f","desc":"dwadwad","birthday":"2017-12-22","wx_number":"weixin","email":"youxiang@qq.com","tel":"13233223322","uid":6454,"product_id":-1,"customer_type":0,"wx_user_group_name":"测试bvc","wx_company_name":null,"product_name":null},{"customer_info_id":"2f85b32c1bab69feb1e2a4b14b654bf2","real_name":"张达力","real_sex":1,"real_phone":"18316317751","contact_address":"广东省惠州市惠城区麦地岸","wx_company_id":-1,"wx_user_group_id":-1,"company_id":"51454009d703c86c91353f61011ecf2f","desc":"-1","birthday":"0000-00-00","wx_number":"-1","email":"-1","tel":"-1","uid":6454,"product_id":-1,"customer_type":0,"wx_user_group_name":null,"wx_company_name":null,"product_name":null}],"page_data":{"count":3,"rows_num":16,"page":"1"}}}
	 * API_URL_本地: http://localhost:91/api/v1/customer/CustomerOperation/getCustomerList
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/customer/CustomerOperation/getCustomerList
     * @param page 分页参数 默认1
     * @param real_name 客户姓名 (选传)
     * @param type 客户类型 0线索 1意向客户 2订单客户 3追销客户
     * @param ascription 客户归属类型 1我的客户 2其他人
	 * @return code 200->成功
	 */
	public function getCustomerList(){
        $data = input('put.');
        $data['company_id'] = $this->company_id;
        $data['uid'] = $this->uid;
        
        return \think\Loader::model('CustomerOperationLogic','logic\v1\customer')->getCustomerList($data);
	}
}