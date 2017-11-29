<?php
namespace app\api\logic\v1\user;
use think\Model;
use think\Db;
use EasyWeChat\Foundation\Application;
use app\api\common\Common;

class UserOperationModel extends Model {
    /**
     * 添加子账号
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
     * 获取子账号列表
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

    /**
     * 设置子账号状态
	 * @param token 登录token
	 * @param uid 要设置离职的用户uid
	 * @param state -1 设置为离职 1恢复正常
	 * @return code 200->成功 3001->更新数据失败
	 */
    public function setUserState($data){
        $uid = $data['uid'];
        $state = $data['state'] == 1 ? 1 : -1;
        $token = $data['token'];

        $request_data = [
            'uid' => $uid,
            'state' => $state
        ];

        $client = new \GuzzleHttp\Client();
        $res = $client->request(
            'POST', 
            combinationApiUrl('/api.php/MeiBackstage/setUserQuit'), 
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
     * 获取我的所有授权公众号或小程序list 及appid
	 * @param company_id 商户company_id
	 * @return code 200->成功
	 */
    public function getWxAuthList($company_id){
        $res = Db::name('openweixin_authinfo')->where(['company_id'=>$company_id])->field('appid,logo,qrcode_url,principal_name,signature,type,nick_name')->select();

        return msg(200,'success',$res);
    }

    /**
     * 设置账号头像
	 * @param uid 设置的用户uid
	 * @param company_id 商户company_id
	 * @param resources_id 资源id
	 * @return code 200->成功
	 */
    public function setUserPortrait($uid,$company_id,$resources_id){
        $resources_res = Db::name('resources')->where(['company_id'=>$company_id,'resources_id'=>$resources_id,'resources_type'=>2])->find();
        if(!$resources_res){
            return msg(3003,'resources_id参数错误');
        }

        $insert_res = Db::name('user_portrait')->insert([
            'uid' => $uid,
            'company_id' => $company_id,
            'resources_id' => $resources_id,
        ]);

        $customer_service_list = Db::name('customer_service')->where(['uid'=>$uid,'company_id'=>$company_id])->select();

        foreach($customer_service_list as $k=>$v){
            $token_info = Common::getRefreshToken($v['appid'],$company_id);
            if($token_info['meta']['code'] == 200){
                $refresh_token = $token_info['body']['refresh_token'];
            }else{
                return $token_info;
            }
    
            $app = new Application(wxOptions());
            $openPlatform = $app->open_platform;
    
            try{
                $wx_sign = 'lyfzkf@'.$v['uid'];
                $staff = $openPlatform->createAuthorizerApplication($v['appid'],$refresh_token)->staff;
                $staff->avatar($wx_sign, $resources_res['resources_route']);
            }catch (\Exception $e) {
                continue;
            }
        }

        if($insert_res){
            return msg(200,'success');
        }else{
            return msg(3001,'设置失败');
        }
    }

    /**
     * 删除子账号客服权限
	 * @param company_id 商户company_id
	 * @param appid 微信公众号appid
	 * @param uid 账户uid
	 * @return code 200->成功
	 */
    public function delUserCustomerService($data){
        $company_id = $data['company_id'];
        $appid = $data['appid'];
        $uid = $data['uid'];

        $res = Db::name('customer_service')->where(['appid'=>$appid,'uid'=>$uid,'company_id'=>$company_id])->find();
        if(!$res){
            return msg('客户账户权限配置信息不存在');
        }

        $token_info = Common::getRefreshToken($appid,$company_id);
        if($token_info['meta']['code'] == 200){
            $refresh_token = $token_info['body']['refresh_token'];
        }else{
            return $token_info;
        }

        $app = new Application(wxOptions());
        $openPlatform = $app->open_platform;

        try{
            $wx_sign = "lyfzkf@$uid";
            $staff = $openPlatform->createAuthorizerApplication($appid,$refresh_token)->staff;
            $staff->delete($wx_sign);
        }catch (\Exception $e) {
            return msg(3001,$e->getMessage());
        }

        Db::name('customer_service')->where(['appid'=>$appid,'uid'=>$uid,'company_id'=>$company_id])->delete();

        return msg(200,'success');
    }

    /**
     * 设置子账户为微信客服账号
	 * @param company_id 商户company_id
	 * @param appid 微信公众号appid
	 * @param uid 账户uid
	 * @param user_name 客服名称
	 * @return code 200->成功
	 */
    public function setUserCustomerService($data){
        $company_id = $data['company_id'];
        $appid = $data['appid'];
        $uid = $data['uid'];
        $user_name = $data['user_name'];
        $token = $data['token'];

        $request_data = [
            'uid' => $uid,
            'company_id' => $company_id
        ];

        $client = new \GuzzleHttp\Client();
        $res = $client->request(
            'POST', 
            combinationApiUrl('/api.php/IvisionBackstage/getUserInfo'), 
            [
                'json' => $request_data,
                'timeout' => 3,
                'headers' => [
                    'token' => $token
                ]
            ]
        );

        $user_info = json_decode($res->getBody(),true);
        if($user_info['meta']['code'] != 200){
            return $user_info;
        }

        $user_info = $user_info['body'];

        if($user_info['company_id'] !== $company_id){
            return msg(3003,'账户不存在');
        }

        $customer_service_res = Db::name('customer_service')->where(['company_id'=>$company_id,'appid'=>$appid,'uid'=>$uid])->find();
        if($customer_service_res){
            return msg(3004,'账户已成为微信客服账号');
        }

        $token_info = Common::getRefreshToken($appid,$company_id);
        if($token_info['meta']['code'] == 200){
            $refresh_token = $token_info['body']['refresh_token'];
        }else{
            return $token_info;
        }

        $app = new Application(wxOptions());
        $openPlatform = $app->open_platform;

        try{
            $wx_sign = "lyfzkf@$uid";
            $staff = $openPlatform->createAuthorizerApplication($appid,$refresh_token)->staff;
            $staff->create($wx_sign, $user_name);
        }catch (\Exception $e) {
            return msg(3001,$e->getMessage());
        }

        Db::name('customer_service')->insert([
            'name' => $user_name,
            'wx_sign' => $wx_sign,
            'appid' => $appid,
            'uid' => $uid,
            'company_id' => $company_id,
            'user_group_id' => $user_info['user_group_id']
        ]);

        return msg(200,'success');
    }

    /**
     * 获取微信客服账号列表
	 * @param company_id 商户company_id
	 * @param appid 微信公众号appid (选传)
	 * @param page 分页参数默认1
	 * @return code 200->成功
	 */
    public function getCustomerServiceList($data){
        $company_id = $data['company_id'];
        $appid = empty($data['appid']) == true ? '' : $data['appid'];
        $page = $data['page'];
        $token = $data['token'];
        
        //分页
        $page_count = 16;
        $show_page = ($page - 1) * $page_count;
        
        $list = Db::name('customer_service')->where(['company_id'=>$company_id,'appid'=>$appid])->limit($show_page,$page_count)->select();
        $count = Db::name('customer_service')->where(['company_id'=>$company_id,'appid'=>$appid])->count();
    
        foreach($list as $k=>$v){
            $v['app_name'] = Db::name('openweixin_authinfo')->where(['appid'=>$v['appid']])->cache(true,60)->value('nick_name');

            $client = new \GuzzleHttp\Client();
            $request_res = $client->request(
                'POST', 
                combinationApiUrl('/api.php/IvisionBackstage/getUserInfo'), 
                [
                    'json' => ['uid'=>$v['uid'],'company_id'=>$company_id],
                    'timeout' => 3,
                    'headers' => [
                        'token' => $token
                    ]
                ]
            );

            $list[$k] = array_merge($v,json_decode($request_res->getBody(),true)['body']);
        }

        $res['data_list'] = count($list) == 0 ? array() : $list;
        $res['page_data']['count'] = $count;
        $res['page_data']['rows_num'] = $page_count;
        $res['page_data']['page'] = $page;
        
        return msg(200,'success',$res);
    }

    /**
     * 修改微信客服名称
	 * @param company_id 商户company_id
	 * @param appid 微信公众号appid
	 * @param uid 账户uid
	 * @param user_name 客服名称
	 * @return code 200->成功
	 */
    public function updateCustomerServiceName($data){
        $company_id = $data['company_id'];
        $appid = $data['appid'];
        $uid = $data['uid'];
        $user_name = $data['user_name'];
        $token = $data['token'];

        $request_data = [
            'uid' => $uid,
            'company_id' => $company_id
        ];

        $client = new \GuzzleHttp\Client();
        $res = $client->request(
            'POST', 
            combinationApiUrl('/api.php/IvisionBackstage/getUserInfo'), 
            [
                'json' => $request_data,
                'timeout' => 3,
                'headers' => [
                    'token' => $token
                ]
            ]
        );

        $user_info = json_decode($res->getBody(),true);
        if($user_info['meta']['code'] != 200){
            return $user_info;
        }

        $user_info = $user_info['body'];

        if($user_info['company_id'] !== $company_id){
            return msg(3003,'账户不存在');
        }

        $token_info = Common::getRefreshToken($appid,$company_id);
        if($token_info['meta']['code'] == 200){
            $refresh_token = $token_info['body']['refresh_token'];
        }else{
            return $token_info;
        }

        $app = new Application(wxOptions());
        $openPlatform = $app->open_platform;

        try{
            $wx_sign = "lyfzkf@$uid";
            $staff = $openPlatform->createAuthorizerApplication($appid,$refresh_token)->staff;
            $staff->update($wx_sign, $user_name);
        }catch (\Exception $e) {
            return msg(3001,$e->getMessage());
        }

        Db::name('customer_service')->where(['appid'=>$appid,'company_id'=>$company_id,'uid'=>$uid])->update([
            'name' => $user_name,
        ]);

        return msg(200,'success');
    }

    /**
     * 设置子账号分组
	 * @param company_id 商户company_id
	 * @param uid 操作人uid
	 * @param set_uid 设置的账号uid
	 * @param user_group_id 账户分组id
	 * @return code 200->成功
	 */
    public function setUserGroup($data){
        $company_id = $data['company_id'];
        $uid = $data['uid'];
        $set_uid = $data['set_uid'];
        $user_group_id = $data['user_group_id'];
        $token = $data['token'];
        
        $login_res = Db::name('login_token')->where(['uid'=>$uid])->find();
        if($login_res['user_type'] != 3){
            return msg(3001,'非管理员无权设置账户分组');
        }

        $request_data = [
            'uid' => $set_uid,
            'user_group_id' => $user_group_id
        ];

        $client = new \GuzzleHttp\Client();
        $request_res = $client->request(
            'POST', 
            combinationApiUrl('/api.php/IvisionBackstage/setUserGroup'), 
            [
                'json' => $request_data,
                'timeout' => 3,
                'headers' => [
                    'token' => $token
                ]
            ]
        );

        $request_res = json_decode($request_res->getBody(),true);
        if($request_res['meta']['code'] != 200){
            return $user_info;
        }

        $update_res = Db::name('login_token')->where(['uid'=>$set_uid])->update(['user_group_id'=>$user_group_id]);
        if($update_res === false){
            return msg(3001,'更新数据失败');
        }   
    
        $customer_service_update_res = Db::name('customer_service')->where(['uid'=>$set_uid])->update(['user_group_id'=>$user_group_id]);
    
        if($customer_service_update_res === false){
            return msg(3002,'更新数据失败');
        }

        return msg(200,'success');
    }
}