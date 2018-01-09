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
     * 获取客资领取规则
     * @param company_id
	 * @return code 200->成功
	 */
    public function getCustomerResourcesRule($company_id){
        // 客资领取周期
        $sustomer_resources_rule = Db::name('company_baseinfo')
        ->where([
            'company_id' => $company_id,
            'configure_key' => 'sustomer_resources_rule',
        ])->value('configure_value');

        $rule_arr = json_decode($sustomer_resources_rule,true);

        $arr_data = [
            'cued_pool' => [
                'cycle' => empty($rule_arr['cued_pool']['cycle']) == true ? 1 : $rule_arr['cued_pool']['cycle'],
                'number' => empty($rule_arr['cued_pool']['number']) == true ? '' : $rule_arr['cued_pool']['number']
            ],
            'cued_pool_recovery' => empty($rule_arr['cued_pool_recovery']) == true ? '' : $rule_arr['cued_pool_recovery'],
            'intention_receive' => [
                'cycle' => empty($rule_arr['intention_receive']['cycle']) == true ? '' : $rule_arr['intention_receive']['cycle'], 
                'number' => empty($rule_arr['intention_receive']['number']) == true ? '' : $rule_arr['intention_receive']['number']
            ],
            'intention_recovery' => empty($rule_arr['intention_recovery']) == true ? '' : $rule_arr['intention_recovery']
        ];

        return msg(200, 'success', $arr_data);
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
     * 删除企业话术
     * @param quick_reply_id 话术id
	 * @param company_id 商户id
	 * @return code 200->成功
	 */
    public function delQuickReply($data){
        $company_id = $data['company_id'];
        $quick_reply_id = $data['quick_reply_id'];

        $del_res = Db::name('quick_reply')->where(['company_id'=>$company_id,'quick_reply_id'=>$quick_reply_id,'type'=>2])->delete();

        if($del_res){
            return msg(200,'success');
        }else{
            return msg(3001,'删除数据失败');
        }
    }

    /**
     * 获取企业常用话术
     * @param company_id 商户id
     * @param page 分页参数
	 * @return code 200->成功
	 */
    public function getEnterpriseSentence($data){
        $company_id = $data['company_id'];
        $page = $data['page'];

        //分页
        $page_count = 16;
        $show_page = ($page - 1) * $page_count;

        $list = Db::name('quick_reply')->where(['company_id'=>$company_id, 'type'=>2])->limit($show_page,$page_count)->select();
        $count = Db::name('quick_reply')->where(['company_id'=>$company_id, 'type'=>2])->limit($show_page,$page_count)->count();

        $res['data_list'] = count($list) == 0 ? array() : $list;
        $res['page_data']['count'] = $count;
        $res['page_data']['rows_num'] = $page_count;
        $res['page_data']['page'] = $page;
        
        return msg(200,'success',$res);
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
    public function getLabelList($data){
        $map['company_id'] = $data['company_id'];

        if(!empty($data['label_group_id'])){
            $map['label_group_id'] = $data['label_group_id'];
        }

        if(!empty($data['label_name'])){
            $label_name = $data['label_name'];
            $map['label_name'] = ['like',"%$label_name%"];
        }

        $label_res = Db::name('label')->where($map)->select();
        
        foreach($label_res as $key=>$value){
            $label_res[$key]['group_name'] = Db::name('label_group')->where(['company_id'=>$data['company_id'],'label_group_id'=>$value['label_group_id']])->value('group_name');
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
     * 个人添加编辑快捷回复分组
     * @param company_id 商户company_id
     * @param reply_group_id 分组id (更新时传入)
     * @param group_name 分组名称
	 * @param uid 所属账号uid
	 * @return code 200->成功
	 */
    public function addUserQuickReplyGroup($data){
        $company_id = $data['company_id'];
        $reply_group_id = empty($data['reply_group_id']) == true ? '' : $data['reply_group_id'];
        $group_name = $data['group_name'];
        $uid = $data['uid'];

        if($reply_group_id){
            $update_res = Db::name('quick_reply_group')
            ->where(['company_id'=>$company_id,'reply_group_id'=>$reply_group_id,'uid'=>$uid,'group_type'=>1])
            ->update([
                'group_name' => $group_name
            ]);

            if($update_res !== false){
                return msg(200,'success');
            }else{
                return msg(3001,'更新数据失败');
            }
        }else{
            $add_res = Db::name('quick_reply_group')
            ->insert([
                'company_id' => $company_id,
                'group_name' => $group_name,
                'group_type' => 1,
                'uid' => $uid
            ]);

            if($add_res){
                return msg(200,'success');
            }else{
                return msg(3001,'插入数据失败');
            }
        }
    }

    /**
     * 获取个人快捷回复分组
     * @param company_id 商户company_id
	 * @param uid 所属账号uid
	 * @return code 200->成功
	 */
    public function getUserQuickReplyGroup($data){
        $company_id = $data['company_id'];
        $uid = $data['uid'];

        $list = Db::name('quick_reply_group')->where(['company_id'=>$company_id,'uid'=>$uid,'group_type'=>1])->select();

        return msg(200,'success',$list);
    }

    /**
     * 删除个人快捷回复分组
     * @param company_id 商户company_id
	 * @param uid 所属账号uid
	 * @param reply_group_id 删除的分组id
	 * @return code 200->成功
	 */
    public function delUserQuickReplyGroup($data){
        $company_id = $data['company_id'];
        $uid = $data['uid'];
        $reply_group_id = $data['reply_group_id'];

        $del_res = Db::name('quick_reply_group')->where(['company_id'=>$company_id,'uid'=>$uid,'reply_group_id'=>$reply_group_id])->delete();

        Db::name('quick_reply')->where(['company_id'=>$company_id,'reply_group_id'=>$reply_group_id])->update(['reply_group_id'=>-1]);

        if($del_res){
            return msg(200,'success');
        }else{
            return msg(3001,'删除失败');
        }
    }

    /**
     * 删除企业快捷回复分组
     * @param company_id 商户company_id
	 * @param uid 所属账号uid
	 * @param reply_group_id 删除的分组id
	 * @return code 200->成功
	 */
    public function delCommonQuickReplyGroup($data){
        $company_id = $data['company_id'];
        $uid = $data['uid'];
        $reply_group_id = $data['reply_group_id'];

        $del_res = Db::name('quick_reply_group')->where(['company_id'=>$company_id,'reply_group_id'=>$reply_group_id,'group_type'=>2])->delete();

        if($del_res){
            Db::name('quick_reply')->where(['company_id'=>$company_id,'reply_group_id'=>$reply_group_id])->update(['reply_group_id'=>-1]);

            return msg(200,'success');
        }else{
            return msg(3001,'删除失败');
        }
    }

    /**
     * 添加编辑企业快捷回复分组
     * @param company_id 商户company_id
     * @param reply_group_id 分组id (更新时传入)
     * @param group_name 分组名称
	 * @param uid 所属账号uid
	 * @return code 200->成功
	 */
    public function addCommonQuickReplyGroup($data){
        $company_id = $data['company_id'];
        $reply_group_id = empty($data['reply_group_id']) == true ? '' : $data['reply_group_id'];
        $group_name = $data['group_name'];
        $uid = $data['uid'];

        if($reply_group_id){
            $update_res = Db::name('quick_reply_group')
            ->where(['company_id'=>$company_id,'reply_group_id'=>$reply_group_id,'uid'=>$uid,'group_type'=>2])
            ->update([
                'group_name' => $group_name
            ]);

            if($update_res !== false){
                return msg(200,'success');
            }else{
                return msg(3001,'更新数据失败');
            }
        }else{
            $add_res = Db::name('quick_reply_group')
            ->insert([
                'company_id' => $company_id,
                'group_name' => $group_name,
                'group_type' => 2,
                'uid' => $uid
            ]);

            if($add_res){
                return msg(200,'success');
            }else{
                return msg(3001,'插入数据失败');
            }
        }
    }

    /**
     * 获取企业快捷回复分组
     * @param company_id 商户company_id
	 * @param uid 所属账号uid
	 * @return code 200->成功
	 */
    public function getCommonQuickReplyGroup($data){
        $company_id = $data['company_id'];
        $uid = $data['uid'];

        $list = Db::name('quick_reply_group')->where(['company_id'=>$company_id,'uid'=>$uid,'group_type'=>2])->select();

        return msg(200,'success',$list);
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