<?php
namespace app\api\controller\v1\user;
use \think\Request;

//授权登录相关操作
class Auth {
    /**
     * 账号登录
	 * 传入JSON格式: {"phone_no":"15916035572","password":"25d55ad283aa400af464c76d713c07ad"}
	 * 返回JSON格式: {"meta":{"code":200,"msg":"success"},"body":{"expiration_date":"2018-11-23 13:39:44","login_token":"c343fce47282f72a59e3e94cfc5430dc","address":"上海市浦东新区张江高科技园区碧波路汤臣豪园南区","company_id":"51454009d703c86c91353f61011ecf2f","company_name":"利亚方舟美容业版","username":"18665281860","logo":"http:\/\/wxyx.lyfz.net\/Public\/Uploads\/shop_logo\/2017-08-29\/59a4e46abcb7d.jpg","phone_no":"18665281860","uid":"98"}}
	 * API_URL_本地: http://localhost:91/api/v1/user/Auth/Login
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/user/Auth/Login
     * @param phone_no 用户手机
	 * @param password 密码md5
	 * @return code 200->成功
	 */
    public function login () {
        $data = input('put.');
        
        return \think\Loader::model('AuthLogic','logic\v1\user')->login($data);
    }

    /**
     * 校验token
	 * 传入JSON格式: {"uid":"6454","token":"9b371b663bdbdbbf32ca010c367f29c1"}
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"},"body":[]}
	 * API_URL_本地: http://localhost:91/api/v1/user/Auth/checkToken
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/user/Auth/checkToken
     * @param uid 用户账号uid
     * @param token 账号token
	 * @return code 200->成功
	 */
    public function checkToken () {
        $data = input('put.');
        
        return \think\Loader::model('AuthLogic','logic\v1\user')->checkToken($data['uid'],$data['token'],$data['client']);
    }

    /**
     * 标记账号在线或不在线
	 * 传入JSON格式: {"uid":"6454","state":"1"}
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"},"body":[]}
	 * API_URL_本地: http://localhost:91/api/v1/user/Auth/setUserOnlineState
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/user/Auth/setUserOnlineState
     * @param uid 账号uid
     * @param state 是否在线 1在线 -1离线
	 * @return code 200->成功
	 */
    public function setUserOnlineState () {
        $data = input('put.');
        
        return \think\Loader::model('AuthLogic','logic\v1\user')->setUserOnlineState($data['uid'],$data['state']);
    }

    /**
     * 获取商户所有客服uid
	 * 传入JSON格式: {"company_id":"51454009d703c86c91353f61011ecf2f"}
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"},"body":[]}
	 * API_URL_本地: http://localhost:91/api/v1/user/Auth/getUidCompanyId
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/user/Auth/getUidCompanyId
     * @param uid 用户uid
	 * @return code 200->成功
	 */
    public function getUidCompanyId () {
        $uid = input('put.uid');
        
        return \think\Loader::model('AuthLogic','logic\v1\user')->getUidCompanyId($uid);
    }
}