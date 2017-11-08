<?php
namespace app\api\controller\v1\User;
use \think\Request;

//授权登录相关操作
class Auth {
    /**
     * 账号登录
	 * json字符串提交 传入JSON格式: {"phone_no":"15916035572","password":"25d55ad283aa400af464c76d713c07ad"}
	 * API_URL_本地: http://localhost:91/api/v1/user/Auth/Login
	 * API_URL_服务器: http://customer.lyfz.net/api/v1/user/Auth/Login
     * @param phone_no 用户手机
	 * @param password 密码md5
	 * @return code 200->成功
	 */
    public function login () {
        $data = input('put.');
        
        return \think\Loader::model('AuthModel','logic\v1\user')->login($data);
    }
    
}