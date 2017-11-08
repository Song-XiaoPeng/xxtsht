<?php
namespace app\api\controller\v1\User;
use app\api\controller\common\AuthController;

//用户账户相关操作
class UserOperation extends AuthController{
    /**
     * 添加客服账号
	 * 传入JSON格式: {"user_group_id":"user_group_id...","phone_no":"phone_no...","user_name":"user_name..."}
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"}}
	 * API_URL_本地: http://localhost:91/api/v1/user/UserOperation/addAccountNumber
	 * API_URL_服务器: http://customer.lyfz.net/api/v1/user/UserOperation/addAccountNumber
     * @param phone_no 用户手机
	 * @param password 密码md5
	 * @return code 200->成功
	 */
    public function addAccountNumber () {
        $data = input('put.');
        
        return \think\Loader::model('UserOperationModel','logic\v1\user')->addAccountNumber($data);
    }
}