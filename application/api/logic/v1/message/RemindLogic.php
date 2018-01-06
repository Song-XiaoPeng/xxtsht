<?php
namespace app\api\logic\v1\message;
use think\Model;
use think\Db;
use app\api\common\Common;
use app\api\logic\v1\customer\CustomerOperationLogic;

class RemindLogic extends Model {
    /**
     * 增加客户提醒
     * @param remind_content 提醒内容
	 * @param wx_user_id 客户基础信息id
	 * @param remind_time 提醒时间
	 * @param uid 账号uid
	 * @param remind_uid 提醒账号uid
	 * @param company_id 商户company_id
	 * @param remind_type 提醒类型 1线索跟踪提醒 2意向跟踪提醒 3回访提醒
	 * @return code 200->成功
	 */
    public function addRemind($data){
        $remind_content = $data['remind_content'];
        $wx_user_id = $data['wx_user_id'];
        $remind_time = $data['remind_time'];
        $uid = $data['uid'];
        $remind_uid = empty($data['remind_uid']) == true ? $uid : $data['remind_uid'];
        $company_id = $data['company_id'];
        $remind_type = empty($data['remind_type']) == true ? -1 : $data['remind_type'];

        $time = date('Y-m-d H:i:s');
        if(strtotime($time) >= strtotime($remind_time)){
            return msg(3002,'提醒时间不合法');
        }

        $customer_service_res = Db::name('customer_service')
        ->where(['company_id'=>$company_id,'uid'=>$remind_uid])
        ->find();
        if(!$customer_service_res){
            return msg(3003,'提醒账号未开通客服权限');
        }

        $remind_id = md5(uniqid());

        $insert_data = [
            'remind_id' => $remind_id,
            'remind_content' => $remind_content,
            'uid' => $uid,
            'company_id' => $company_id,
            'add_time' => $time,
            'remind_time' => $remind_time,
            'remind_uid' => $remind_uid,
            'wx_user_id' => $wx_user_id,
            'remind_type' => $remind_type
        ];

        $insert_res = Db::name('remind')->insert($insert_data);

        if($insert_res){
            return msg(200,'success',['remind_id'=>$remind_id]);
        }else{
            return msg(3001,'插入数据失败');
        }
    }

    /**
     * 获取客户提醒列表
     * @param page 分页参数默认1
     * @param wx_user_id 客户基础信息id
	 * @param uid 账号uid
	 * @param company_id 商户company_id
	 * @param is_remind 是否已提醒 -1否 1是
	 * @return code 200->成功
	 */
    public function getRemindList($data){
        $company_id = $data['company_id'];
        $page = $data['page'];
        $uid = $data['uid'];
        $wx_user_id = $data['wx_user_id'];
        $is_remind = empty($data['is_remind']) == true ? -1 : $data['is_remind'];

        //分页
        $page_count = 16;
        $show_page = ($page - 1) * $page_count;

        $remind_list = Db::name('remind')
        ->where(['company_id'=>$company_id, 'wx_user_id'=>$wx_user_id])
        ->limit($show_page, $page_count)
        ->select();

        $count = Db::name('remind')
        ->where(['company_id'=>$company_id, 'wx_user_id'=>$wx_user_id])
        ->count();

        foreach($remind_list as $k=>$v){
            $wx_user_sql = Db::name('wx_user')
            ->partition([], "", ['type'=>'md5','num'=>config('separate')['wx_user']])
            ->where(['wx_user_id'=>$v['wx_user_id']])
            ->buildSql();
    
            $wx_user_list = Db::table('tb_customer_info')
            ->alias('a')
            ->join([$wx_user_sql=> 'w'], 'a.customer_info_id = w.customer_info_id','RIGHT')
            ->select();
    
            $customer_operation = new CustomerOperationLogic();
            $wx_user_res = $customer_operation->getCustomerDetails($wx_user_list)[0];

            $remind_list[$k] = array_merge($remind_list[$k], $wx_user_res);
        }

        $res['data_list'] = count($remind_list) == 0 ? array() : $remind_list;
        $res['page_data']['count'] = $count;
        $res['page_data']['rows_num'] = $page_count;
        $res['page_data']['page'] = $page;
        
        return msg(200,'success',$res);
    }

    /**
     * 删除客户提醒
     * @param remind_id 删除的提醒id
	 * @param uid 账号uid
	 * @param company_id 商户company_id
	 * @return code 200->成功
	 */
    public function delRemind($remind_id,$uid,$company_id){
        $del_res = Db::name('remind')->where(['remind_id'=>$remind_id,'company_id'=>$company_id,'uid'=>$uid])->delete();

        if($del_res){
            return msg(200,'success');
        }else{
            return msg(3001,'删除失败');
        }
    }

