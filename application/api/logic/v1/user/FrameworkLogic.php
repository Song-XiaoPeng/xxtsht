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
                $customer_service_res = Db::name('customer_service')->where(['company_id'=>$company_id,'uid'=>$uid])->select();
                if($customer_service_res){
                    foreach($customer_service_res as $k=>$v){
                        try{
                            $token_info = Common::getRefreshToken($v['appid'],$company_id);
                            if($token_info['meta']['code'] == 200){
                                $refresh_token = $token_info['body']['refresh_token'];
                            }else{
                                return $token_info;
                            }

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

    /**
     * 获取用户列表
	 * @param company_id 商户id
	 * @param page 分页参数默认1
	 * @param user_state 账号状态 1正常 -1已停止
	 * @param user_group_id 部门id 选传
	 * @param text 搜索关键词 选传
	 * @return code 200->成功
	 */
    public function getUserList($data){
        $company_id = $data['company_id'];
        $page = $data['page'];
        $user_state = $data['user_state'];
        $user_group_id = empty($data['user_group_id']) == true ? '' : $data['user_group_id'];
        $text = empty($data['text']) == true ? '' : $data['text'];

        //分页
        $page_count = 16;
        $show_page = ($page - 1) * $page_count;

        $map['company_id'] = $company_id;
        $map['user_type'] = 4;
        $map['user_state'] = $user_state;
        if(!empty($user_group_id)){
            $map['user_group_id'] = $user_group_id;
        }

        if(!empty($text)){
            $map['user_name|phone_no'] = array('like',"%$text%");
        }

        $user_list = Db::name('user')
        ->where($map)
        ->limit($show_page,$page_count)
        ->select();

        $count = Db::name('user')->where($map)->count();
        
        foreach($user_list as $k=>$v){
            if($v['position_id'] != -1){
                $user_list[$k]['position_name'] = Db::name('position')->where(['position_id'=>$v['position_id']])->cache(true,60)->value('position_name');
            }else{
                $user_list[$k]['position_name'] = null;
            }

            $resources_id = Db::name('user_portrait')->where(['uid'=>$v['uid']])->value('resources_id');
            if($resources_id){
                $user_list[$k]['avatar_url'] = 'http://'.$_SERVER['HTTP_HOST'].'/api/v1/we_chat/Business/getImg?resources_id='.$resources_id;
                $user_list[$k]['portrait'] = $resources_id;
            }else{
                $user_list[$k]['avatar_url'] = 'http://wxyx.lyfz.net/Public/mobile/images/default_portrait.jpg';
                $user_list[$k]['portrait'] = $resources_id;
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
                $user_list[$k]['customer_service_list'] = [];
                $user_list[$k]['model_list'] = [];
            }

            if(count($customer_service_list) > 0){
                $user_list[$k]['is_customer_service'] = 1;
            }else{
                $user_list[$k]['is_customer_service'] = -1;
            }

            $user_list[$k]['client_version'] = empty($v['client_version']) == true ? null : $v['client_version'];
            $user_list[$k]['client_network_mac'] = empty($v['client_network_mac']) == true ? null : $v['client_network_mac'];

            if($v['user_group_id'] != -1){
                $user_group_name = Db::name('user_group')->where(['company_id'=>$company_id,'user_group_id'=>$v['user_group_id']])->cache(true,60)->value('user_group_name');

                $user_list[$k]['user_group_name'] = empty($user_group_name) == true ? '未分组' : $user_group_name;
            }else{
                $user_list[$k]['user_group_name'] = null;
            }

            if($v['user_state'] == 1){
                $user_list[$k]['user_state_name'] = '正常';
            }else{
                $user_list[$k]['user_state_name'] = '禁用';
            }
        }

        $res['data_list'] = count($user_list) == 0 ? array() : $user_list;
        $res['page_data']['count'] = $count;
        $res['page_data']['rows_num'] = $page_count;
        $res['page_data']['page'] = $page;
        
        return msg(200, 'success', $res);
    }

    /**
     * 获取组织架构图数据
	 * @param company_id 商户id
	 * @return code 200->成功
	 */
    public function getFrameworkData($company_id){
        //获取部门数据
        $group_list = Db::name('user_group')->where(['company_id'=>$company_id])->select();

        $parent_list = [];
        $son_list = [];

        foreach($group_list as $k=>$v){
            //获取岗位信息
            $position_list = Db::name('position')
            ->where(['company_id'=>$company_id,'user_group_id'=>$v['user_group_id']])
            ->field('')
            ->select();

            foreach($position_list as $i=>$t){
                $user_list = Db::name('user')
                ->where(['company_id'=>$company_id,'position_id'=>$t['position_id']])
                ->field('uid,user_name,phone_no')
                ->cache(true,60)
                ->select();

                $position_list[$i]['staff'] = $user_list;
            }

            $v['position'] = $position_list;

            //获取领导层信息
            $person_charge = json_decode($v['person_charge'],true);
            if($person_charge){
                foreach($person_charge as $i=>$uid){
                    $user_arr[] = Db::name('user')
                    ->where(['company_id'=>$company_id,'uid'=>$uid])
                    ->field('uid,user_name,phone_no')
                    ->cache(true,60)
                    ->find();
                }

                $v['person_charge'] = $user_arr;
            }else{
                $v['person_charge'] = [];
            }

            if($v['parent_id'] == -1){
                array_push($parent_list,$v);
            }else{
                array_push($son_list,$v);
            }
        }

        foreach($parent_list as $k=>$v){
            foreach($son_list as $c=>$t){
                if($t['parent_id'] == $v['user_group_id']){
                    $parent_list[$k]['son_list'][] = $t;
                }
            }
        }

        return msg(200,'success',$parent_list);
    }

    /**
     * 设置部门领导
	 * @param company_id 商户id
	 * @param set_uid 设置的账号uid
	 * @param user_group_id 分组id
	 * @return code 200->成功
	 */
    public function setLeader($data){
        $company_id = $data['company_id'];
        $set_uid = intval($data['set_uid']);
        $user_group_id = $data['user_group_id'];

        $map['company_id'] = $company_id;
        $map['person_charge'] = ['like', "%$set_uid%"];
        $user_group_res = Db::name('user_group')->where($map)->find();

        $person_charge = json_decode($user_group_res['person_charge'], true);

        $is_reset = false;

        if ($person_charge) {
            foreach($person_charge as $k=>$v){
                if($v == $set_uid){
                    $is_reset = true;
                    unset($person_charge[$k]);
                }
            }
        }

        if($is_reset){
            $person_charge = array_values($person_charge);

            Db::name('user_group')
            ->where(['user_group_id' => $user_group_res['user_group_id']])
            ->update(['person_charge' => json_encode($person_charge)]);
        }

        $group_res = Db::name('user_group')
        ->where(['user_group_id' => $user_group_id])
        ->find();

        $person_charge_arr = json_decode($group_res['person_charge'],true);
        if(!$person_charge_arr){
            $person_charge_arr = [];
        }

        array_push($person_charge_arr, $set_uid);

        $update_res = Db::name('user_group')
        ->where(['user_group_id' => $user_group_id])
        ->update(['person_charge' => json_encode($person_charge_arr)]);

        if($update_res !== false){
            return msg(200,'success');
        }else{
            return msg(3001,'更新数据失败');
        }
    }

    /**
     * 获取我的下属账号信息list
	 * @param company_id 商户id
	 * @param uid 登录账号uid
	 * @return code 200->成功
	 */
    public function getSubordinateList($data){
        $company_id = $data['company_id'];
        $uid = $data['uid'];
        $user_type = $data['user_type'];

        $user_group_arr = Db::name('user_group')->where(['company_id'=>$company_id])->field('user_group_id,user_group_name')->cache(true,60)->select();

        if($user_type == 3){
            $user_list = Db::name('user')->where(['company_id'=>$company_id])->cache(true,60)->field('uid,phone_no,user_name,user_group_id')->select();
        }else{
            $map['person_charge'] = ['like',"%$uid%"];
            $map['company_id'] = $company_id;

            $user_group = Db::name('user_group')
            ->where($map)
            ->cache(true,60)
            ->find();

            if ($user_group['parent_id'] == -1) {
                $son_list = Db::name('user_group')
                ->where(['parent_id'=>$user_group['user_group_id']])
                ->cache(true,60)
                ->select();

                $son_list = array_column($son_list, 'user_group_id');

                $user_list = Db::name('user')->where(['company_id'=>$company_id,'user_group_id'=>['in',$son_list]])->field('uid,phone_no,user_name,user_group_id')->select();
            }
        }

        if(!empty($user_list)){
            foreach($user_group_arr as $key=>$value){
                $user_group_arr[$key]['uid_list'] = [];
    
                foreach($user_list as $i=>$t){
                    if($value['user_group_id'] == $t['user_group_id']){
                        $user_group_arr[$key]['uid_list'][] = $t;
                    }
                }
            }
        }else{
            $user_group_arr = [];
        }

        return msg(200,'success',$user_group_arr);
    }
}