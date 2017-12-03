<?php
namespace app\api\controller\v1\update;

//客户端获取升级版本
class Client{
    /**
     * 客户端获取升级版本
     * 请求类型 get
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"},"body":null}
	 * API_URL_本地: http://localhost:91/api/v1/update/Client/getVersion
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/update/Client/getVersion
	 * @return code 200->成功
	 */
    public function getVersion(){
        return \think\Loader::model('ClientLogic','logic\v1\update')->getVersion();
    }
}