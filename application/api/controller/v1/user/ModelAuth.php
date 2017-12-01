<?php
namespace app\api\controller\v1\user;
use app\api\common\Auth;

//后台模块授权相关操作
class ModelAuth extends Auth{
    /**
     * 获取授权模块列表
     * 请求类型 get
	 * 传入JSON格式: 
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"},"body":[{"model_id":1,"model_name":"客服接待","superior_id":-1},{"model_id":2,"model_name":"客户管理","superior_id":-1},{"model_id":3,"model_name":"跟踪提醒","superior_id":-1},{"model_id":4,"model_name":"监控","superior_id":-1},{"model_id":5,"model_name":"数据分析","superior_id":-1},{"model_id":6,"model_name":"访问情况分析","superior_id":5},{"model_id":7,"model_name":"粉丝数量分析","superior_id":5},{"model_id":8,"model_name":"工作质量分析","superior_id":5},{"model_id":9,"model_name":"质量分析","superior_id":5},{"model_id":10,"model_name":"微信营销平台","superior_id":-1},{"model_id":11,"model_name":"授权接入","superior_id":10},{"model_id":12,"model_name":"增强功能","superior_id":10},{"model_id":13,"model_name":"带参二维码","superior_id":10},{"model_id":14,"model_name":"群发激活","superior_id":10}]}
	 * API_URL_本地: http://localhost:91/api/v1/user/ModelAuth/getModelAuthList
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/user/ModelAuth/getModelAuthList
	 * @return code 200->成功
	 */
    public function getModelAuthList () {
        if($this->user_type != 3){
            return msg(6009,'非管理员账户无权操作');
        }

        $data['company_id'] = $this->company_id;
        $data['uid'] = $this->uid;
        
        return \think\Loader::model('MaModel','logic\v1\user')->getModelAuthList($data);
    }

    /**
     * 设置账号模块授权
     * 请求类型 post
	 * 传入JSON格式: {"uid":"6454","model_list":[1,2]}
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"},"body":null}
	 * API_URL_本地: http://localhost:91/api/v1/user/ModelAuth/setUserModelAuth
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/user/ModelAuth/setUserModelAuth
	 * @return code 200->成功
	 */
    public function setUserModelAuth(){
        if($this->user_type != 3){
            return msg(6009,'非管理员账户无权操作');
        }

        $data = input('put.');
        $data['company_id'] = $this->company_id;

        if($this->uid == $data['uid']){
            return msg(3001,'管理员账户无法设置');
        }
        
        return \think\Loader::model('MaModel','logic\v1\user')->setUserModelAuth($data);
    }


}