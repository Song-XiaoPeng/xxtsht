<?php
namespace app\api\controller\v1\we_chat;
use app\api\common\Auth;

//微信后台操作业务处理
class WxOperation extends Auth{
    /**
     * 获取公众号菜单List
	 * 请求类型：post
	 * 传入JSON格式: {"appid":"wx52bf4acbefcf4653"}
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"},"body":[{"type":"click","name":"今日歌曲","key":"V1001_TODAY_MUSIC","sub_button":[]},{"name":"菜单","sub_button":[{"type":"view","name":"搜索","url":"http:\/\/www.soso.com\/","sub_button":[]},{"type":"view","name":"视频","url":"http:\/\/v.qq.com\/","sub_button":[]},{"type":"click","name":"赞一下我们","key":"V1001_GOOD","sub_button":[]}]}]}
	 * API_URL_本地: http://localhost:91/api/v1/we_chat/WxOperation/getMenuList
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/we_chat/WxOperation/getMenuList
     * @param appid 公众号或小程序appid
	 * @return code 200->成功
	 */
    public function getMenuList(){
        $data = input('put.');
        $data['company_id'] = $this->company_id;
        
        return \think\Loader::model('WxOperationModel','logic\v1\we_chat')->getMenuList($data);
    }

    /**
     * 设置公众号菜单
	 * 请求类型：post
	 * 传入JSON格式: {"appid":"wx52bf4acbefcf4653","menu_list":[{"type":"click","name":"今日测试","key":"V1001_TODAY_MUSIC"},{"name":"菜单","sub_button":[{"type":"view","name":"搜索","url":"http:\/\/www.soso.com\/"},{"type":"view","name":"视频","url":"http:\/\/v.qq.com\/"},{"type":"click","name":"赞一下我们","key":"V1001_GOOD"}]}]}
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"},"body":null}
	 * API_URL_本地: http://localhost:91/api/v1/we_chat/WxOperation/setMenu
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/we_chat/WxOperation/setMenu
     * @param appid 公众号或小程序appid
     * @param menu_list 菜单数据
	 * @return code 200->成功
	 */
    public function setMenu(){
        $data = input('put.');
        $data['company_id'] = $this->company_id;
        
        return \think\Loader::model('WxOperationModel','logic\v1\we_chat')->setMenu($data);
    }

    /**
     * 设置微信回复规则
	 * 请求类型：post
	 * 传入JSON格式: {"appid":"wx52bf4acbefcf4653","key_word":"测试","reply_text":"测试123123"}
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"},"body":null}
	 * API_URL_本地: http://localhost:91/api/v1/we_chat/WxOperation/setMessageRuld
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/we_chat/WxOperation/setMessageRuld
     * @param appid 公众号或小程序appid
     * @param message_rule_id 回复规则id
     * @param key_word 回复关键词
     * @param reply_text 回复文本内容
     * @param rule_type 响应类型 1文本回复 2接入到指定客服 3接入到指定客服组 4关注自动回复
     * @param user_group_id 客服分组id rule_type为3必传
     * @param uid 客服id rule_type为2必传
	 * @return code 200->成功
	 */
    public function setMessageRuld(){
        $data = input('put.');
        $data['company_id'] = $this->company_id;
        
        return \think\Loader::model('WxOperationModel','logic\v1\we_chat')->setMessageRuld($data);
    }

    /**
     * 获取自动回复关键词列表
	 * 请求类型：post
	 * 传入JSON格式: {"appid":"wx52bf4acbefcf4653","page":"1"}
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"},"body":null}
	 * API_URL_本地: http://localhost:91/api/v1/we_chat/WxOperation/getMessageRuleList
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/we_chat/WxOperation/getMessageRuleList
     * @param appid 公众号或小程序appid
     * @param company_id 商户company_id
     * @param page 分页参数默认1
	 * @return code 200->成功
	 */
    public function getMessageRuleList(){
        $data = input('put.');
        $data['company_id'] = $this->company_id;
        
        return \think\Loader::model('WxOperationModel','logic\v1\we_chat')->getMessageRuleList($data);
    }

