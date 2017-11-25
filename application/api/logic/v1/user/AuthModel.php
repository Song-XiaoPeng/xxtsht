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
            config('auth_server_url').'/api.php/Auth/companyLogin', 
            [
                'json' => $request_data,
                'timeout' => 3
            ]
        );

        $body = json_decode($res->getBody(),true);
        if($body['meta']['code'] != 200){
            return msg($body['meta']['code'],$body['meta']['message']);
        }

        if(!array_key_exists('11',$body['body']['auth_info'])){
            return msg(3009,'未获得功能模块授权');
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
        $group_name = $body['body']['group_name'];
        $user_group_id = $body['body']['user_group_id'];
        $user_type = $body['body']['user_type'];

        if(strtotime($time) > strtotime($expiration_date)){
            return msg(3010,'账号已过期',['expiration_date'=>$expiration_date]);
        }

        $this->addAuthCache($uid,$login_token,$company_id,$phone_no,$user_type,$user_group_id,$expiration_date);

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
                'phone_no' => $phone_no,
                'user_group_id' => $user_group_id,
                'group_name' => $group_name,
                'user_type' => $user_type,
                'uid' => $uid
            ]
        );
    }

    /**
     * 添加授权缓存
     * @param token 登录的token
	 * @param uid 账户uid
	 * @param company_id 商户company_id
	 * @param expiration_date 模块到期时间
	 * @return code 200->成功
	 */
    private function addAuthCache ($uid,$token,$company_id,$phone_no,$user_type,$user_group_id,$expiration_date) {
        $auth_res = Db::name('login_token')->where(['uid'=>$uid])->find();

        $time = date('Y-m-d H:i:s');

        if($auth_res){
            Db::name('login_token')->where(['tid'=>$auth_res['tid']])->update(['token'=>$token,'add_time'=>$time,'expiration_date'=>$expiration_date]);
        }else{
            Db::name('login_token')->insert([
                'token'=>$token,
                'company_id'=>$company_id,
                'uid'=>$uid,
                'add_time'=>$time,
                'expiration_date'=>$expiration_date
            ]);
        }

        $count = Db::name('login_token')->where(['company_id'=>$company_id])->count();
        if($count > 1){
            Db::name('login_token')->where(['company_id'=>$company_id])->update(['expiration_date'=>$expiration_date]);
        }

        return msg(200,'success');
    }

    /**
     * 更新授权时间
     * @param company_id 修改商家授权的company_id
     * @param expiration_date 到期时间
	 * @return code 200->成功
	 */
    public function updateAuthTime($data){
        $company_id = $data['company_id'];
        $expiration_date = $data['expiration_date'];

        $res = Db::name('login_token')->where(['company_id'=>$company_id])->update(['expiration_date'=>$expiration_date]);
        if($res){
            return msg(200,'success');
        }else{
            return msg(3001,'更新数据失败');
        }
    }

    
}