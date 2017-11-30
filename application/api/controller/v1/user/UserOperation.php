<?php
namespace app\api\controller\v1\user;
use app\api\common\Auth;

//用户账户相关操作
class UserOperation extends Auth{
    /**
     * 添加子账号
     * 请求类型 post
	 * 传入JSON格式: {"user_group_id":"user_group_id...","phone_no":"phone_no...","user_name":"user_name..."}
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"}}
	 * API_URL_本地: http://localhost:91/api/v1/user/UserOperation/addAccountNumber
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/user/UserOperation/addAccountNumber
     * @param phone_no 用户手机
	 * @param password 密码md5
	 * @param user_group_id 分组id
	 * @param user_name 账户名称姓名
	 * @return code 200->成功
	 */
    public function addAccountNumber () {
        $data = input('put.');
        $data['token'] = $this->token;
        
        return \think\Loader::model('UserOperationModel','logic\v1\user')->addAccountNumber($data);
    }

    /**
     * 设置账号头像
     * 请求类型 post
	 * 传入JSON格式: {"uid":"uid...","resources_id":"resources_id..."}
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"}}
	 * API_URL_本地: http://localhost:91/api/v1/user/UserOperation/setUserPortrait
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/user/UserOperation/setUserPortrait
	 * @param uid 设置的用户uid
	 * @param resources_id 资源id
	 * @return code 200->成功
	 */
    public function setUserPortrait () {
        $data = input('put.');
        $data['company_id'] = $this->company_id;
        
        return \think\Loader::model('UserOperationModel','logic\v1\user')->setUserPortrait($data['uid'],$data['company_id'],$data['resources_id']);
    }

    /**
     * 获取子账号列表
     * 请求类型 post
	 * 传入JSON格式: {"user_group_id":5,"page":1}
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"},"body":{"user_list":[{"user_name":"黄晨","user_state":"1","user_group_id":"15","phone_no":"18665281864","create_time":"2017-08-29 14:50:29","uid":"4680","user_type":"4","is_order":"1","user_group_name":"皮肤外科医师","user_state_name":"正常"}],"page_data":{"count":"1","rows_num":16,"page":1}}}
	 * API_URL_本地: http://localhost:91/api/v1/user/UserOperation/getUserList
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/user/UserOperation/getUserList
	 * @param user_group_id 分组id(选传)
	 * @param page 分页参数默认1
	 * @return code 200->成功
	 */
    public function getUserList () {
        $data = input('put.');
        $data['token'] = $this->token;
        
        return \think\Loader::model('UserOperationModel','logic\v1\user')->getUserList($data);
    }

    /**
     * 获取子账号分组
     * 请求类型 get
	 * 传入JSON格式: 无
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"},"body":[{"user_group_id":"1","user_group_name":"技师","data":null,"desc":null,"company_id":"51454009d703c86c91353f61011ecf2f","extra":null},{"user_group_id":"2","user_group_name":"员工","data":null,"desc":null,"company_id":"51454009d703c86c91353f61011ecf2f","extra":null},{"user_group_id":"5","user_group_name":"收银","data":null,"desc":null,"company_id":"51454009d703c86c91353f61011ecf2f","extra":null},{"user_group_id":"11","user_group_name":"清洁","data":null,"desc":null,"company_id":"51454009d703c86c91353f61011ecf2f","extra":null},{"user_group_id":"12","user_group_name":"理发师","data":null,"desc":null,"company_id":"51454009d703c86c91353f61011ecf2f","extra":null},{"user_group_id":"13","user_group_name":"造型师","data":null,"desc":null,"company_id":"51454009d703c86c91353f61011ecf2f","extra":null},{"user_group_id":"14","user_group_name":"美容主诊医师","data":null,"desc":null,"company_id":"51454009d703c86c91353f61011ecf2f","extra":null},{"user_group_id":"15","user_group_name":"皮肤外科医师","data":null,"desc":null,"company_id":"51454009d703c86c91353f61011ecf2f","extra":null}]}
	 * API_URL_本地: http://localhost:91/api/v1/user/UserOperation/getUserGroup
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/user/UserOperation/getUserGroup
	 * @return code 200->成功
	 */
    public function getUserGroup () {
        return \think\Loader::model('UserOperationModel','logic\v1\user')->getUserGroup($this->token);
    }

    /**
     * 添加子账号账户分组
     * 请求类型 post
	 * 传入JSON格式: {"user_group_name":"客服一组"}
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"},"body":{"user_group_id":"43"}}
	 * API_URL_本地: http://localhost:91/api/v1/user/UserOperation/addUserGroup
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/user/UserOperation/addUserGroup
	 * @param user_group_name 分组名称
	 * @return code 200->成功
	 */
    public function addUserGroup () {
        $data = input('put.');
        $data['token'] = $this->token;
        
        return \think\Loader::model('UserOperationModel','logic\v1\user')->addUserGroup($data);
    }

    /**
     * 删除子账号账户分组
     * 请求类型 post
	 * 传入JSON格式: {"user_group_id":"user_group_id..."}
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"}}
	 * API_URL_本地: http://localhost:91/api/v1/user/UserOperation/delUserGroup
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/user/UserOperation/delUserGroup
	 * @param user_group_id 分组id
	 * @return code 200->成功
	 */
    public function delUserGroup () {
        $data = input('put.');
        $data['token'] = $this->token;
        
        return \think\Loader::model('UserOperationModel','logic\v1\user')->delUserGroup($data);
    }

