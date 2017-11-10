<?php
namespace app\api\controller\v1\we_chat;
use app\api\common\Auth;

//微信后台操作业务处理
class WxOperation extends Auth{
    /**
     * 获取公众号菜单List
	 * 请求类型：post
	 * 传入JSON格式: {"appid":"wx52bf4acbefcf4653"}
	 * 返回JSON格式: {"meta":{"code":200,"msg":"success"},"body":[{"type":"click","name":"今日歌曲","key":"V1001_TODAY_MUSIC","sub_button":[]},{"name":"菜单","sub_button":[{"type":"view","name":"搜索","url":"http:\/\/www.soso.com\/","sub_button":[]},{"type":"view","name":"视频","url":"http:\/\/v.qq.com\/","sub_button":[]},{"type":"click","name":"赞一下我们","key":"V1001_GOOD","sub_button":[]}]}]}
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
	 * 返回JSON格式: {"meta":{"code":200,"msg":"success"},"body":null}
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
}