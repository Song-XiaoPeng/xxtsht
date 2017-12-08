<?php
namespace app\api\logic\v1\statistics;
use think\Model;
use think\Db;
use app\api\common\Common;

class SurveyLogic extends Model {
    /**
     * 获取首页概况信息
	 * @return code 200->成功
	 */
    public function getHomeSurvey($company_id,$uid){
        $customer_service_total = $this->getCustomerServiceTotal($company_id)['body'];

        $visitor_total = $this->getVisitorTotal($company_id)['body'];

        $response_time = [
            'pending_response' => 0,
            'average_response' => 0
        ];

        return msg(200,'success',[
            'customer_service_total' => $customer_service_total,
            'visitor_total' => $visitor_total,
            'response_time' => $response_time,
        ]);
    }

    /**
     * 获取总客服数量
     * @param company_id 商户company_id
	 * @return code 200->成功
	 */
    public function getCustomerServiceTotal($company_id){
        $on_line_total = Db::name('customer_service')->where(['company_id'=>$company_id,'state'=>1])->cache(true,10)->count();
        $off_line_total = Db::name('customer_service')->where(['company_id'=>$company_id,'state'=>-1])->cache(true,10)->count();
        $customer_service_total = Db::name('customer_service')->where(['company_id'=>$company_id])->cache(true,60)->count();

        return msg(200,'success',[
            'on_line_total' => $on_line_total,
            'off_line_total' => $off_line_total,
            'total' => $customer_service_total
        ]);
    }

    /**
     * 获取在线访客数量
     * @param company_id 商户company_id
	 * @return code 200->成功
	 */
    public function getVisitorTotal($company_id){
        $reception_total = Db::name('message_session')
        ->partition('', '', ['type'=>'md5','num'=>config('separate')['message_session']])
        ->where(['company_id'=>$company_id,'state'=>1])
        ->cache(true,10)
        ->count();

        $total = Db::name('message_session')
        ->partition('', '', ['type'=>'md5','num'=>config('separate')['message_session']])
        ->where(['company_id'=>$company_id])
        ->cache(true,10)
        ->count();

        return msg(200,'success',[
            'reception_total' => $reception_total,
            'total' => $total
        ]);
    }

    /**
     * 获取客服排名
     * @param company_id 商户company_id
	 * @param type 1今天 2昨天 3近一周 4近一月 5自定义时间段
	 * @param start_time 开始时间
	 * @param end_time 结束时间
	 * @return code 200->成功
	 */
    public function getCustomerServiceRanking($data){
        $company_id = $data['company_id'];
        $type = $data['type'];
        $start_time = empty($data['start_time']) == true ? '' : $data['start_time'];
        $end_time = empty($data['end_time']) == true ? '' : $data['end_time'];

        $list = Db::name('customer_service')->where(['company_id'=>$company_id])->group('uid')->field('name,customer_service_id,uid')->select();

        foreach($list as $k=>$v){
            $statistical_data = [
                'session_total' => 0, //会话总数
                'first_session' => 0, //首次会话
                'effective' => 0, //有效会话
                'effective_percentage' => 0, //有效会话率
                'invalid_session' => 0, //无效会话
                'session_missing' => 0, //会话遗漏
                'own_session' => 0, //手动接入会话
                'auto_session' => 0, //自动分配会话
                'active_session' => 0, //主动发起会话
                'collection' => 0, //采集客咨
                'collection_percentage' => 0, //采集客咨率
                'send_message_total' => 0, //发出消息
            ];

            $statistical_data['session_total'] = $this->getSessionTotal($v['uid'],$company_id,$type,$start_time,$end_time);

            $statistical_data['first_session'] = $this->getFirstSession($v['uid'],$company_id,$type,$start_time,$end_time);

            $effective = $this->getEffective($v['uid'],$company_id,$type,$start_time,$end_time);

            $statistical_data['effective'] = $effective;
            $statistical_data['auto_session'] = $effective;

            $invalid_session = $this->getInvalidSession($v['uid'],$company_id,$type,$start_time,$end_time);

            $statistical_data['invalid_session'] = $invalid_session;
            $statistical_data['session_missing'] = $invalid_session;

            $statistical_data['collection'] = $this->getCollection($v['uid'],$company_id,$type,$start_time,$end_time);

            $statistical_data['send_message_total'] = $this->getSendMessageTotal($v['uid'],$company_id,$type,$start_time,$end_time);

            $list[$k] = array_merge($statistical_data,$v);
        }

        return msg(200,'success',$list);
    }

