<?php
namespace app\api\logic\v1\customer;
use think\Model;
use think\Db;
use EasyWeChat\Foundation\Application;
use app\api\common\Common;

class CustomerOperationLogic extends Model {
    /**
     * 设置客户信息
     * @param company_id 商户company_id
     * @param appid 客户来源appid
     * @param customer_type 客户类型 0线索 1意向客户 2订单客户 3追销客户
     * @param uid 客服账号uid
	 * @param openid 客户微信openid
	 * @param real_name 客户真实姓名
	 * @param real_sex 客户真实性别 0未知 1男 2女
	 * @param real_phone 客户真实联系手机
	 * @param contact_address 客户联系地址
	 * @param wx_company_id 所属公司
	 * @param wx_user_group_id 所属用户分组id
	 * @param desc 备注
	 * @param product_id 意向产品id
	 * @param customer_info_id 客户信息id (关联时选传)
	 * @return code 200->成功
	 */
    public function setCustomerInfo($data){
        $company_id = $data['company_id'];
        $appid = $data['appid'];
        $uid = $data['uid'];
        $openid = $data['openid'];
        $real_name = $data['real_name'];
        $customer_type = $data['customer_type'];
        $real_sex = $data['real_sex'] == '' ? 0 : $data['real_sex'];
        $customer_info_id = empty($data['customer_info_id']) == true ? '' : $data['customer_info_id'];
        $real_phone = empty($data['real_phone']) == true ? null : $data['real_phone'];
        $contact_address = empty($data['contact_address']) == true ? '' : $data['contact_address'];
        $wx_company_id = empty($data['wx_company_id']) == true ? -1 : $data['wx_company_id'];
        $desc = empty($data['desc']) == true ? '' : $data['desc'];
        $wx_user_group_id = empty($data['wx_user_group_id']) == true ? -1 : $data['wx_user_group_id'];
        $birthday = empty($data['birthday']) == true ? null : $data['birthday'];
        $wx_number = empty($data['wx_number']) == true ? '' : $data['wx_number'];
        $email = empty($data['email']) == true ? '' : $data['email'];
        $tel = empty($data['tel']) == true ? '' : $data['tel'];
        $product_id = empty($data['product_id']) == true ? -1 : $data['product_id'];

        $time = date('Y-m-d H:i:s');

        $wx_user_res = Db::name('wx_user')
        ->partition('', '', ['type'=>'md5','num'=>config('separate')['wx_user']])
        ->where(['appid'=>$appid,'openid'=>$openid])
        ->find();
    
        if(!$wx_user_res){
            return msg(3001,'客户微信基础信息不存在或未同步');
        }

        //限制状态更改
        $wx_user_state = $wx_user_res['is_clue'];
        $is_correct_state = false;
        switch ($customer_type) {
            case 0:
                if($wx_user_state == -1 
                || $wx_user_state == 2
                || $wx_user_state == 3
                || $wx_user_state == 4
                || $wx_user_state == 5){
                    $is_correct_state = true;
                }
                break;
            case 1:
                if($wx_user_state == 3 
                || $wx_user_state == 4
                || $wx_user_state == 5){
                    $is_correct_state = true;;
                }
                break;
            case 2:
                if($wx_user_state == 5){
                    $is_correct_state = true;;
                }
        }

        //限制回滚状态
        if($is_correct_state){
            switch($wx_user_state){
                case -1:
                    $customer_type = 0;
                    break;
                case 2:
                    $customer_type = 1;
                    break;
                case 4:
                    $customer_type = 2;
                    break;
                case 5:
                    $customer_type = 3;
                    break;
            }
        }


        if(empty($customer_info_id) == true){
            $customer_info_id = '';
        }

        if(!empty($customer_info_id)){
            $customer_info_res = Db::name('customer_info')
            ->where(['customer_info_id'=>$customer_info_id,'company_id'=>$company_id])
            ->find();
            if(!$customer_info_res){
                return msg(3005,'customer_info_id参数错误');
            }
        }

        if(empty($wx_user_res['customer_info_id']) == true && empty($customer_info_id) == true){
            $customer_info_id = md5(uniqid());
            
            $db_operation_res = Db::name('customer_info')
            ->insert([
                'customer_info_id' => $customer_info_id,
                'real_name' => $real_name,
                'real_sex' => $real_sex,
                'real_phone' => $real_phone,
                'contact_address' => $contact_address,
                'wx_company_id' => $wx_company_id,
                'wx_user_group_id' => $wx_user_group_id,
                'company_id' => $company_id,
                'desc' => $desc,
                'birthday' => $birthday,
                'wx_number' => $wx_number,
                'email' => $email,
                'tel' => $tel,
                'uid' => $uid,
                'product_id' => $product_id,
                'customer_type' => $customer_type,
                'add_time' => date('Y-m-d H:i:s'),
            ]);

            $wx_user_data['customer_service_uid'] = $uid;
        }else{
            $customer_info_id = empty($customer_info_id) == true ? $wx_user_res['customer_info_id'] : $customer_info_id;

            $db_operation_res = Db::name('customer_info')
            ->where([
                'customer_info_id' => $customer_info_id,
                'company_id' => $company_id
            ])
            ->update([
                'real_name' => $real_name,
                'real_sex' => $real_sex,
                'real_phone' => $real_phone,
                'contact_address' => $contact_address,
                'wx_company_id' => $wx_company_id,
                'wx_user_group_id' => $wx_user_group_id,
                'desc' => $desc,
                'birthday' => $birthday,
                'wx_number' => $wx_number,
                'email' => $email,
                'tel' => $tel,
                'product_id' => $product_id,
                'customer_type' => $customer_type,
            ]);
        }

        $wx_user_data['customer_info_id'] = $customer_info_id;
        
        //微信客户状态
        switch($customer_type){
            // 0线索 
            case 0:
                $wx_user_data['is_clue'] = -1;
                $wx_user_data['set_clue_time'] = $time;
                break;
            // 1意向客户
            case 1:
                $wx_user_data['is_clue'] = 2;
                break;
            // 2订单客户
            case 2:
                $wx_user_data['is_clue'] = 4;
                break;
            // 3追销客户
            case 3:
                $wx_user_data['is_clue'] = 5;
                break;
        }

        $db_operation_res = Db::name('wx_user')
        ->partition(['company_id'=>$company_id], 'company_id', ['type'=>'md5','num'=>config('separate')['wx_user']])
        ->where(['appid'=>$appid,'openid'=>$openid])
        ->update($wx_user_data);

        if($db_operation_res !== false){
            return msg(200,'success',['customer_info_id'=>$customer_info_id]);
        }else{
            return msg(3002,'数据操作失败');
        }
    }

