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
     * 设置公众号标签
     * @param company_id 商户company_id
     * @param label_group_id 标签分组id
	 * @param label_name 标签名称
	 * @return code 200->成功
	 */
    public function setLabel($data){
        $company_id = $data['company_id'];
        $label_name = $data['label_name'];
        $label_group_id = empty($data['label_group_id']) == true ? -1 : $data['label_group_id'];

        if($label_group_id != -1){
            $label_group_res = Db::name('label_group')->where(['company_id'=>$company_id,'label_group_id'=>$label_group_id])->find();
            if(!$label_group_res){
                return msg(3001,'label_group_id参数错误');
            }
        }

        $label_res = Db::name('label')->where(['company_id'=>$company_id,'label_name'=>$label_name])->find();
        if($label_res){
            return msg(3002,'标签名称已存在');
        }

        $wx_list = Db::name('openweixin_authinfo')->where(['company_id'=>$company_id])->select();
        foreach($wx_list as $value){
            try{
                $token_info = Common::getRefreshToken($value['appid'],$company_id);
                if($token_info['meta']['code'] == 200){
                    $refresh_token = $token_info['body']['refresh_token'];
                }else{
                    continue;
                }

                $app = new Application(wxOptions());
                $openPlatform = $app->open_platform;
                $tag = $openPlatform->createAuthorizerApplication($value['appid'],$refresh_token)->user_tag;

                $tag_id = $tag->create($label_name)['tag']['id'];

                $label_id = Db::name('label')->where(['company_id'=>$company_id,'label_name'=>$label_name])->value('label_id');
                if(!$label_id){
                    $label_id = Db::name('label')->insertGetId([
                        'label_name' => $label_name,
                        'company_id' => $company_id,
                        'label_group_id' => $label_group_id
                    ]);
                }

                Db::name('label_tag')->insert([
                    'tag_id' => $tag_id,
                    'label_id' => $label_id,
                    'appid' => $value['appid'],
                    'company_id' => $company_id
                ]);
            }catch (\Exception $e) {
                continue;
            }
        }

        return msg(200,'success');
    }

    /**
     * 获取标签列表
     * @param company_id 商户company_id
	 * @return code 200->成功
	 */
    public function getLabelList($company_id){
        $label_res = Db::name('label')->where(['company_id'=>$company_id])->select();
        
        foreach($label_res as $key=>$value){
            $label_res[$key]['group_name'] = Db::name('label_group')->where(['company_id'=>$company_id,'label_group_id'=>$value['label_group_id']])->value('group_name');
        }

        return msg(200,'success',$label_res);
    }

     /**
     * 获取所有标签分组
     * @param company_id 商户company_id
	 * @return code 200->成功
	 */
    public function getLabelGroup($company_id){
        $label_res = Db::name('label_group')->where(['company_id'=>$company_id])->select();
        
        return msg(200,'success',$label_res);
    }

    /**
     * 修改标签
     * @param company_id 商户company_id
     * @param label_id 标签id
     * @param label_group_id 标签分组id
	 * @param label_name 标签名称
	 * @return code 200->成功
	 */
    public function updateLabel($data){
        $company_id = $data['company_id'];
        $label_id = $data['label_id'];
        $label_group_id = empty($data['label_group_id']) == true ? -1 : $data['label_group_id'];
        $label_name = $data['label_name'];

        if($label_group_id != -1){
            $label_group_res = Db::name('label_group')->where(['company_id'=>$company_id,'label_group_id'=>$label_group_id])->find();
            if(!$label_group_res){
                return msg(3001,'label_group_id参数错误');
            }
        }

        $label_tag_list = Db::name('label_tag')->where(['label_id'=>$label_id,'company_id'=>$company_id])->select();

        $wx_list = Db::name('openweixin_authinfo')->where(['company_id'=>$company_id])->select();
        foreach($wx_list as $value){
            try{
                $token_info = Common::getRefreshToken($value['appid'],$company_id);
                if($token_info['meta']['code'] == 200){
                    $refresh_token = $token_info['body']['refresh_token'];
                }else{
                    continue;
                }

                $app = new Application(wxOptions());
                $openPlatform = $app->open_platform;
                $tag = $openPlatform->createAuthorizerApplication($value['appid'],$refresh_token)->user_tag;

                foreach($label_tag_list as $index){
                    $tag->update($index['tag_id'], $label_name);
                }
            }catch (\Exception $e) {
                continue;
            }
        }

        Db::name('label')->where(['company_id'=>$company_id,'label_id'=>$label_id])->update(['label_name'=>$label_name,'label_group_id'=>$label_group_id]);

        return msg(200,'success');
    }

    /**
     * 删除标签
     * @param company_id 商户company_id
     * @param label_id 标签id
	 * @return code 200->成功
	 */
    public function delLabel($data){
        $company_id = $data['company_id'];
        $label_id = $data['label_id'];
 
        $label_res = Db::name('label')->where(['label_id'=>$label_id,'company_id'=>$company_id])->find();
        if(!$label_res){
            return msg(3001,'标签不存在');
        }

        $label_tag_list = Db::name('label_tag')->where(['label_id'=>$label_id,'company_id'=>$company_id])->select();
        foreach($label_tag_list as $value){
            try{
                $token_info = Common::getRefreshToken($value['appid'],$company_id);
                if($token_info['meta']['code'] == 200){
                    $refresh_token = $token_info['body']['refresh_token'];
                }else{
                    continue;
                }

                $app = new Application(wxOptions());
                $openPlatform = $app->open_platform;
                $tag = $openPlatform->createAuthorizerApplication($value['appid'],$refresh_token)->user_tag;

                $tag->delete($value['tag_id']);
            }catch (\Exception $e) {
                continue;
            }

            Db::name('label_tag')->where(['label_tag_id'=>$value['label_tag_id'],'company_id'=>$company_id])->delete();
        }

        Db::name('label')->where(['label_id'=>$label_id,'company_id'=>$company_id])->delete();

        return msg(200,'success');
    }

    /**
     * 添加修改标签分组
     * @param company_id 商户company_id
     * @param label_group_id 标签分组id(修改选传)
	 * @param group_name 标签分组名称
	 * @return code 200->成功
	 */
    public function updateLabelGroup($data){
        $company_id = $data['company_id'];
        $label_group_id = empty($data['label_group_id']) == true ? null : $data['label_group_id'];
        $group_name = $data['group_name'];
        
        if($label_group_id){
            $update_res = Db::name('label_group')->where(['company_id'=>$company_id,'label_group_id'=>$label_group_id])->update(['group_name'=>$group_name]);
            if($update_res !== false){
                return msg(200,'success',['label_group_id'=>$label_group_id]);
            }else{
                return msg(3001,'更新数据失败');
            }
        }

        $add_res = Db::name('label_group')->insert([
            'company_id' => $company_id,
            'group_name' => $group_name
        ]);

        if($add_res){
            return msg(200,'success');
        }else{
            return msg(3001,'插入数据失败');
        }
    }

    /**
     * 添加修改标签分组
     * @param company_id 商户company_id
     * @param label_group_id 标签分组id
	 * @return code 200->成功
	 */
    public function delLabelGroup($data){
        $company_id = $data['company_id'];
        $label_group_id = $data['label_group_id'];

        $del_res = Db::name('label_group')->where(['label_group_id'=>$label_group_id,'company_id'=>$company_id])->delete();
        if($del_res){
            return msg(200,'success');
        }else{
            return msg(3001,'删除失败');
        }
    }

    /**
     * 会话规则设置
     * @param company_id 商户company_id
     * @param rule_type 无所属咨询分配规则 1平均分配 2抢单模式
     * @param overtime 超时会话 多少分钟未回复客户自动关闭
	 * @return code 200->成功
	 */
    public function setSessionRule($data){
        $company_id = $data['company_id'];
        $rule_type = $data['rule_type'];
        $overtime = $data['overtime'];

        $configure_value = [
            'rule_type' => $rule_type,
            'overtime' => $overtime
        ];

        $company_baseinfo_id = Db::name('company_baseinfo')->where(['company_id'=>$company_id,'configure_key'=>'session_rule'])->value('company_baseinfo_id');
        if($company_baseinfo_id){
            $insert_res = Db::name('company_baseinfo')
            ->where(['company_id'=>$company_id,'company_baseinfo_id'=>$company_baseinfo_id])
            ->update([
                'configure_key' => 'session_rule',
                'configure_value' => json_encode($configure_value)
            ]);
        }else{
            $insert_res = Db::name('company_baseinfo')->insert([
                'company_id' => $company_id,
                'configure_key' => 'session_rule',
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
     * 获取会话规则
     * @param company_id 商户company_id
	 * @return code 200->成功
	 */
    public function getSessionRule($company_id){
        $configure_value = Db::name('company_baseinfo')->where(['company_id'=>$company_id,'configure_key'=>'session_rule'])->value('configure_value');
        if($configure_value){
            return msg(200,'success',json_decode($configure_value));
        }else{
            return msg(200,'success',['rule_type'=>'','overtime'=>'']);
        }
    }

    /**
     * 同步所有公众号标签
     * @param company_id 商户company_id
	 * @return code 200->成功
	 */
    public function syncWxLabel($company_id){
        $wx_list = Db::name('openweixin_authinfo')->where(['company_id'=>$company_id])->select();
    
        //同步微信端至本地系统
        foreach($wx_list as $value){
            try{
                $token_info = Common::getRefreshToken($value['appid'],$company_id);
                if($token_info['meta']['code'] == 200){
                    $refresh_token = $token_info['body']['refresh_token'];
                }else{
                    continue;
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

                $label_res = Db::name('label')->where(['company_id'=>$company_id,'label_name'=>$v['name']])->find();
                if(!$label_res){
                    $label_id = Db::name('label')->insertGetId([
                        'company_id' => $company_id,
                        'label_name' => $v['name']
                    ]);
                }else{
                    $label_id = $label_res['label_id'];
                }

                Db::name('label_tag')->insert([
                    'company_id' => $company_id,
                    'label_id' => $label_id,
                    'appid' => $value['appid'],
                    'tag_id' => $v['id'],
                ]);
            }
        }

        //同步微信端至本地系统
        $label_arr = Db::name('label')->where(['company_id'=>$company_id])->select();
        foreach($label_arr as $value){
            foreach($wx_list as $index){
                $label_tag_res = Db::name('label_tag')->where(['company_id'=>$company_id,'appid'=>$index['appid'],'label_id'=>$value['label_id']])->find();
                if(!$label_tag_res){
                    try{
                        $token_info = Common::getRefreshToken($index['appid'],$company_id);
                        if($token_info['meta']['code'] == 200){
                            $refresh_token = $token_info['body']['refresh_token'];
                        }else{
                            continue;
                        }
        
                        $app = new Application(wxOptions());
                        $openPlatform = $app->open_platform;
                        $tag = $openPlatform->createAuthorizerApplication($index['appid'],$refresh_token)->user_tag;

                        $tag_id = $tag->create($value['label_name'])['tag']['id'];

                        Db::name('label_tag')->insert([
                            'company_id' => $company_id,
                            'tag_id' => $tag_id,
                            'label_id' => $value['label_id'],
                            'appid' => $index['appid'],
                            'company_id' => $company_id,
                        ]);
                    }catch (\Exception $e) {
                        continue;
                    }
                }
            }
        }

        return msg(200,'success');
    }
}