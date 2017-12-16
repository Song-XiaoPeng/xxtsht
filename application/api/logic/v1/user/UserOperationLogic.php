<?php
namespace app\api\logic\v1\user;
use think\Model;
use think\Db;
use EasyWeChat\Foundation\Application;
use app\api\common\Common;

class UserOperationLogic extends Model {
    /**
     * 添加子账号
	 * @param user_group_id 权限分组id
	 * @param phone_no 子账号手机(账号)
	 * @param password 子账号登录密码md5值
	 * @param company_id 商户id
	 * @return code 200->成功
	 */
    public function addAccountNumber($data){
        $phone_no = $data['phone_no'];
        $password = $data['password'];
        $user_name = $data['user_name'];
        $user_group_id = $data['user_group_id'];
        $company_id = $data['company_id'];

        $insert_data = [
            'user_group_id' => $user_group_id,
            'phone_no' => $phone_no,
            'password' => $password,
            'company_id' => $company_id,
            'user_name' => $user_name,
            'user_type' => 4,
            'create_time' => date('Y-m-d H:i:s'),
        ];

        $user_info = Db::name('user')->where(['phone_no'=>$phone_no])->find();
        if($user_info){
            return msg(3002,'账号已存在请更换');
        }

        $add_res = Db::name('user')->insert($insert_data);
        if($add_res){
            return msg(200,'success');
        }else{
            return msg(3001,'插入数据失败');
        }
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
        $company_id = $data['company_id'];
        $user_group_id = empty($data['user_group_id']) == true ? '' : $data['user_group_id'];

        //分页
        $page_count = 16;
        $show_page = ($page - 1) * $page_count;

        $user_list = Db::name('user')->where(['company_id'=>$company_id])->limit($show_page,$page_count)->select();
        $count = Db::name('user')->where(['company_id'=>$company_id])->count();

        foreach($user_list as $k=>$v){
            $resources_id = Db::name('user_portrait')->where(['uid'=>$v['uid']])->value('resources_id');
            if($resources_id){
                $user_list[$k]['avatar_url'] = 'http://'.$_SERVER['HTTP_HOST'].'/api/v1/we_chat/Business/getImg?resources_id='.$resources_id;
            }else{
                $user_list[$k]['avatar_url'] = 'http://wxyx.lyfz.net/Public/mobile/images/default_portrait.jpg';
            }

            if($v['user_type'] != 3){
                $model_list = Db::name('model_auth')->where(['company_id'=>$company_id,'model_auth_uid'=>$v['uid']])->value('model_list');
                
                $user_list[$k]['model_list'] = json_decode($model_list);


                $customer_service_list = Db::name('customer_service')->where(['company_id'=>$company_id,'uid'=>$v['uid']])->select();

                foreach($customer_service_list as $key=>$value){
                    $customer_service_list[$key]['app_name'] = Db::name('openweixin_authinfo')->where(['appid'=>$value['appid']])->cache(true,60)->value('nick_name');
                }

                $user_list[$k]['customer_service_list'] = empty($customer_service_list) == true ? null : $customer_service_list;
            }else{
                $user_list[$k]['model_list'] = null;
            }

            $user_list[$k]['client_version'] = empty($v['client_version']) == true ? null : $v['client_version'];
            $user_list[$k]['client_network_mac'] = empty($v['client_network_mac']) == true ? null : $v['client_network_mac'];

            $user_group_name = Db::name('user_group')->where(['company_id'=>$company_id,'user_group_id'=>$v['user_group_id']])->cache(true,60)->value('user_group_name');

            $user_list[$k]['user_group_name'] = empty($user_group_name) == true ? '未分组' : $user_group_name;

            if($v['user_state'] == 1){
                $user_list[$k]['user_state_name'] = '正常';
            }else{
                $user_list[$k]['user_state_name'] = '禁用';
            }
        }

        $page_data['count'] = $count;
        $page_data['rows_num'] = $page_count;
        $page_data['page'] = $page;

        return msg(200,'success',[
            'user_list' => $user_list,
            'page_data' => $page_data
        ]);
    }

    /**
     * 获取子账号分组
	 * @param token 账号登录token
	 * @return code 200->成功
	 */
    public function getUserGroup($company_id){
        $group_res = Db::name('user_group')->where(['company_id'=>$company_id])->select();

        return msg(200,'success',$group_res);
    }