    /**
     * 获取会话总数
     * @param uid 客服uid
     * @param company_id 商户company_id
	 * @param type 1今天 2昨天 3近一周 4近一月 5自定义时间段
	 * @param start_time 开始时间
	 * @param end_time 结束时间
	 * @return code 200->成功
	 */
    private function getSessionTotal($uid, $company_id, $type, $start_time = '', $end_time = ''){
        switch($type){
            case 1:
                $yesterday_res = getDayTimeSolt();
                $begin_time = $yesterday_res['begin_time'];
                $end_time = $yesterday_res['end_time'];
                break;
            case 2:
                $yesterday_res = getYesTerdayTimeSolt();
                $begin_time = $yesterday_res['begin_time'];
                $end_time = $yesterday_res['end_time'];
                break;
            case 3:
                $yesterday_res = getWeekTimeSolt();
                $begin_time = $yesterday_res['begin_time'];
                $end_time = $yesterday_res['end_time'];
                break;  
            case 4:
                $month_res = getMonthTimeSolt();
                $begin_time = $month_res['begin_time'];
                $end_time = $month_res['end_time'];
                break;
            case 5:
                $begin_time = $start_time;
                $end_time = $end_time;
                break;
        }

        $session_total = Db::query("SELECT COUNT(*) AS count FROM ( SELECT * FROM tb_message_session_1 UNION SELECT * FROM tb_message_session_2 UNION SELECT * FROM tb_message_session_3 UNION SELECT * FROM tb_message_session_4 UNION SELECT * FROM tb_message_session_5 UNION SELECT * FROM tb_message_session_6 UNION SELECT * FROM tb_message_session_7 UNION SELECT * FROM tb_message_session_8) AS message_session WHERE  `company_id` = '$company_id'  AND `uid` = $uid  AND `add_time` BETWEEN '$begin_time' AND '$end_time' LIMIT 1")[0]['count'];

        return $session_total;
    }

    /**
     * 获取首次会话
     * @param uid 客服uid
     * @param company_id 商户company_id
	 * @param type 1今天 2昨天 3近一周 4近一月 5自定义时间段
	 * @param start_time 开始时间
	 * @param end_time 结束时间
	 * @return code 200->成功
	 */
    private function getFirstSession($uid, $company_id, $type, $start_time = '', $end_time = ''){
        switch($type){
            case 1:
                $yesterday_res = getDayTimeSolt();
                $begin_time = $yesterday_res['begin_time'];
                $end_time = $yesterday_res['end_time'];
                break;
            case 2:
                $yesterday_res = getYesTerdayTimeSolt();
                $begin_time = $yesterday_res['begin_time'];
                $end_time = $yesterday_res['end_time'];
                break;
            case 3:
                $yesterday_res = getWeekTimeSolt();
                $begin_time = $yesterday_res['begin_time'];
                $end_time = $yesterday_res['end_time'];
                break;  
            case 4:
                $month_res = getMonthTimeSolt();
                $begin_time = $month_res['begin_time'];
                $end_time = $month_res['end_time'];
                break;
            case 5:
                $begin_time = $start_time;
                $end_time = $end_time;
                break;
        }

        $session_total = Db::query("SELECT COUNT(*) AS count FROM ( SELECT COUNT(*) FROM ( SELECT * FROM tb_message_session_1 UNION SELECT * FROM tb_message_session_2 UNION SELECT * FROM tb_message_session_3 UNION SELECT * FROM tb_message_session_4 UNION SELECT * FROM tb_message_session_5 UNION SELECT * FROM tb_message_session_6 UNION SELECT * FROM tb_message_session_7 UNION SELECT * FROM tb_message_session_8) AS message_session WHERE  `company_id` = '$company_id' AND `uid` = $uid  AND `add_time` BETWEEN '$begin_time' AND '$end_time' GROUP BY `customer_wx_openid` ) `_group_count_` LIMIT 1")[0]['count'];

        return $session_total;
    }