    /**
     * 删除自动回复关键词
	 * 请求类型：post
	 * 传入JSON格式: {"message_rule_id":"12"}
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"},"body":null}
	 * API_URL_本地: http://localhost:91/api/v1/we_chat/WxOperation/delMessageRule
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/we_chat/WxOperation/delMessageRule
     * @param message_rule_id 删除的规则id
	 * @return code 200->成功
	 */
    public function delMessageRule(){
        $data = input('put.');
        $data['company_id'] = $this->company_id;
        
        return \think\Loader::model('WxOperationModel','logic\v1\we_chat')->delMessageRule($data);
    }

    /**
     * 上传微信永久素材图片
	 * 请求类型：post
	 * 传入JSON格式: 
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"},"body":{"media_id":"DK1BXr8UdRlmTE3wPAz4z8R7eUN8AyM5XzixXwCfQIY","url":"http:\/\/mmbiz.qpic.cn\/mmbiz_jpg\/x1g8iaZfs9oCTJSOViaXKBy1RotY0z5l9roS2VFxib9l7tXRP4GkolYVqFibXmCsQxTaTusZET5mMlmibVWJuOYCAkw\/0?wx_fmt=jpeg"}}
	 * API_URL_本地: http://localhost:91/api/v1/we_chat/WxOperation/uploadSourceMaterial
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/we_chat/WxOperation/uploadSourceMaterial
     * @param file 文件下标名称
	 * @return code 200->成功
	 */
    public function uploadSourceMaterial(){
        $data = input('get.');
        $data['company_id'] = $this->company_id;
        $data['token'] = $this->token;

        return \think\Loader::model('WxOperationModel','logic\v1\we_chat')->uploadSourceMaterial($data);
    }

    public function wxUploadImg(){
        $data = input('put.');
        $data['company_id'] = $this->company_id;

        return \think\Loader::model('WxOperationModel','logic\v1\we_chat')->wxUploadImg($data);
    }

    /**
     * 发布或更新微信图文素材
	 * 请求类型：post
	 * 传入JSON格式: {"appid":"wx52bf4acbefcf4653","title":"测试","author":"作者","digest":"备注测试","show_cover_pic":1,"thumb_media_id":"DK1BXr8UdRlmTE3wPAz4z0U1YSTBpTqY8f8PE1fCZJo","content_source_url":"http://www.lyfz.net","content":"content....."}
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"},"body":{"media_id":"DK1BXr8UdRlmTE3wPAz4z4bNO-SoGEEu3yPk-9pd27Q"}}
	 * API_URL_本地: http://localhost:91/api/v1/we_chat/WxOperation/addArticle
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/we_chat/WxOperation/addArticle
     * @param company_id 商户company_id
     * @param appid 公众号appid
     * @param mediaId mediaId图文素材id 存在则更新
     * @param title 标题
     * @param thumb_media_id 图文消息的封面图片素材id（必须是永久mediaID）
     * @param author 作者
     * @param digest 图文消息的摘要，仅有单图文消息才有摘要，多图文此处为空。如果本字段为没有填写，则默认抓取正文前64个字。
     * @param show_cover_pic 是否显示封面，0为false，即不显示，1为true，即显示
     * @param content_source_url 图文消息的原文地址，即点击“阅读原文”后的URL
	 * @return code 200->成功
	 */
    public function addArticle(){
        $data = input('put.');
        $data['company_id'] = $this->company_id;

        return \think\Loader::model('WxOperationModel','logic\v1\we_chat')->addArticle($data);
    }

    /**
     * 获取微信永久素材列表
	 * 请求类型：post
	 * 传入JSON格式: {"page":1,"appid":"appid....","type":"news"}
	 * 返回JSON格式: 
	 * API_URL_本地: http://localhost:91/api/v1/we_chat/WxOperation/getArticleList
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/we_chat/WxOperation/getArticleList
     * @param page 分页参数默认1
     * @param appid 公众号appid
     * @param type 素材的类型，图片（image）、视频（video）、语音 （voice）、图文（news）
	 * @return code 200->成功
	 */
    public function getArticleList(){
        $data = input('put.');

        return \think\Loader::model('WxOperationModel','logic\v1\we_chat')
        ->getArticleList(
            $this->company_id,
            $data['appid'],
            $data['page'],
            $data['type']
        );
    }

