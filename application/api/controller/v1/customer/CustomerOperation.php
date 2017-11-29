<?php
namespace app\api\controller\v1\customer;
use app\api\common\Auth;

//客户信息相关操作
class CustomerOperation extends Auth{
    /**
     * 设置客户信息
     * 请求类型 post
	 * 传入JSON格式: {"user_group_id":"user_group_id...","phone_no":"phone_no...","user_name":"user_name..."}
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"}}
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
	 * @return code 200->成功
	 */
    public function setCustomerInfo () {
        $data = input('put.');
        $data['company_id'] = $this->company_id;
        
        return \think\Loader::model('CustomerOperationModel','logic\v1\customer')->setCustomerInfo($data);
    }
}