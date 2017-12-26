<?php
namespace app\api\logic\v1\user;
use think\Model;
use think\Db;
use app\api\common\Common;

class FrameworkLogic extends Model {
    /**
     * 添加部门
	 * @param user_group_id 选传存在则修改
	 * @param department_name 部门名称
	 * @param parent_id 上级部门id
	 * @param desc 部门描述
	 * @param company_id 商户id
	 * @return code 200->成功
	 */
    public function addDepartment($data){
        $user_group_id = empty($data['user_group_id']) == true ? '' : $data['user_group_id'];
        $department_name = $data['department_name'];
        $parent_id = empty($data['parent_id']) == true ? '' : $data['parent_id'];
        $desc = $data['desc'];
        $company_id = $data['company_id'];

        if($user_group_id){
            Db::name('user_group')
            ->where(['company_id'=>$company_id,'user_group_id'=>$user_group_id])
            ->update([
                'user_group_name' => $department_name,
                'parent_id' => $parent_id,
                'desc' => $desc
            ]);
        }else{
            $user_group_id = Db::name('user_group')->insertGetId([
                'user_group_name' => $department_name,
                'company_id' => $company_id,
                'parent_id' => $parent_id,
                'desc' => $desc
            ]);
        }

        if($user_group_id){
            return msg(200,'success',['user_group_id'=>$user_group_id]);
        }else{
            return msg(3001,'插入数据失败');
        }
    }

    /**
     * 获取部门列表
	 * @param company_id 商户id
	 * @return code 200->成功
	 */
    public function getDepartmentList($company_id){
        $list = Db::name('user_group')->where(['company_id'=>$company_id])->select();

        return msg(200,'success',$list);
    }

    /**
     * 添加岗位
	 * @param company_id 商户id
	 * @param position_id 岗位id
	 * @param position_name 岗位名称
	 * @param user_group_id 所属部门id
	 * @param describe 岗位描述
	 * @return code 200->成功
	 */
    public function addPosition($data){
        $company_id = $data['company_id'];
        $position_id = $data['position_id'];
        $position_name = $data['position_name'];
        $user_group_id = $data['user_group_id'];
        $describe = $data['describe'];

        if($position_id){
            Db::name('position')
            ->where(['position_id'=>$position_id,'company_id'=>$company_id])
            ->update([
                'position_name' => $position_name,
                'user_group_id' => $user_group_id,
                'describe' => $describe
            ]);
        }else{
            $position_id = Db::name('position')->insertGetId([
                'company_id' => $company_id,
                'position_name' => $position_name,
                'user_group_id' => $user_group_id,
                'describe' => $describe
            ]);
        }

        if($position_id){
            return msg(200, 'success', ['position_id'=>$position_id]);
        }else{
            return msg(3001,'插入数据失败');
        }
    }

    /**
     * 删除岗位
	 * @param company_id 商户id
	 * @param position_id 岗位id
	 * @return code 200->成功
	 */
    public function delPosition($data){
        $company_id = $data['company_id'];
        $position_id = $data['position_id'];

        Db::name('position')->where(['position_id'=>$position_id,'company_id'=>$company_id])->delete();

        $user_list = Db::name('user')->where(['company_id'=>$company_id,'position_id'=>$position_id])->select();

        foreach($user_list as $v){
            Db::name('user')->where(['uid'=>$v['uid']])->update(['position_id'=>-1]);
        }

        return msg(200, 'success');
    }

    /**
     * 获取岗位列表
	 * @param company_id 商户id
	 * @param user_group_id 部门id
	 * @return code 200->成功
	 */
    public function getPositionList($data){
        $company_id = $data['company_id'];
        $user_group_id = $data['user_group_id'];

        $list = Db::name('position')->where(['company_id'=>$company_id,'user_group_id'=>$user_group_id])->select();

        return msg(200, 'success', $list);
    }

    /**
     * 删除部门
	 * @param company_id 商户id
	 * @param user_group_id 部门id
	 * @return code 200->成功
	 */
    public function delDepartment($data){
        $company_id = $data['company_id'];
        $user_group_id = $data['user_group_id'];

        Db::name('user_group')->where(['company_id'=>$company_id,'user_group_id'=>$user_group_id])->delete();

        $user_list = Db::name('user')->where(['company_id'=>$company_id,'user_group_id'=>$user_group_id])->select();

        foreach($user_list as $v){
            Db::name('user')->where(['uid'=>$v['uid']])->update(['user_group_id'=>-1]);
        }

        return msg(200,'success');
    }