    /**
     * 删除微信永久素材
	 * 请求类型：post
	 * 传入JSON格式: {"appid":"appid...","mediaId":"12"}
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"},"body":null}
	 * API_URL_本地: http://localhost:91/api/v1/we_chat/WxOperation/delSourceMaterial
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/we_chat/WxOperation/delSourceMaterial
     * @param appid 微信公众号appid
     * @param mediaId 素材id
	 * @return code 200->成功
	 */
    public function delSourceMaterial(){
        $data = input('put.');
        
        return \think\Loader::model('WxOperationModel','logic\v1\we_chat')
        ->delSourceMaterial(
            $this->company_id,
            $data['appid'],
            $data['mediaId']
        );
    }

    /**
     * 获取微信永久素材详情
	 * 请求类型：post
	 * 传入JSON格式: {"appid":"appid...","mediaId":"12"}
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"},"body":null}
	 * API_URL_本地: http://localhost:91/api/v1/we_chat/WxOperation/getSourceMaterial
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/we_chat/WxOperation/getSourceMaterial
     * @param appid 微信公众号appid
     * @param mediaId 素材id
	 * @return code 200->成功
	 */
    public function getSourceMaterial(){
        $data = input('put.');
        
        return \think\Loader::model('WxOperationModel','logic\v1\we_chat')
        ->getSourceMaterial(
            $this->company_id,
            $data['appid'],
            $data['mediaId']
        );
    }

    /**
     * 创建任务计划
	 * 请求类型：post
	 * 传入JSON格式: {"appid":"appid...","type":"1"}
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"},"body":null}
	 * API_URL_本地: http://localhost:91/api/v1/we_chat/WxOperation/syncWxUser
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/we_chat/WxOperation/syncWxUser
     * @param company_id 商户company_id
     * @param appid 公众号appid
     * @param type 任务类型 1同步粉丝列表 2同步粉丝基本信息
	 * @return code 200->成功
	 */
    public function syncWxUser(){
        $data = input('put.');
        
        return \think\Loader::model('WxOperationModel','logic\v1\we_chat')
        ->syncWxUser(
            $this->company_id,
            $this->uid,
            $data['appid'],
            $data['type']
        );
    }

    /**
     * 获取任务计划列表
	 * 请求类型：post
	 * 传入JSON格式: {"page":"1"}
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"},"body":{"data_list":[{"task_id":1,"task_type":1,"state":0,"speed_progress":0,"add_time":"2017-11-16 17:27:46","handle_end_time":null,"appid":"wx52bf4acbefcf4653","company_id":"51454009d703c86c91353f61011ecf2f","uid":98,"extra":null}],"page_data":{"count":1,"rows_num":12,"page":"1"}}}
	 * API_URL_本地: http://localhost:91/api/v1/we_chat/WxOperation/getTaskList
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/we_chat/WxOperation/getTaskList
     * @param page 分页参数默认1
	 * @return code 200->成功
	 */
    public function getTaskList(){
        $data = input('put.');
        
        return \think\Loader::model('WxOperationModel','logic\v1\we_chat')
        ->getTaskList(
            $this->company_id,
            $data['page']
        );
    }

    /**
     * 获取微信粉丝用户列表
	 * 请求类型：post
	 * 传入JSON格式: {"page":"1"}
	 * 返回JSON格式: 
	 * API_URL_本地: http://localhost:91/api/v1/we_chat/WxOperation/getWxUserList
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/we_chat/WxOperation/getWxUserList
     * @param page 分页参数默认1
     * @param nickname 搜索微信昵称 (选传)
     * @param real_name 微信用户真实姓名 (选传)
     * @param real_phone 微信用户真实联系电话 (选传)
     * @param wx_company_id 微信用户归属公司分组id (选传)
	 * @return code 200->成功
	 */
    public function getWxUserList(){
        $data = input('put.');
        $data['company_id'] = $this->company_id;

        return \think\Loader::model('WxOperationModel','logic\v1\we_chat')->getWxUserList($data);
    }

    /**
     * 创建或编辑微信用户公司
	 * 请求类型：post
	 * 传入JSON格式: {"wx_comapny_name":"利亚方舟","person_charge_phone":"","person_charge_name":"","person_charge_sex":"","remarks":"","contact_address":""}
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"},"body":{"wx_company_id":"4"}}
	 * API_URL_本地: http://localhost:91/api/v1/we_chat/WxOperation/addWxUserComapnyGroup
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/we_chat/WxOperation/addWxUserComapnyGroup
     * @param wx_comapny_name 公司名称
     * @param wx_company_id 更新编辑时传入
     * @param person_charge_phone 公司负责人联系电话 (选传)
     * @param person_charge_name 公司负责人联系电话 (选传)
     * @param person_charge_sex 公司负责人联系电话 (选传)
     * @param remarks 公司负责人联系电话 (选传)
     * @param contact_address 公司联系地址 （选传）
	 * @return code 200->成功
	 */
    public function addWxUserComapnyGroup(){
        $data = input('put.');
        $data['company_id'] = $this->company_id;

        return \think\Loader::model('WxOperationModel','logic\v1\we_chat')->addWxUserComapnyGroup($data);
    }
    
