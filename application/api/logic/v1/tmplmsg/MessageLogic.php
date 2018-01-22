<?php
namespace app\api\logic\v1\tmplmsg;
use think\Model;
use think\Db;
use EasyWeChat\Foundation\Application;
use app\api\common\Common;

class MessageLogic extends Model {
    /**
     * 获取所有消息模板列表
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

        //匹配模板变量
        foreach($template_list as $k=>$v){
            $template_list[$k]['field'] = extractWxTemplate($v['content']);
        }

        return msg(200,'success',array_reverse($template_list));
    }

    /**
     * 添加消息模板
     * @param company_id 所属商户company_id
     * @param appid 公众号appid
     * @param short_id 添加的模板ID
	 * @return code 200->成功
	 */
    public function addTemplate($data){
        $company_id = $data['company_id'];
        $appid = $data['appid'];
        $short_id = $data['short_id'];

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

            $add_res = $notice->addTemplate($short_id);
        } catch (\Exception $e) {
            return msg(3010,$e->getMessage());
        }

        return msg(200,'success',['template_id'=>$add_res['template_id']]);
    }

    /**
     * 删除消息模板
     * @param company_id 所属商户company_id
     * @param appid 公众号appid
     * @param template_id 删除的模板ID
	 * @return code 200->成功
	 */
    public function delTemplate($data){
        $company_id = $data['company_id'];
        $appid = $data['appid'];
        $template_id = $data['template_id'];

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
            
            $del_res = $notice->deletePrivateTemplate($template_id);
        } catch (\Exception $e) {
            return msg(3010,$e->getMessage());
        }

        return msg(200,'success');
    }

}