    /**
     * 获取有效会话
     * @param uid 客服uid
     * @param company_id 商户company_id
	 * @param type 1今天 2昨天 3近一周 4近一月 5自定义时间段
	 * @param start_time 开始时间
	 * @param end_time 结束时间
	 * @return code 200->成功
	 */
    private function getEffective($uid, $company_id, $type, $start_time = '', $end_time = ''){
        switch($type){
            case 1:
                $yesterday_res = getDayTimeSolt();
                $begin_time = $yesterday_res['begin_time'];
                $end_time = $yesterday_res['end_time'];
                break;
            case 2:
                $yesterday_res = getYesTerdayTimeSolt();
                $begin_time = $yesterday_res['begin_time'];
                $end_time = $yesterday_res['end_time'];
                break;
            case 3:
                $yesterday_res = getWeekTimeSolt();
                $begin_time = $yesterday_res['begin_time'];
                $end_time = $yesterday_res['end_time'];
                break;  
            case 4:
                $month_res = getMonthTimeSolt();
                $begin_time = $month_res['begin_time'];
                $end_time = $month_res['end_time'];
                break;
            case 5:
                $begin_time = $start_time;
                $end_time = $end_time;
                break;
        }

        $session_total = Db::query("SELECT COUNT(*) AS count FROM ( SELECT * FROM tb_message_session_1 UNION SELECT * FROM tb_message_session_2 UNION SELECT * FROM tb_message_session_3 UNION SELECT * FROM tb_message_session_4 UNION SELECT * FROM tb_message_session_5 UNION SELECT * FROM tb_message_session_6 UNION SELECT * FROM tb_message_session_7 UNION SELECT * FROM tb_message_session_8) AS message_session WHERE  `company_id` = '$company_id'  AND `uid` = $uid  AND `state` IN (-1,1)  AND `add_time` BETWEEN '$begin_time' AND '$end_time' LIMIT 1")[0]['count'];

        return $session_total;
    }

    /**
     * 获取无效会话
     * @param uid 客服uid
     * @param company_id 商户company_id
	 * @param type 1今天 2昨天 3近一周 4近一月 5自定义时间段
	 * @param start_time 开始时间
	 * @param end_time 结束时间
	 * @return code 200->成功
	 */
    private function getInvalidSession($uid, $company_id, $type, $start_time = '', $end_time = ''){
        switch($type){
            case 1:
                $yesterday_res = getDayTimeSolt();
                $begin_time = $yesterday_res['begin_time'];
                $end_time = $yesterday_res['end_time'];
                break;
            case 2:
                $yesterday_res = getYesTerdayTimeSolt();
                $begin_time = $yesterday_res['begin_time'];
                $end_time = $yesterday_res['end_time'];
                break;
            case 3:
                $yesterday_res = getWeekTimeSolt();
                $begin_time = $yesterday_res['begin_time'];
                $end_time = $yesterday_res['end_time'];
                break;  
            case 4:
                $month_res = getMonthTimeSolt();
                $begin_time = $month_res['begin_time'];
                $end_time = $month_res['end_time'];
                break;
            case 5:
                $begin_time = $start_time;
                $end_time = $end_time;
                break;
        }

        $session_total = Db::query("SELECT COUNT(*) AS count FROM ( SELECT * FROM tb_message_session_1 UNION SELECT * FROM tb_message_session_2 UNION SELECT * FROM tb_message_session_3 UNION SELECT * FROM tb_message_session_4 UNION SELECT * FROM tb_message_session_5 UNION SELECT * FROM tb_message_session_6 UNION SELECT * FROM tb_message_session_7 UNION SELECT * FROM tb_message_session_8) AS message_session WHERE  `company_id` = '$company_id'  AND `uid` = $uid  AND `state` = -2  AND `add_time` BETWEEN '$begin_time' AND '$end_time' LIMIT 1")[0]['count'];

        return $session_total;
    }

