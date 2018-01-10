<?php
namespace app\api\logic\v1\extension;
use think\Model;
use think\Db;
use EasyWeChat\Foundation\Application;
use app\api\common\Common;
use Endroid\QrCode\QrCode;

class ExtensionLogic extends Model {
    /**
     * 创建或编辑推广二维码
     * @param appid 公众号appid (编辑无法修改)
     * @param company_id 商户company_id
     * @param qrcode_id 二维码id (修改时传入)
     * @param type 二维码类型 1永久二维码 2临时二维码 (编辑无法修改)
     * @param uid 创建人uid
     * @param activity_name 活动名称或渠道名称
     * @param qrcode_group_id 二维码分组id 活动分组id 或渠道分组id
     * @param invalid_day 有效天数 单位 日 临时二维码 (编辑无法修改)
     * @param label 关注自动打标签
     * @param customer_service_id 关注的用户专属客服id
     * @param customer_service_group_id 关注的用户专属客服分组id
     * @param reception_type 接待类型 1指定客服 2指定客服分组 3不指定
     * @param reply_type 自动回复类型 -1不回复 1文本内容 2图片 3微信图文信息
     * @param media_id 回复微信媒体id
     * @param resources_id 回复资源id
     * @param reply_text 回复文本内容
     * @return code 200->成功
	 */
    public function createQrcode($data){
        $appid = empty($data['appid']) == true ? '' : $data['appid'];
        $type = empty($data['type']) == true ? '' : $data['type'];
        $company_id = $data['company_id'];
        $uid = $data['uid'];
        $activity_name = $data['activity_name'];
        $reply_type = $data['reply_type'];
        $invalid_time = empty($data['invalid_time']) == true ? '' : $data['invalid_time'];
        $media_id = empty($data['media_id']) == true ? '' : $data['media_id'];
        $resources_id = empty($data['resources_id']) == true ? '' : $data['resources_id'];
        $reply_text = empty($data['reply_text']) == true ? '' : $data['reply_text'];
        $reception_type = $data['reception_type'];
        $qrcode_group_id = $data['qrcode_group_id'];
        $qrcode_id = empty($data['qrcode_id']) == true ? '' : $data['qrcode_id'];
        $invalid_day = empty($data['invalid_day']) == true ? '' : $data['invalid_day'];
        $label = empty($data['label']) == true ? '' : $data['label'];
        $customer_service_id = empty($data['customer_service_id']) == true ? '' : $data['customer_service_id'];
        $customer_service_group_id = empty($data['customer_service_group_id']) == true ? '' : $data['customer_service_group_id'];

        if(empty($customer_service_id) == false && empty($customer_service_group_id) == false){
            return msg(3004,'专属客服与专属客服分组只能选取一个选项');
        }

        if(!empty($customer_service_group_id)){
            $group_res = Db::name('extension_qrcode_group')->where(['company_id'=>$company_id,'qrcode_group_id'=>$qrcode_group_id,'del'=>-1])->find();
            if(empty($group_res)){
                return msg(3002,'分组不存在');
            }
        }

        if(empty($customer_service_id) == false && $reception_type == 1){
            $customer_service_res = Db::name('customer_service')->where(['appid'=>$appid,'company_id'=>$company_id,'customer_service_id'=>$customer_service_id])->find();
            if(empty($customer_service_res)){
                return msg(3003,'客服不存在');
            }
        }

        if($label){
            foreach($label as $label_id){
                $label_res = Db::name('label')->where(['company_id'=>$company_id,'label_id'=>$label_id])->find();
                if(!$label_res){
                    return msg(3009,'标签不存在');
                }
            }
        }

        if($qrcode_id){
            $update_res = Db::name('extension_qrcode')
            ->where(['qrcode_id'=>$qrcode_id,'company_id'=>$company_id])
            ->update([
                'invalid_time' => $invalid_time,
                'activity_name' => $activity_name,
                'label' => json_encode($label),
                'customer_service_id' => $customer_service_id,
                'reception_type' => $reception_type,
                'customer_service_group_id' => $customer_service_group_id,
                'qrcode_group_id' => $qrcode_group_id,
                'reply_type' => $reply_type,
                'reply_text' => $reply_text,
                'resources_id' => $resources_id,
                'media_id' => $media_id,
                'invalid_day' => $invalid_day
            ]);

            if($update_res !== false){
                return msg(200,'success');
            }else{
                return msg(3001,'更新数据失败');
            }
        }

        try{
            $qrcode_id = md5(uniqid());

            $token_info = Common::getRefreshToken($appid,$company_id);
            if($token_info['meta']['code'] == 200){
                $refresh_token = $token_info['body']['refresh_token'];
            }else{
                return $token_info;
            }
    
            $app = new Application(wxOptions());
            $openPlatform = $app->open_platform;
            $qrcode = $openPlatform->createAuthorizerApplication($appid,$refresh_token)->qrcode;
    
            $qrcode_data = 'qrscene_'.$qrcode_id;

            if($type == 1){
                $qrcode_result = $qrcode->forever($qrcode_data);
                $invalid_time = '';
            }else if($type == 2){
                $qrcode_result = $qrcode->temporary($qrcode_data, $invalid_day * 24 * 3600);
                $invalid_time = date('Y-m-d H:i:s',strtotime("+$invalid_day day"));
            }

            $ticket = $qrcode_result->ticket;
            $qrcode_url = $qrcode->url($ticket);

            Db::name('extension_qrcode')->insert([
                'qrcode_id' => $qrcode_id,
                'company_id' => $company_id,
                'appid' => $appid,
                'type' => $type,
                'invalid_time' => $invalid_time,
                'activity_name' => $activity_name,
                'create_time' => date('Y-m-d H:i:s'),
                'qrcode_url' => $qrcode_url,
                'label' => json_encode($label),
                'create_uid' => $uid,
                'customer_service_id' => $customer_service_id,
                'reception_type' => $reception_type,
                'customer_service_group_id' => $customer_service_group_id,
                'qrcode_group_id' => $qrcode_group_id,
                'reply_type' => $reply_type,
                'reply_text' => $reply_text,
                'resources_id' => $resources_id,
                'media_id' => $media_id,
                'invalid_day' => $invalid_day
            ]);

            return msg(200,'success',['qrcode_id'=>$qrcode_id,'qrcode_url'=>$qrcode_url]);
        }catch (\Exception $e) {
            return msg(3001,$e->getMessage());
        }
    }