    /**
     * 设置子账号状态
     * 请求类型 post
	 * 传入JSON格式: {"uid":12,"state":"1"}
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"}}
	 * API_URL_本地: http://localhost:91/api/v1/user/UserOperation/setUserState
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/user/UserOperation/setUserState
	 * @param uid 要设置离职的用户uid
	 * @param state -1 设置为离职 1恢复正常
	 * @return code 200->成功 3001->更新数据失败
	 */
    public function setUserState () {
        $data = input('put.');
        $data['token'] = $this->token;
        
        return \think\Loader::model('UserOperationModel','logic\v1\user')->setUserState($data);
    }

    /**
     * 获取我的所有授权公众号或小程序list 及appid
     * 请求类型 get
	 * 传入JSON格式: 无
	 * 返回JSON格式: {"meta":{"code":200,"msg":"success"},"body":[{"appid":"wx52bf4acbefcf4653","logo":"http:\/\/wx.qlogo.cn\/mmopen\/cBkN596BI8KvudqGQDndh8xNPttAoJ7J96PMxfTGPR0j6iaIMGtvopS2LWqhiaUZZ47wZic1S2xPGqLNdChj9j5MvFCzvKQcq4ic\/0","qrcode_url":"http:\/\/mmbiz.qpic.cn\/mmbiz_jpg\/x1g8iaZfs9oByZkPjib3LSW22UoChf6uyEkX0vh84KtdgaHoB1hMxZlpBExYeQs9LjKwaXW3jM8Z1YahuuialfTUg\/0","principal_name":"惠州市利亚方舟科技有限公司","signature":null,"type":1,"nick_name":"利亚方舟软件"}]}
	 * API_URL_本地: http://localhost:91/api/v1/user/UserOperation/getWxAuthList
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/user/UserOperation/getWxAuthList
	 * @return code 200->成功
	 */
    public function getWxAuthList () {
        return \think\Loader::model('UserOperationModel','logic\v1\user')->getWxAuthList($this->company_id);
    }

    /**
     * 设置子账户为微信客服账号
     * 请求类型 post
	 * 传入JSON格式: {"appid":"wx52bf4acbefcf4653","uid":"6092","user_name":"客服二"}
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"},"body":null}
	 * API_URL_本地: http://localhost:91/api/v1/user/UserOperation/setUserCustomerService
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/user/UserOperation/setUserCustomerService
	 * @param appid 微信公众号appid
	 * @param uid 账户uid
	 * @param user_name 客服名称
	 * @return code 200->成功
	 */
    public function setUserCustomerService () {
        $data = input('put.');
        $data['company_id'] = $this->company_id;
        $data['token'] = $this->token;
        
        return \think\Loader::model('UserOperationModel','logic\v1\user')->setUserCustomerService($data);
    }

    /**
     * 删除子账号客服权限
     * 请求类型 post
	 * 传入JSON格式: {"uid":"6092","appid":"wx52bf4acbefcf4653"}
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"},"body":null}
	 * API_URL_本地: http://localhost:91/api/v1/user/UserOperation/delUserCustomerService
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/user/UserOperation/delUserCustomerService
	 * @param appid 微信公众号appid
	 * @param uid 账户uid
	 * @return code 200->成功
	 */
    public function delUserCustomerService () {
        $data = input('put.');
        $data['company_id'] = $this->company_id;
        
        return \think\Loader::model('UserOperationModel','logic\v1\user')->delUserCustomerService($data);
    }

    /**
     * 获取微信客服账号列表
     * 请求类型 post
	 * 传入JSON格式: {"appid":"wx52bf4acbefcf4653","page":"1"}
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"},"body":null}
	 * API_URL_本地: http://localhost:91/api/v1/user/UserOperation/getCustomerServiceList
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/user/UserOperation/getCustomerServiceList
	 * @param appid 微信公众号appid (选传)
	 * @param page 分页参数默认1
	 * @return code 200->成功
	 */
    public function getCustomerServiceList(){
        $data = input('put.');
        $data['company_id'] = $this->company_id;
        $data['token'] = $this->token;
        
        return \think\Loader::model('UserOperationModel','logic\v1\user')->getCustomerServiceList($data);
    }

    /**
     * 修改微信客服名称
     * 请求类型 post
	 * 传入JSON格式: {"appid":"wx52bf4acbefcf4653","uid":"6092","user_name":"客服二"}
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"},"body":null}
	 * API_URL_本地: http://localhost:91/api/v1/user/UserOperation/updateCustomerServiceName
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/user/UserOperation/updateCustomerServiceName
	 * @param appid 微信公众号appid (选传)
	 * @param page 分页参数默认1
	 * @return code 200->成功
	 */
    public function updateCustomerServiceName(){
        $data = input('put.');
        $data['company_id'] = $this->company_id;
        $data['token'] = $this->token;

        return \think\Loader::model('UserOperationModel','logic\v1\user')->updateCustomerServiceName($data);
    }

    /**
     * 设置子账号分组
     * 请求类型 post
	 * 传入JSON格式: {"set_uid":"6454","user_group_id":"12"}
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"},"body":null}
	 * API_URL_本地: http://localhost:91/api/v1/user/UserOperation/setUserGroup
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/user/UserOperation/setUserGroup
	 * @param set_uid 设置的uid
	 * @param user_group_id 设置的新分组id
	 * @return code 200->成功
	 */
    public function setUserGroup(){
        $data = input('put.');
        $data['company_id'] = $this->company_id;
        $data['token'] = $this->token;
        $data['uid'] = $this->uid;

        return \think\Loader::model('UserOperationModel','logic\v1\user')->setUserGroup($data);
    }
}