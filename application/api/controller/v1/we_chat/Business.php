<?php
namespace app\api\controller\v1\we_chat;
use \think\Log;
use think\Config;

//微信前台业务处理微信数据回调对接方法
class Business {
    //微信授权事件处理
    public function authCallback(){
        return \think\Loader::model('BusinessModel','logic\v1\we_chat')->authCallback();
    }

    //微信公众号消息与事件处理
    public function messageEvent(){
        $data = input('get.');
        if(empty($data['appid'])){
            return;
        }else{
            $data['appid'] = trim($data['appid'],'/');
        }

        return \think\Loader::model('BusinessModel','logic\v1\we_chat')->messageEvent($data);
    }

    //获取第三方公众号授权链接
    public function getAuthUrl(){
        $company_id = input('get.company_id');

        return \think\Loader::model('BusinessModel','logic\v1\we_chat')->getAuthUrl($company_id);
    }

    //授权成功跳转页面
    public function authCallbackPage(){
        $data = input('get.');

        return \think\Loader::model('BusinessModel','logic\v1\we_chat')->authCallbackPage($data);
    }

    //上传微信永久素材图片
    public function wx_upload_img(){
        $data = input('get.');
        
        return \think\Loader::model('WxOperationModel','logic\v1\we_chat')->wx_upload_img($data);
    }

    /**
     * 获取素材内容
	 * 请求类型：get
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/we_chat/Business/getMaterial?company_id=51454009d703c86c91353f61011ecf2f&appid=wx88c6052d06eaaf7d&media_id=Z7WLojs-GQBiAvmBmO8XY0r1bPGAKOJCn4eVFbfnYnPjB2l9A4VHhTr3567RlnWl&type=1
     * @param company_id 商户company_id
     * @param appid 公众号或小程序appid
     * @param media_id 素材id
     * @param type 素材类型 1图片 2视频 3语音
	 * @return code 200->成功
	 */
    public function getMaterial(){
        $data = input('get.');
        
        return \think\Loader::model('BusinessModel','logic\v1\we_chat')->getMaterial($data['appid'],$data['company_id'],$data['media_id'],$data['type']);
    }
}