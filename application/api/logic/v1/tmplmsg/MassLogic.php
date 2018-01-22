<?php
namespace app\api\logic\v1\tmplmsg;
use think\Model;
use think\Db;
use EasyWeChat\Foundation\Application;
use app\api\common\Common;

class MassLogic extends Model {
    /**
     * 添加群发模板消息任务
     * @param company_id 所属商户company_id
     * @param appid 公众号appid
     * @param template_id 群发的模板id
     * @param template_data 群发模板数据
     * @param template_url 模板跳转链接
     * @param type 群发类型 1全部用户 2指定分组
	 * @return code 200->成功
	 */
    public function addMassTmplMsg($data){
        $company_id = $data['company_id'];
        $appid = $data['appid'];
        $template_id = $data['template_id'];
        $template_data = $data['template_data'];
        $template_url = $data['template_url'];
        $type = $data['type'];

        
    }

}