<?php
namespace app\api\logic\v1\tmplmsg;
use think\Model;
use think\Db;
use EasyWeChat\Foundation\Application;
use app\api\common\Common;

class MessageLogic extends Model {
    /**
     * 获取所有模板列表
     * @param company_id 所属商户company_id
     * @param appid 公众号appid
	 * @return code 200->成功
	 */
    public function getAllTemplateList($data){
        $company_id = $data['company_id'];
        $appid = $data['appid'];

        try {
            $token_info = Common::getRefreshToken($appid,$company_id);
            if($token_info['meta']['code'] == 200){
                $refresh_token = $token_info['body']['refresh_token'];
            }else{
                return $token_info;
            }

            $app = new Application(wxOptions());
            $openPlatform = $app->open_platform;

            $notice = $openPlatform->createAuthorizerApplication($appid,$refresh_token)->notice;

            $template_list = $notice->getPrivateTemplates();
        } catch (\Exception $e) {
            return msg(3010,$e->getMessage());
        }

        if(!empty($template_list['template_list'])){
            $template_list = $template_list['template_list'];
        }else{
            $template_list = [];
        }

        return msg(200,'success',$template_list);
    }
}