<?php
namespace app\api\logic\v1\setting;
use think\Model;
use think\Db;
use app\api\common\Common;

class RuleLogic extends Model {
    /**
     * 设置客资领取规则
     * @param company_id 商户company_id
     * @param cued_pool 线索池领取周期 {"cycle":"1","number":"1"}
     * @param cued_pool_recovery 线索池回收周期 单位天
     * @param intention_receive 意向领取周期 {"cycle":"1","number":"1"}
     * @param intention_recovery 意向回收周期 单位天
	 * @return code 200->成功
	 */
    public function setCustomerResourcesRule($data){
        $company_id = $data['company_id'];
        $cued_pool = $data['cued_pool'];
        $cued_pool_recovery = $data['cued_pool_recovery'];
        $intention_receive = $data['intention_receive'];
        $intention_recovery = $data['intention_recovery'];

        $configure_value = [
            'cued_pool' => $cued_pool,
            'cued_pool_recovery' => $cued_pool_recovery,
            'intention_receive' => $intention_receive,
            'intention_recovery' => $intention_recovery
        ];

        $company_baseinfo_id = Db::name('company_baseinfo')->where(['company_id'=>$company_id,'configure_key'=>'sustomer_resources_rule'])->value('company_baseinfo_id');
        if($company_baseinfo_id){
            $insert_res = Db::name('company_baseinfo')
            ->where(['company_id'=>$company_id,'company_baseinfo_id'=>$company_baseinfo_id])
            ->update([
                'configure_key' => 'sustomer_resources_rule',
                'configure_value' => json_encode($configure_value)
            ]);
        }else{
            $insert_res = Db::name('company_baseinfo')->insert([
                'company_id' => $company_id,
                'configure_key' => 'sustomer_resources_rule',
                'configure_value' => json_encode($configure_value)
            ]);
        }

        if($insert_res){
            return msg(200,'success');
        }else{
            return msg(3001,'插入数据失败');
        }
    }
}