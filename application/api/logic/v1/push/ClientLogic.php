<?php
namespace app\api\logic\v1\push;
use think\Model;
use think\Db;
use GatewayClient\Gateway;

class ClientLogic extends Model {
    /**
     * 获取商户下所有在线客户端id
     * @param company_id 商户company_id
     */
    public function getClientCountByGroup($company_id){
        try {
            Gateway::$registerAddress = config('gw_address');

            $count = Gateway::getClientCountByGroup($company_id);
    
            return msg(200,'success',['count'=>$count]);
        } catch (\Exception $e) {
            return msg(200,'success',['count'=>0]);
        }
    }
}