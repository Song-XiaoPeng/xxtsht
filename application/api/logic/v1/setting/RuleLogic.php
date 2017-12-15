<?php
namespace app\api\logic\v1\setting;
use think\Model;
use think\Db;
use app\api\common\Common;
use EasyWeChat\Foundation\Application;

class RuleLogic extends Model {
    /**
     * 设置客资领取规则
     * @param company_id 商户company_id
     * @param cued_pool 线索池领取周期 {"cycle":"1","number":"1"}
     * @param cued_pool_recovery 线索池回收周期 单位天
     * @param intention_receive 意向领取周期 {"cycle":"1","number":"1"}
     * @param intention_recovery 意向回收周期 单位天
	 * @return code 200->成功
	 */
    public function setCustomerResourcesRule($data){
        $company_id = $data['company_id'];
        $cued_pool = $data['cued_pool'];
        $cued_pool_recovery = $data['cued_pool_recovery'];
        $intention_receive = $data['intention_receive'];
        $intention_recovery = $data['intention_recovery'];

        $configure_value = [
            'cued_pool' => $cued_pool,
            'cued_pool_recovery' => $cued_pool_recovery,
            'intention_receive' => $intention_receive,
            'intention_recovery' => $intention_recovery
        ];

        $company_baseinfo_id = Db::name('company_baseinfo')->where(['company_id'=>$company_id,'configure_key'=>'sustomer_resources_rule'])->value('company_baseinfo_id');
        if($company_baseinfo_id){
            $insert_res = Db::name('company_baseinfo')
            ->where(['company_id'=>$company_id,'company_baseinfo_id'=>$company_baseinfo_id])
            ->update([
                'configure_key' => 'sustomer_resources_rule',
                'configure_value' => json_encode($configure_value)
            ]);
        }else{
            $insert_res = Db::name('company_baseinfo')->insert([
                'company_id' => $company_id,
                'configure_key' => 'sustomer_resources_rule',
                'configure_value' => json_encode($configure_value)
            ]);
        }

        if($insert_res){
            return msg(200,'success');
        }else{
            return msg(3001,'插入数据失败');
        }
    }

    /**
     * 设置商户公共快捷回复语句
     * @param quick_reply_id 存在则是编辑
	 * @param text 快捷回复语句
	 * @return code 200->成功
	 */
    public function setCommonQuickReplyText($data){
        $quick_reply_id = empty($data['quick_reply_id']) == true ? false : $data['quick_reply_id'];
        $text = $data['text'];
        $company_id = $data['company_id'];

        if($quick_reply_id){
            $update_res = Db::name('quick_reply')
            ->where([
                'quick_reply_id' => $quick_reply_id,
                'company_id' => $company_id,
                'type' => 2
            ])
            ->update([
                'quick_reply_text' => $text
            ]);

            if($update_res !== false){
                return msg(200,'success');
            }else{
                return msg(3002,'更新数据失败');
            }
        }

        $quick_reply_id = Db::name('quick_reply')
        ->insertGetId([
            'quick_reply_text' => $text,
            'company_id' => $company_id,
            'type' => 2
        ]);

        if($quick_reply_id){
            return msg(200,'success',['quick_reply_id'=>$quick_reply_id]);
        }else{
            return msg(3001,'更新数据失败');
        }
    }

    /**
     * 同步所有公众号标签
     * @param company_id 商户company_id
	 * @return code 200->成功
	 */
    public function syncWxLabel($company_id){
        $wx_list = Db::name('openweixin_authinfo')->where(['company_id'=>$company_id])->select();
    

        foreach($wx_list as $value){
            try{
                $token_info = Common::getRefreshToken($value['appid'],$company_id);
                if($token_info['meta']['code'] == 200){
                    $refresh_token = $token_info['body']['refresh_token'];
                }else{
                    return $token_info;
                }

                $app = new Application(wxOptions());
                $openPlatform = $app->open_platform;
                $tag = $openPlatform->createAuthorizerApplication($value['appid'],$refresh_token)->user_tag;
                $tag_list = $tag->lists()->tags;
            }catch (\Exception $e) {
                continue;
            }

            foreach($tag_list as $v){
                $label_id = Db::name('label_tag')->where(['company_id'=>$company_id,'tag_id'=>$v['id'],'appid'=>$value['appid']])->value('label_id');
                if($label_id){
                    Db::name('label')->where(['label_id'=>$label_id])->update(['label_name'=>$v['name']]);
                    continue;
                }

                $label_id = Db::name('label')->insertGetId([
                    'company_id' => $company_id,
                    'label_name' => $v['name']
                ]);

                Db::name('label_tag')->insert([
                    'company_id' => $company_id,
                    'label_id' => $label_id,
                    'appid' => $value['appid'],
                    'tag_id' => $v['id'],
                ]);
            }
        }
    
        return msg(200,'success');
    }
}