    /**
     * 添加子账号账户分组
	 * @param company_id 商户company_id
	 * @param user_group_name 分组名称
	 * @return code 200->成功
	 */
    public function addUserGroup($data){
        $company_id = $data['company_id'];
        $user_group_name = $data['user_group_name'];

        $add_res = Db::name('user_group')->insert([
            'user_group_name' => $user_group_name,
            'company_id' => $company_id
        ]);

        if($add_res){
            return msg(200,'success');
        }else{
            return msg(3001,'插入数据失败');
        }
    }

    /**
     * 删除子账号账户分组
	 * @param user_group_id 分组id
	 * @return code 200->成功
	 */
    public function delUserGroup($data){
        $user_group_id = $data['user_group_id'];
        $company_id = $data['company_id'];

        $del_res = Db::name('user_group')->where(['company_id'=>$company_id,'user_group_id'=>$user_group_id])->delete();
        if($del_res){
            return msg(200,'success');
        }else{
            return msg(3001,'删除失败');
        }
    }

    /**
     * 设置子账号状态
	 * @param company_id 商户company_id
	 * @param uid 要设置离职的用户uid
	 * @param state -1 设置为离职 1恢复正常
	 * @return code 200->成功 3001->更新数据失败
	 */
    public function setUserState($data){
        $uid = $data['uid'];
        $state = $data['state'] == 1 ? 1 : -1;
        $company_id = $data['company_id'];

        $update_res = Db::name('user')
        ->where(['company_id'=>$company_id,'uid'=>$uid])
        ->update([
            'user_state'=>$state
        ]);

        if($update_res !== false){
            return msg(200,'success');
        }else{
            return msg(3001,'更新数据失败');
        }   
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
        $resources_res = Db::name('resources')->where(['company_id'=>$company_id,'resources_id'=>$resources_id])->find();
        if(!$resources_res){
            return msg(3003,'resources_id参数错误');
        }

        $file_suffix = ['jpg','png','gif','jpeg','bmp'];
        if(!in_array($resources_res['file_suffix_name'], $file_suffix)){
            return msg(3003,'图像文件不合法');
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
                $staff = $openPlatform->createAuthorizerApplication($v['appid'],$refresh_token)->staff;
                $staff->avatar($v['wx_sign'], $resources_res['resources_route']);
            }catch (\Exception $e) {
                continue;
            }
        }

