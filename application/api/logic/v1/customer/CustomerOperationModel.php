<?php
namespace app\api\logic\v1\customer;
use think\Model;
use think\Db;
use EasyWeChat\Foundation\Application;
use app\api\common\Common;

class CustomerOperationModel extends Model {
    /**
     * 设置客户信息
     * @param company_id 商户company_id
     * @param appid 客户来源appid
	 * @param openid 客户微信openid
	 * @param real_name 客户真实姓名
	 * @param real_sex 客户真实性别 0未知 1男 2女
	 * @param real_phone 客户真实联系手机
	 * @param contact_address 客户联系地址
	 * @param wx_company_id 所属公司
	 * @param wx_user_group_id 所属用户分组id
	 * @param desc 备注
	 * @param customer_info_id 客户信息id (关联时选传)
	 * @return code 200->成功
	 */
    public function setCustomerInfo($data){
        $company_id = $data['company_id'];
        $appid = $data['appid'];
        $openid = $data['openid'];
        $real_name = $data['real_name'];
        $real_sex = $data['real_sex'] == '' ? 0 : $data['real_sex'];
        $customer_info_id = empty($data['customer_info_id']) == true ? '' : $data['customer_info_id'];
        $real_phone = empty($data['real_phone']) == true ? '' : $data['real_phone'];
        $contact_address = empty($data['contact_address']) == true ? '' : $data['contact_address'];
        $wx_company_id = empty($data['wx_company_id']) == true ? -1 : $data['wx_company_id'];
        $desc = empty($data['desc']) == true ? -1 : $data['desc'];
        $wx_user_group_id = empty($data['wx_user_group_id']) == true ? -1 : $data['wx_user_group_id'];

        $wx_user_res = Db::name('wx_user')
        ->partition('', '', ['type'=>'md5','num'=>5])
        ->where(['appid'=>$appid,'openid'=>$openid])
        ->find();
    
        if(!$wx_user_res){
            return msg(3001,'客户微信基础信息不存在或未同步');
        }

        if(!empty($customer_info_id)){
            $customer_info_res = Db::name('customer_info')
            ->partition(
                ['customer_info_id' => $customer_info_id],
                'customer_info_id',
                ['type' => 'md5','num' => 5]
            )
            ->where(['customer_info_id'=>$customer_info_id,'company_id'=>$company_id])
            ->find();
            if(!$customer_info_res){
                return msg(3005,'customer_info_id参数错误');
            }
        }

        if(empty($wx_user_res['customer_info_id']) == true && empty($customer_info_id) == true){
            $customer_info_id = md5(uniqid());
            
            $db_operation_res = Db::name('customer_info')
            ->partition(
                ['customer_info_id' => $customer_info_id],
                'customer_info_id',
                ['type' => 'md5','num' => 5]
            )
            ->insert([
                'customer_info_id' => $customer_info_id,
                'real_name' => $real_name,
                'real_sex' => $real_sex,
                'real_phone' => $real_phone,
                'contact_address' => $contact_address,
                'wx_company_id' => $wx_company_id,
                'wx_user_group_id' => $wx_user_group_id,
                'company_id' => $company_id,
                'desc' => $desc,
            ]);
        }else{
            $db_operation_res = Db::name('customer_info')
            ->partition(
                ['customer_info_id' => empty($customer_info_id) == true ? $wx_user_res['customer_info_id'] : $customer_info_id],
                'customer_info_id',
                ['type' => 'md5','num' => 5]
            )
            ->where([
                'customer_info_id' => empty($customer_info_id) == true ? $wx_user_res['customer_info_id'] : $customer_info_id,
                'company_id' => $company_id
            ])
            ->update([
                'real_name' => $real_name,
                'real_sex' => $real_sex,
                'real_phone' => $real_phone,
                'contact_address' => $contact_address,
                'wx_company_id' => $wx_company_id,
                'wx_user_group_id' => $wx_user_group_id,
                'desc' => $desc,
            ]);
        }

        $db_operation_res = Db::name('wx_user')
        ->partition(['company_id'=>$company_id], 'company_id', ['type'=>'md5','num'=>5])
        ->where(['appid'=>$appid,'openid'=>$openid])
        ->update(['customer_info_id'=>$customer_info_id]);

        if($db_operation_res !== false){
            return msg(200,'success');
        }else{
            return msg(3002,'数据操作失败');
        }
    }
}