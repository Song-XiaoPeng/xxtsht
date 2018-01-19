<?php
namespace app\api\logic\v1\event;
use think\Model;
use think\Db;
use EasyWeChat\Foundation\Application;

class InteractiveLogic extends Model {
    /**
     * 记录微信用户交互轨迹事件
     * @param appid 公众号或小程序appid
     * @param openid 用户微信openid
     * @param event 事件类型 1点击公众号菜单
	 * @return code 200->成功
	 */
    public function recordInteractiveEvent($data){
        $appid = $data['appid'];
        $openid = $data['openid'];
        $event = $data['event'];

        //获取商户company_id
        $company_id = Db::name('openweixin_authinfo')->where(['appid'=>$appid])->value('company_id');

        switch($event['type']){
            //记录点击公众号用户轨迹事件
            case 1:
                return $this->recordWxMenu([
                    'company_id' => $company_id,
                    'appid' => $appid,
                    'openid' => $openid,
                    'menu_type' => $event['menu_type'],
                    'event_key' => $event['event_key']
                ]);
                break;
        }
    }

    /**
     * 记录用户微信菜单点击事件轨迹
     * @param company_id 所属商户company_id
     * @param appid 公众号或小程序appid
     * @param openid 用户微信openid
     * @param menu_type 菜单类型
     * @param event_key 菜单key
	 * @return code 200->成功
	 */
    private function recordWxMenu($data){
        $appid = $data['appid'];
        $openid = $data['openid'];
        $menu_type = $data['menu_type'];
        $event_key = $data['event_key'];
        $company_id = $data['company_id'];

        $time = date('Y-m-d H:i:s');

        $cache_key = $appid.'_menu';

        if(empty(cache($cache_key))){
            try {
                $token_info = Common::getRefreshToken($appid,$company_id);
                if($token_info['meta']['code'] == 200){
                    $refresh_token = $token_info['body']['refresh_token'];
                }else{
                    return $token_info;
                }
    
                $app = new Application(wxOptions());
                $openPlatform = $app->open_platform;
    
                $menu = $openPlatform->createAuthorizerApplication($appid,$refresh_token)->menu;
    
                $menu_data = $menu->all()['menu']['button'];
    
                cache($cache_key, $menu_data, 21600);
            } catch (\Exception $e) {
                return msg(3010,$e->getMessage());
            }
        }else{
            $menu_data = cache($cache_key);
        }

        $menu_list = [];

        foreach($menu_data as $k=>$v){
            if(!empty($v['sub_button'])){
                foreach($v['sub_button'] as $c){
                    array_push($menu_list,$c);
                }
            }else{
                array_push($menu_list,$v);
            }
        }

        foreach($menu_list as $k=>$v){
            switch($v['type']){
                case 'view';
                    if($v['url'] == $event_key){
                        $desc = '点击菜单'.$v['name'];
                    }
                    break;
                case 'click';
                    if($v['key'] == $event_key){
                        $desc = '点击菜单'.$v['name'];
                    }
                    break;
            }
        }

        if(empty($desc)){
            return msg(3003,'未找到菜单');
        }

        $insert_res = Db::name('wx_user_operation')
        ->insert([
            'company_id' => $company_id,
            'appid' => $appid,
            'openid' => $openid,
            'event' => 1,
            'create_time' => $time,
            'desc' => $desc
        ]);

        if($insert_res){
            return msg(200,'success');
        }else{
            return msg(3001,'插入数据失败');
        }
    }
}