    /**
     * 获取微信用户公司List
	 * 请求类型：post
	 * 传入JSON格式: {"wx_comapny_name":"利亚方舟","page":"1"}
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"},"body":{"data_list":[{"wx_company_id":4,"wx_comapny_name":"利亚方舟","person_charge_phone":0,"person_charge_name":"","person_charge_sex":0,"remarks":"","company_id":"51454009d703c86c91353f61011ecf2f"}],"page_data":{"count":1,"rows_num":16,"page":"1"}}}
	 * API_URL_本地: http://localhost:91/api/v1/we_chat/WxOperation/getWxUserComapnyGroupList
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/we_chat/WxOperation/getWxUserComapnyGroupList
     * @param page 分页参数默认1
     * @param wx_comapny_name 公司名称 (搜索选传)
	 * @return code 200->成功
	 */
    public function getWxUserComapnyGroupList(){
        $data = input('put.');
        $data['company_id'] = $this->company_id;

        return \think\Loader::model('WxOperationModel','logic\v1\we_chat')->getWxUserComapnyGroupList($data);
    }

    /**
     * 删除微信用户公司
	 * 请求类型：post
	 * 传入JSON格式: {"wx_company_id":"12"}
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"},"body":null}
	 * API_URL_本地: http://localhost:91/api/v1/we_chat/WxOperation/delWxUserComapny
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/we_chat/WxOperation/delWxUserComapny
     * @param wx_company_id 公司id
	 * @return code 200->成功
	 */
    public function delWxUserComapny(){
        $data = input('put.');
        $data['company_id'] = $this->company_id;

        return \think\Loader::model('WxOperationModel','logic\v1\we_chat')->delWxUserComapny($data);
    }

    /**
     * 添加编辑客户池分组
	 * 请求类型：post
	 * 传入JSON格式: {"group_name":"测试"}
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"},"body":{"wx_user_group_id":"1"}}
	 * API_URL_本地: http://localhost:91/api/v1/we_chat/WxOperation/addCustomerGroup
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/we_chat/WxOperation/addCustomerGroup
     * @param group_name 分组名称
     * @param wx_user_group_id 分组id 编辑传入
	 * @return code 200->成功
	 */
    public function addCustomerGroup(){
        $data = input('put.');
        $data['company_id'] = $this->company_id;

        return \think\Loader::model('WxOperationModel','logic\v1\we_chat')->addCustomerGroup($data);
    }

    /**
     * 删除客户池分组
	 * 请求类型：post
	 * 传入JSON格式: {"wx_user_group_id":"12"}
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"},"body":null}
	 * API_URL_本地: http://localhost:91/api/v1/we_chat/WxOperation/delCustomerGroup
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/we_chat/WxOperation/delCustomerGroup
     * @param wx_user_group_id 删除的分组id
	 * @return code 200->成功
	 */
    public function delCustomerGroup(){
        $data = input('put.');
        $data['company_id'] = $this->company_id;

        return \think\Loader::model('WxOperationModel','logic\v1\we_chat')->delCustomerGroup($data);
    }

