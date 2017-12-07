<?php
namespace app\api\logic\v1\message;
use think\Model;
use think\Db;
use app\api\common\Common;

class RemindLogic extends Model {
    /**
     * 增加客户提醒
     * @param remind_content 提醒内容
	 * @param customer_info_id 客户基础信息id
	 * @param remind_time 提醒时间
	 * @param uid 账号uid
	 * @param remind_uid 提醒账号uid
	 * @param company_id 商户company_id
	 * @return code 200->成功
	 */
    public function addRemind($data){
        $remind_content = $data['remind_content'];
        $customer_info_id = $data['customer_info_id'];
        $remind_time = $data['remind_time'];
        $uid = $data['uid'];
        $remind_uid = $data['remind_uid'];
        $company_id = $data['company_id'];

        $time = date('Y-m-d H:i:s');
        if(strtotime($time) >= strtotime($remind_time)){
            return msg(3002,'提醒时间不合法');
        }

        $customer_info = Db::name('customer_info')
        ->where(['company_id'=>$company_id,'customer_info_id'=>$customer_info_id])
        ->find();
        if(!$customer_info){
            return msg(3001,'客户信息不存在');
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
            'customer_info_id' => $customer_info['customer_info_id'],
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
     * @param customer_info_id 客户基础信息id
	 * @param uid 账号uid
	 * @param company_id 商户company_id
	 * @return code 200->成功
	 */
    public function getRemindList($data){
        $company_id = $data['company_id'];
        $customer_info_id = $data['customer_info_id'];
        $uid = $data['uid'];
        $token = $data['token'];
        $page = empty($data['page']) == true ? '' : $data['page'];

        if($customer_info_id){
            $map['customer_info_id'] = $customer_info_id;
        }
        $map['company_id'] = $company_id;
        $map['uid'] = $uid;

        //分页
        $page_count = 16;
        $show_page = ($page - 1) * $page_count;

        $list = Db::name('remind')->where($map)->limit($show_page,$page_count)->select();
        $count = Db::name('remind')->where($map)->count();

        foreach($list as $k=>$v){
            $customer_info_res = Db::name('customer_info')
            ->where(['customer_info_id'=>$v['customer_info_id']])
            ->cache(true,10)
            ->find();
            if(!$customer_info_res){
                $list[$k]['wx_user_info'] = null;
                $list[$k]['customer_info'] = null;
                continue;
            }

            if($customer_info_res['wx_company_id']){
                $customer_info_res['wx_comapny_name'] = Db::name('wx_user_company')->where(['wx_company_id'=>$customer_info_res['wx_company_id']])->cache(true,10)->value('wx_comapny_name');
            }else{
                $customer_info_res['wx_comapny_name'] = null;
            }

            if($customer_info_res['wx_user_group_id']){
                $customer_info_res['wx_user_group_name'] = Db::name('wx_user_group')->where(['wx_user_group_id'=>$customer_info_res['wx_user_group_id']])->cache(true,10)->value('group_name');
            }else{
                $customer_info_res['wx_user_group_name'] = null;
            }

            if($customer_info_res['wx_user_group_id']){
                $customer_info_res['product_name'] = Db::name('product')->where(['product_id'=>$customer_info_res['product_id']])->cache(true,10)->value('product_name');
            }else{
                $customer_info_res['product_name'] = null;
            }

            $client = new \GuzzleHttp\Client();
            $request_res = $client->request(
                'POST', 
                combinationApiUrl('/api.php/IvisionBackstage/getUserInfo'), 
                [
                    'json' => ['uid'=>$v['uid'],'company_id'=>$company_id],
                    'timeout' => 3,
                    'headers' => [
                        'token' => $token
                    ]
                ]
            );

            $list[$k]['create_user_name'] = json_decode($request_res->getBody(),true)['body']['user_name'];

            $customer_service_name = Db::name('customer_service')->where(['uid'=>$v['remind_uid'],'company_id'=>$company_id])->cache(true,30)->value('name');

            $list[$k]['customer_service_name'] = empty($customer_service_name) == true ? '客服已删除' : $customer_service_name;

            $list[$k]['customer_info'] = $customer_info_res;
        }

        $res['data_list'] = count($list) == 0 ? array() : $list;
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
	 * @return code 200->成功
	 */
    public function updateRemindTime($data){
        $remind_id = $data['remind_id'];
        $remind_time = $data['remind_time'];
        $remind_content = $data['remind_content'];
        $uid = $data['uid'];
        $company_id = $data['company_id'];

        $time = date('Y-m-d H:i:s');
        if(strtotime($time) >= strtotime($remind_time)){
            return msg(3002,'提醒时间不合法');
        }

        $update_time_res = Db::name('remind')->where(['company_id'=>$company_id,'uid'=>$uid])->update(['remind_time'=>$remind_time]);
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
     * @param page 分页参数 默认1
     * @param uid 账号uid
     * @param search_text 搜索名称(选传)
     * @param customer_type 客户类型 0线索 1意向客户 2订单客户 3追销客户
     * @param time_type 筛选时间条件类型 1今日需联系 2昨日需联系 3本周需联系 4本月需联系 5超时需联系 6已完成
	 * @return code 200->成功
	 */
    public function getAllRemindList($data){
        $company_id = $data['company_id'];
        $uid = $data['uid'];
        $user_type = $data['user_type'];
        $customer_type = $data['customer_type'];
        $time_type = $data['time_type'];
        $search_text = empty($data['search_text']) == true ? '' : $data['search_text'];
        $page = $data['page'];

        $time = date('Y-m-d H:i:s');

        //分页
        $page_count = 16;
        $show_page = ($page - 1) * $page_count;

        $map['tb_remind.company_id'] = $company_id;
        $map['tb_customer_info.customer_type'] = $customer_type;
        $map['tb_remind.is_complete'] = -1;

        if($user_type != 3){
            $map['tb_remind.remind_uid'] = $uid;
        }

        if($search_text != ''){
            $map['tb_customer_info.real_name'] = $search_text;
        }

        switch($time_type){
            case 1:
                $list = Db::name('remind')
                ->alias('a')
                ->join('tb_customer_info b','a.customer_info_id = b.customer_info_id')
                ->limit($show_page,$page_count)
                ->where($map)
                ->whereTime('remind_time', 'today')
                ->select();

                $count = Db::name('remind')
                ->alias('a')
                ->join('tb_customer_info b','a.customer_info_id = b.customer_info_id')
                ->where($map)
                ->whereTime('remind_time', 'today')
                ->count();
                break;
            case 2:
                $list = Db::name('remind')
                ->alias('a')
                ->join('tb_customer_info b','a.customer_info_id = b.customer_info_id')
                ->where($map)->limit($show_page,$page_count)
                ->whereTime('remind_time', 'yesterday')
                ->select();

                $count = Db::name('remind')
                ->alias('a')
                ->join('tb_customer_info b','a.customer_info_id = b.customer_info_id')
                ->where($map)
                ->whereTime('remind_time', 'yesterday')
                ->count();
                break;
            case 3:
                $list = Db::name('remind')
                ->alias('a')
                ->join('tb_customer_info b','a.customer_info_id = b.customer_info_id')
                ->where($map)
                ->limit($show_page,$page_count)
                ->whereTime('remind_time', 'week')
                ->select();

                $count = Db::name('remind')
                ->alias('a')
                ->join('tb_customer_info b','a.customer_info_id = b.customer_info_id')
                ->where($map)
                ->whereTime('remind_time', 'week')
                ->count();
                break;
            case 4:
                $list = Db::name('remind')
                ->alias('a')
                ->join('tb_customer_info b','a.customer_info_id = b.customer_info_id')
                ->where($map)
                ->limit($show_page,$page_count)
                ->whereTime('remind_time', 'month')
                ->select();

                $count = Db::name('remind')
                ->alias('a')
                ->join('tb_customer_info b','a.customer_info_id = b.customer_info_id')
                ->where($map)
                ->whereTime('remind_time', 'month')
                ->count();
                break;
            case 5:
                $list = Db::name('remind')
                ->alias('a')
                ->join('tb_customer_info b','a.customer_info_id = b.customer_info_id')
                ->where($map)
                ->limit($show_page,$page_count)
                ->whereTime('remind_time', '<', $time)
                ->select();

                $count = Db::name('remind')
                ->alias('a')
                ->join('tb_customer_info b','a.customer_info_id = b.customer_info_id')
                ->where($map)
                ->whereTime('remind_time', '<', $time)
                ->count();
                break;
            case 6:
                $map['is_complete'] = 1;
                $list = Db::name('remind')
                ->alias('a')
                ->join('tb_customer_info b','a.customer_info_id = b.customer_info_id')
                ->where($map)
                ->limit($show_page,$page_count)
                ->select();

                $count = Db::name('remind')
                ->alias('a')
                ->join('tb_customer_info b','a.customer_info_id = b.customer_info_id')
                ->where($map)
                ->count();
                break;
        }

        foreach($list as $k=>$v){
            $product_name = Db::name('product')->where(['product_id'=>$v['product_id']])->cache(true,60)->value('product_name');

            $list[$k]['product_name'] = empty($product_name) == true ? '暂无产品' : $product_name;

            $wx_comapny_name = Db::name('wx_user_company')->where(['wx_company_id'=>$v['wx_company_id']])->cache(true,60)->value('wx_comapny_name');

            $list[$k]['wx_comapny_name'] = empty($wx_comapny_name) == true ? '' : $wx_comapny_name;

            $group_name = Db::name('wx_user_group')->where(['wx_user_group_id'=>$v['wx_user_group_id']])->cache(true,60)->value('group_name');
            
            $list[$k]['wx_user_group_name'] = empty($group_name) == true ? '' : $group_name;
        }

        $res['data_list'] = count($list) == 0 ? array() : $list;
        $res['page_data']['count'] = $count;
        $res['page_data']['rows_num'] = $page_count;
        $res['page_data']['page'] = $page;
        
        return msg(200,'success',$res);
    }
}