    /**
     * 判断是否允许领取线索池客户
     * @param company_id 商户company_id
     * @param uid 登录的账号uid
	 * @return code 200->成功
	 */
    public function isAllowClue($company_id, $uid){
        $yesterday_res = getDayTimeSolt();
        $begin_time = $yesterday_res['begin_time'];
        $end_time = $yesterday_res['end_time'];

        $count = Db::query("SELECT COUNT(*) AS count FROM ( SELECT * FROM tb_wx_user_1 UNION SELECT * FROM tb_wx_user_2 UNION SELECT * FROM tb_wx_user_3 UNION SELECT * FROM tb_wx_user_4 UNION SELECT * FROM tb_wx_user_5) AS wx_user WHERE  `customer_service_uid` = '$uid' AND `company_id` = '$company_id'  AND `is_clue` = -1  AND `set_clue_time` BETWEEN '$begin_time' AND '$end_time' LIMIT 1")[0]['count'];

        dump($count);
    }

    /**
     * 获取客户信息
     * @param company_id 商户company_id
     * @param appid 客户来源appid
	 * @param openid 客户微信openid
	 * @return code 200->成功
	 */
    public function getWxCustomerInfo($data){
        $company_id = $data['company_id'];
        $appid = $data['appid'];
        $openid = $data['openid'];

        $customer_info_id = Db::name('wx_user')
        ->partition('', '', ['type'=>'md5','num'=>config('separate')['wx_user']])
        ->where(['appid'=>$appid,'openid'=>$openid])
        ->value('customer_info_id');

        if(!$customer_info_id){
            return msg(3001,'暂无客户信息');
        }

        $customer_info = Db::name('customer_info')
        ->where(['customer_info_id'=>$customer_info_id])
        ->find();
        if(!$customer_info){
            return msg(3002,'暂无客户信息');
        }

        if($customer_info['wx_user_group_id'] != -1){
            $customer_info['wx_user_group_name'] = Db::name('wx_user_group')
            ->where(['wx_user_group_id'=>$customer_info['wx_user_group_id']])
            ->find();
        }else{
            $customer_info['wx_user_group_name'] = null;
        }

        if($customer_info['wx_company_id'] != -1){
            $customer_info['wx_company_name'] = Db::name('wx_user_company')
            ->where(['wx_company_id'=>$customer_info['wx_company_id']])
            ->find();
        }else{
            $customer_info['wx_company_name'] = null;
        }

        return msg(200,'success',$customer_info);
    }

