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
	 * @param real_name 客户真实姓名
	 * @param real_sex 客户真实性别 0未知 1男 2女
	 * @param real_phone 客户真实联系手机
	 * @param contact_address 客户联系地址
	 * @param wx_company_id 所属公司
	 * @param wx_user_group_id 所属用户分组id
	 * @param desc 备注
	 * @param customer_info_id 客户信息id (关联时选传)
	 * @return code 200->成功
	 */
    public function setCustomerInfo () {
        $data = input('put.');
        $data['company_id'] = $this->company_id;
        
        return \think\Loader::model('CustomerOperationModel','logic\v1\customer')->setCustomerInfo($data);
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
        
        return \think\Loader::model('CustomerOperationModel','logic\v1\customer')->getWxCustomerInfo($data);
	}
}