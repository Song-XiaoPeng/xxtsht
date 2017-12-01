<?php
namespace app\api\logic\v1\message;
use think\Model;
use think\Db;

class InteractionModel extends Model {
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
        ->partition('', '', ['type'=>'md5','num'=>10])
        ->where([
            'uid' => $uid,
            'company_id' => $company_id,
            'customer_wx_openid' => $customer_wx_openid
        ])
        ->order('add_time asc')
        ->limit($show_page,$page_count)
        ->select();

        $count = Db::name('message_data')
        ->partition('', '', ['type'=>'md5','num'=>10])
        ->where([
            'uid' => $uid,
            'company_id' => $company_id,
            'customer_wx_openid' => $customer_wx_openid
        ])
        ->count();

        $res['data_list'] = count($message_res) == 0 ? array() : $message_res;
        $res['page_data']['count'] = $count;
        $res['page_data']['rows_num'] = $page_count;
        $res['page_data']['page'] = $page;

        return msg(200,'success',$res);
    }
}