    /**
     * 获取客户信息列表
     * @param company_id 商户company_id
     * @param page 分页参数 默认1
     * @param uid 登录账号uid
     * @param type 客户类型 1意向客户 2订单客户 3追销客户
     * @param ascription 客户归属类型 1我的客户 2其他人
     * @param real_name 客户姓名 (选传)
	 * @return code 200->成功
	 */
    public function getCustomerList($data){
        $company_id = $data['company_id'];
        $uid = $data['uid'];
        $page = $data['page'];
        $type = $data['type'];
        $ascription = $data['ascription'];
        $real_name = empty($data['real_name']) == true ? '' : $data['real_name'];

        //分页
        $page_count = 16;
        $show_page = ($page - 1) * $page_count;

        $map['real_name'] = ['like',"%$real_name%"];
        $map['company_id'] = $company_id;
        $map['customer_type'] = $type;

        if($ascription == 1){
            $map['uid'] = $uid;
        }

        $customer_info_res = Db::name('customer_info')
        ->limit($show_page,$page_count)
        ->where($map)
        ->select();

        $count = Db::name('customer_info')
        ->where($map)
        ->count();
        
        foreach($customer_info_res as $k=>$v){
            if($v['wx_user_group_id'] != -1){
                $customer_info_res[$k]['wx_user_group_name'] = Db::name('wx_user_group')
                ->where(['wx_user_group_id'=>$v['wx_user_group_id']])
                ->cache(true,60)
                ->value('group_name');
            }else{
                $customer_info_res[$k]['wx_user_group_name'] = null;
            }
    
            if($v['wx_company_id'] != -1){
                $customer_info_res[$k]['wx_company_name'] = Db::name('wx_user_company')
                ->where(['wx_company_id'=>$v['wx_company_id']])
                ->cache(true,60)
                ->value('wx_company_name');
            }else{
                $customer_info_res[$k]['wx_company_name'] = null;
            }

            if($v['product_id'] != -1){
                $customer_info_res[$k]['product_name'] = Db::name('product')
                ->where(['product_id'=>$v['product_id']])
                ->cache(true,60)
                ->value('product_name');
            }else{
                $customer_info_res[$k]['product_name'] = null;
            }
        }

        $res['data_list'] = count($customer_info_res) == 0 ? array() : $customer_info_res;
        $res['page_data']['count'] = $count;
        $res['page_data']['rows_num'] = $page_count;
        $res['page_data']['page'] = $page;
        
        return msg(200,'success',$res);
    }

