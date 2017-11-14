<?php
namespace app\api\logic\v1\we_chat;
use think\Model;
use think\Db;
use EasyWeChat\Foundation\Application;
use app\api\common\Common;
use think\Log;

//微信后台操作业务类
class WxOperationModel extends Model {
    /**
     * 获取菜单List
     * @param appid 公众号或小程序appid
     * @param company_id 商户company_id
	 * @return code 200->成功
	 */
    public function getMenuList($data){
        $company_id = $data['company_id'];
        $appid = $data['appid'];

        $token_info = Common::getRefreshToken($appid,$company_id);
        if($token_info['meta']['code'] == 200){
            $refresh_token = $token_info['body']['refresh_token'];
        }else{
            return $token_info;
        }

        $app = new Application(wxOptions());
        $openPlatform = $app->open_platform;
        $menu = $openPlatform->createAuthorizerApplication($appid,$refresh_token)->menu;

        return msg(200,'success',$menu->all()['menu']['button']);
    }

    /**
     * 设置菜单
     * @param appid 公众号或小程序appid
     * @param company_id 商户company_id
     * @param menu_list 菜单数据
	 * @return code 200->成功
	 */
    public function setMenu($data){
        $appid = $data['appid'];
        $company_id = $data['company_id'];
        $menu_list = $data['menu_list'];

        $token_info = Common::getRefreshToken($appid,$company_id);
        if($token_info['meta']['code'] == 200){
            $refresh_token = $token_info['body']['refresh_token'];
        }else{
            return $token_info;
        }

        $app = new Application(wxOptions());
        $openPlatform = $app->open_platform;
        $menu = $openPlatform->createAuthorizerApplication($appid,$refresh_token)->menu;

        return msg(200,'success');
    }

    /**
     * 设置自动回复关键词
     * @param appid 公众号或小程序appid
     * @param company_id 商户company_id
     * @param message_rule_id 回复规则id
     * @param key_word 回复关键词
     * @param reply_text 回复文本内容
     * @param rule_type 响应类型 1文本回复 2接入到指定客服 3接入到指定客服组 4关注自动回复
     * @param user_group_id 客服分组id rule_type为3必传
     * @param uid 客服id rule_type为2必传
	 * @return code 200->成功
	 */
    public function setMessageRuld($data){
        $company_id = $data['company_id'];
        $appid = $data['appid'];
        $key_word = $data['rule_type'] == 4 ? 'follow_reply' : $data['key_word'];
        $reply_text = empty($data['reply_text']) == true ? '' : $data['reply_text'];
        $message_rule_id = empty($data['message_rule_id']) == true ? '' : $data['message_rule_id'];
        $rule_type = $data['rule_type'];
        $user_group_id = empty($data['user_group_id']) == true ? '' : $data['message_rule_id'];
        $uid = empty($data['uid']) == true ? '' : $data['uid'];
        $pattern = $data['pattern'] == 2 ? 2 : 1;

        if($rule_type == 2){
            if(!$uid){
                return msg('客服未选择');
            }
        }

        if($rule_type == 3){
            if(!$user_group_id){
                return msg('客服分组未选择');
            }
        }

        if($rule_type != 4){
            $rule_res = Db::name('message_rule')->where(['company_id'=>$company_id,'appid'=>$appid,'key_word'=>$key_word])->find();
            if($rule_res){
                return msg(3002,'回复关键词已存在'); 
            }
        }else{
            $follow_reply = Db::name('message_rule')->where(['company_id'=>$company_id,'appid'=>$appid,'key_word'=>'follow_reply'])->find();
            if($follow_reply){
                $message_rule_id = $follow_reply['message_rule_id'];
            }
        }

        if($message_rule_id){
            $res = Db::name('message_rule')->where([
                'company_id' => $company_id,
                'message_rule_id' => $message_rule_id,
                'appid' => $appid
            ])->update([
                'key_word' => $key_word,
                'reply_text' => emoji_encode($reply_text),
                'rule_type' => $rule_type,
                'pattern' => $pattern,
                'user_group_id' => $user_group_id,
                'uid' => $uid
            ]);
        }else{
            $add_time = date('Y-m-d H:i:s');

            $res = Db::name('message_rule')->insert([
                'reply_text' => emoji_encode($reply_text),
                'key_word' => $key_word,
                'rule_type' => $rule_type,
                'company_id' => $company_id,
                'appid' => $appid,
                'pattern' => $pattern,
                'add_time' => $add_time,
                'user_group_id' => $user_group_id,
                'uid' => $uid
            ]);
        }

        if($res){
            return msg(200,'success');
        }else{
            return msg(3001,'数据更新或插入失败');
        }
    }

    /**
     * 获取自动回复关键词列表
     * @param appid 公众号或小程序appid
     * @param company_id 商户company_id
     * @param page 分页参数默认1
	 * @return code 200->成功
	 */
    public function getMessageRuleList($data){
        $company_id = $data['company_id'];
        $page = $data['page'];
        $appid = $data['appid'];

        //分页
        $page_count = 16;
        $show_page = ($page - 1) * $page_count;

        $message_rule_res = Db::name('message_rule')->where(['appid'=>$appid,'company_id'=>$company_id])->limit($show_page,$page_count)->select();
        $count = Db::name('message_rule')->where(['appid'=>$appid,'company_id'=>$company_id])->count();

        if(empty($message_rule_res == false)){
            foreach($message_rule_res as $k=>$v){
                $message_rule_res[$k]['reply_text'] = emoji_decode($v['reply_text']);
            }
        }

        $res['data_list'] = count($message_rule_res) == 0 ? array() : $message_rule_res;
        $res['page_data']['count'] = $count;
        $res['page_data']['rows_num'] = $page_count;
        $res['page_data']['page'] = $page;
        
        return msg(200,'success',$res);
    }

    /**
     * 删除自动回复关键词
     * @param message_rule_id 删除的规则od
	 * @return code 200->成功
	 */
    public function delMessageRule($data){
        $company_id = $data['company_id'];
        $message_rule_id = $data['message_rule_id'];

        $del_res = Db::name('message_rule')->where(['company_id'=>$company_id,'message_rule_id'=>$message_rule_id])->delete();
        if($del_res){
            return msg(200,'success');
        }else{
            return msg(3001,'删除失败');
        }
    }
}