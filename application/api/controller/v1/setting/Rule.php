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
     * 获取企业常用话术
     * 请求类型 post
	 * 传入JSON格式: {"page": 1}
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"},"body":{"data_list":[{"quick_reply_id":15,"quick_reply_text":"测试快捷回复语句","uid":null,"company_id":"51454009d703c86c91353f61011ecf2f","type":2,"use_count":0},{"quick_reply_id":16,"quick_reply_text":"测试快捷回复语句","uid":null,"company_id":"51454009d703c86c91353f61011ecf2f","type":2,"use_count":0}],"page_data":{"count":2,"rows_num":16,"page":1}}}
	 * API_URL_本地: http://localhost:91/api/v1/setting/Rule/getEnterpriseSentence
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/setting/Rule/getEnterpriseSentence
     * @param page 分页参数默认1
	 * @return code 200->成功
	 */
	public function getEnterpriseSentence(){
		$data = input('put.');
		$data['company_id'] = $this->company_id;

		return \think\Loader::model('RuleLogic','logic\v1\setting')->getEnterpriseSentence($data);
	}

	/**
     * 删除企业话术
     * 请求类型 post
	 * 传入JSON格式: {"quick_reply_id": 12}
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"},"body":""}
	 * API_URL_本地: http://localhost:91/api/v1/setting/Rule/delQuickReply
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/setting/Rule/delQuickReply
     * @param quick_reply_id 话术id
	 * @return code 200->成功
	 */
	public function delQuickReply(){
		$data = input('put.');
		$data['company_id'] = $this->company_id;

		return \think\Loader::model('RuleLogic','logic\v1\setting')->delQuickReply($data);
	}

	/**
     * 获取客资领取规则
     * 请求类型 get
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"},"body":{"cued_pool":{"cycle":"1","number":"12"},"cued_pool_recovery":"2","intention_receive":{"cycle":"1","number":"12"},"intention_recovery":"3"}}
	 * API_URL_本地: http://localhost:91/api/v1/setting/Rule/getCustomerResourcesRule
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/setting/Rule/getCustomerResourcesRule
	 * @return code 200->成功
	 */
	public function getCustomerResourcesRule(){
		return \think\Loader::model('RuleLogic','logic\v1\setting')->getCustomerResourcesRule($this->company_id);
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
     * 获取所有标签分组
     * 请求类型 get
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"},"body":[{"label_group_id":2,"group_name":"测试","company_id":"51454009d703c86c91353f61011ecf2f"}]}
	 * API_URL_本地: http://localhost:91/api/v1/setting/Rule/getLabelGroup
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/setting/Rule/getLabelGroup
	 * @return code 200->成功
	 */
	public function getLabelGroup(){
		return \think\Loader::model('RuleLogic','logic\v1\setting')->getLabelGroup($this->company_id);
	}

	/**
     * 获取标签列表
     * 请求类型 get
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"},"body":[{"label_id":23,"label_name":"星标组","company_id":"51454009d703c86c91353f61011ecf2f","label_group_id":-1,"group_name":null},{"label_id":24,"label_name":"已成交","company_id":"51454009d703c86c91353f61011ecf2f","label_group_id":-1,"group_name":null},{"label_id":25,"label_name":"意向","company_id":"51454009d703c86c91353f61011ecf2f","label_group_id":-1,"group_name":null},{"label_id":26,"label_name":"同事","company_id":"51454009d703c86c91353f61011ecf2f","label_group_id":-1,"group_name":null},{"label_id":27,"label_name":"商家","company_id":"51454009d703c86c91353f61011ecf2f","label_group_id":-1,"group_name":null},{"label_id":28,"label_name":"利亚官网","company_id":"51454009d703c86c91353f61011ecf2f","label_group_id":-1,"group_name":null},{"label_id":29,"label_name":"同行","company_id":"51454009d703c86c91353f61011ecf2f","label_group_id":-1,"group_name":null},{"label_id":30,"label_name":"ERP内小程序","company_id":"51454009d703c86c91353f61011ecf2f","label_group_id":-1,"group_name":null}]}
	 * API_URL_本地: http://localhost:91/api/v1/setting/Rule/getLabelList
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/setting/Rule/getLabelList
	 * @return code 200->成功
	 */
	public function getLabelList(){
		$data = input('get.');
		$data['company_id'] = $this->company_id;

		return \think\Loader::model('RuleLogic','logic\v1\setting')->getLabelList($data);
	}

	/**
     * 个人添加编辑快捷回复分组
     * 请求类型 post
	 * 传入JSON格式: {"reply_group_id":"12","group_name":"测试"}
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"},"body":null}
	 * API_URL_本地: http://localhost:91/api/v1/setting/Rule/addUserQuickReplyGroup
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/setting/Rule/addUserQuickReplyGroup
     * @param reply_group_id 分组id (更新时传入)
     * @param group_name 分组名称
	 * @return code 200->成功
	 */
	public function addUserQuickReplyGroup(){
		$data = input('put.');
		$data['company_id'] = $this->company_id;
		$data['uid'] = $this->uid;

		return \think\Loader::model('RuleLogic','logic\v1\setting')->addUserQuickReplyGroup($data);
	}

	/**
     * 获取个人快捷回复分组
     * 请求类型 get
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"},"body":null}
	 * API_URL_本地: http://localhost:91/api/v1/setting/Rule/getUserQuickReplyGroup
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/setting/Rule/getUserQuickReplyGroup
	 * @return code 200->成功
	 */
	public function getUserQuickReplyGroup(){
		$data = input('put.');
		$data['company_id'] = $this->company_id;
		$data['uid'] = $this->uid;

		return \think\Loader::model('RuleLogic','logic\v1\setting')->getUserQuickReplyGroup($data);
	}

	/**
     * 删除个人快捷回复分组
     * 请求类型 post
	 * 传入JSON格式: {"reply_group_id":"12"}
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"},"body":null}
	 * API_URL_本地: http://localhost:91/api/v1/setting/Rule/delUserQuickReplyGroup
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/setting/Rule/delUserQuickReplyGroup
	 * @param reply_group_id 删除的分组id
	 * @return code 200->成功
	 */
	public function delUserQuickReplyGroup(){
		$data = input('put.');
		$data['company_id'] = $this->company_id;
		$data['uid'] = $this->uid;

		return \think\Loader::model('RuleLogic','logic\v1\setting')->delUserQuickReplyGroup($data);
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