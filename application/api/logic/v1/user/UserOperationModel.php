<?php
namespace app\api\logic\v1\user;
use think\Model;
use think\Db;

class UserOperationModel extends Model {
    /**
     * 添加客服账号
	 * @param user_group_id 权限分组id
	 * @param phone_no 子账号手机(账号)
	 * @param password 子账号登录密码md5值
	 * @return code 200->成功
	 */
    public function addAccountNumber($data){
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
            combinationApiUrl('/api.php/MeiBackstage/addSonUser'), 
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

    /**
     * 获取客服子账号列表
	 * @param user_group_id 分组id(选传)
	 * @param page 分页参数默认1
	 * @param token 账号登录token
	 * @return code 200->成功
	 */
    public function getUserList($data){
        $token = $data['token'];
        $page = $data['page'];
        $user_group_id = empty($data['user_group_id']) == true ? '' : $data['user_group_id'];

        $request_data = [
            'page' => $page,
            'user_group_id' => $user_group_id
        ];

        $client = new \GuzzleHttp\Client();
        $res = $client->request(
            'PUT', 
            combinationApiUrl('/api.php/MeiBackstage/getSonUserList'), 
            [
                'json' => $request_data,
                'timeout' => 3,
                'headers' => [
                    'token' => $token
                ]
            ]
        );

        return json_decode($res->getBody(),true);
    }

    /**
     * 获取子账号分组
	 * @param token 账号登录token
	 * @return code 200->成功
	 */
    public function getUserGroup($token){
        $client = new \GuzzleHttp\Client();
        $res = $client->request(
            'GET', 
            combinationApiUrl('/api.php/MeiBackstage/getUserGroup'), 
            [
                'timeout' => 3,
                'headers' => [
                    'token' => $token
                ]
            ]
        );

        return json_decode($res->getBody(),true);
    }

    /**
     * 添加子账号账户分组
	 * @param token 账号登录token
	 * @param user_group_name 分组名称
	 * @return code 200->成功
	 */
    public function addUserGroup($data){
        $token = $data['token'];
        $user_group_name = $data['user_group_name'];

        $request_data = [
            'user_group_name' => $user_group_name
        ];

        $client = new \GuzzleHttp\Client();
        $res = $client->request(
            'POST', 
            combinationApiUrl('/api.php/MeiBackstage/addUserGroup'), 
            [
                'json' => $request_data,
                'timeout' => 3,
                'headers' => [
                    'token' => $token
                ]
            ]
        );

        return json_decode($res->getBody(),true);
    }

    /**
     * 删除子账号账户分组
	 * @param user_group_id 分组id
	 * @return code 200->成功
	 */
    public function delUserGroup($data){
        $user_group_id = $data['user_group_id'];
        $token = $data['token'];

        $request_data = [
            'user_group_id' => $user_group_id
        ];

        $client = new \GuzzleHttp\Client();
        $res = $client->request(
            'POST', 
            combinationApiUrl('/api.php/MeiBackstage/delUserGroup'), 
            [
                'json' => $request_data,
                'timeout' => 3,
                'headers' => [
                    'token' => $token
                ]
            ]
        );

        return json_decode($res->getBody(),true);
    }
}