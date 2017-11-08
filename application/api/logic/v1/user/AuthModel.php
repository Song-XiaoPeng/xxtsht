<?php
namespace app\api\logic\v1\user;
use think\Model;
use think\Db;

class AuthModel extends Model {
    /**
     * 账号登录
     * @param phone_no 用户手机
	 * @param password 密码md5
	 * @return code 200->成功
	 */
    public function login ($data) {
        $phone_no = $data['phone_no'];
        $password = $data['password'];
        $time = date('Y-m-d H:i:s');

        $client = new \GuzzleHttp\Client();

        $request_data = [
            'phone_no' => $phone_no,
            'password' => $password
        ];

        $res = $client->request(
            'PUT', 
            'http://wxyx.lyfz.net/api.php/Auth/companyLogin', 
            [
                'json' => $request_data
            ]
        );

        $body = json_decode($res->getBody(),true);
        if($body['meta']['code'] != 200){
            return msg($body['meta']['code'],$body['meta']['message']);
        }

        $expiration_date = $body['body']['auth_info']['11']['expiration_date'];
        $login_token = $body['body']['token'];
        $address = $body['body']['address'];
        $company_id = $body['body']['company_id'];
        $uid = $body['body']['uid'];
        $company_name = $body['body']['company_name'];
        $username = $body['body']['username'];
        $logo = $body['body']['logo'];
        $phone_no = $body['body']['phone_no'];

        if(strtotime($time) > strtotime($expiration_date)){
            return msg(3010,'账号已过期',['expiration_date'=>$expiration_date]);
        }

        return msg(
            200,
            'success',
            [
                'expiration_date' => $expiration_date,
                'login_token' => $login_token,
                'address' => $address,
                'company_id' => $company_id,
                'company_name' => $company_name,
                'username' => $username,
                'logo' => $logo,
                'phone_no' => $phone_no
            ]
        );
    }
}