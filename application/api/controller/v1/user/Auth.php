<?php
namespace app\api\controller\v1\User;
use \think\Request;

//授权登录相关操作
class Auth {
    /**
     * 账号登录
	 * 传入JSON格式: {"phone_no":"15916035572","password":"25d55ad283aa400af464c76d713c07ad"}
	 * 返回JSON格式: {"meta":{"code":200,"msg":"success"},"body":{"expiration_date":"2018-11-23 13:39:44","login_token":"c343fce47282f72a59e3e94cfc5430dc","address":"上海市浦东新区张江高科技园区碧波路汤臣豪园南区","company_id":"51454009d703c86c91353f61011ecf2f","company_name":"利亚方舟美容业版","username":"18665281860","logo":"http:\/\/wxyx.lyfz.net\/Public\/Uploads\/shop_logo\/2017-08-29\/59a4e46abcb7d.jpg","phone_no":"18665281860","uid":"98"}}
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
    
    /**
     * 更新授权时间
	 * 传入JSON格式: {"token":"9339912335lyfz","company_id":"15916035572","expiration_date":"2015-05-11 12:20:12"}
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"},"body":[]}
	 * API_URL_本地: http://localhost:91/api/v1/user/Auth/updateAuthTime
	 * API_URL_服务器: http://customer.lyfz.net/api/v1/user/Auth/updateAuthTime
     * @param company_id 商户id
	 * @param expiration_date 授权到期时间
	 * @return code 200->成功
	 */
    public function updateAuthTime () {
        $data = input('put.');
        
        if($data['token'] != '9339912335lyfz'){
            return msg(3001,'token错误');
        }
        
        return \think\Loader::model('AuthModel','logic\v1\user')->updateAuthTime($data);
    }
}