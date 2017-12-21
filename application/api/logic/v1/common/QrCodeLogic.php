<?php
namespace app\api\logic\v1\common;
use think\Model;
use think\Db;
use Endroid\QrCode\QrCode;

class QrCodeLogic extends Model {
    /**
     * 获取微信二维码
	 * @param code 二维码code值
	 * @return code 200->成功
	 */
    public function getQrCode($code){
        $qrCode = new QrCode($code);

        header('Content-Type: '.$qrCode->getContentType());
        echo $qrCode->writeString();
    }
}