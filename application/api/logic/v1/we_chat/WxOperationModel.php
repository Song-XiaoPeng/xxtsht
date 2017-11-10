<?php
namespace app\api\logic\v1\we_chat;
use think\Model;
use think\Db;
use EasyWeChat\Foundation\Application;
use app\api\common\Common;

//微信后台操作业务类
class WxOperationModel extends Model {
    /**
     * 获取菜单List
     * @param appid 公众号或小程序appid
     * @param company_id 商户company_id
	 * @return code 200->成功
	 */
    public function getMenuList($data){
        $company_id = $data['company_id'];
        $appid = $data['appid'];

        $token_info = Common::getRefreshToken($appid,$company_id);
        if($token_info['meta']['code'] == 200){
            $refresh_token = $token_info['body']['refresh_token'];
        }else{
            return $token_info;
        }

        $app = new Application(wxOptions());
        $openPlatform = $app->open_platform;
        $menu = $openPlatform->createAuthorizerApplication($appid,$refresh_token)->menu;

        return msg(200,'success',$menu->all()['menu']['button']);
    }

    /**
     * 设置菜单
     * @param appid 公众号或小程序appid
     * @param company_id 商户company_id
     * @param menu_list 菜单数据
	 * @return code 200->成功
	 */
    public function setMenu($data){
        $appid = $data['appid'];
        $company_id = $data['company_id'];
        $menu_list = $data['menu_list'];

        $token_info = Common::getRefreshToken($appid,$company_id);
        if($token_info['meta']['code'] == 200){
            $refresh_token = $token_info['body']['refresh_token'];
        }else{
            return $token_info;
        }

        $app = new Application(wxOptions());
        $openPlatform = $app->open_platform;
        $menu = $openPlatform->createAuthorizerApplication($appid,$refresh_token)->menu;

        return msg(200,'success');
    }
}