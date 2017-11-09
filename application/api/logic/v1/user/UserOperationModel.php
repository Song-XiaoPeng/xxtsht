<?php
namespace app\api\logic\v1\user;
use think\Model;
use think\Db;

class UserOperationModel extends Model {
    /**
     * 添加客服账号
	 * @param company_id 商户company_id
	 * @param user_group_id 权限分组id
	 * @param phone_no 子账号手机(账号)
	 * @param password 子账号登录密码md5值
	 * @return code 200->成功 3001->插入数据失败 3002账号重复
	 */
    public function addAccountNumber($data){
        $company_id = $data['company_id'];
        $token = $data['token'];
        $phone_no = $data['phone_no'];
        $password = $data['password'];
        $user_name = $data['user_name'];
        $user_group_id = $data['user_group_id'];

        $request_data = [
            'user_group_id' => $user_group_id,
            'phone_no' => $phone_no,
            'password' => $password,
            'user_name' => $user_name
        ];

        $client = new \GuzzleHttp\Client();
        $res = $client->request(
            'PUT', 
            config('auth_server_url').'/api.php/MeiBackstage/addSonUser', 
            [
                'json' => $request_data,
                'timeout' => 3,
                'headers' => [
                    'token' => $token
                ]
            ]
        );

        $body = json_decode($res->getBody(),true);
        if($body['meta']['code'] != 200){
            return msg($body['meta']['code'],$body['meta']['message']);
        }

        return msg(200,'success');
    }
}