    /**
     * 创建推广二维码分组
	 * @param company_id 商户company_id
	 * @param uid 登录用户uid
	 * @param qrcode_group_name 分组名称
	 * @param group_type 分组类型 1渠道 2限时推广
	 * @return code 200->成功
	 */
    public function addQrcodeGroup($data){
        $company_id = $data['company_id'];
        $uid = $data['uid'];
        $qrcode_group_name = $data['qrcode_group_name'];
        $group_type = $data['group_type'];

        $qrcode_group_id = Db::name('extension_qrcode_group')->insertGetId([
            'company_id' => $company_id,
            'uid' => $uid,
            'qrcode_group_name' => $qrcode_group_name,
            'group_type' => $group_type
        ]);

        if($qrcode_group_id){
            return msg(200,'success',['qrcode_group_id'=>$qrcode_group_id]);
        }else{
            return msg(200,'插入数据失败');
        }
    }

    /**
     * 删除推广二维码分组
	 * @param company_id 商户company_id
	 * @param qrcode_group_id 分组id
	 * @return code 200->成功
	 */
    public function delQrcodeGroup($data){
        $company_id = $data['company_id'];
        $qrcode_group_id = $data['qrcode_group_id'];
    
        $del_res = Db::name('extension_qrcode_group')->where([
            'qrcode_group_id' => $qrcode_group_id,
            'company_id' => $company_id,
        ])->update([
            'del' => 1
        ]);

        if($del_res !== false){
            return msg(200,'success');
        }else{
            return msg(200,'更新数据失败');
        }
    }

    /**
     * 编辑推广二维码分组名称
	 * @param company_id 商户company_id
	 * @param qrcode_group_id 分组id
	 * @param qrcode_group_name 分组名称
	 * @return code 200->成功
	 */
    public function editQrcodeGroupName($data){
        $company_id = $data['company_id'];
        $qrcode_group_id = $data['qrcode_group_id'];
        $qrcode_group_name = $data['qrcode_group_name'];
    
        $update_res = Db::name('extension_qrcode_group')->where([
            'qrcode_group_id' => $qrcode_group_id,
            'company_id' => $company_id,
        ])->update([
            'qrcode_group_name' => $qrcode_group_name
        ]);

        if($update_res !== false){
            return msg(200,'success');
        }else{
            return msg(200,'更新数据失败');
        }
    }

