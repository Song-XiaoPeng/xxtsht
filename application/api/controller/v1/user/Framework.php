<?php
namespace app\api\controller\v1\user;
use app\api\common\Auth;

class Framework extends Auth{
    /**
     * 添加部门
     * 请求类型 post
	 * 传入JSON格式: {"user_group_id":"user_group_id...","department_name":"department_name...","parent_id":"parent_id...","desc":"desc.."}
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"}}
	 * API_URL_本地: http://localhost:91/api/v1/user/Framework/addDepartment
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/user/Framework/addDepartment
	 * @param user_group_id 选传存在则修改
	 * @param department_name 部门名称
	 * @param parent_id 上级部门id
	 * @param desc 部门描述
	 * @return code 200->成功
	 */
    public function addDepartment () {
        $data = input('put.');
        $data['company_id'] = $this->company_id;
        
        return \think\Loader::model('FrameworkLogic','logic\v1\user')->addDepartment($data);
	}
	
    /**
     * 获取部门列表
     * 请求类型 get
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"}}
	 * API_URL_本地: http://localhost:91/api/v1/user/Framework/getDepartmentList
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/user/Framework/getDepartmentList
	 * @param company_id 商户id
	 * @return code 200->成功
	 */
    public function getDepartmentList () {       
        return \think\Loader::model('FrameworkLogic','logic\v1\user')->getDepartmentList($this->company_id);
	}
	
    /**
     * 添加岗位
     * 请求类型 post
	 * 传入JSON格式: {"position_name":"测试","user_group_id":"372","describe":"123"}
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"},"body":{"position_id":"4"}}
	 * API_URL_本地: http://localhost:91/api/v1/user/Framework/addPosition
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/user/Framework/addPosition
	 * @param position_name 岗位名称
	 * @return code 200->成功
	 */
    public function addPosition () {
        $data = input('put.');
        $data['company_id'] = $this->company_id;
        
        return \think\Loader::model('FrameworkLogic','logic\v1\user')->addPosition($data);
	}

    /**
     * 删除岗位
	 * 请求类型 post
	 * 传入JSON格式: {"position_id":"12"}
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"},"body":""}
	 * API_URL_本地: http://localhost:91/api/v1/user/Framework/delPosition
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/user/Framework/delPosition
	 * @param position_id 岗位id
	 * @return code 200->成功
	 */
	public function delPosition(){
        $data = input('put.');
        $data['company_id'] = $this->company_id;
        
        return \think\Loader::model('FrameworkLogic','logic\v1\user')->delPosition($data);
	}

    /**
     * 获取岗位列表
	 * 请求类型 post
	 * 传入JSON格式: {"user_group_id":"372"}
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"},"body":[{"position_id":5,"position_name":"测试","user_group_id":372,"company_id":"51454009d703c86c91353f61011ecf2f"},{"position_id":6,"position_name":"程序员","user_group_id":372,"company_id":"51454009d703c86c91353f61011ecf2f"}]}
	 * API_URL_本地: http://localhost:91/api/v1/user/Framework/getPositionList
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/user/Framework/getPositionList
	 * @param user_group_id 部门id
	 * @return code 200->成功
	 */
	public function getPositionList(){
        $data = input('put.');
        $data['company_id'] = $this->company_id;
        
        return \think\Loader::model('FrameworkLogic','logic\v1\user')->getPositionList($data);
	}

    /**
     * 删除部门
	 * 请求类型 post
	 * 传入JSON格式: {"user_group_id":"372"}
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"},"body":null}
	 * API_URL_本地: http://localhost:91/api/v1/user/Framework/delDepartment
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/user/Framework/delDepartment
	 * @param user_group_id 部门id
	 * @return code 200->成功
	 */
	public function delDepartment(){
        $data = input('put.');
        $data['company_id'] = $this->company_id;
        
        return \think\Loader::model('FrameworkLogic','logic\v1\user')->delDepartment($data);
	}

    /**
     * 添加编辑用户
	 * 请求类型 post
	 * 传入JSON格式: {"phone_no":"13249318008","user_name":"张三","password":"password..","user_group_id":"user_group_id..","position_id":"position_id..","portrait":"portrait..","is_customer_service":1,"sex":1}
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"},"body":{"uid":33}}
	 * API_URL_本地: http://localhost:91/api/v1/user/Framework/addUser
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/user/Framework/addUser
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
	public function addUser(){
        $data = input('put.');
        $data['company_id'] = $this->company_id;
        
        return \think\Loader::model('FrameworkLogic','logic\v1\user')->addUser($data);
	}

    /**
     * 获取用户列表
	 * 请求类型 post
	 * 传入JSON格式: {"page":1,"user_state":1,"user_group_id":"132","text":"张三"}
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"},"body":{"uid":33}}
	 * API_URL_本地: http://localhost:91/api/v1/user/Framework/getUserList
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/user/Framework/getUserList
	 * @param page 分页参数默认1
	 * @param user_state 账号状态 1正常 -1已停止
	 * @param user_group_id 部门id 选传
	 * @param text 搜索关键词 选传
	 * @return code 200->成功
	 */
	public function getUserList(){
        $data = input('put.');
        $data['company_id'] = $this->company_id;
        
        return \think\Loader::model('FrameworkLogic','logic\v1\user')->getUserList($data);
	}

    /**
     * 获取我的下属账号信息list
	 * 请求类型 get
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"},"body":[{"user_group_id":409,"user_group_name":"惠州团队","uid_list":[{"uid":8051,"phone_no":"18675770677","user_name":"杨玉宝","user_group_id":409},{"uid":8059,"phone_no":"13233223322","user_name":"测试1","user_group_id":409},{"uid":8060,"phone_no":"13255225522","user_name":"阿凡达","user_group_id":409},{"uid":8061,"phone_no":"13939393939","user_name":"测试2","user_group_id":409}]},{"user_group_id":410,"user_group_name":"武汉团队","uid_list":[{"uid":8052,"phone_no":"18627819931","user_name":"鲁杰","user_group_id":410}]},{"user_group_id":411,"user_group_name":"西安团队","uid_list":[{"uid":8053,"phone_no":"18634397484","user_name":"朱启章","user_group_id":411}]},{"user_group_id":412,"user_group_name":"总经办","uid_list":[{"uid":8054,"phone_no":"18665281861","user_name":"钟北清","user_group_id":412}]},{"user_group_id":413,"user_group_name":"CEO领导层","uid_list":[]},{"user_group_id":416,"user_group_name":"afwcxbb","uid_list":[]}]}
	 * API_URL_本地: http://localhost:91/api/v1/user/Framework/getSubordinateList
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/user/Framework/getSubordinateList
	 * @return code 200->成功
	 */
	public function getSubordinateList(){
        $data['company_id'] = $this->company_id;
        $data['uid'] = $this->uid;
        $data['user_type'] = $this->user_type;
        
        return \think\Loader::model('FrameworkLogic','logic\v1\user')->getSubordinateList($data);
	}

    /**
     * 获取组织架构图数据
	 * 请求类型 get
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"},"body":{"uid":33}}
	 * API_URL_本地: http://localhost:91/api/v1/user/Framework/getFrameworkData
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/user/Framework/getFrameworkData
	 * @return code 200->成功
	 */
	public function getFrameworkData(){
        return \think\Loader::model('FrameworkLogic','logic\v1\user')->getFrameworkData($this->company_id);
	}
}