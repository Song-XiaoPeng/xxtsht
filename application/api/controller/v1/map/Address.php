<?php
namespace app\api\controller\v1\map;
use app\api\common\Auth;

class Address extends Auth{
    /**
     * 腾讯地图逆地址解析
     * 请求类型 post
	 * 传入JSON格式: {"lat":"lat..","lng":"lng.."}
	 * 返回JSON格式: {"meta":{"code":200,"message":"success"},"body":null}
	 * API_URL_本地: http://localhost:91/api/v1/map/Address/geocoder
	 * API_URL_服务器: http://kf.lyfz.net/api/v1/map/Address/geocoder
	 * @param lat 纬度
	 * @param lng 经度
	 * @return code 200->成功
	 */
    public function geocoder(){
        $data = input('put.');
        
        return \think\Loader::model('AddressLogic','logic\v1\map')->geocoder($data['lat'],$data['lng']);
    }
}