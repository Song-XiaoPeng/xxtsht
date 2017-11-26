<?php
namespace app\api\logic\v1\extension;
use think\Model;
use think\Db;
use EasyWeChat\Foundation\Application;
use app\api\common\Common;

class ExtensionModel extends Model {
    /**
     * 创建推广二维码
     * @param appid 公众号appid
     * @param company_id 商户company_id
     * @param type 二维码类型 1永久二维码 2临时二维码
     * @param uid 创建人uid
     * @param activity_name 活动名称或渠道名称
     * @param qrcode_group_id 二维码分组id 活动分组id 或渠道分组id
     * @param invalid_day 有效天数 单位 日 临时二维码
     * @param label 关注自动打标签
     * @param customer_service_id 关注的用户专属客服id
     * @param customer_service_group_id 关注的用户专属客服分组id
     * @return code 200->成功
	 */
    public function createQrcode($data){
        $appid = $data['appid'];
        $type = $data['type'] == 1 ? 1:2;
        $company_id = $data['company_id'];
        $uid = $data['uid'];
        $activity_name = $data['activity_name'];
        $qrcode_group_id = $data['qrcode_group_id'];
        $invalid_day = empty($data['invalid_day']) == true ? '' : $data['invalid_day'];
        $label = empty($data['label']) == true ? '' : $data['label'];
        $customer_service_id = empty($data['customer_service_id']) == true ? '' : $data['customer_service_id'];
        $customer_service_group_id = empty($data['customer_service_group_id']) == true ? '' : $data['customer_service_group_id'];

        $group_res = Db::name('extension_qrcode_group')->where(['company_id'=>$company_id,'qrcode_group_id'=>$qrcode_group_id,'del'=>-1])->find();
        if(empty($group_res)){
            return msg(3002,'分组不存在');
        }

        $customer_service_res = Db::name('customer_service')->where(['appid'=>$appid,'company_id'=>$company_id,'customer_service_id'=>$customer_service_id])->find();
        if(empty($customer_service_res)){
            return msg(3003,'客服不存在');
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
    
            $qrcode_data = json_encode(['extension_qrcode_id'=>$qrcode_id]);

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
                'customer_service_group_id' => $customer_service_group_id,
                'qrcode_group_id' => $qrcode_group_id
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
}