        if($insert_res){
            return msg(200,'success',['avatar_url'=>'http://'.$_SERVER['HTTP_HOST'].'/api/v1/we_chat/Business/getImg?resources_id='.$resources_id]);
        }else{
            return msg(3001,'设置失败');
        }
    }

    /**
     * 删除子账号客服权限
	 * @param company_id 商户company_id
	 * @param uid 账户uid
	 * @return code 200->成功
	 */
    public function delUserCustomerService($data){
        $company_id = $data['company_id'];
        $uid = $data['uid'];

        $openweixin_authinfo_res = Db::name('openweixin_authinfo')->where(['company_id'=>$company_id])->select();

        $success = 0;

        $app = new Application(wxOptions());
        $openPlatform = $app->open_platform;

        foreach($openweixin_authinfo_res as $value){
            $token_info = Common::getRefreshToken($appid,$company_id);
            if($token_info['meta']['code'] == 200){
                $refresh_token = $token_info['body']['refresh_token'];
            }else{
                return $token_info;
            }
    
            try{
                $wx_sign = "lyfzkf@$uid";
                $staff = $openPlatform->createAuthorizerApplication($appid,$refresh_token)->staff;
                $staff->delete($wx_sign);

                $success++;
            }catch (\Exception $e) {
                return msg(3001,$e->getMessage());
            }
    
            Db::name('customer_service')->where(['appid'=>$appid,'uid'=>$uid,'company_id'=>$company_id])->delete();
        }

        if($success == count($openweixin_authinfo_res)){
            return msg(200,'success');
        }else{
            return msg(3001,'删除失败');
        }
    }

    /**
     * 设置子账户为微信客服账号
	 * @param company_id 商户company_id
	 * @param uid 账户uid
	 * @param user_name 客服名称
	 * @return code 200->成功
	 */
    public function setUserCustomerService($data){
        $company_id = $data['company_id'];
        $uid = $data['uid'];
        $user_name = $data['user_name'];

        $user_info = Db::name('user')->where(['company_id'=>$company_id,'uid'=>$uid])->find();
        if($user_info){
            return msg(3001,'子账号不存在');
        }

        $openweixin_authinfo_res = Db::name('openweixin_authinfo')->where(['company_id'=>$company_id])->select();

        $app = new Application(wxOptions());
        $openPlatform = $app->open_platform;

        foreach($openweixin_authinfo_res as $value){
            $token_info = Common::getRefreshToken($value['appid'],$company_id);
            if($token_info['meta']['code'] == 200){
                $refresh_token = $token_info['body']['refresh_token'];
            }else{
                return $token_info;
            }

            $customer_service_res = Db::name('customer_service')->where(['company_id'=>$company_id,'appid'=>$value['appid'],'uid'=>$uid])->find();
            if($customer_service_res){
                continue;
            }

            try{
                $wx_sign = "lyfzkf@$uid";
                $staff = $openPlatform->createAuthorizerApplication($value['appid'],$refresh_token)->staff;
                $staff->create($wx_sign, $user_name);
            }catch (\Exception $e) {
                return msg(3001,$e->getMessage());
            }
    
            $resources_id = Db::name('user_portrait')->where(['uid'=>$uid,'company_id'=>$company_id])->value('resources_id');
            if($resources_id){
                $resources_route = Db::name('resources')->where(['company_id'=>$company_id,'resources_id'=>$resources_id])->value('resources_route');
                if($resources_route){
                    try{
                        $staff->avatar($wx_sign, $resources_route);
                    }catch (\Exception $e) {
                    }
                }
            }
    
            Db::name('customer_service')->insert([
                'name' => $user_name,
                'wx_sign' => $wx_sign,
                'appid' => $value['appid'],
                'uid' => $uid,
                'company_id' => $company_id,
                'user_group_id' => $user_info['user_group_id']
            ]);    
        }

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
        
        //分页
        $page_count = 16;
        $show_page = ($page - 1) * $page_count;
        
        $list = Db::name('customer_service')->where(['company_id'=>$company_id,'appid'=>$appid])->limit($show_page,$page_count)->select();
        $count = Db::name('customer_service')->where(['company_id'=>$company_id,'appid'=>$appid])->count();
    
        foreach($list as $k=>$v){
            $v['app_name'] = Db::name('openweixin_authinfo')->where(['appid'=>$v['appid']])->cache(true,60)->value('nick_name');

            $portrait_id = Db::name('user_portrait')->where(['uid'=>$v['uid']])->value('resources_id');

            $resources_id = Db::name('user_portrait')->where(['uid'=>$v['uid'],'company_id'=>$company_id])->value('resources_id');

            $user_info['avatar_url'] = empty($resources_id) == true ? 'http://wxyx.lyfz.net/Public/mobile/images/default_portrait.jpg' : 'http://'.$_SERVER['HTTP_HOST'].'/api/v1/we_chat/Business/getImg?resources_id='.$resources_id;

            $list[$k] = array_merge($v,$user_info);
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

        $user_info = Db::name('user')->where(['company_id'=>$company_id,'uid'=>$uid])->find();

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
        
        $login_res = Db::name('user')->where(['uid'=>$uid])->find();
        if($login_res['user_type'] != 3){
            return msg(3001,'非管理员无权设置账户分组');
        }

        $group_res = Db::name('user_group')->where(['company_id'=>$company_id,'user_group_id'=>$user_group_id])->find();
        if(!$group_res){
            return msg(3005,'分组不存在');
        }

        $update_res = Db::name('user')->where(['uid'=>$set_uid])->update(['user_group_id'=>$user_group_id]);
        if($update_res === false){
            return msg(3004,'更新数据失败');
        }   
    
        $customer_service_update_res = Db::name('customer_service')->where(['uid'=>$set_uid])->update(['user_group_id'=>$user_group_id]);
    
        if($customer_service_update_res === false){
            return msg(3002,'更新数据失败');
        }

        return msg(200,'success');
    }

    /**
     * 解除子账号硬件绑定
	 * @param company_id 商户company_id
	 * @param uid 子账号uid
	 * @return code 200->成功
	 */
    public function relieveUserBind($company_id,$uid){
        $update_res = Db::name('user')->where(['company_id'=>$company_id,'uid'=>$uid])->update(['client_network_mac'=>null]);

        if($update_res !== false){
            return msg(200,'success');
        }else{
            return msg(3001,'更新数据失败');
        }
    }

    /**
     * 账号注销
	 * @param company_id 商户company_id
	 * @param uid 子账号uid
	 * @return code 200->成功
	 */
    public function accountCancellation($company_id,$uid){
        $update_res = Db::name('customer_service')->where(['company_id'=>$company_id,'uid'=>$uid])->update(['state'=>-1]);
        if($update_res !== false){
            return msg(200,'success');
        }else{
            return msg(3001,'更新成功');
        }
    }
}