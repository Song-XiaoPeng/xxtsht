<?php
namespace app\api\logic\v1\message;
use think\Model;
use think\Db;

class RemindLogic extends Model {
    /**
     * 增加客户提醒
     * @param remind_content 提醒内容
	 * @param wx_user_id 提醒客户微信基础信息id
	 * @param remind_time 提醒时间
	 * @param uid 账号uid
	 * @param company_id 商户company_id
	 * @return code 200->成功
	 */
    public function addRemind($data){
        $remind_content = $data['remind_content'];
        $wx_user_id = $data['wx_user_id'];
        $remind_time = $data['remind_time'];
        $uid = $data['uid'];
        $company_id = $data['company_id'];
        $remind_openid = $data['remind_openid'];

        $wx_user_res = Db::name('wx_user')
        ->partition('', '', ['type'=>'md5','num'=>config('separate')['wx_user']])
        ->where(['company_id'=>$company_id,'wx_user_id'=>$wx_user_id])
        ->find();

        if(!$wx_user_res){
            return msg(3001,'客户基础信息不存在');
        }

        $remind_id = md5(uniqid());

        $insert_res = Db::name('remind')->insertGetId([
            'remind_id' => $remind_id,
            'remind_content' => $remind_content,
            'wx_user_id' => $wx_user_id,
            'uid' => $uid,
            'company_id' => $company_id,
            'add_time' => date('Y-m-d H:i:s'),
            'remind_time' => $remind_time,
            'remind_openid' => $remind_openid,
        ]);

        if($insert_res){
            return msg(200,'success',['remind_id'=>$remind_id]);
        }else{
            return msg(3001,'插入数据失败');
        }
    }

    /**
     * 获取客户提醒列表
     * @param page 分页参数默认1 空返回全部
     * @param wx_user_id 提醒的客户微信基础信息id
	 * @param uid 账号uid
	 * @param is_remind 是否已经提醒 1是 -1否 不传返回全部
	 * @param company_id 商户company_id
	 * @return code 200->成功
	 */
    public function getRemindList($data){
        $company_id = $data['company_id'];
        $wx_user_id = $data['wx_user_id'];
        $uid = $data['uid'];
        $page = empty($data['page']) == true ? '' : $data['page'];
        $is_remind = empty($data['is_remind']) == true ? '' : $data['is_remind'];

        if($wx_user_id){
            $map['wx_user_id'] = $wx_user_id;
        }
        if($is_remind){
            $map['is_remind'] = $is_remind;
        }
        $map['company_id'] = $company_id;
        $map['uid'] = $uid;

        //分页
        if($page){
            $page_count = 16;
            $show_page = ($page - 1) * $page_count;

            $list = Db::name('remind')->where($map)->limit($show_page,$page_count)->select();
            $count = Db::name('remind')->where($map)->count();
        }else{
            $list = Db::name('remind')->where($map)->select();
        }

        foreach($list as $k=>$v){
            $wx_user_info = Db::name('wx_user')
            ->partition('', '', ['type'=>'md5','num'=>config('separate')['wx_user']])
            ->where(['wx_user_id'=>$v['wx_user_id']])
            ->field('nickname,portrait,qrcode_id,customer_info_id,groupid,subscribe_time,appid,subscribe')
            ->cache(true,10)
            ->find();
            if(!$wx_user_info){
                $list[$k]['wx_user_info'] = null;
                $list[$k]['customer_info'] = null;
                continue;
            }

            $customer_info_res = Db::name('customer_info')
            ->partition('', '', ['type'=>'md5','num'=>config('separate')['customer_info']])
            ->where(['customer_info_id'=>$wx_user_info['customer_info_id']])
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
                $customer_info_res['product_name'] = Dn::name('product')->where(['product_id'=>$customer_info_res['product_id']])->cache(true,10)->value('product_name');
            }else{
                $customer_info_res['product_name'] = null;
            }

            $list[$k]['customer_info'] = $customer_info_res;
            $list[$k]['wx_user_info'] = $wx_user_info_res;
        }

        $res['data_list'] = count($list) == 0 ? array() : $list;
        if($page){
            $res['page_data']['count'] = $count;
            $res['page_data']['rows_num'] = $page_count;
            $res['page_data']['page'] = $page;
        }else{
            $res['page_data']= null;
        }
        
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
        $del_res = Db::name('remind')->where(['remind_id'=>$remind_id,'uid'=>$uid,'company_id'=>$company_id])->delete();

        if($del_res){
            return msg(200,'success');
        }else{
            return msg(3001,'删除失败');
        }
    }

    /**
     * 修改客户提醒时间
     * @param remind_id 修改的提醒id
     * @param remind_time 提醒时间
	 * @param uid 账号uid
	 * @param company_id 商户company_id
	 * @return code 200->成功
	 */
    public function updateRemindTime($remind_id,$uid,$company_id,$remind_time){
        $update_res = Db::name('remind')->where(['remind_id'=>$remind_id,'uid'=>$uid,'company_id'=>$company_id,'is_remind'=>-1])->update([
            'remind_time' => $remind_time
        ]);

        if($update_res !== false){
            return msg(200,'success');
        }else{
            return msg(3001,'修改失败');
        }
    }
}