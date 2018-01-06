<?php
namespace app\api\logic\v1\message;
use think\Model;
use think\Db;
use app\api\common\Common;

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

            if($c['message_type'] == 2 && $c['opercode'] == 3){
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
     * @param uid_list 指定客服人员的会话数据 选传
	 * @return code 200->成功
	 */
    public function getMonitorSessionList($data){
        $company_id = $data['company_id'];
        $uid_list = empty($data['uid_list']) == true ? [] : $data['uid_list'];

        $time = date('Y-m-d H:i:s');

        $session_map['company_id'] = $data['company_id'];
        $session_map['state'] = ['in',[0,1]];

        if(!empty($uid_list)){
            $session_map['uid'] = ['in',$uid_list];
        }

        if(empty($uid_list)){
            $session_list = [];
        }else{
            $session_list = Db::name('message_session')
            ->partition('', '', ['type'=>'md5','num'=>config('separate')['message_session']])
            ->where($session_map)
            ->order('receive_message_time desc')
            ->cache(true,12)
            ->select();
        }

        $line_up_session_res = Db::name('message_session')
        ->partition('', '', ['type'=>'md5','num'=>config('separate')['message_session']])
        ->where(['company_id'=>$company_id,'state'=>3])
        ->order('receive_message_time desc')
        ->cache(true,12)
        ->select();

        $list = array_merge($session_list,$line_up_session_res);

        $pending_access_session = []; //等待中

        $line_up_session = []; //排队中

        $conversation_session = []; //会话中

        foreach($list as $k=>$v){
            $customer_service_name = Db::name('user')->where(['company_id'=>$company_id,'uid'=>$v['uid']])->cache(true,120)->value('user_name');

            $v['customer_service_name'] = empty($customer_service_name) == true ? null : $customer_service_name;

            $nick_name = Db::name('openweixin_authinfo')->where(['company_id'=>$v['company_id'],'appid'=>$v['appid']])->cache(true,120)->value('nick_name');

            $resources_id = Db::name('user_portrait')->where(['uid'=>$v['uid'],'company_id'=>$company_id])->cache(true,120)->value('resources_id');
            if($resources_id){
                $v['customer_service_avatar'] = 'http://'.$_SERVER['HTTP_HOST'].'/api/v1/we_chat/Business/getImg?resources_id='.$resources_id;
            }else{
                $v['customer_service_avatar'] = 'http://wxyx.lyfz.net/Public/mobile/images/default_portrait.jpg';
            }

            $v['nick_name'] = empty($nick_name) == true ? '暂无' : $nick_name;

            if($v['state'] == 0){
                $v['used_time'] = timediff($v['add_time'], $time);
                array_push($pending_access_session, $v);
            }

            if($v['state'] == 1){
                array_push($conversation_session, $v);
            }

            if($v['state'] == 3){
                $v['used_time'] = timediff($v['add_time'], $time);
                array_push($line_up_session, $v);
            }
        }

        return msg(200, 'success', [
            'pending_access_session' => $pending_access_session,
            'line_up_session' => $line_up_session,
            'conversation_session' => $conversation_session
        ]);
    }

    /**
     * 商户监控获取聊天消息
	 * @param company_id 商户company_id
	 * @param page 分页参数默认1
	 * @param session_id 会话id
	 * @return code 200->成功
	 */
    public function getMonitorMessage($data){
        $company_id = $data['company_id'];
        $page = empty($data['page']) == true ? 1:$data['page'];
        $session_id = $data['session_id'];

        //分页
        $page_count = 300;
        $show_page = ($page - 1) * $page_count;

        $list = Db::name('message_data')
        ->partition('', '', ['type'=>'md5','num'=>config('separate')['message_data']])
        ->where(['session_id'=>$session_id,'company_id'=>$company_id])
        ->limit($show_page, $page_count)
        ->order('add_time desc')
        ->select();

        $count = Db::name('message_data')
        ->partition('', '', ['type'=>'md5','num'=>config('separate')['message_data']])
        ->where(['session_id'=>$session_id,'company_id'=>$company_id])
        ->count();

        foreach($list as $i=>$c){
            if($c['message_type'] == 2 && $c['opercode'] == 1){
                $list[$i]['file_url'] = 'http://'.$_SERVER['HTTP_HOST'].'/api/v1/we_chat/Business/getImg?resources_id='.$c['resources_id'];
            }

            if($c['message_type'] == 2 && $c['opercode'] == 3){
                $list[$i]['file_url'] = 'http://'.$_SERVER['HTTP_HOST'].'/api/v1/we_chat/Business/getImg?resources_id='.$c['resources_id'];
            }

            if($c['message_type'] == 2 && $c['opercode'] == 2){
                $list[$i]['file_url'] = getWximg($c['file_url']);
            }

            if($c['message_type'] == 3 || $c['message_type'] == 4 && $c['opercode'] == 1){
                $list[$i]['file_url'] = 'http://'.$_SERVER['HTTP_HOST'].'/api/v1/we_chat/Business/getImg?resources_id='.$c['resources_id'];
            }
        }

        $res['data_list'] = count($list) == 0 ? array() : $list;
        $res['page_data']['count'] = $count;
        $res['page_data']['rows_num'] = $page_count;
        $res['page_data']['page'] = $page;

        return msg(200,'success',$res);
    }

    /**
     * 监控关闭会话
	 * @param company_id 商户company_id
	 * @param session_id 关闭会话id
	 * @param close_explain 会话关闭原因
	 * @return code 200->成功
	 */
    public function closeSession($data){
        $company_id = $data['company_id'];
        $session_id = $data['session_id'];
        $close_explain = empty($data['close_explain']) == true ? '监控操作关闭' : $data['close_explain'];

        $session_data = Db::name('message_session')
        ->partition('', '', ['type'=>'md5','num'=>config('separate')['message_session']])
        ->where(['company_id'=>$company_id,'session_id'=>$session_id,'state'=>['in',[0,1,3]]])
        ->find();

        if(empty($session_data)){
            return msg(3001,'会话不存在');
        }

        $session_update_res = Db::name('message_session')
        ->partition(['session_id' => $session_id], 'session_id', ['type'=>'md5','num'=>config('separate')['message_session']])
        ->where(['session_id'=>$session_data['session_id']])
        ->update(['state'=>-4,'close_explain'=>$close_explain]);
        if($session_update_res === false){
            return msg(3002,'更新数据失败');
        }

        try {
            $redis = Common::createRedis();
                
            $redis->select(0);

            $session_list = $redis->sMembers($session_data['uid']);

            foreach($session_list as $str){
                $session_arr = json_decode($str,true);
            
                if($session_arr['session_id'] == $session_data['session_id']){
                    $redis->SREM($session_data['uid'], $str);
                }
            }

            $redis->select(2);

            $session_list2 = $redis->sMembers($company_id);

            foreach($session_list2 as $str){
                $session_arr = json_decode($str,true);
            
                if($session_arr['session_id'] == $session_data['session_id']){
                    $redis->SREM($session_data['uid'], $str);
                }
            }
        } catch (\Exception $e) {
            return msg(3003,$e->getMessage());
        }

        return msg(200,'success');
    }

    /**
     * 会话评价
	 * @param company_id 商户company_id
	 * @param session_id 关闭会话id
	 * @param uid 评价账号uid
	 * @param content 评价内容
	 * @param screenshot 评价级别 1好评 2中评 3差评
	 * @return code 200->成功
	 */
    public function sessionEvaluate($data){
        $company_id = $data['company_id'];
        $session_id = $data['session_id'];
        $uid = $data['uid'];
        $screenshot = $data['screenshot'];
        $content = $data['content'];
        $add_time = date('Y-m-d H:i:s');

        $insert_res = Db::name('session_evaluate')
        ->insert([
            'company_id' => $company_id,
            'session_id' => $session_id,
            'content' => $content,
            'uid' => $uid,
            'add_time' => $add_time,
            'screenshot' => $screenshot
        ]);

        if($insert_res){
            return msg(200, 'success');
        }else{
            return msg(3001, '插入数据失败');
        }
    }
}