    /**
     * 修改客户提醒
     * @param remind_id 修改的提醒id
     * @param remind_time 提醒时间
     * @param remind_content 提醒内容
	 * @param uid 账号uid
	 * @param company_id 商户company_id
     * @param remind_type 提醒类型 1线索跟踪提醒 2意向跟踪提醒 3回访提醒
	 * @return code 200->成功
	 */
    public function updateRemindTime($data){
        $remind_id = $data['remind_id'];
        $remind_time = $data['remind_time'];
        $remind_content = $data['remind_content'];
        $uid = $data['uid'];
        $company_id = $data['company_id'];
        $remind_type = empty($data['remind_type']) == true ? -1 : $data['remind_type'];

        $time = date('Y-m-d H:i:s');
        if(strtotime($time) >= strtotime($remind_time)){
            return msg(3002,'提醒时间不合法');
        }

        $update_time_res = Db::name('remind')->where(['remind_id'=>$remind_id,'company_id'=>$company_id,'uid'=>$uid])->update(['remind_time'=>$remind_time,'remind_content'=>$remind_content,'remind_type'=>$remind_type]);
        if($update_time_res !== false){
            return msg(200,'success');
        }else{
            return msg(3001,'更新数据失败');
        }
    }

    /**
     * 设置提醒已完成
     * @param company_id 商户company_id
     * @param uid 账号uid
     * @param remind_id 提醒id
	 * @return code 200->成功
	 */
    public function setComplete($data){
        $company_id = $data['company_id'];
        $uid = $data['uid'];
        $remind_id = $data['remind_id'];
        $complete_content = empty($data['complete_content']) == true ? '' : $data['complete_content'];
        
        $remind_res = Db::name('remind')->where(['company_id'=>$company_id,'remind_id'=>$remind_id])->find();
        if(!$remind_res){
            return msg(3001,'remind_id错误');
        }

        $remind_res = Db::name('remind')->where(['remind_id'=>$remind_id])->update(['is_complete'=>1,'complete_content'=>$complete_content]);
        if($remind_res !== false){
            return msg(200,'success');
        }else{
            return msg(3001,'更新数据失败');
        }
    }

    /**
     * 获取客户的跟踪提醒列表
     * @param company_id 商户company_id
     * @param user_type 账户类型
     * @param page 分页参数 默认1
     * @param uid 账号uid
     * @param search_text 搜索名称(选传)
     * @param remind_type 提醒类型 1线索跟踪提醒 2意向跟踪提醒 3回访提醒
     * @param time_type 筛选时间条件类型 1今日需联系 2昨日需联系 3本周需联系 4本月需联系 5超时需联系 6已完成
	 * @return code 200->成功
	 */
    public function getAllRemindList($data){
        $company_id = $data['company_id'];
        $uid = $data['uid'];
        $user_type = $data['user_type'];
        $remind_type = empty($data['remind_type']) == true ? -1 : $data['remind_type'];
        $time_type = empty($data['time_type']) == true ? '' : $data['time_type'];
        $search_text = empty($data['search_text']) == true ? '' : $data['search_text'];
        $page = $data['page'];
        $time = date('Y-m-d H:i:s');

        //分页
        $page_count = 16;
        $show_page = ($page - 1) * $page_count;

        $map['tb_remind.company_id'] = $company_id;
        $map['tb_remind.uid'] = $uid;
        $map['tb_remind.remind_type'] = $remind_type;
        if($search_text){
            $map['tb_customer_info.real_name'] = ['like', "%$search_text%"];
        }
        if($time_type == 6){
            $map['tb_remind.is_complete'] = 1;
        }

        $wx_user_sql = Db::name('wx_user')
        ->partition([], "", ['type'=>'md5','num'=>config('separate')['wx_user']])
        ->buildSql();

        $list = Db::name('remind')
        ->alias('a')
        ->where($map)
        ->join([$wx_user_sql=> 'b'], 'a.wx_user_id = b.wx_user_id','RIGHT')
        ->join('tb_customer_info c', 'b.customer_info_id = c.customer_info_id','LEFT')
        ->limit($show_page, $page_count)
        ->select();

        $count = Db::name('remind')
        ->alias('a')
        ->where($map)
        ->join([$wx_user_sql=> 'b'], 'a.wx_user_id = b.wx_user_id','RIGHT')
        ->join('tb_customer_info c', 'b.customer_info_id = c.customer_info_id','LEFT')
        ->count();

        $res['data_list'] = count($list) == 0 ? array() : $list;
        $res['page_data']['count'] = $count;
        $res['page_data']['rows_num'] = $page_count;
        $res['page_data']['page'] = $page;
        
        return msg(200,'success',$res);
    }
}