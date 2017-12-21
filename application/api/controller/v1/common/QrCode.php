<?php
namespace app\api\controller\v1\common;
use app\api\common\Auth;

class QrCode extends Auth{
    /**
     * 获取二维码
	 * 请求类型：get
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"},"body":null}
	 * API_URL_本地: http://localhost:91/api/v1/common/QrCode/getQrCode
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/common/QrCode/getQrCode
	 * @param code 二维码code值
	 * @return code 200->成功
	 */
    public function getQrCode(){
        $code = input('get.code');

        \think\Loader::model('QrCodeLogic','logic\v1\common')->getQrCode($code);
    }
}