    /**
     * 获取推广二维码分组list
	 * @param company_id 商户company_id
	 * @param group_type 分组类型 1渠道 2限时推广
	 * @return code 200->成功
	 */
    public function getQrcodeGroupList($data){
        $company_id = $data['company_id'];
        $group_type = $data['group_type'];
    
        $list = Db::name('extension_qrcode_group')->where([
            'company_id' => $company_id,
            'group_type' => $group_type,
            'del' => -1,
        ])
        ->field('qrcode_group_id,qrcode_group_name')
        ->select();

        return msg(200,'success',$list);
    }

    /**
     * 获取推广二维码list
	 * @param company_id 商户company_id
	 * @param type 类型 1渠道 2限时推广
	 * @param page 分页参数默认1
	 * @return code 200->成功
	 */
    public function getQrcodList($data){
        $company_id = $data['company_id'];
        $type = $data['type'] == 1 ? 1:2;
        $page = $data['page'];
    
        //分页
        $page_count = 16;
        $show_page = ($page - 1) * $page_count;

        $map['company_id'] = $company_id;
        $map['type'] = $type;
        $map['is_del'] = -1;

        $list = Db::name('extension_qrcode')
        ->where($map)
        ->limit($show_page,$page_count)
        ->order('create_time desc')
        ->select();

        $count = Db::name('extension_qrcode')
        ->where($map)
        ->count();

        foreach($list as $k=>$v){
            $nick_name = Db::name('openweixin_authinfo')->where(['appid'=>$v['appid']])->cache(true,60)->value('nick_name');

            $list[$k]['nick_name'] = empty($nick_name) == true ? '公众号或小程序已解绑' : $nick_name;

            $list[$k]['qrcode_group_name'] = Db::name('extension_qrcode_group')->where(['qrcode_group_id'=>$v['qrcode_group_id']])->cache(true,60)->value('qrcode_group_name');

            $list[$k]['attention_num'] = Db::name('wx_user')
            ->partition([], "", ['type'=>'md5','num'=>config('separate')['wx_user']])
            ->where(['company_id'=>$company_id,'qrcode_id'=>$v['qrcode_id']])
            ->cache(true,360)
            ->count();

            $label = json_decode($v['label'],true);
            if($label){
                foreach($label as $index=>$label_id){
                    $label_name = Db::name('label')->where(['label_id'=>$label_id])->value('label_name');
    
                    $label_arr[$index]['label_id'] = $label_id;
                    $label_arr[$index]['label_name'] = $label_name;
                }
            }else{
                $label_arr = [];
            }

            $list[$k]['label'] = $label_arr;

            $user_info = Db::name('user')->where(['uid'=>$v['create_uid'],'company_id'=>$company_id])->cache(true,60)->find();
    
            $user_group_name = Db::name('user_group')->where(['company_id'=>$company_id,'user_group_id'=>$user_info['user_group_id']])->cache(true,60)->value('user_group_name');

            if($v['reply_type'] = 2 && empty($v['resources_id']) == false){
                $list[$k]['file_url'] = 'http://'.$_SERVER['HTTP_HOST'].'/api/v1/we_chat/Business/getImg?resources_id='.$v['resources_id'];
            }else{
                $list[$k]['file_url'] = '';
            }

            $list[$k]['create_user_name'] = empty($user_info['user_name']) == true ? '账号不存在' : $user_info['user_name'];
            $list[$k]['create_user_group_name'] = empty($user_group_name) == true ? '分组不存在' : $user_group_name;

            $customer_service_name = Db::name('customer_service')->where(['company_id'=>$company_id,'customer_service_id'=>$v['customer_service_id']])->value('name');
            $list[$k]['customer_service_name'] = empty($customer_service_name) == true ? '暂无' : $customer_service_name;

            //产生意向客户数量
            $list[$k]['intention_num'] = Db::name('wx_user')
            ->partition([], "", ['type'=>'md5','num'=>config('separate')['wx_user']])
            ->where(['company_id'=>$company_id,'qrcode_id'=>$v['qrcode_id'],'is_clue'=>array('in',[2,3])])
            ->cache(true,360)
            ->count();

            //产生订单客户数量
            $list[$k]['order_num'] = Db::name('wx_user')
            ->partition([], "", ['type'=>'md5','num'=>config('separate')['wx_user']])
            ->where(['company_id'=>$company_id,'qrcode_id'=>$v['qrcode_id'],'is_clue'=>4])
            ->cache(true,360)
            ->count();
        }

        $res['data_list'] = count($list) == 0 ? array() : $list;
        $res['page_data']['count'] = $count;
        $res['page_data']['rows_num'] = $page_count;
        $res['page_data']['page'] = $page;
        
        return msg(200,'success',$res);
    }

