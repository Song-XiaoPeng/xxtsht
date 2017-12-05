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
	 * @param type 1今天 2昨天 3近3天 4近一周 5近一月 6自定义时间段
	 * @param start_time 开始时间
	 * @param end_time 结束时间
	 * @return code 200->成功
	 */
    public function getCustomerServiceRanking($data){
        $company_id = $data['company_id'];
        $type = $data['type'];
        $start_time = empty($data['start_time']) == true ? '' : $data['start_time'];
        $end_time = empty($data['end_time']) == true ? '' : $data['end_time'];

        $list = Db::name('customer_service')->where(['company_id'=>$company_id])->field('name,customer_service_id,uid')->select();

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

            $statistical_data['session_total'] = Db::name('message_session')
            ->partition('', '', ['type'=>'md5','num'=>config('separate')['message_session']])
            ->where(['company_id'=>$company_id,'uid'=>$v['uid']])->cache(true,60)->count();

            $statistical_data['first_session'] = Db::name('message_session')
            ->partition('', '', ['type'=>'md5','num'=>config('separate')['message_session']])
            ->where(['company_id'=>$company_id,'uid'=>$v['uid']])->group('customer_wx_openid')->cache(true,60)->count();

            $effective = Db::name('message_session')
            ->partition('', '', ['type'=>'md5','num'=>config('separate')['message_session']])
            ->where(['company_id'=>$company_id,'uid'=>$v['uid'],'state'=>array('in',[-1,1])])
            ->cache(true,60)
            ->count();

            $statistical_data['effective'] = $effective;
            $statistical_data['auto_session'] = $effective;

            $invalid_session = Db::name('message_session')
            ->partition('', '', ['type'=>'md5','num'=>config('separate')['message_session']])
            ->where(['company_id'=>$company_id,'uid'=>$v['uid'],'state'=>-2])
            ->cache(true,60)
            ->count();

            $statistical_data['invalid_session'] = $invalid_session;
            $statistical_data['session_missing'] = $invalid_session;

            $statistical_data['collection'] = Db::name('customer_info')
            ->partition('', '', ['type'=>'md5','num'=>config('separate')['customer_info']])
            ->where(['uid'=>$v['uid']])
            ->count();

            $statistical_data['send_message_total'] = Db::name('message_data')
            ->partition('', '', ['type'=>'md5','num'=>config('separate')['message_data']])
            ->where(['uid'=>$v['uid']])
            ->cache(true,60)
            ->count();

            $list[$k] = array_merge($statistical_data,$v);
        }

        return msg(200,'success',$list);
    }
}