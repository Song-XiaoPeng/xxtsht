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
	 * 传入JSON格式: {"appid":"wx52bf4acbefcf4653","type":"1","send_type":"1","send_time":"","openid_list":"","group_id":"","send_message_type":"1","media_id":"","text":"测试"}
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"},"body":null}
	 * API_URL_本地: http://localhost:91/api/v1/we_chat/WxOperation/addMassNews
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/we_chat/WxOperation/addMassNews
     * @param appid 公众号或小程序appid
     * @param type 群发类型 1全部 2按分组 3指定用户
     * @param send_type 发送时效 1立即发送 2定时发送
     * @param send_time 定时群发时间 (选传)
     * @param openid_list 发送指定的用户openid list (选传)
     * @param group_id 发送指定的微信分组id (选传)
     * @param send_message_type 群发消息类型 1文字 2图文消息 3图片
     * @param media_id 群发的图文信息id (选传)
     * @param text 群发文字 (选传)
	 * @return code 200->成功
	 */
    public function addMassNews(){
        $data = input('put.');
        $data['company_id'] = $this->company_id;

        return \think\Loader::model('WxOperationModel','logic\v1\we_chat')->addMassNews($data);
    }

    /**
     * 删除群发消息
	 * 请求类型：post
	 * 传入JSON格式: {"appid":"wx52bf4acbefcf4653","news_id":"1"}
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"},"body":null}
	 * API_URL_本地: http://localhost:91/api/v1/we_chat/WxOperation/delMassNews
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/we_chat/WxOperation/delMassNews
     * @param appid 公众号appid
     * @param news_id 删除的群发id
	 * @return code 200->成功
	 */
    public function delMassNews(){
        $data = input('put.');
        $data['company_id'] = $this->company_id;

        return \think\Loader::model('WxOperationModel','logic\v1\we_chat')->delMassNews($data);
    }

    /**
     * 获取群发消息列表
	 * 请求类型：post
	 * 传入JSON格式: {"appid":"wx52bf4acbefcf4653","page":"1"}
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"},"body":{"data_list":[{"news_id":3,"type":1,"send_type":1,"send_time":"0000-00-00 00:00:00","state":0,"openid_list":"","group_id":0,"send_message_type":1,"media_id":"","text":"测试","appid":"wx52bf4acbefcf4653","company_id":"51454009d703c86c91353f61011ecf2f","add_time":"0000-00-00 00:00:00"},{"news_id":4,"type":1,"send_type":1,"send_time":"0000-00-00 00:00:00","state":0,"openid_list":"","group_id":0,"send_message_type":1,"media_id":"","text":"测试","appid":"wx52bf4acbefcf4653","company_id":"51454009d703c86c91353f61011ecf2f","add_time":"2017-11-18 16:26:51"},{"news_id":5,"type":1,"send_type":1,"send_time":"0000-00-00 00:00:00","state":0,"openid_list":"","group_id":0,"send_message_type":1,"media_id":"","text":"测试","appid":"wx52bf4acbefcf4653","company_id":"51454009d703c86c91353f61011ecf2f","add_time":"2017-11-18 16:27:06"}],"page_data":{"count":3,"rows_num":16,"page":"1"}}}
	 * API_URL_本地: http://localhost:91/api/v1/we_chat/WxOperation/getMassNewsList
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/we_chat/WxOperation/getMassNewsList
     * @param appid 公众号appid
     * @param page 分页参数默认1
	 * @return code 200->成功
	 */
    public function getMassNewsList(){
        $data = input('put.');
        $data['company_id'] = $this->company_id;

        return \think\Loader::model('WxOperationModel','logic\v1\we_chat')->getMassNewsList($data['appid'],$data['company_id'],$data['page']);
    }

    /**
     * 获取用户增减数据(最大时间跨度：7)
	 * 请求类型：post
	 * 传入JSON格式: {"appid":"wx52bf4acbefcf4653","start_date":"2017-11-07","end_date":"2017-11-13"}
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"},"body":null}
	 * API_URL_本地: http://localhost:91/api/v1/we_chat/WxOperation/getUserSummary
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/we_chat/WxOperation/getUserSummary
     * @param appid 公众号appid
     * @param start_date 查询开始日期
     * @param end_date 查询结束日期
	 * @return code 200->成功
	 */
    public function getUserSummary(){
        $data = input('put.');
        $data['company_id'] = $this->company_id;

        return \think\Loader::model('WxOperationModel','logic\v1\we_chat')->getUserSummary($data);
    }

    /**
     * 获取累计用户数据(最大时间跨度：7)
	 * 请求类型：post
	 * 传入JSON格式: {"appid":"wx52bf4acbefcf4653","start_date":"2017-11-07","end_date":"2017-11-13"}
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"},"body":[{"ref_date":"2017-11-07","user_source":0,"cumulate_user":725},{"ref_date":"2017-11-08","user_source":0,"cumulate_user":725},{"ref_date":"2017-11-09","user_source":0,"cumulate_user":725},{"ref_date":"2017-11-10","user_source":0,"cumulate_user":725},{"ref_date":"2017-11-11","user_source":0,"cumulate_user":725},{"ref_date":"2017-11-12","user_source":0,"cumulate_user":725},{"ref_date":"2017-11-13","user_source":0,"cumulate_user":725}]}
	 * API_URL_本地: http://localhost:91/api/v1/we_chat/WxOperation/getUserCumulate
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/we_chat/WxOperation/getUserCumulate
     * @param appid 公众号appid
     * @param start_date 查询开始日期
     * @param end_date 查询结束日期
	 * @return code 200->成功
	 */
    public function getUserCumulate(){
        $data = input('put.');
        $data['company_id'] = $this->company_id;

        return \think\Loader::model('WxOperationModel','logic\v1\we_chat')->getUserCumulate($data);
    }

    /**
     * 获取图文群发每日数据(最大时间跨度：1)
	 * 请求类型：post
	 * 传入JSON格式: {"appid":"wx52bf4acbefcf4653","start_date":"2017-11-07","end_date":"2017-11-07"}
	 * 返回JSON格式: 
	 * API_URL_本地: http://localhost:91/api/v1/we_chat/WxOperation/getArticleSummary
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/we_chat/WxOperation/getArticleSummary
     * @param appid 公众号appid
     * @param start_date 查询开始日期
     * @param end_date 查询结束日期
	 * @return code 200->成功
	 */
    public function getArticleSummary(){
        $data = input('put.');
        $data['company_id'] = $this->company_id;

        return \think\Loader::model('WxOperationModel','logic\v1\we_chat')->getArticleSummary($data);
    }

    /**
     * 获取图文群发总数据(最大时间跨度：1)
	 * 请求类型：post
	 * 传入JSON格式: {"appid":"wx52bf4acbefcf4653","start_date":"2017-11-07","end_date":"2017-11-07"}
	 * 返回JSON格式: 
	 * API_URL_本地: http://localhost:91/api/v1/we_chat/WxOperation/getArticleTotal
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/we_chat/WxOperation/getArticleTotal
     * @param appid 公众号appid
     * @param start_date 查询开始日期
     * @param end_date 查询结束日期
	 * @return code 200->成功
	 */
    public function getArticleTotal(){
        $data = input('put.');
        $data['company_id'] = $this->company_id;

        return \think\Loader::model('WxOperationModel','logic\v1\we_chat')->getArticleTotal($data);
    }

    /**
     * 获取图文分享转发数据(最大时间跨度：7)
	 * 请求类型：post
	 * 传入JSON格式: {"appid":"wx52bf4acbefcf4653","start_date":"2017-11-07","end_date":"2017-11-07"}
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"},"body":[{"ref_date":"2017-11-10","user_source":0,"share_scene":1,"share_count":1,"share_user":1},{"ref_date":"2017-11-10","user_source":0,"share_scene":2,"share_count":1,"share_user":1}]}
	 * API_URL_本地: http://localhost:91/api/v1/we_chat/WxOperation/getUserShareSummary
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/we_chat/WxOperation/getUserShareSummary
     * @param appid 公众号appid
     * @param start_date 查询开始日期
     * @param end_date 查询结束日期
	 * @return code 200->成功
	 */
    public function getUserShareSummary(){
        $data = input('put.');
        $data['company_id'] = $this->company_id;

        return \think\Loader::model('WxOperationModel','logic\v1\we_chat')->getUserShareSummary($data);
    }

    /**
     * 获取消息发送概况数据(最大时间跨度：7)
	 * 请求类型：post
	 * 传入JSON格式: {"appid":"wx52bf4acbefcf4653","start_date":"2017-11-07","end_date":"2017-11-13"}
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"},"body":[{"ref_date":"2017-11-08","user_source":0,"msg_type":1,"msg_user":1,"msg_count":1},{"ref_date":"2017-11-10","user_source":0,"msg_type":1,"msg_user":1,"msg_count":1},{"ref_date":"2017-11-11","user_source":0,"msg_type":1,"msg_user":1,"msg_count":33}]}
	 * API_URL_本地: http://localhost:91/api/v1/we_chat/WxOperation/getUpstreamMessageSummary
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/we_chat/WxOperation/getUpstreamMessageSummary
     * @param appid 公众号appid
     * @param start_date 查询开始日期
     * @param end_date 查询结束日期
	 * @return code 200->成功
	 */
    public function getUpstreamMessageSummary(){
        $data = input('put.');
        $data['company_id'] = $this->company_id;

        return \think\Loader::model('WxOperationModel','logic\v1\we_chat')->getUpstreamMessageSummary($data);
    }

    /**
     * 发送客服信息
	 * 请求类型：post
	 * 传入JSON格式: {"session_id":"4f56b7fd6021401b5b476c4e4eab9200","message":"测试","type":"1"}
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"},"body":null}
	 * API_URL_本地: http://localhost:91/api/v1/we_chat/WxOperation/sendMessage
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/we_chat/WxOperation/sendMessage
     * @param session_id 会话id
     * @param message 消息内容
     * @param type 1文字 2图片 3文件 4视频  5声音 5图文信息素材 7链接
     * @param resources_id 资源id
     * @param media_id 素材id
	 * @return code 200->成功
	 */
    public function sendMessage(){
        $data = input('put.');
        $data['company_id'] = $this->company_id;
        $data['uid'] = $this->uid;

        return \think\Loader::model('WxOperationModel','logic\v1\we_chat')->sendMessage($data);
    }

    /**
     * 会话接入
	 * 请求类型：post
	 * 传入JSON格式: {"session_id":"38a3843d6d2c733e5b7212f993af453e"}
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"},"body":null}
	 * API_URL_本地: http://localhost:91/api/v1/we_chat/WxOperation/sessionAccess
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/we_chat/WxOperation/sessionAccess
     * @param session_id 会话id
	 * @return code 200->成功
	 */
    public function sessionAccess(){
        $data = input('put.');
        $data['company_id'] = $this->company_id;
        $data['uid'] = $this->uid;

        return \think\Loader::model('WxOperationModel','logic\v1\we_chat')->sessionAccess($data['company_id'],$data['uid'],$data['session_id']);
    }

    /**
     * 获取会话列表
	 * 请求类型：post
	 * 传入JSON格式: {"type":1,"page":1}
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"},"body":{"data_list":[{"session_id":"38a3843d6d2c733e5b7212f993af453e","customer_service_id":91601557,"customer_wx_openid":"olKKojskJPK46Q8m4pXWo6pcLr20","add_time":"2017-11-22 13:17:47","state":1,"uid":6452,"appid":"wx52bf4acbefcf4653","company_id":"51454009d703c86c91353f61011ecf2f","previous_customer_service_id":null,"customer_wx_nickname":"Junwen"}],"page_data":{"count":1,"rows_num":16,"page":1}}}
	 * API_URL_本地: http://localhost:91/api/v1/we_chat/WxOperation/getSessionHistoryList
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/we_chat/WxOperation/getSessionHistoryList
     * @param type 会话类型 -2接待超时关闭 -1会话关闭 0等待接入会话 1会话中
     * @param page 分页参数默认1
	 * @return code 200->成功
	 */
    public function  getSessionHistoryList(){
        $data = input('put.');
        $data['company_id'] = $this->company_id;
        $data['uid'] = $this->uid;

        return \think\Loader::model('WxOperationModel','logic\v1\we_chat')->getSessionHistoryList($data);
    }

    /**
     * 获取会话消息
	 * 请求类型：get
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"},"body":{"bdff9bb74cab59e104fa035a881c203b":[{"text":"5555","opercode":2,"file_url":null,"lng":null,"lat":null,"add_time":"2017-11-24 19:58:39","message_type":1,"page_title":null,"page_desc":null,"map_scale":null,"map_label":null,"map_img":null,"media_id":null}]}}
	 * API_URL_本地: http://localhost:91/api/v1/we_chat/WxOperation/getMessage
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/we_chat/WxOperation/getMessage
	 * @return code 200->成功
	 */
    public function getMessage(){
        $data['company_id'] = $this->company_id;
        $data['uid'] = $this->uid;

        return \think\Loader::model('WxOperationModel','logic\v1\we_chat')->getMessage($data);
    }

    /**
     * 结束会话
	 * 请求类型：post
	 * 传入JSON格式: {"session_list":["294bbb5b94de0c044e66f6caec067856","b2fe3202dff09473cf9aa2b9f15ad79f"]}
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"},"body":null}
	 * API_URL_本地: http://localhost:91/api/v1/we_chat/WxOperation/closeSession
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/we_chat/WxOperation/closeSession
     * @param session_list 结束的会话id list
	 * @return code 200->成功
	 */
    public function closeSession(){
        $data = input('put.');
        $data['company_id'] = $this->company_id;
        $data['uid'] = $this->uid;

        return \think\Loader::model('WxOperationModel','logic\v1\we_chat')->closeSession($data);
    }

    /**
     * 获取待接入会话列表
	 * 请求类型：get
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"},"body":null}
	 * API_URL_本地: http://localhost:91/api/v1/we_chat/WxOperation/getSessionList
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/we_chat/WxOperation/getSessionList
	 * @return code 200->成功
	 */
    public function getSessionList(){
        $data = input('get.');
        $data['company_id'] = $this->company_id;
        $data['uid'] = $this->uid;

        return \think\Loader::model('WxOperationModel','logic\v1\we_chat')->getSessionList($data);
    }

    /**
     * 上传文件
	 * 请求类型：post
	 * 返回JSON格式: {"meta":{"code":200,"message":"messgae"},"body":{"name":"5a18e57837a68.jpg","extension":"jpg","mime":"image\/jpeg","size":33067,"md5":"11999a05ab49ddbc7574bc4531c471ea","dimensions":{"width":800,"height":533}}}
	 * API_URL_本地: http://localhost:91/api/v1/we_chat/WxOperation/uploadResources
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/we_chat/WxOperation/uploadResources
     * @param file 文件流字段名称
     * @param resources_type 资源类型 1 im数据文件 2客服头像
	 * @return code 200->成功
	 */
    public function uploadResources(){
        $data = input('post.');
        $data['company_id'] = $this->company_id;
        $data['uid'] = $this->uid;

        return \think\Loader::model('WxOperationModel','logic\v1\we_chat')->uploadResources($data);
    }

    public function test(){
        return \think\Loader::model('WxOperationModel','logic\v1\we_chat')->test();
    }
}