    /**
     * 设置微信个性化菜单
	 * 请求类型：post
	 * 传入JSON格式: {"appid":"wx52bf4acbefcf4653","menu_list":[{"type":"click","name":"今日测试","key":"V1001_TODAY_MUSIC"},{"name":"菜单","sub_button":[{"type":"view","name":"搜索","url":"http:\/\/www.soso.com\/"},{"type":"view","name":"视频","url":"http:\/\/v.qq.com\/"},{"type":"click","name":"赞一下我们","key":"V1001_GOOD"}]}],"match_rule":{"tag_id":"","sex":"1","country":"中国","province":"广东","city":"惠州","client_platform_type":"","language":"zh_CN"}}
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"},"body":null}
	 * API_URL_本地: http://localhost:91/api/v1/we_chat/WxOperation/setWxIndividualizationMenu
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/we_chat/WxOperation/setWxIndividualizationMenu
     * @param appid 微信公众号id
     * @param menu_list 菜单数据
     * @param match_rule  菜单匹配规则
	 * @return code 200->成功
	 * 详细设置参数参考微信官方文档https://mp.weixin.qq.com/wiki?t=resource/res_main&id=mp1455782296
	 */
    public function setWxIndividualizationMenu(){
        $data = input('put.');
        $data['company_id'] = $this->company_id;

        return \think\Loader::model('WxOperationModel','logic\v1\we_chat')->setWxIndividualizationMenu($data);
    }

    /**
     * 获取个性化菜单数据
	 * 请求类型：post
	 * 传入JSON格式: {"appid":"wx52bf4acbefcf4653"}
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"},"body":[{"button":[{"type":"click","name":"今日测试","key":"V1001_TODAY_MUSIC","sub_button":[]},{"name":"菜单","sub_button":[{"type":"view","name":"搜索","url":"http:\/\/www.soso.com\/","sub_button":[]},{"type":"view","name":"视频","url":"http:\/\/v.qq.com\/","sub_button":[]},{"type":"click","name":"赞一下我们","key":"V1001_GOOD","sub_button":[]}]}],"matchrule":{"sex":"1","country":"中国","province":"广东","city":"惠州","language":"zh_CN"},"menuid":407336786},{"button":[{"type":"click","name":"今日测试","key":"V1001_TODAY_MUSIC","sub_button":[]},{"name":"菜单","sub_button":[{"type":"view","name":"搜索","url":"http:\/\/www.soso.com\/","sub_button":[]},{"type":"view","name":"视频","url":"http:\/\/v.qq.com\/","sub_button":[]},{"type":"click","name":"赞一下我们","key":"V1001_GOOD","sub_button":[]}]}],"matchrule":{"sex":"1","country":"中国","province":"广东","city":"惠州","language":"zh_CN"},"menuid":407336768}]}
	 * API_URL_本地: http://localhost:91/api/v1/we_chat/WxOperation/getWxIndividualizationMenu
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/we_chat/WxOperation/getWxIndividualizationMenu
     * @param appid 微信公众号id
	 * @return code 200->成功
	 * 详细设置参数参考微信官方文档https://mp.weixin.qq.com/wiki?t=resource/res_main&id=mp1434698695
	 */
    public function getWxIndividualizationMenu(){
        $data = input('put.');
        $data['company_id'] = $this->company_id;

        return \think\Loader::model('WxOperationModel','logic\v1\we_chat')->getWxIndividualizationMenu($data);
    }

    /**
     * 删除个性化菜单
	 * 请求类型：post
	 * 传入JSON格式: {"appid":"wx52bf4acbefcf4653","menuId":"12"}
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"},"body":null}
	 * API_URL_本地: http://localhost:91/api/v1/we_chat/WxOperation/delWxIndividualizationMenu
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/we_chat/WxOperation/delWxIndividualizationMenu
     * @param appid 微信公众号id
     * @param menuId 菜单id
	 * @return code 200->成功
	 */
    public function delWxIndividualizationMenu(){
        $data = input('put.');
        $data['company_id'] = $this->company_id;

        return \think\Loader::model('WxOperationModel','logic\v1\we_chat')->delWxIndividualizationMenu($data);
    }

    /**
     * 获取客户池分组list
	 * 请求类型：post
	 * 传入JSON格式: {"group_name":"测试"}
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"},"body":[{"wx_user_group_id":2,"group_name":"测试","company_id":"51454009d703c86c91353f61011ecf2f"}]}
	 * API_URL_本地: http://localhost:91/api/v1/we_chat/WxOperation/getCustomerGroupList
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/we_chat/WxOperation/getCustomerGroupList
     * @param group_name 分组名称
	 * @return code 200->成功
	 */
    public function getCustomerGroupList(){
        $data = input('put.');
        $data['company_id'] = $this->company_id;

        return \think\Loader::model('WxOperationModel','logic\v1\we_chat')->getCustomerGroupList($data);
    }

