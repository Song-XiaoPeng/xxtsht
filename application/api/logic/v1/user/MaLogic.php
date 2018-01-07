<?php
namespace app\api\logic\v1\user;
use think\Model;
use think\Db;
use app\api\common\Common;

class MaLogic extends Model {
    /**
     * 获取授权模块列表
	 * @param company_id 商户company_id
	 * @param uid 登录账号uid
	 * @return code 200->成功
	 */
    public function getModelAuthList($data){
        $company_id = $data['company_id'];
        $uid = $data['uid'];

        $res = Db::name('model_list')->where(['is_enable'=>1])->cache(true,60)->select();

        return msg(200,'success',$res);
    }

    /**
     * 设置账号模块授权
	 * @param company_id 商户company_id
	 * @param uid 设置的子账号uid
	 * @param model_list 授权的模块id list
	 * @return code 200->成功
	 */
    public function setUserModelAuth($data){
        $company_id = $data['company_id'];
        $uid = $data['uid'];
        $model_list = $data['model_list'];

        if(count($model_list) == 0){
            $model_list = [];
        }

        foreach($model_list as $v){
            $model_res = Db::name('model_list')->where(['model_id'=>$v])->cache(true,60)->find();
            if(!$model_res){
                return msg(3002,'model_list参数错误');
            }

            if($v == 1){
                $res = Db::name('customer_service')->where(['uid'=>$uid])->find();
                if(!$res){
                    return msg(3003,'子账号未开通客服权限无法启用客服接待模块');
                }
            }
        }

        $auth_res = Db::name('model_auth')->where(['company_id'=>$company_id,'model_auth_uid'=>$uid])->find();
        if($auth_res){
            $update_res = Db::name('model_auth')->where(['model_auth_id'=>$auth_res['model_auth_id']])->update([
                'model_list' => json_encode($model_list)
            ]);
            
            if($update_res !== false){
                return msg(200,'success');
            }else{
                return msg(3002,'更新数据失败');
            }
        }else{
            $insert_res = Db::name('model_auth')->insert([
                'model_auth_uid' => $uid,
                'company_id' => $company_id,
                'model_list' => json_encode($model_list)
            ]);

            if($insert_res){
                return msg(200,'success');
            }else{
                return msg(3002,'插入数据失败');
            }
        }
    }
}