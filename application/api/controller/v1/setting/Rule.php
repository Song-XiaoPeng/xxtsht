<?php
namespace app\api\controller\v1\setting;
use app\api\common\Auth;

//系统规则设置相关操作
class Rule extends Auth{
	/**
     * 设置客资领取规则
     * 请求类型 post
	 * 传入JSON格式: {"cued_pool":{"cycle":"1","number":"1"},"cued_pool_recovery":3,"intention_receive":{"cycle":"1","number":"1"},"intention_recovery":3}
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"},"body":null}
	 * API_URL_本地: http://localhost:91/api/v1/setting/Rule/setCustomerResourcesRule
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/setting/Rule/setCustomerResourcesRule
     * @param cued_pool 线索池领取周期 {"cycle":"1","number":"1"} cycle->1本天 2本周 3本月 number->限制领取数量
     * @param cued_pool_recovery 线索池回收周期 单位天
     * @param intention_receive 意向领取周期 {"cycle":"1","number":"1"} cycle->1本天 2本周 3本月 number->限制领取数量
     * @param intention_recovery 意向回收周期 单位天
	 * @return code 200->成功
	 */
	public function setCustomerResourcesRule(){
		$data = input('put.');
		$data['company_id'] = $this->company_id;

		return \think\Loader::model('RuleLogic','logic\v1\setting')->setCustomerResourcesRule($data);
	}
	
	/**
     * 设置商户公共快捷回复语句
     * 请求类型 post
	 * 传入JSON格式: {"quick_reply_id":"","text":"测试快捷回复语句"}
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"},"body":{"quick_reply_id":"16"}}
	 * API_URL_本地: http://localhost:91/api/v1/setting/Rule/setCommonQuickReplyText
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/setting/Rule/setCommonQuickReplyText
     * @param quick_reply_id 存在则是编辑
	 * @param text 快捷回复语句
	 * @return code 200->成功
	 */
	public function setCommonQuickReplyText(){
		$data = input('put.');
		$data['company_id'] = $this->company_id;

		return \think\Loader::model('RuleLogic','logic\v1\setting')->setCommonQuickReplyText($data);
    }
}