    /**
     * 获取微信公众号分组
	 * 请求类型：post
	 * 传入JSON格式: {"appid":"wx52bf4acbefcf4653"}
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"},"body":[{"id":0,"name":"默认组","count":654},{"id":1,"name":"屏蔽组","count":1},{"id":2,"name":"星标组","count":0},{"id":101,"name":"成交客户","count":4},{"id":103,"name":"同事","count":17},{"id":104,"name":"意向客户","count":49},{"id":105,"name":"其他合作","count":0}]}
	 * API_URL_本地: http://localhost:91/api/v1/we_chat/WxOperation/getWxGroup
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/we_chat/WxOperation/getWxGroup
     * @param appid 公众号appid
	 * @return code 200->成功
	 */
    public function getWxGroup(){
        $data = input('put.');
        $data['company_id'] = $this->company_id;

        return \think\Loader::model('WxOperationModel','logic\v1\we_chat')->getWxGroup($data);
    }

    /**
     * 创建或编辑微信公众号分组
	 * 请求类型：post
	 * 传入JSON格式: {"appid":"wx52bf4acbefcf4653","name":"分组名称","group_id":"123"}
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"},"body":{"group_id":108}}
	 * API_URL_本地: http://localhost:91/api/v1/we_chat/WxOperation/addWxGroup
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/we_chat/WxOperation/addWxGroup
     * @param appid 公众号appid
     * @param name 分组名称
     * @param group_id 分组id 更新传入
	 * @return code 200->成功
	 */
    public function addWxGroup(){
        $data = input('put.');
        $data['company_id'] = $this->company_id;

        return \think\Loader::model('WxOperationModel','logic\v1\we_chat')->addWxGroup($data);
    }

    /**
     * 删除微信公众号分组
	 * 请求类型：post
	 * 传入JSON格式: {"appid":"wx52bf4acbefcf4653","group_id":"123"}
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"},"body":null}
	 * API_URL_本地: http://localhost:91/api/v1/we_chat/WxOperation/delWxGroup
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/we_chat/WxOperation/delWxGroup
     * @param appid 公众号appid
     * @param group_id 删除的分组id
	 * @return code 200->成功
	 */
    public function delWxGroup(){
        $data = input('put.');
        $data['company_id'] = $this->company_id;

        return \think\Loader::model('WxOperationModel','logic\v1\we_chat')->delWxGroup($data);
    }

    /**
     * 移动用户到指定微信分组
	 * 请求类型：post
	 * 传入JSON格式: {"appid":"wx52bf4acbefcf4653","group_id":"108","openid_list":["olKKojmBgaRHV-YZMZNnbojrgqdU","olKKojrzeVC4yujKLL4_pyMnMMMs"]}
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"},"body":null}
	 * API_URL_本地: http://localhost:91/api/v1/we_chat/WxOperation/moveUserWxGroup
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/we_chat/WxOperation/moveUserWxGroup
     * @param appid 公众号或小程序appid
     * @param group_id 移到到新的分组id
     * @param openid_list 移动的用户openid list ["olKKojmBgaRHV-YZMZNnbojrgqdU","olKKojrzeVC4yujKLL4_pyMnMMMs"]
	 * @return code 200->成功
	 */
    public function moveUserWxGroup(){
        $data = input('put.');
        $data['company_id'] = $this->company_id;

        return \think\Loader::model('WxOperationModel','logic\v1\we_chat')->moveUserWxGroup($data);
    }

    /**
     * 添加群发
	 * 请求类型：post
	 * 传入JSON格式: {"appid":"wx52bf4acbefcf4653","group_id":"108","openid_list":["olKKojmBgaRHV-YZMZNnbojrgqdU","olKKojrzeVC4yujKLL4_pyMnMMMs"]}
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"},"body":null}
	 * API_URL_本地: http://localhost:91/api/v1/we_chat/WxOperation/moveUserWxGroup
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/we_chat/WxOperation/moveUserWxGroup
     * @param appid 公众号或小程序appid
     * @param group_id 移到到新的分组id
     * @param openid_list 移动的用户openid list ["olKKojmBgaRHV-YZMZNnbojrgqdU","olKKojrzeVC4yujKLL4_pyMnMMMs"]
	 * @return code 200->成功
	 */
    public function addMassNews(){
        $data = input('put.');
        $data['company_id'] = $this->company_id;

        return \think\Loader::model('WxOperationModel','logic\v1\we_chat')->addMassNews($data);
    }
}