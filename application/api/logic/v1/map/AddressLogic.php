<?php
namespace app\api\logic\v1\map;
use think\Model;
use think\Db;
use app\api\common\Common;

class AddressLogic extends Model {
    /**
     * 腾讯地图逆地址解析
	 * @param lat 纬度
	 * @param lng 经度
	 * @return code 200->成功
	 */
    public function geocoder($lat,$lng){
        if(empty($lat) == true || empty($lng) == true){
            return msg(3001, '暂无相关地址信息');
        }

        $url = 'http://apis.map.qq.com/ws/geocoder/v1/?location='.$lat.','.$lng.'&key='.config('web_service_api');

        $client = new \GuzzleHttp\Client();
        $res = $client->request('GET', $url);

        $arr = json_decode($res->getBody(),true);

        return msg(200,'success',$arr['result']);
    }
}