    /**
     * 获取线索客户列表
     * @param company_id 商户company_id
     * @param real_name 微信昵称(选传模糊搜索)
     * @param page 分页参数 默认1
     * @param uid 登录账号uid
     * @param type 类型 1线索池客户 2线索客户
     * @param ascription 客户线索归属 1我的 2下属 3全部
	 * @return code 200->成功
	 */
    public function getClueCustomer($data){
        $company_id = $data['company_id'];
        $real_name = empty($data['real_name']) == true ? '' : $data['real_name'];
        $page = $data['page'];
        $ascription = empty($data['ascription']) == true ? 1 : $data['ascription'];
        $uid = $data['uid'];
        $user_type = $data['user_type'];
        $type = empty($data['type']) == true ? 1:$data['type'];

        //分页
        $page_count = 16;
        $show_page = ($page - 1) * $page_count;

        $map['company_id'] = $company_id;
        $map['is_clue'] = 1;

        //获取我的团队账号
        $uid_res = Common::getAscriptionUidList($company_id, $uid, $user_type);
        if($uid_res['meta']['code'] != 200){
            return $uid_res;
        }

        switch($user_type){
            case '3':
                if($type == 1){
                    $map['is_clue'] = 1;
                }else if($type == 2){
                    $map['is_clue'] = -1;
                }
                break;
            case '4':
                if ($type == 2 && $ascription == 1) {
                    $map['customer_service_uid'] = $uid;
                    $map['is_clue'] = -1;
                }else if ($type == 2 && $ascription == 2) {
                    if(!empty($uid_res['body'])){
                        $uid_list = $uid_res['body'];
                        foreach($uid_list as $k=>$v){
                            if($v == $uid){
                                unset($uid_list[$k]);
                            }
                        }
    
                        $uid_list = array_values($uid_list);
    
                        $map['customer_service_uid'] = array('in',$uid_list);
                        $map['is_clue'] = -1;
                    }else{
                        $res['data_list'] = [];
                        $res['page_data']['count'] = 0;
                        $res['page_data']['rows_num'] = $page_count;
                        $res['page_data']['page'] = $page;
                        
                        return msg(200,'success',$res);
                    }
                }else if ($type == 2 && $ascription == 3) {
                    if(!empty($uid_res['body'])){
                        $map['customer_service_uid'] = array('in',$uid_res['body']);
                        $map['is_clue'] = -1;
                    }else{
                        $map['customer_service_uid'] = $uid;
                        $map['is_clue'] = -1;
                    }
                }
                break;
        }

        $wx_user_sql = Db::name('wx_user')
        ->partition([], "", ['type'=>'md5','num'=>config('separate')['wx_user']])
        ->where($map)
        ->order('subscribe_time desc')
        ->buildSql();

        if ($type == 1) {
            $customer_info_map['is_clue'] = 1;
        }else{
            $customer_info_map['is_clue'] = -1;
        }

        $customer_info_map['real_name'] = array('like',"%$real_name%");

        if($real_name){
            $wx_user_list = Db::table('tb_customer_info')
            ->alias('a')
            ->join([$wx_user_sql=> 'w'], 'a.customer_info_id = w.customer_info_id','RIGHT')
            ->where($customer_info_map)
            ->limit($show_page,$page_count)
            ->select();

            $count = Db::table('tb_customer_info')
            ->alias('a')
            ->join([$wx_user_sql=> 'w'], 'a.customer_info_id = w.customer_info_id','RIGHT')
            ->where($customer_info_map)
            ->count();
        }else{
            $wx_user_list = Db::table('tb_customer_info')
            ->alias('a')
            ->join([$wx_user_sql=> 'w'], 'a.customer_info_id = w.customer_info_id','RIGHT')
            ->limit($show_page,$page_count)
            ->select();

            $count = Db::table('tb_customer_info')
            ->alias('a')
            ->join([$wx_user_sql=> 'w'], 'a.customer_info_id = w.customer_info_id','RIGHT')
            ->count();
        }

        foreach($wx_user_list as $k=>$v){
            $wx_user_list[$k]['app_name'] = Db::name('openweixin_authinfo')->where(['appid'=>$v['appid']])->cache(true,60)->value('nick_name');

            if($v['qrcode_id']){
                $wx_user_list[$k]['source_qrcode_name'] = Db::name('extension_qrcode')->where(['qrcode_id'=>$v['qrcode_id']])->cache(true,60)->value('activity_name');
            }else{
                $wx_user_list[$k]['source_qrcode_name'] = '暂无来源二维码';
            }

            if($v['product_id']){
                $wx_user_list[$k]['product_name'] = Db::name('product')->where(['product_id'=>$v['product_id']])->cache(true,60)->value('product_name');
            }else{
                $wx_user_list[$k]['product_name'] = null;
            }

            if($v['customer_service_uid']){
                $wx_user_list[$k]['customer_service_name'] = Db::name('user')->where(['uid'=>$v['customer_service_uid']])->cache(true,60)->value('user_name');
            }else{
                $wx_user_list[$k]['customer_service_name'] = null;
            }

            if($v['wx_company_id']){
                $wx_user_list[$k]['wx_comapny_name'] = Db::name('wx_user_company')->where(['wx_company_id'=>$v['wx_company_id']])->cache(true,60)->value('wx_comapny_name');
            }else{
                $wx_user_list[$k]['wx_comapny_name'] = null;
            }
        }

        $res['data_list'] = count($wx_user_list) == 0 ? array() : $wx_user_list;
        $res['page_data']['count'] = $count;
        $res['page_data']['rows_num'] = $page_count;
        $res['page_data']['page'] = $page;
        
        return msg(200,'success',$res);
    }

