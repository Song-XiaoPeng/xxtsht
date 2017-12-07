<?php
namespace app\api\logic\v1\message;
use think\Model;
use think\Db;

class InteractionLogic extends Model {
    /**
     * 获取客服历史会话列表
     * @param page 分页参数
	 * @param company_id 商户company_id
	 * @param uid 客服账号uid
	 * @return code 200->成功
	 */
    public function getHistorySession($data){
        $page = $data['page'];
        $uid = $data['uid'];
        $company_id = $data['company_id'];

        //分页
        $page_count = 16;
        $show_page = ($page - 1) * $page_count;

        $session_res = Db::name('message_session')
        ->partition('', '', ['type'=>'md5','num'=>config('separate')['message_session']])
        ->where([
            'uid' => $uid,
            'company_id' => $company_id,
            'state' => ['in',[-2,-1]],
        ])
        ->group('customer_wx_openid')
        ->field('customer_wx_openid,state,uid,appid,previous_customer_service_id,customer_wx_nickname,customer_wx_portrait')
        ->limit($show_page,$page_count)
        ->select();

        $count = Db::name('message_session')
        ->partition('', '', ['type'=>'md5','num'=>config('separate')['message_session']])
        ->where([
            'uid' => $uid,
            'company_id' => $company_id,
            'state' => ['in',[-2,-1]],
        ])
        ->group('customer_wx_openid')
        ->count();

        foreach($session_res as $k=>$v){
            $session_res[$k]['app_name'] = Db::name('openweixin_authinfo')->where(['appid'=>$v['appid']])->cache(true,360)->value('nick_name');
        }

        $res['data_list'] = count($session_res) == 0 ? array() : $session_res;
        $res['page_data']['count'] = $count;
        $res['page_data']['rows_num'] = $page_count;
        $res['page_data']['page'] = $page;

        return msg(200,'success',$res);
    }

    /**
     * 获取客户历史消息记录
     * @param page 分页参数
	 * @param company_id 商户company_id
	 * @param uid 客服账号uid
	 * @param customer_wx_openid 客户微信openid
	 * @return code 200->成功
	 */
    public function getHistoryMessage($data){
        $page = $data['page'];
        $customer_wx_openid = $data['customer_wx_openid'];
        $uid = $data['uid'];
        $company_id = $data['company_id'];

        //分页
        $page_count = 200;
        $show_page = ($page - 1) * $page_count;

        $message_res = Db::name('message_data')
        ->partition('', '', ['type'=>'md5','num'=>config('separate')['message_data']])
        ->where([
            'uid' => $uid,
            'company_id' => $company_id,
            'customer_wx_openid' => $customer_wx_openid
        ])
        ->order('add_time desc')
        ->limit($show_page,$page_count)
        ->select();

        $count = Db::name('message_data')
        ->partition('', '', ['type'=>'md5','num'=>config('separate')['message_data']])
        ->where([
            'uid' => $uid,
            'company_id' => $company_id,
            'customer_wx_openid' => $customer_wx_openid
        ])
        ->count();

        foreach($message_res as $i=>$c){
            if($c['message_type'] == 2 && $c['opercode'] == 1){
                $message_res[$i]['file_url'] = 'http://'.$_SERVER['HTTP_HOST'].'/api/v1/we_chat/Business/getImg?resources_id='.$c['resources_id'];
            }

            if($c['message_type'] == 2 && $c['opercode'] == 2){
                $message_res[$i]['file_url'] = getWximg($c['file_url']);
            }

            if($c['message_type'] == 3 || $c['message_type'] == 4 && $c['opercode'] == 1){
                $message_res[$i]['file_url'] = 'http://'.$_SERVER['HTTP_HOST'].'/api/v1/we_chat/Business/getImg?resources_id='.$c['resources_id'];
            }
        }

        $res['data_list'] = count($message_res) == 0 ? array() : $message_res;
        $res['page_data']['count'] = $count;
        $res['page_data']['rows_num'] = $page_count;
        $res['page_data']['page'] = $page;

        return msg(200,'success',$res);
    }

    /**
     * 获取已接入会话列表
	 * @param company_id 商户company_id
	 * @param uid 客服账号uid
	 * @return code 200->成功
	 */
    public function getAlreadyAccess($company_id,$uid){
        $session_res = Db::name('message_session')
        ->partition('', '', ['type'=>'md5','num'=>config('separate')['message_session']])
        ->where([
            'company_id' => $company_id,
            'uid' => $uid,
            'state' => 1,
        ])
        ->select();

        foreach($session_res as $k=>$v){
            $session_res[$k]['app_name'] = Db::name('openweixin_authinfo')->where(['appid'=>$v['appid']])->value('nick_name');

            $session_res[$k]['session_frequency'] = Db::name('message_session')->partition('', '', ['type'=>'md5','num'=>config('separate')['message_session']])->where(['customer_wx_openid'=>$v['customer_wx_openid'],'company_id'=>$company_id])->cache(true,60)->count();

            $session_res[$k]['invitation_frequency'] = 0;
        }

        return msg(200,'success',$session_res);
    }

    /**
     * 商户监控会话列表获取
	 * @param company_id 商户company_id
	 * @return code 200->成功
	 */
    public function getMonitorSessionList($company_id){
        set_time_limit(60);

        while (true) {
            $message_session = Db::name('message_session')->where(['company_id'=>$company_id,'state'=>1])->select();

            

            sleep(2);
        }
    }
}