    /**
     * 获取采集客咨
     * @param uid 客服uid
     * @param company_id 商户company_id
	 * @param type 1今天 2昨天 3近一周 4近一月 5自定义时间段
	 * @param start_time 开始时间
	 * @param end_time 结束时间
	 * @return code 200->成功
	 */
    private function getCollection($uid, $company_id, $type, $start_time = '', $end_time = ''){
        switch($type){
            case 1:
                $yesterday_res = getDayTimeSolt();
                $begin_time = $yesterday_res['begin_time'];
                $end_time = $yesterday_res['end_time'];
                break;
            case 2:
                $yesterday_res = getYesTerdayTimeSolt();
                $begin_time = $yesterday_res['begin_time'];
                $end_time = $yesterday_res['end_time'];
                break;
            case 3:
                $yesterday_res = getWeekTimeSolt();
                $begin_time = $yesterday_res['begin_time'];
                $end_time = $yesterday_res['end_time'];
                break;  
            case 4:
                $month_res = getMonthTimeSolt();
                $begin_time = $month_res['begin_time'];
                $end_time = $month_res['end_time'];
                break;
            case 5:
                $begin_time = $start_time;
                $end_time = $end_time;
                break;
        }

        $session_total = Db::name('customer_info')->where(['company_id'=>$company_id,'uid'=>$uid])->where('add_time','between time',[$begin_time,$end_time])->count();

        return $session_total;
    }
    
    /**
     * 获取发出消息
     * @param uid 客服uid
     * @param company_id 商户company_id
	 * @param type 1今天 2昨天 3近一周 4近一月 5自定义时间段
	 * @param start_time 开始时间
	 * @param end_time 结束时间
	 * @return code 200->成功
	 */
    private function getSendMessageTotal($uid, $company_id, $type, $start_time = '', $end_time = ''){
        switch($type){
            case 1:
                $yesterday_res = getDayTimeSolt();
                $begin_time = $yesterday_res['begin_time'];
                $end_time = $yesterday_res['end_time'];
                break;
            case 2:
                $yesterday_res = getYesTerdayTimeSolt();
                $begin_time = $yesterday_res['begin_time'];
                $end_time = $yesterday_res['end_time'];
                break;
            case 3:
                $yesterday_res = getWeekTimeSolt();
                $begin_time = $yesterday_res['begin_time'];
                $end_time = $yesterday_res['end_time'];
                break;  
            case 4:
                $month_res = getMonthTimeSolt();
                $begin_time = $month_res['begin_time'];
                $end_time = $month_res['end_time'];
                break;
            case 5:
                $begin_time = $start_time;
                $end_time = $end_time;
                break;
        }

        $session_total = Db::query("SELECT COUNT(*) AS count FROM ( SELECT * FROM tb_message_data_1 UNION SELECT * FROM tb_message_data_2 UNION SELECT * FROM tb_message_data_3 UNION SELECT * FROM tb_message_data_4 UNION SELECT * FROM tb_message_data_5 UNION SELECT * FROM tb_message_data_6 UNION SELECT * FROM tb_message_data_7 UNION SELECT * FROM tb_message_data_8 UNION SELECT * FROM tb_message_data_9 UNION SELECT * FROM tb_message_data_10) AS message_data WHERE  `company_id` = '$company_id'  AND `uid` = $uid  AND `add_time` BETWEEN '$begin_time' AND '$end_time' LIMIT 1")[0]['count'];

        return $session_total;
    }
}