    /**
     * 获取线索数据统计
     * @param company_id 商户company_id
     * @param uid 登录账号uid
	 * @return code 200->成功
	 */
    public function getClueStatisticData($company_id, $uid, $user_type){
        if($user_type != 3){
            //获取我的团队账号
            $uid_res = Common::getAscriptionUidList($company_id, $uid, $user_type);
            if($uid_res['meta']['code'] != 200){
                return $uid_res;
            }

            $uid_list = $uid_res['body'];
            array_push($uid_list, $uid);

            $map['customer_service_uid'] = ['in', $uid_list];
        }

        //获取总线索数据
        $map['company_id'] = $company_id;
        $map['is_clue'] = -1;
        $clue = Db::name('wx_user')
        ->partition([], "", ['type'=>'md5','num'=>config('separate')['wx_user']])
        ->where($map)
        ->count();

        //获取今日加入线索的数据
        $yesterday_res = getDayTimeSolt();
        $begin_time = $yesterday_res['begin_time'];
        $end_time = $yesterday_res['end_time'];
        $today = Db::query("SELECT COUNT(*) AS count FROM ( SELECT * FROM tb_wx_user_1 UNION SELECT * FROM tb_wx_user_2 UNION SELECT * FROM tb_wx_user_3 UNION SELECT * FROM tb_wx_user_4 UNION SELECT * FROM tb_wx_user_5) AS wx_user WHERE  `company_id` = '$company_id'  AND `is_clue` = -1  AND `set_clue_time` BETWEEN '$begin_time' AND '$end_time' LIMIT 1")[0]['count'];

        $arr = [
            'clue' => $clue,
            'today' => $today,
            'follow_up' => 0,
            'intention' => 0
        ];
        
        return msg(200,'success',$arr);
    }

    /**
     * 模糊搜索获取客户信息
     * @param company_id 商户company_id
     * @param real_name 客户姓名 (选传)
     * @param real_phone 客户手机 (选传)
	 * @return code 200->成功
	 */
    public function searchCustomerInfo($data){
        $company_id = $data['company_id'];
        $real_name = empty($data['real_name']) == true ? '' : $data['real_name'];
        $real_phone = empty($data['real_phone']) == true ? '' : $data['real_phone'];

        if($real_name){
            $map['real_name'] = ['like',"%$real_name%"];
        }

        if($real_phone){
            $map['real_phone'] = ['like',"%$real_phone%"];
        }
        $map['company_id'] = $company_id;

        $customer_info_res = Db::name('customer_info')
        ->where($map)
        ->select();
        
        foreach($customer_info_res as $k=>$v){
            if($v['wx_user_group_id'] != -1){
                $customer_info_res[$k]['wx_user_group_name'] = Db::name('wx_user_group')
                ->where(['wx_user_group_id'=>$v['wx_user_group_id']])
                ->cache(true,60)
                ->value('group_name');
            }else{
                $customer_info_res[$k]['wx_user_group_name'] = null;
            }
    
            if($v['wx_company_id'] != -1){
                $customer_info_res[$k]['wx_company_name'] = Db::name('wx_user_company')
                ->where(['wx_company_id'=>$v['wx_company_id']])
                ->cache(true,60)
                ->value('wx_company_name');
            }else{
                $customer_info_res[$k]['wx_company_name'] = null;
            }

            if($v['product_id'] != -1){
                $customer_info_res[$k]['product_name'] = Db::name('product')
                ->where(['product_id'=>$v['product_id']])
                ->cache(true,60)
                ->value('product_name');
            }else{
                $customer_info_res[$k]['product_name'] = null;
            }
        }

        return msg(200,'success',$customer_info_res);
    }

