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
        $real_phone = empty($data['real_phone']) == true ? '' : $data['real_phone'];
        $contact_address = empty($data['contact_address']) == true ? '' : $data['contact_address'];
        $wx_company_id = empty($data['wx_company_id']) == true ? -1 : $data['wx_company_id'];
        $desc = empty($data['desc']) == true ? -1 : $data['desc'];
        $wx_user_group_id = empty($data['wx_user_group_id']) == true ? -1 : $data['wx_user_group_id'];
        $birthday = empty($data['birthday']) == true ? -1 : $data['birthday'];
        $wx_number = empty($data['wx_number']) == true ? -1 : $data['wx_number'];
        $email = empty($data['email']) == true ? -1 : $data['email'];
        $tel = empty($data['tel']) == true ? -1 : $data['tel'];
        $product_id = empty($data['product_id']) == true ? -1 : $data['product_id'];

        $wx_user_res = Db::name('wx_user')
        ->partition('', '', ['type'=>'md5','num'=>config('separate')['wx_user']])
        ->where(['appid'=>$appid,'openid'=>$openid])
        ->find();
    
        if(!$wx_user_res){
            return msg(3001,'客户微信基础信息不存在或未同步');
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

        $db_operation_res = Db::name('wx_user')
        ->partition(['company_id'=>$company_id], 'company_id', ['type'=>'md5','num'=>config('separate')['wx_user']])
        ->where(['appid'=>$appid,'openid'=>$openid])
        ->update(['customer_info_id'=>$customer_info_id]);

        if($db_operation_res !== false){
            return msg(200,'success',['customer_info_id'=>$customer_info_id]);
        }else{
            return msg(3002,'数据操作失败');
        }
    }

    /**
     * 客户管理修改添加客户信息
     * @param company_id 商户company_id
     * @param customer_type 客户类型 0其他 1意向客户 2订单客户 3追销客户
     * @param uid 客服账号uid
	 * @param real_name 客户真实姓名
	 * @param real_sex 客户真实性别 0未知 1男 2女
	 * @param real_phone 客户真实联系手机
	 * @param contact_address 客户联系地址
	 * @param wx_company_id 所属公司
	 * @param wx_user_group_id 所属用户分组id
	 * @param desc 备注
	 * @param product_id 意向产品id
	 * @param customer_info_id 客户信息id (更新时选传)
	 * @return code 200->成功
	 */
    public function crmUpdate($data){
        $company_id = $data['company_id'];
        $uid = $data['uid'];
        $real_name = $data['real_name'];
        $customer_type = $data['customer_type'];
        $real_sex = $data['real_sex'] == '' ? 0 : $data['real_sex'];
        $customer_info_id = empty($data['customer_info_id']) == true ? '' : $data['customer_info_id'];
        $real_phone = empty($data['real_phone']) == true ? '' : $data['real_phone'];
        $contact_address = empty($data['contact_address']) == true ? '' : $data['contact_address'];
        $wx_company_id = empty($data['wx_company_id']) == true ? -1 : $data['wx_company_id'];
        $desc = empty($data['desc']) == true ? -1 : $data['desc'];
        $wx_user_group_id = empty($data['wx_user_group_id']) == true ? -1 : $data['wx_user_group_id'];
        $birthday = empty($data['birthday']) == true ? -1 : $data['birthday'];
        $wx_number = empty($data['wx_number']) == true ? -1 : $data['wx_number'];
        $email = empty($data['email']) == true ? -1 : $data['email'];
        $tel = empty($data['tel']) == true ? -1 : $data['tel'];
        $product_id = empty($data['product_id']) == true ? -1 : $data['product_id'];

        if(!empty($customer_info_id)){
            $customer_info_res = Db::name('customer_info')
            ->where(['customer_info_id'=>$customer_info_id,'company_id'=>$company_id])
            ->find();
            if(!$customer_info_res){
                return msg(3005,'customer_info_id参数错误');
            }
        }

        if(empty($customer_info_res)){
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

        if($db_operation_res !== false){
            return msg(200,'success',['customer_info_id'=>$customer_info_id]);
        }else{
            return msg(3002,'数据操作失败');
        }
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
            return msg(3001,'暂无客户信息');
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
	 * @return code 200->成功
	 */
    public function getClueCustomer($data){
        $company_id = $data['company_id'];
        $real_name = $data['real_name'];
        $page = $data['page'];

        //分页
        $page_count = 16;
        $show_page = ($page - 1) * $page_count;

        if($real_name){
            $map['real_name'] = array('like',"%$real_name%");
        }

        $map['company_id'] = $company_id;
        $map['customer_info_id'] = ['not in',[-1]];
        
        $wx_user_list = Db::name('wx_user')
        ->partition([], "", ['type'=>'md5','num'=>config('separate')['wx_user']])
        ->where($map)
        ->limit($show_page,$page_count)
        ->order('wx_user_id desc')
        ->select();

        $count = Db::name('wx_user')
        ->partition([], "", ['type'=>'md5','num'=>config('separate')['wx_user']])
        ->where($map)
        ->count();
    
        foreach($wx_user_list as $k=>$v){
            $wx_user_list[$k]['app_name'] = Db::name('openweixin_authinfo')->where(['appid'=>$v['appid']])->value('nick_name');

            if($v['qrcode_id']){
                $wx_user_list[$k]['source_qrcode_name'] = Db::name('activity_name')->where(['qrcode_id'=>$qrcode_id])->cache(true,60)->value('activity_name');
            }else{
                $wx_user_list[$k]['source_qrcode_name'] = '暂无来源二维码';
            }
        }

        $res['data_list'] = count($wx_user_list) == 0 ? array() : $wx_user_list;
        $res['page_data']['count'] = $count;
        $res['page_data']['rows_num'] = $page_count;
        $res['page_data']['page'] = $page;
        
        return msg(200,'success',$res);
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