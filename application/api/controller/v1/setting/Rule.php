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
	
	/**
     * 设置公众号标签
     * 请求类型 post
	 * 传入JSON格式: {"label_group_id":"","label_name":"标签名称"}
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"},"body":null}
	 * API_URL_本地: http://localhost:91/api/v1/setting/Rule/setLabel
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/setting/Rule/setLabel
     * @param label_group_id 标签分组id
	 * @param label_name 标签名称
	 * @return code 200->成功
	 */
	public function setLabel(){
		$data = input('put.');
		$data['company_id'] = $this->company_id;

		return \think\Loader::model('RuleLogic','logic\v1\setting')->setLabel($data);
	}

	/**
     * 修改标签
     * 请求类型 post
	 * 传入JSON格式: {"label_group_id":"","label_name":"标签名称","label_id":"12"}
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"},"body":null}
	 * API_URL_本地: http://localhost:91/api/v1/setting/Rule/updateLabel
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/setting/Rule/updateLabel
     * @param label_id 标签id
     * @param label_group_id 标签分组id
	 * @param label_name 标签名称
	 * @return code 200->成功
	 */
	public function updateLabel(){
		$data = input('put.');
		$data['company_id'] = $this->company_id;

		return \think\Loader::model('RuleLogic','logic\v1\setting')->updateLabel($data);
	}

	/**
     * 会话规则设置
     * 请求类型 post
	 * 传入JSON格式: {"rule_type":"","overtime":""}
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"},"body":null}
	 * API_URL_本地: http://localhost:91/api/v1/setting/Rule/setSessionRule
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/setting/Rule/setSessionRule
     * @param rule_type 无所属咨询分配规则 1平均分配 2抢单模式
     * @param overtime 超时会话 多少分钟未回复客户自动关闭
	 * @return code 200->成功
	 */
	public function setSessionRule(){
		$data = input('put.');
		$data['company_id'] = $this->company_id;

		return \think\Loader::model('RuleLogic','logic\v1\setting')->setSessionRule($data);
	}

	/**
     * 添加修改标签分组
     * 请求类型 post
	 * 传入JSON格式: {"label_group_id":"","group_name":"标签分组名称"}
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"},"body":null}
	 * API_URL_本地: http://localhost:91/api/v1/setting/Rule/updateLabelGroup
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/setting/Rule/updateLabelGroup
     * @param label_group_id 标签分组id(修改选传)
	 * @param group_name 标签分组名称
	 * @return code 200->成功
	 */
	public function updateLabelGroup(){
		$data = input('put.');
		$data['company_id'] = $this->company_id;

		return \think\Loader::model('RuleLogic','logic\v1\setting')->updateLabelGroup($data);
	}

	/**
     * 删除标签分组
     * 请求类型 post
	 * 传入JSON格式: {"label_group_id":"12"}
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"},"body":null}
	 * API_URL_本地: http://localhost:91/api/v1/setting/Rule/delLabelGroup
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/setting/Rule/delLabelGroup
     * @param label_group_id 标签分组id
	 * @return code 200->成功
	 */
	public function delLabelGroup(){
		$data = input('put.');
		$data['company_id'] = $this->company_id;

		return \think\Loader::model('RuleLogic','logic\v1\setting')->delLabelGroup($data);
	}

	/**
     * 获取会话规则
     * 请求类型 get
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"},"body":{"rule_type":"1","overtime":"2"}}
	 * API_URL_本地: http://localhost:91/api/v1/setting/Rule/getSessionRule
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/setting/Rule/getSessionRule
	 * @return code 200->成功
	 */
	public function getSessionRule(){
		return \think\Loader::model('RuleLogic','logic\v1\setting')->getSessionRule($this->company_id);
	}

	/**
     * 删除标签
     * 请求类型 post
	 * 传入JSON格式: {"label_id":"12"}
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"},"body":null}
	 * API_URL_本地: http://localhost:91/api/v1/setting/Rule/delLabel
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/setting/Rule/delLabel
     * @param label_id 标签id
	 * @return code 200->成功
	 */
	public function delLabel(){
		$data = input('put.');
		$data['company_id'] = $this->company_id;

		return \think\Loader::model('RuleLogic','logic\v1\setting')->delLabel($data);
	}
	
	/**
     * 同步所有公众号标签
     * 请求类型 get
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"},"body":null}
	 * API_URL_本地: http://localhost:91/api/v1/setting/Rule/syncWxLabel
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/setting/Rule/syncWxLabel
	 * @return code 200->成功
	 */
	public function syncWxLabel(){
		return \think\Loader::model('RuleLogic','logic\v1\setting')->syncWxLabel($this->company_id);
    }
}