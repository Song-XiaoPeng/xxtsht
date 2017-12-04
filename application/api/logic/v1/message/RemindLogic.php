<?php
namespace app\api\logic\v1\message;
use think\Model;
use think\Db;

class RemindLogic extends Model {
    /**
     * 增加客户提醒
     * @param remind_content 提醒内容
	 * @param wx_user_id 提醒客户微信基础信息id
	 * @param remind_time 提醒时间
	 * @param uid 账号uid
	 * @param company_id 商户company_id
	 * @return code 200->成功
	 */
    public function addRemind($data){
        $remind_content = $data['remind_content'];
        $wx_user_id = $data['wx_user_id'];
        $remind_time = $data['remind_time'];
        $uid = $data['uid'];
        $company_id = $data['company_id'];

        $wx_user_res = Db::name('wx_user')
        ->partition('', '', ['type'=>'md5','num'=>config('separate')['wx_user']])
        ->where(['company_id'=>$company_id,'wx_user_id'=>$wx_user_id])
        ->find();

        if(!$wx_user_res){
            return msg(3001,'客户基础信息不存在');
        }

        $insert_res = Db::name('remind')->insert([
            'remind_content' => $remind_content,
            'wx_user_id' => $wx_user_id,
            'uid' => $uid,
            'company_id' => $company_id,
            'add_time' => date('Y-m-d H:i:s'),
            'remind_time' => $remind_time,
        ]);

        if($insert_res){
            return msg(200,'success');
        }else{
            return msg(3001,'插入数据失败');
        }
    }
}