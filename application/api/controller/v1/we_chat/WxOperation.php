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
	 * API_URL_本地: http://localhost:91/api/v1/we_chat/WxOperation/getMenuList
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/we_chat/WxOperation/getMenuList
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
}