    /**
     * 删除推广二维码
	 * @param company_id 商户company_id
	 * @param uid 账号uid
	 * @param qrcode_id 删除的qrcodeid
	 * @return code 200->成功
	 */
    public function delQrcod($data){
        $company_id = $data['company_id'];
        $uid = $data['uid'];
        $qrcode_id = $data['qrcode_id'];

        $update_res = Db::name('extension_qrcode')->where(['qrcode_id'=>$qrcode_id,'company_id'=>$company_id,'create_uid'=>$uid])->update(['is_del'=>1]);

        if ($update_res !== false) {
            return msg(200,'success');
        } else {
            return msg(3001,'删除失败');
        }
    }

    /**
     * 添加编辑红包活动
	 * @param activity_name 红包活动名称
	 * @param company_id 商户id
	 * @param activity_id 活动id 存在则编辑
	 * @param number 红包数量
	 * @param amount 红包金额
	 * @param amount_start 随机金额开始
	 * @param amount_end 随机金额结束
	 * @param amount_type 派发金额方式1固定金额 2随机金额
	 * @param start_time 活动开始时间
	 * @param end_time 活动结束时间
	 * @param create_time 创建时间
	 * @param is_follow 是否强制关注 1是 -1否
	 * @param qrcode_id 强制关注二维码
	 * @param appid 运营公众号appid
	 * @param is_share 是否强制分享 1是 -1否
	 * @param share_url 分享链接
	 * @param share_cover 分享封面资源id
	 * @param amount_upper_limit 派发金额上限
	 * @param is_open 是否开启 1是 -1否
	 * @return code 200->成功
	 */
    public function addRedEnvelopes($data){
        $company_id = $data['company_id'];
        $activity_id = empty($data['activity_id']) == true ? '' : $data['activity_id'];
        $activity_name = $data['activity_name'];
        $number = $data['number'];
        $share_title = empty($data['share_title']) == true ? '' : $data['share_title'];
        $amount = empty($data['amount']) == true ? 0 : $data['amount'];
        $amount_start = empty($data['amount_start']) == true ? 0 : $data['amount_start'];
        $amount_end = empty($data['amount_end']) == true ? 0 : $data['amount_end'];
        $amount_type = $data['amount_type'];
        $start_time = $data['start_time'];
        $end_time = $data['end_time'];
        $create_time = date('Y-m-d H:i:s');
        $is_follow = $data['is_follow'];
        $appid = $data['appid'];
        $payment = empty($data['payment']) == true ? 3 : $data['payment'];
        $receive_count = empty($data['receive_count']) == true ? 1 : $data['receive_count'];
        $qrcode = empty($data['qrcode']) == true ? '' : $data['qrcode'];
        $is_share = $data['is_share'];
        $share_url = empty($data['share_url']) == true ? '' : $data['share_url'];
        $share_cover = empty($data['share_cover']) == true ? '' : $data['share_cover'];
        $amount_upper_limit = $data['amount_upper_limit'];
        $is_open = $data['is_open'];
        $details_list = $data['details_list'];

        if(empty($activity_name)){
            return msg(3015,'红包活动名称不能为空');
        }

        if(empty($amount_upper_limit)){
            return msg(3007,'金额上限不能为空');
        }

        if($amount_type == 1){
            if(empty($amount)){
                return msg(3006,'红包金额不能为空');
            }

            if($amount < 1){
                return msg(3008,'最小金额不能小于1元');
            }

            if($amount > 200){
                return msg(3009,'最大金额不能大于200元');
            }
        }

        if($amount_type == 2){
            if($amount_start < 1){
                return msg(3010,'最小金额不能小于1元');
            }

            if($amount_start > 200){
                return msg(3011,'最大金额不能大于200元');
            }

            if($amount_end < 1){
                return msg(3010,'最小金额不能小于1元');
            }

            if($amount_end > 200){
                return msg(3011,'最大金额不能大于200元');
            }

            if($amount_start > $amount_end){
                return msg(3012,'金额范围错误');
            }
        }

        if($start_time == '0000-00-00 00:00:00' || $end_time == '0000-00-00 00:00:00'){
            return msg(3013, '活动开始时间与结束时间不能为空');
        }

        if($activity_id){
            $update_res = Db::name('red_envelopes')
            ->where(['company_id'=>$company_id,'activity_id'=>$activity_id])
            ->update([
                'activity_name' => $activity_name,
                'amount' => $amount,
                'amount_type' => $amount_type,
                'is_follow' => $is_follow,
                'is_share' => $is_share,
                'share_url' => $share_url,
                'start_time' => $start_time,
                'end_time' => $end_time,
                'amount_start' => $amount_start,
                'amount_end' => $amount_end,
                'payment' => $payment,
                'receive_count' => $receive_count,
                'qrcode' => $qrcode,
                'share_cover' => $share_cover,
                'amount_upper_limit' => $amount_upper_limit,
                'details_list' => json_encode($details_list),
                'is_open' => $is_open,
                'share_title' => $share_title
            ]);

            if($update_res !== false){
                return msg(200,'success',['activity_id'=>$activity_id]);
            }else{
                return msg(3001,'更新数据失败');
            }
        }else{
            if(empty($number)){
                return msg(3008,'红包数量不能为空');
            }    

            if($number > 100000){
                return msg(3009,'每活动不得超过10000个红包');
            }

            $activity_id = md5(uniqid());

            $add_res = Db::name('red_envelopes')->insert([
                'company_id' => $company_id,
                'activity_id' => $activity_id,
                'activity_name' => $activity_name,
                'number' => $number,
                'amount' => $amount,
                'amount_start' => $amount_start,
                'amount_end' => $amount_end,
                'amount_type' => $amount_type,
                'start_time' => $start_time,
                'end_time' => $end_time,
                'create_time' => $create_time,
                'is_follow' => $is_follow,
                'appid' => $appid,
                'is_share' => $is_share,
                'share_url' => $share_url,
                'share_cover' => $share_cover,
                'amount_upper_limit' => $amount_upper_limit,
                'details_list' => json_encode($details_list),
                'is_open' => $is_open,
                'share_title' => $share_title
            ]);

            for ($i = 0; $i < $number; $i++) {
                Db::name('red_envelopes_id')->insert([
                    'red_envelopes_id' => randCode(32),
                    'appid' => $appid,
                    'activity_id' => $activity_id,
                    'company_id' => $company_id
                ]);
            }

            if($add_res){
                return msg(200,'success',['activity_id'=>$activity_id]);
            }else{
                return msg(3001,'插入数据失败');
            }
        }
    }

