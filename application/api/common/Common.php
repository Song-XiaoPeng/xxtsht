<?php
namespace app\api\common;
use think\Db;

class Common {
    //获取商户公众号或小程序授权信息
    public static function getRefreshToken($appid,$company_id){
        $refresh_token = Db::name('openweixin_authinfo')->where(['appid'=>$appid,'company_id'=>$company_id])->cache(true,120)->value('refresh_token');
        if(!$refresh_token){
            return msg(3001,'appid不存在');
        }
    
        return msg(200,'success',['refresh_token'=>$refresh_token]);
    }
}