    /**
     * 添加客户意向产品
     * @param company_id 商户company_id
     * @param product_name 产品名称
     * @param product_id 产品id （选传存在则更新）
	 * @return code 200->成功
	 */
    public function addProduct($data){
        $company_id = $data['company_id'];
        $product_name = $data['product_name'];
        $product_id = empty($data['product_id']) == true ? '' : $data['product_id'];

        if($product_id){
            $product_res = Db::name('product')->where(['company_id'=>$company_id,'product_id'=>$product_id])->find();
            if(!$product_res){
                return msg(3001,'产品不存在');
            }

            $update_res = Db::name('product')->where(['product_id'=>$product_id])->update(['product_name'=>$product_name]);
            if($update_res !== false){
                return msg(200,'success',['product_id'=>$product_id]);
            }else{
                return msg(3002,'更新数据失败');
            }
        }

        $product_id = Db::name('product')->insertGetId([
            'product_name' => $product_name,
            'company_id' => $company_id,
        ]);

        if($product_id){
            return msg(200,'success',['product_id'=>$product_id]);
        }else{
            return msg(3003,'插入数据失败');
        }
    }

    /**
     * 删除客户意向产品
     * @param company_id 商户company_id
     * @param product_id 产品id
	 * @return code 200->成功
	 */
    public function delProduct($data){
        $company_id = $data['company_id'];
        $product_id = $data['product_id'];

        $update_res = Db::name('product')
        ->where(['company_id'=>$company_id,'product_id'=>$product_id])
        ->update(['is_del'=>1]);

        if($update_res !== false){
            return msg(200,'success');
        }else{
            return msg(3001,'更新数据失败');
        }
    }

    /**
     * 模糊搜索客户意向产品List
     * @param company_id 商户company_id
     * @param product_name 模糊搜索名称
	 * @return code 200->成功
	 */
    public function searchProduct($data){
        $company_id = $data['company_id'];
        $product_name = empty($data['product_name']) == true ? '' : $data['product_name'];

        if(!$product_name){
            return msg(200,'success',[]);
        }

        $list = Db::name('product')->where(['company_id'=>$company_id,'is_del'=>-1,'product_name'=>['like',"%$product_name%"]])->cache(true,3)->select();
    
        return msg(200,'success',$list);
    }

    /**
     * 获取意向产品list
     * @param company_id 商户company_id
     * @param page 分页参数默认1
	 * @return code 200->成功
	 */
    public function getProductList($data){
        $company_id = $data['company_id'];
        $page = $data['page'];

        //分页
        $page_count = 16;
        $show_page = ($page - 1) * $page_count;

        $list = Db::name('product')
        ->limit($show_page,$page_count)
        ->where(['company_id'=>$company_id,'is_del'=>-1])
        ->select();

        $count = Db::name('product')
        ->where(['company_id'=>$company_id,'is_del'=>-1])
        ->count();

        $res['data_list'] = count($list) == 0 ? array() : $list;
        $res['page_data']['count'] = $count;
        $res['page_data']['rows_num'] = $page_count;
        $res['page_data']['page'] = $page;
        
        return msg(200,'success',$res);
    }
}