    /**
     * 获取红包list
	 * @param company_id 商户id
	 * @param page 分页参数 默认1
	 * @return code 200->成功
	 */
    public function getRedEnvelopesList($company_id, $page){
        //分页
        $page_count = 16;
        $show_page = ($page - 1) * $page_count;

        $list = Db::name('red_envelopes')->where(['company_id'=>$company_id])->limit($show_page,$page_count)->order('create_time desc')->select();
        $count = Db::name('red_envelopes')->where(['company_id'=>$company_id])->count();

        foreach($list as $k=>$v){
            $list[$k]['app_name'] = Db::name('openweixin_authinfo')->where(['company_id'=>$company_id,'appid'=>$v['appid']])->cache(true,60)->value('nick_name');

            $details_list = json_decode($v['details_list']);

            if($details_list){
                foreach($details_list as $i=>$resources_id){
                    $list[$k]['details_url_list'][$i]['resources_id'] = $resources_id;
                    $list[$k]['details_url_list'][$i]['url'] = 'http://'.$_SERVER['HTTP_HOST'].'/api/v1/we_chat/Business/getImg?resources_id='.$resources_id;
                }
            }

            $list[$k]['details_list'] = json_decode($v['details_list']);

            if($v['share_cover']){
                $list[$k]['share_cover_url'] = 'http://'.$_SERVER['HTTP_HOST'].'/api/v1/we_chat/Business/getImg?resources_id='.$v['share_cover'];
            }else{
                $list[$k]['share_cover_url'] = null;
            }

            if($v['is_open'] == 1){
                $list[$k]['state'] = '开启';
            }else{
                $list[$k]['state'] = '关闭';
            }
        }

        $res['data_list'] = count($list) == 0 ? array() : $list;
        $res['page_data']['count'] = $count;
        $res['page_data']['rows_num'] = $page_count;
        $res['page_data']['page'] = $page;
        
        return msg(200,'success',$res);
    }

