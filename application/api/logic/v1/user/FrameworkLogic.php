<?php
namespace app\api\logic\v1\user;
use think\Model;
use think\Db;
use app\api\common\Common;
use EasyWeChat\Foundation\Application;

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

        foreach($list as $k=>$v){
            if($v['parent_id'] != -1){
                $list[$k]['parent_name'] = Db::name('user_group')->where(['user_group_id'=>$v['parent_id']])->value('user_group_name');
            }else{
                $list[$k]['parent_name'] = '顶级部门';
            }
        }

        return msg(200,'success',empty($list) == true ? [] : $list);
    }

    /**
     * 添加岗位
	 * @param company_id 商户id
	 * @param position_id 岗位id（存在则编辑）
	 * @param position_name 岗位名称
	 * @param user_group_id 所属部门id
	 * @param describe 岗位描述
	 * @return code 200->成功
	 */
    public function addPosition($data){
        $company_id = $data['company_id'];
        $position_id = empty($data['position_id']) == true ? '' : $data['position_id'];
        $position_name = $data['position_name'];
        $user_group_id = $data['user_group_id'];
        $position_superior_id = empty($data['position_superior_id']) == true ? -1 : $data['position_superior_id'];
        $describe = $data['describe'];

        if($position_id){
            Db::name('position')
            ->where(['position_id'=>$position_id,'company_id'=>$company_id])
            ->update([
                'position_name' => $position_name,
                'user_group_id' => $user_group_id,
                'describe' => $describe,
                'position_superior_id' => $position_superior_id
            ]);
        }else{
            $position_id = Db::name('position')->insertGetId([
                'company_id' => $company_id,
                'position_name' => $position_name,
                'user_group_id' => $user_group_id,
                'describe' => $describe,
                'position_superior_id' => $position_superior_id
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
        $user_group_id = empty($data['user_group_id']) == true ? '' : $data['user_group_id'];

        $map['company_id'] = $company_id;

        if($user_group_id){
            $map['user_group_id'] = $user_group_id;
        }

        $list = Db::name('position')->where($map)->order('position_id desc')->select();

        foreach($list as $k=>$v){
            $list[$k]['position_superior_name'] = Db::name('position')->where(['position_id'=>$v['position_superior_id']])->value('position_name');
        }

        return msg(200, 'success', empty($list) == true ? [] : $list);
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

        //添加数据 判断是否达到最大添加客服权限
        if($is_customer_service == 1){
            $max_customer_service = Db::name('company')->where(['company_id'=>$company_id])->value('max_customer_service');
            
            $num = Db::name('customer_service')->where(['company_id'=>$company_id])->group('uid')->count();

            if(!empty($uid)){
                $user_num = Db::name('customer_service')->where(['company_id'=>$company_id,'uid'=>$uid])->group('uid')->count();
                
                if($num >= $max_customer_service && $user_num == 0){
                    return msg(3009,'达到最大可设置的客服数量');
                }
            }else{
                if($num >= $max_customer_service){
                    return msg(3010,'达到最大可设置的客服数量');
                }
            }
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
                'create_time' => date('Y-m-d H:i:s'),
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
            $app = new Application(wxOptions());
            $openPlatform = $app->open_platform;

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
        ->order('create_time desc')
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

    // 职位递归
    function genPositionTree($items,$pid = "position_superior_id") {
        $map  = [];
        $tree = [];    
        foreach ($items as &$it){ $map[$it['position_id']] = &$it; }  //数据的ID名生成新的引用索引树
        foreach ($items as &$it){
            $parent = &$map[$it[$pid]];
            if($parent) {
                $parent['position'][] = &$it;
            }else{
                $tree[] = &$it;
            }
        }
        return $tree;
    }

    // 部门递归
    function genDepartmentTree($items,$pid ="parent_id") {
        $map  = [];
        $tree = [];    
        foreach ($items as &$it){ $map[$it['user_group_id']] = &$it; }  //数据的ID名生成新的引用索引树
        foreach ($items as &$it){
            $parent = &$map[$it[$pid]];
            if($parent) {
                $parent['department'][] = &$it;
            }else{
                $tree[] = &$it;
            }
        }
        return $tree;
    }

    /**
     * 获取组织架构图数据
	 * @param company_id 商户id
	 * @return code 200->成功
	 */
    public function getFrameworkData($company_id){
        //获取职位数据
        $position_list = Db::name('position')->where(['company_id'=>$company_id])->field('position_id,position_name,user_group_id,position_superior_id')->select();
        foreach($position_list as $k=>$v){
            if($v['position_id'] != -1){
                $position_list[$k]['user_list'] = Db::name('user')->where(['company_id'=>$company_id,'position_id'=>$v['position_id'],'user_state'=>1,'user_type'=>4])->field('uid,phone_no,user_name')->select();
            }else{
                $position_list[$k]['user_list'] = [];
            }

            $position_list[$k]['position'] = [];
        }

        $position_arr = $this->genPositionTree($position_list, 'position_superior_id');

        //获取部门数据
        $group_list = Db::name('user_group')->where(['company_id'=>$company_id])->field('user_group_id,user_group_name,parent_id')->select();
        foreach($group_list as $k=>$v){
            $group_list[$k]['position'] = [];
            $group_list[$k]['department'] = [];

            foreach($position_arr as $c=>$t){
                if($v['user_group_id'] == $t['user_group_id']){
                    $group_list[$k]['position'][] = $t;
                }
            }
        }

        $group_list = $this->genDepartmentTree($group_list, 'parent_id');

        return msg(200,'success',$group_list);
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

        $user_res = Db::name('user')->where(['company_id'=>$company_id,'uid'=>$uid])->find();
        if($user_res['user_type'] == 3){
            $user_list = Db::name('user')
            ->where(['company_id'=>$company_id,'user_type'=>4])
            ->field('uid,phone_no,user_name,sex')
            ->select();

            return msg(200,'success',empty($user_list) == true ? [] : $user_list);
        }

        $group_res = $this->getAllGroupIds($user_res['user_group_id']);

        $position_list = [];

        foreach($group_res as $user_group_id){
            $position_arr = Db::name('position')
            ->where(['company_id'=>$company_id,'position_superior_id'=>-1,'user_group_id'=>$user_group_id])
            ->cache(true,60)
            ->select();
            foreach($position_arr as $c=>$t){
                $arr = $this->getAllPositionIds($t['position_id']);
                foreach($arr as $i=>$h){
                    array_push($position_list,$h);
                }
            }
        }

        $user_list = Db::name('user')
        ->where(['company_id'=>$company_id,'position_id'=>['in',$position_list],'uid'=>['not in',[$uid]]])
        ->field('uid,phone_no,user_name,sex')
        ->select();

        return msg(200,'success',empty($user_list) == true ? [] : $user_list);
    }

    //获取子岗位
    public function getAllPositionIds($position_id){
        //初始化ID数组
        $array[] = $position_id;
        do
        {
            $ids = '';
            $where['position_superior_id'] = array('in',$position_id);
            $cate = Db::name('position')->where($where)->cache(true,60)->select();
            foreach ($cate as $k=>$v)
            {
                $array[] = $v['position_id'];
                $ids .= ',' . $v['position_id'];
            }
            $ids = substr($ids, 1, strlen($ids));
            $position_id = $ids;
        }
        while (!empty($cate));
        return $array;
    }

    //获取子部门
    public function getAllGroupIds($user_group_id){
        //初始化ID数组
        $array[] = $user_group_id;
        do
        {
            $ids = '';
            $where['parent_id'] = array('in',$user_group_id);
            $cate = Db::name('user_group')->where($where)->cache(true,60)->select();
            foreach ($cate as $k=>$v)
            {
                $array[] = $v['user_group_id'];
                $ids .= ',' . $v['user_group_id'];
            }
            $ids = substr($ids, 1, strlen($ids));
            $user_group_id = $ids;
        }
        while (!empty($cate));
        return $array;
    }
}