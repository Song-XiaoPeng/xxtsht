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
	 * @param position_name 岗位名称
	 * @param user_group_id 所属部门id
	 * @param describe 岗位描述
	 * @return code 200->成功
	 */
    public function addPosition($data){
        $company_id = $data['company_id'];
        $position_name = $data['position_name'];
        $user_group_id = $data['user_group_id'];
        $describe = $data['describe'];

        $position_id = Db::name('position')->insertGetId([
            'company_id' => $company_id,
            'position_name' => $position_name,
            'describe' => $describe
        ]);

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

        $del_res = Db::name('position')->where(['position_id'=>$position_id,'company_id'=>$company_id])->delete();

        if($del_res){
            return msg(200, 'success');
        }else{
            return msg(3001,'删除数据失败');
        }
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
     * 添加用户
	 * @param company_id 商户id
	 * @return code 200->成功
	 */
    public function addUser($data){

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
}