<?php
namespace app\api\logic\v1\trajectory;
use think\Model;
use think\Db;
use EasyWeChat\Foundation\Application;
use think\Log;
use app\api\common\Common;
use app\api\logic\v1\we_chat\BusinessLogic;

class InteractiveLogic extends Model {
    /**
     * 记录微信用户交互轨迹事件
     * @param appid 公众号或小程序appid
     * @param openid 用户微信openid
     * @param event_type 事件类型数据
	 * @return code 200->成功
	 */
    public static function recordInteractiveEvent($data){
        $appid = $data['appid'];
        $openid = $data['openid'];
        $event_type = $data['event_type'];
        $event_key = $data['event_key'];

        //获取商户company_id
        $company_id = Db::name('openweixin_authinfo')->where(['appid'=>$appid])->value('company_id');

        //记录点击公众号菜单轨迹事件
        if($event_type == 'VIEW' || $event_type == 'CLICK'){
            return self::recordWxMenu([
                'company_id' => $company_id,
                'appid' => $appid,
                'openid' => $openid,
                'menu_type' => $event_type,
                'event_key' => $event_key
            ]);
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
    private static function recordWxMenu($data){
        $appid = $data['appid'];
        $openid = $data['openid'];
        $menu_type = $data['menu_type'];
        $event_key = $data['event_key'];
        $company_id = $data['company_id'];

        //判断是否支持的菜单时间类型
        if($menu_type != 'VIEW' && $menu_type != 'CLICK'){
            return msg(3009,'不支持记录的事件类型');
        }

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
                        $desc = '点击公众号菜单->'.$v['name'];
                    }
                    break;
                case 'click';
                    if($v['key'] == $event_key){
                        $desc = '点击公众号菜单->'.$v['name'];
                    }
                    break;
            }
        }

        if(empty($desc)){
            return msg(3003,'未找到菜单');
        }

        $WxBusiness = new BusinessLogic();
        $WxBusiness->createSession($appid, $openid, 'other');

        //判断是否存在客服会话
        $session_res = $WxBusiness->getSession($appid, $openid);
        if ($session_res) {
            if ($session_res['session_state'] == 2) {//群聊
                $opercode = 4;
            } else {
                $opercode = 2;
            }
            Common::addMessagge($appid, $openid, $session_res['session_id'], $session_res['customer_service_id'], $session_res['uid'], 1, $opercode, ['text' => $desc]);
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

    /**
     * 获取用户事件轨迹
     * @param company_id 所属商户company_id
     * @param appid 公众号或小程序appid
     * @param openid 用户微信openid
     * @param page 分页参数
     * @param type 事件类型 1点击公众号菜单
	 * @return code 200->成功
	 */
    public function getEventTrajectory($data){
        $company_id = $data['company_id'];
        $appid = $data['appid'];
        $openid = $data['openid'];
        $page = $data['page'];
        $type = $data['type'];

        //分页
        $page_count = 16;
        $show_page = ($page - 1) * $page_count;

        $list = Db::name('wx_user_operation')
        ->where(['company_id'=>$company_id,'appid'=>$appid,'openid'=>$openid,'event'=>$type])
        ->limit($show_page,$page_count)
        ->order('create_time desc')
        ->select();

        $count = Db::name('wx_user_operation')
        ->where(['company_id'=>$company_id,'appid'=>$appid,'openid'=>$openid])
        ->count();

        foreach($list as $k=>$v){
            switch($v['event']){
                case 1:
                    $list[$k]['event'] = '公众号菜单';
                    break;
            }
        }

        $res['data_list'] = count($list) == 0 ? array() : $list;
        $res['page_data']['count'] = $count;
        $res['page_data']['rows_num'] = $page_count;
        $res['page_data']['page'] = $page;
        
        return msg(200,'success',$res);
    }
}