    /**
     * 添加编辑用户
	 * @param company_id 商户id
	 * @param uid 存在则编辑
	 * @param phone_no 账号手机
	 * @param user_name 账号姓名
	 * @param password 账户密码md5
	 * @param user_group_id 部门id
	 * @param position_id 岗位id
	 * @param portrait 头像 上传的资源id
	 * @param is_customer_service 是否客服 1是 -1否
	 * @param sex 账号性别 1男 2女
	 * @return code 200->成功
	 */
    public function addUser($data){
        $uid = empty($data['uid']) == true ? '' : $data['uid'];
        $company_id = $data['company_id'];
        $phone_no = $data['phone_no'];
        $user_name = $data['user_name'];
        $password = $data['password'];
        $user_group_id = empty($data['user_group_id']) == true ? -1 : $data['user_group_id'];
        $position_id = empty($data['position_id']) == true ? -1 : $data['position_id'];
        $portrait = $data['portrait'];
        $is_customer_service = $data['is_customer_service'];
        $sex = $data['sex'];

        //判断账号是否存在
        $user_res = Db::name('user')->where(['company_id'=>$company_id,'phone_no'=>$phone_no])->find();
        if(empty($user_res) == false && empty($data['uid']) == true){
            return msg(3001,'账号已存在');
        }

        if(empty($uid)){
            //新增数据
            $uid = Db::name('user')->insertGetId([
                'company_id' => $company_id,
                'phone_no' => $phone_no,
                'password' => $password,
                'user_type' => 4,
                'user_group_id' => $user_group_id,
                'position_id' => $position_id,
                'user_name' => $user_name,
                'sex' => $sex
            ]);

            //设置头像
            Db::name('user_portrait')->insert([
                'uid' => $uid,
                'resources_id' => $portrait,
                'company_id' => $company_id
            ]);
        }else{
            //更新数据
            Db::name('user')
            ->where(['company_id'=>$company_id,'uid'=>$uid])
            ->update([
                'phone_no' => $phone_no,
                'password' => $password,
                'user_group_id' => $user_group_id,
                'position_id' => $position_id,
                'user_name' => $user_name,
                'sex' => $sex
            ]);
        }

        //设为客服逻辑操作
        if($is_customer_service == 1){
            //设置客服操作
            $UserOperationLogic = new UserOperationLogic();
            $UserOperationLogic->setUserCustomerService(['uid'=>$uid,'company_id'=>$company_id,'user_name'=>$user_name]);

            //判断是否执行修改客服姓名
            if($user_res['user_name'] != $user_name){
                $token_info = Common::getRefreshToken($value['appid'],$company_id);
                if($token_info['meta']['code'] == 200){
                    $refresh_token = $token_info['body']['refresh_token'];
                }else{
                    return $token_info;
                }

                $customer_service_res = Db::name('customer_service')->where(['company_id'=>$company_id,'appid'=>$value['appid'],'uid'=>$uid])->find();
                if($customer_service_res){
                    foreach($customer_service_res as $k=>$v){
                        try{
                            $staff = $openPlatform->createAuthorizerApplication($v['appid'],$refresh_token)->staff;
                            $staff->update($v['wx_sign'], $user_name);
                        }catch (\Exception $e) {
                            continue;
                        }
                    }
                }
            }
        }else{
            //取消账号客服操作
            $UserOperationLogic = new UserOperationLogic();
            $UserOperationLogic->delUserCustomerService(['uid'=>$uid,'company_id'=>$company_id]);
        }

        //判断是否替换头像
        $user_portrait = Db::name('user_portrait')->where(['company_id'=>$company_id,'uid'=>$uid])->value('resources_id');
        if($user_portrait != $portrait){
            $UserOperationLogic = new UserOperationLogic();
            $UserOperationLogic->setUserPortrait($uid,$company_id,$portrait);
        }

        return msg(200,'success',['uid'=>$uid]);
    }


}