    /**
     * 删除红包活动
	 * @param activity_id 活动id
	 * @param company_id 商户id
	 * @return code 200->成功
	 */
    public function delRedEnvelopes($company_id, $activity_id){
        Db::name('red_envelopes')
        ->where(['company_id'=>$company_id,'activity_id'=>$activity_id])
        ->delete();

        Db::name('red_envelopes_id')
        ->where(['company_id'=>$company_id,'activity_id'=>$activity_id])
        ->delete();

        return msg(200,'success');
    }

    /**
     * 查看红包二维码列表
	 * @param activity_id 活动id
	 * @param company_id 商户id
	 * @return code 200->成功
	 */
    public function getRedEnvelopeList($company_id, $activity_id, $page, $token){
        //分页
        $page_count = 6;
        $show_page = ($page - 1) * $page_count;

        $list = Db::name('red_envelopes_id')->where(['company_id'=>$company_id,'activity_id'=>$activity_id,'is_receive'=>1])->limit($show_page,$page_count)->select();
        $count = Db::name('red_envelopes_id')->where(['company_id'=>$company_id,'activity_id'=>$activity_id,'is_receive'=>1])->count();

        foreach($list as $k=>$v){
            if($v['is_receive'] == 1){
                $list[$k]['is_receive'] = '是';
            }else{
                $list[$k]['is_receive'] = '否';
            }

            $code = base64_encode(json_encode(['red_envelopes_id'=>$v['red_envelopes_id'],'activity_id'=>$activity_id]));

            $list[$k]['qrcode_url'] = 'http://'.$_SERVER['HTTP_HOST']."/api/v1/common/QrCode/getQrCode?code=$code&token=$token";
        }

        $res['data_list'] = count($list) == 0 ? array() : $list;
        $res['page_data']['count'] = $count;
        $res['page_data']['rows_num'] = $page_count;
        $res['page_data']['page'] = $page;
        
        return msg(200,'success',$res);
    }

    /**
     * 批量生成二维码
	 * @param activity_id 活动id
	 * @param company_id 商户id
	 * @return code 200->成功
	 */
    public function createQrcodeZip($company_id, $activity_id, $token){
        $list = Db::name('red_envelopes_id')->where(['company_id'=>$company_id,'activity_id'=>$activity_id])->select();

        $catalog_name = md5(uniqid());
        $save_catalog = "../uploads/qrcode/$catalog_name";
        if(!file_exists($save_catalog)){
            mkdir($save_catalog, 0766);
            chmod($save_catalog, 0766);
        }

        foreach($list as $k=>$v){
            $code = base64_encode(json_encode(['red_envelopes_id'=>$v['red_envelopes_id'],'activity_id'=>$activity_id]));

            $save_file = $save_catalog.'/'.$k.'.png';

            $qrcode_value = 'http://'.$_SERVER['HTTP_HOST'].'/home/Redenvelopes?code='.$code;

            $qrCode = new QrCode($qrcode_value);
            $qrCode->writeFile($save_file);
        }

        $zipFile = new \PhpZip\ZipFile();
        $zipFile
            ->addDir($save_catalog)
            ->saveAsFile($save_catalog.".zip")
            ->close();

        $PSize = filesize($save_catalog.".zip");
        $picture_data = fread(fopen($save_catalog.".zip", "r"), $PSize);

        unlink($save_catalog.".zip");

        Db::name('qrcode_del_list')->insert(['path'=>$save_catalog]);

        return response($picture_data)->contentType('application/x-zip-compressed');
    }

    /**
     * 批量生成二维码文本内容
	 * @param activity_id 活动id
	 * @param company_id 商户id
	 * @return code 200->成功
	 */
    public function generateRedEnvelopes($company_id, $activity_id){
        $list = Db::name('red_envelopes_id')->where(['company_id'=>$company_id,'activity_id'=>$activity_id])->select();

        $arr = [];

        foreach($list as $k=>$v){
            $code = base64_encode(json_encode(['red_envelopes_id'=>$v['red_envelopes_id'],'activity_id'=>$activity_id]));

            $arr[$k]['url'] = 'http://'.$_SERVER['HTTP_HOST'].'/home/Redenvelopes?code='.$code;
        }

        exportCsv($arr,[],date('YmdHis').'.csv');
    }
}