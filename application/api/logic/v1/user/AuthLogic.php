<?php
namespace app\api\logic\v1\user;
use think\Model;
use think\Db;

class AuthLogic extends Model {
    /**
     * 账号登录
     * @param phone_no 用户手机
	 * @param password 密码md5
	 * @return code 200->成功
	 */
    public function login ($data) {
        $phone_no = $data['phone_no'];
        $password = $data['password'];
        $client_version = empty($data['version']) == true ? '' : $data['version'];
        $client_network_mac = empty($data['client_network_mac']) == true ? '' : $data['client_network_mac'];
        $time = date('Y-m-d H:i:s');

        if(empty($client_network_mac)){
            return msg(3001,'无法获取客户端硬件识别码');
        }

        $client_version_res = Db::name('client_version')->where(['client_version_id'=>'6'])->cache(true,60)->find();
        if($client_version_res['version'] != $client_version){
            return msg(3006,'请升级到最新版本'.$client_version_res['version']);
        }

        $user_info = Db::name('user')->where(['phone_no'=>$phone_no,'password'=>$password])->find();
        if(!$user_info){
            return msg(3003,'账号或密码错误');
        }

        if($user_info['client_network_mac'] != $client_network_mac && $user_info['is_bind_hardware'] == 1 && $user_info['user_type'] != 3){
            return msg(3009,'不允许在未授权的设备登录');
        }

        $token = md5(uniqid());

        Db::name('user')->where(['company_id'=>$user_info['company_id'],'uid'=>$user_info['uid']])->update([
            'token' => $token
        ]);

        $user_group_name = Db::name('user_group')->where(['user_group_id'=>$user_info['user_group_id']])->value('user_group_name');

        $user_info['group_name'] = empty($user_group_name) == true ? '未分组' : $user_group_name;

        $company_info = Db::name('company')->where(['company_id'=>$user_info['company_id']])->find();

        if(strtotime($time) > strtotime($company_info['expiration_date'])){
            return msg(3010,'账号已过期',['expiration_date'=>$company_info['expiration_date']]);
        }

        $portrait_id = Db::name('user_portrait')->where(['uid'=>$user_info['uid']])->value('resources_id');

        $expiration_date = $company_info['expiration_date'];
        $login_token = $token;
        $address = $company_info['address'];
        $company_id = $company_info['company_id'];
        $uid = $user_info['uid'];
        $company_name = $company_info['company_name'];
        $username = $user_info['user_name'];
        $logo = 'http://wxyx.lyfz.net/Public/Uploads/shop_logo/2017-08-29/59a4e46abcb7d.jpg';
        $phone_no = $user_info['phone_no'];
        $group_name = $user_info['group_name'];
        $user_group_id = $user_info['user_group_id'];
        $user_type = $user_info['user_type'];
        $avatar_url = empty($portrait_id) == true ? 'http://wxyx.lyfz.net/Public/mobile/images/default_portrait.jpg' : 'http://'.$_SERVER['HTTP_HOST'].'/api/v1/we_chat/Business/getImg?resources_id='.$portrait_id;

        if($user_info['user_type'] != 3){
            $model_list = Db::name('model_auth')->where(['company_id'=>$user_info['company_id'],'model_auth_uid'=>$user_info['uid']])->value('model_list');
            
            $model_list = json_decode($model_list);
        }else{
            $model_data = Db::name('model_list')->cache(true,60)->select();

            $model_list = [];

            foreach($model_data as $k=>$v){
                if($v['model_id'] == 1){
                    continue;
                }
                array_push($model_list,$v['model_id']);
            }
        }

        Db::name('customer_service')->where(['company_id'=>$company_id,'uid'=>$uid])->update(['state'=>1]);

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
                'user_type' => $user_type,
                'logo' => $logo,
                'phone_no' => $phone_no,
                'user_group_id' => $user_group_id,
                'group_name' => $group_name,
                'uid' => $uid,
                'avatar_url' => $avatar_url,
                'model_list' => $model_list,
            ]
        );
    }

    /**
     * 校验token
     * @param uid 用户账号uid
     * @param token 账号token
	 * @return code 200->成功
	 */
    public function checkToken($uid,$token){
        $check_res = Db::name('user')->where(['token'=>$token,'uid'=>$uid])->find();
        if($check_res){
            return msg(200,'success');
        }else{
            return msg(3001,'校验失败');
        }
    }

    /**
     * 获取商户所有客服uid
     * @param company_id 商户id
	 * @return code 200->成功
	 */
    public function getUidCompanyId($uid){
        $company_id = Db::name('user')->where(['uid'=>$uid])->cache(true,120)->value('company_id');
        
        return msg(200,'success',['company_id'=>$company_id]);
    }
}