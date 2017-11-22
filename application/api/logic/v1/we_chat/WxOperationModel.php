<?php
namespace app\api\logic\v1\we_chat;
use think\Model;
use think\Db;
use EasyWeChat\Foundation\Application;
use app\api\common\Common;
use think\Log;
use EasyWeChat\Message\Article;
use EasyWeChat\Message\Text;

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
        $menu->add($menu_list);

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
        $user_group_id = empty($data['user_group_id']) == true ? '' : $data['user_group_id'];
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

    /**
     * 上传微信永久素材图片
     * @param company_id 商户company_id
     * @param appid 公众号appid
     * @param token 登录token
	 * @return code 200->成功
	 */
    public function uploadSourceMaterial($data){
        $company_id = $data['company_id'];
        $appid = $data['appid'];
        $token = $data['token'];

        $file = request()->file('file');
 
        if($file){
            $date = date('Y-m-d');
            $save_path = '../uploads/source_material';

            $path = '/uploads/source_material';
            $info = $file->validate(['size'=>3567810,'ext'=>'jpg,png,gif,jpeg'])->rule('uniqid')->move($save_path);
            if($info){
                $file_name = $info->getFilename();

                $img_url = config('file_url').$path.'/'.$file_name; 

                $relative_path = '..'.$path.'/'.$file_name;

                $request_data = [
                    'appid' => $appid,
                    'relative_path' => $relative_path,
                ];

                $client = new \GuzzleHttp\Client();
                $request_res = $client->request(
                    'PUT',
                    'http://'.$_SERVER['HTTP_HOST'].'/api/v1/we_chat/WxOperation/wxUploadImg?token='.$token,
                    [
                        'json' => $request_data,
                        'timeout' => 10
                    ]
                );

                $request_arr = json_decode($request_res->getBody(),true);
                return msg(200,'success',$request_arr['body']);
            }else{
                return msg(3001,$file->getError());
            }
        } else {
            return msg(3002,'未收到文件');
        }
    }

    //微信上传图片
    public function wxUploadImg($data){
        $appid = $data['appid'];
        $company_id = $data['company_id'];
        $relative_path = $data['relative_path'];

        $token_info = Common::getRefreshToken($appid,$company_id);
        if($token_info['meta']['code'] == 200){
            $refresh_token = $token_info['body']['refresh_token'];
        }else{
            return $token_info;
        }

        $app = new Application(wxOptions());
        $openPlatform = $app->open_platform;
        $material = $openPlatform->createAuthorizerApplication($appid,$refresh_token)->material;
        $result = $material->uploadImage($relative_path);
        @unlink($relative_path);
        
        return msg(200,'success',['media_id'=>$result['media_id'],'url'=>$result['url']]);
    }

    //上传微信文章永久图片
    public function uploadArticleImg($appid,$company_id,$relative_path){
        $token_info = Common::getRefreshToken($appid,$company_id);
        if($token_info['meta']['code'] == 200){
            $refresh_token = $token_info['body']['refresh_token'];
        }else{
            return $token_info;
        }

        $app = new Application(wxOptions());
        $openPlatform = $app->open_platform;
        $material = $openPlatform->createAuthorizerApplication($appid,$refresh_token)->material;
        $result = $material->uploadArticleImage($relative_path);
        @unlink($relative_path);
        
        return msg(200,'success',['url'=>$result['url']]);
    }
    
    /**
     * 发布或更新微信图文素材
     * @param company_id 商户company_id
     * @param appid 公众号appid
     * @param mediaId mediaId图文素材id 存在则更新
     * @param title 标题
     * @param thumb_media_id 图文消息的封面图片素材id（必须是永久mediaID）
     * @param author 作者
     * @param digest 图文消息的摘要，仅有单图文消息才有摘要，多图文此处为空。如果本字段为没有填写，则默认抓取正文前64个字。
     * @param show_cover_pic 是否显示封面，0为false，即不显示，1为true，即显示
     * @param content_source_url 图文消息的原文地址，即点击“阅读原文”后的URL
	 * @return code 200->成功
	 */
    public function addArticle($data){
        $company_id = $data['company_id'];
        $appid = $data['appid'];
        $content = $data['content'];
        $title = $data['title'];
        $thumb_media_id = $data['thumb_media_id'];
        $author = $data['author'];
        $digest = $data['digest'];
        $show_cover_pic = $data['show_cover_pic'];
        $content_source_url = $data['content_source_url'];
        $mediaId = empty($data['mediaId']) == true ? '' : $data['mediaId'];

        if(empty($content)){
            return msg(3001,'content参数不能为空');
        }

        $pattern1 = '/url\(\'{0,1}\"{0,1}(.*?)\'{0,1}\"{0,1}\)/'; 
        preg_match_all($pattern1,$content,$match1); 

        $pattern2 = '/<[img|IMG].*?src=[\'|\"](.*?(?:[\.gif|\.jpg]))[\'|\"].*?[\/]?>/'; 
        preg_match_all($pattern2,$content,$match2); 

        $img_res = array_merge($match1[1],$match2[1]);

        $save_path = '../uploads/source_material';

        foreach($img_res as $url){
            $relative_path = getImage($url,$save_path)['save_path'];
            if(empty($relative_path)){
                continue;
            }

            $upload_res = $this->uploadArticleImg($appid,$company_id,$relative_path);
            if($upload_res['meta']['code'] != 200){
                continue;
            }

            $content = str_replace($url, $upload_res['body']['url'], $content);
        }

        if(!empty($mediaId)){
            $data['content'] = $content;
            return $this->updateArticle($data);
        }

        $token_info = Common::getRefreshToken($appid,$company_id);
        if($token_info['meta']['code'] == 200){
            $refresh_token = $token_info['body']['refresh_token'];
        }else{
            return $token_info;
        }

        $app = new Application(wxOptions());
        $openPlatform = $app->open_platform;
        $material = $openPlatform->createAuthorizerApplication($appid,$refresh_token)->material;

        $article = new Article([
            'title' => $title,
            'thumb_media_id' => $thumb_media_id,
            'author' => $author,
            'digest' => $digest,
            'show_cover_pic' => $show_cover_pic,
            'content' => $content,
            'content_source_url' => $content_source_url
        ]);
        $article_res = $material->uploadArticle($article);

        if(!empty($article_res['media_id'])){
            return msg(200,'success',['media_id'=>$article_res['media_id']]);
        }else{
            return msg(3002,'微信服务器故障请重试！');
        }
    }

    /**
     * 更新微信图文素材
     * @param company_id 商户company_id
     * @param appid 公众号appid
     * @param title 标题
     * @param thumb_media_id 图文消息的封面图片素材id（必须是永久mediaID）
     * @param author 作者
     * @param digest 图文消息的摘要，仅有单图文消息才有摘要，多图文此处为空。如果本字段为没有填写，则默认抓取正文前64个字。
     * @param show_cover_pic 是否显示封面，0为false，即不显示，1为true，即显示
     * @param content_source_url 图文消息的原文地址，即点击“阅读原文”后的URL
     * @param mediaId 需要更新的图文素材id
	 * @return code 200->成功
	 */
    public function updateArticle($data){
        $company_id = $data['company_id'];
        $appid = $data['appid'];
        $content = $data['content'];
        $title = $data['title'];
        $thumb_media_id = $data['thumb_media_id'];
        $author = $data['author'];
        $digest = $data['digest'];
        $show_cover_pic = $data['show_cover_pic'];
        $content_source_url = $data['content_source_url'];
        $mediaId = $data['mediaId'];

        $token_info = Common::getRefreshToken($appid,$company_id);
        if($token_info['meta']['code'] == 200){
            $refresh_token = $token_info['body']['refresh_token'];
        }else{
            return $token_info;
        }

        $app = new Application(wxOptions());
        $openPlatform = $app->open_platform;
        $material = $openPlatform->createAuthorizerApplication($appid,$refresh_token)->material;

        $article_res = $material->updateArticle(
            $mediaId,
            new Article([
                'title' => $title,
                'thumb_media_id' => $thumb_media_id,
                'author' => $author,
                'digest' => $digest,
                'show_cover_pic' => $show_cover_pic,
                'content' => $content,
                'content_source_url' => $content_source_url
            ])
        );

        if($article_res['errcode'] == 0){
            return msg(200,'success');
        }else{
            return msg(3002,$article_res['errmsg']);
        }
    }

    /**
     * 获取微信永久素材列表
     * @param company_id 商户company_id
     * @param appid 公众号appid
     * @param page 分页参数默认1
     * @param type 素材的类型，图片（image）、视频（video）、语音 （voice）、图文（news）
	 * @return code 200->成功
	 */
    public function getArticleList($company_id,$appid,$page,$type){
        //分页
        $page_count = 12;
        $show_page = ($page - 1) * $page_count;

        $token_info = Common::getRefreshToken($appid,$company_id);
        if($token_info['meta']['code'] == 200){
            $refresh_token = $token_info['body']['refresh_token'];
        }else{
            return $token_info;
        }

        $app = new Application(wxOptions());
        $openPlatform = $app->open_platform;
        $material = $openPlatform->createAuthorizerApplication($appid,$refresh_token)->material;
        
        $lists = $material->lists($type, $show_page, $page_count);
        
        $res['data_list'] = count($lists['item']) == 0 ? array() : $lists['item'];
        $res['page_data']['count'] = $lists['total_count'];
        $res['page_data']['rows_num'] = $page_count;
        $res['page_data']['page'] = $page;
        
        return msg(200,'success',$res);
    }

    /**
     * 删除微信永久素材
     * @param company_id 商户company_id
     * @param appid 公众号appid
     * @param mediaId 素材id
	 * @return code 200->成功
	 */
    public function delSourceMaterial($company_id,$appid,$mediaId){
        $token_info = Common::getRefreshToken($appid,$company_id);
        if($token_info['meta']['code'] == 200){
            $refresh_token = $token_info['body']['refresh_token'];
        }else{
            return $token_info;
        }

        $app = new Application(wxOptions());
        $openPlatform = $app->open_platform;
        $material = $openPlatform->createAuthorizerApplication($appid,$refresh_token)->material;

        $res = $material->delete($mediaId);
        if($res['errcode'] == 0){
            return msg(200,'success');
        }else{
            return msg(3001,$res['errmsg']);
        }
    }

    /**
     * 获取微信永久素材详情
     * @param company_id 商户company_id
     * @param appid 公众号appid
     * @param mediaId 素材id
	 * @return code 200->成功
	 */
    public function getSourceMaterial($company_id,$appid,$mediaId){
        $token_info = Common::getRefreshToken($appid,$company_id);
        if($token_info['meta']['code'] == 200){
            $refresh_token = $token_info['body']['refresh_token'];
        }else{
            return $token_info;
        }

        $app = new Application(wxOptions());
        $openPlatform = $app->open_platform;
        $material = $openPlatform->createAuthorizerApplication($appid,$refresh_token)->material;

        $res = $material->get($mediaId);

        return msg(200,'success',$res);
    }

    /**
     * 创建任务计划
     * @param company_id 商户company_id
     * @param appid 公众号appid
     * @param uid 操作人uid
     * @param type 任务类型 1同步粉丝列表 2同步粉丝基本信息
	 * @return code 200->成功
	 */
    public function syncWxUser($company_id,$uid,$appid,$type){
        $time = date('Y-m-d H:i:s');

        $map['company_id'] = $company_id;
        $map['state'] = array('in',[0,1]);
        $num = Db::name('task')->where($map)->count();
        if($num >= 1){
            return msg(3001,'每次执行任务数量不能大于1件');
        }

        $data = [
            'company_id' => $company_id,
            'appid' => $appid,
            'task_type' => $type,
            'add_time' => $time,
            'uid' => $uid,
            'company_id' => $company_id
        ];

        $insert_data = Db::name('task')->insert($data);
    
        if($insert_data){
            return msg(200,'success');
        }else{
            return msg(3001,'插入数据失败');
        }
    }

    /**
     * 获取任务计划列表
     * @param company_id 商户company_id
     * @param page 分页参数默认1
	 * @return code 200->成功
	 */
    public function getTaskList($company_id,$page){
        //分页
        $page_count = 12;
        $show_page = ($page - 1) * $page_count;

        $list = Db::name('task')->where(['company_id'=>$company_id])->limit($show_page,$page_count)->order('add_time desc')->select();
        $count = Db::name('task')->where(['company_id'=>$company_id])->count();

        foreach($list as $k=>$v){
            $list[$k]['app_name'] = Db::name('openweixin_authinfo')->where(['appid'=>$v['appid']])->cache(true,60)->value('nick_name');
        }

        $res['data_list'] = count($list) == 0 ? array() : $list;
        $res['page_data']['count'] = $count;
        $res['page_data']['rows_num'] = $page_count;
        $res['page_data']['page'] = $page;
        
        return msg(200,'success',$res);
    }

    /**
     * 获取微信粉丝用户列表
     * @param page 分页参数默认1
     * @param nickname 搜索微信昵称 (选传)
     * @param real_name 微信用户真实姓名 (选传)
     * @param real_phone 微信用户真实联系电话 (选传)
     * @param wx_company_id 微信用户归属公司分组id (选传)
	 * @return code 200->成功
     */
    public function getWxUserList($data){
        $company_id = $data['company_id'];
        $page = $data['page'];
        $appid = empty($data['appid']) == true ? '' : $data['appid'];
        $nickname = empty($data['nickname']) == true ? '' : $data['nickname'];
        $real_name = empty($data['real_name']) == true ? '' : $data['real_name'];
        $real_phone = empty($data['real_phone']) == true ? '' : $data['real_phone'];
        $wx_company_id = empty($data['wx_company_id']) == true ? '' : $data['wx_company_id'];

        //分页
        $page_count = 16;
        $show_page = ($page - 1) * $page_count;

        if($appid){
            $map['appid'] = $appid;
        }
        
        if($nickname){
            $map['nickname'] = array('like',"%$nickname%");
        }

        if($real_name){
            $map['real_name'] = array('like',"%$real_name%");
        }

        if($real_phone){
            $map['real_phone'] = array('like',"%$real_phone%");
        }

        if($wx_company_id){
            $map['wx_company_id'] = $wx_company_id;
        }

        $map['company_id'] = $company_id;
        
        $wx_user_list = Db::name('wx_user')->where($map)->limit($show_page,$page_count)->order('wx_user_id desc')->select();

        $count = Db::name('wx_user')->where($map)->count();
    
        foreach($wx_user_list as $k=>$v){
            $wx_user_list[$k]['app_name'] = Db::name('openweixin_authinfo')->where(['appid'=>$v['appid']])->value('nick_name');
        }

        $res['data_list'] = count($wx_user_list) == 0 ? array() : $wx_user_list;
        $res['page_data']['count'] = $count;
        $res['page_data']['rows_num'] = $page_count;
        $res['page_data']['page'] = $page;
        
        return msg(200,'success',$res);
    }

    /**
     * 创建或编辑微信用户公司分组
     * @param wx_comapny_name 公司名称
     * @param wx_company_id 更新编辑时传入
     * @param person_charge_phone 公司负责人联系电话 (选传)
     * @param person_charge_name 公司负责人联系电话 (选传)
     * @param contact_address 公司联系地址 (选传)
     * @param remarks 公司负责人联系电话 (选传)
	 * @return code 200->成功
	 */
    public function addWxUserComapnyGroup($data){
        $company_id = $data['company_id'];
        $wx_comapny_name = $data['wx_comapny_name'];
        $wx_company_id = empty($data['wx_company_id']) == true ? '' : $data['wx_company_id'];
        $person_charge_phone = empty($data['person_charge_phone']) == true ? '' : $data['person_charge_phone'];
        $person_charge_name = empty($data['person_charge_name']) == true ? '' : $data['person_charge_name'];
        $person_charge_sex = empty($data['person_charge_sex']) == true ? '' : $data['person_charge_sex'];
        $remarks = empty($data['remarks']) == true ? '' : $data['remarks'];
        $contact_address = empty($data['contact_address']) == true ? '' : $data['contact_address'];

        if(!$wx_company_id){
            $wx_company_id = Db::name('wx_user_company')->insertGetId([
                'company_id' => $company_id,
                'wx_comapny_name' => $wx_comapny_name,
                'person_charge_phone' => $person_charge_phone,
                'person_charge_name' => $person_charge_name,
                'person_charge_sex' => $person_charge_sex,
                'remarks' => $remarks,
                'contact_address' => $contact_address
            ]);
        }else{
            $wx_company_id = Db::name('wx_user_company')->where(['company_id'=>$company_id,'wx_company_id'=>$wx_company_id])->update([
                'wx_comapny_name' => $wx_comapny_name,
                'person_charge_phone' => $person_charge_phone,
                'person_charge_name' => $person_charge_name,
                'person_charge_sex' => $person_charge_sex,
                'remarks' => $remarks,
                'contact_address' => $contact_address
            ]);
        }

        if($wx_company_id){
            return msg(200,'success',['wx_company_id'=>$wx_company_id]);
        }else{
            return msg(3001,'插入数据失败');
        }
    }

    /**
     * 获取微信用户公司分组List
     * @param company_id 商户company_id 
     * @param page 分页参数默认1 
     * @param wx_comapny_name 公司名称 (搜索选传)
	 * @return code 200->成功
	 */
    public function getWxUserComapnyGroupList($data){
        $company_id = $data['company_id'];
        $page = $data['page'];
        $wx_comapny_name = empty($data['wx_comapny_name']) == true ? '' : $data['wx_comapny_name'];

        //分页
        $page_count = 16;
        $show_page = ($page - 1) * $page_count;

        $map['company_id'] = $company_id;
        if($wx_comapny_name){
            $map['wx_comapny_name'] = array('like',"%$wx_comapny_name%");
        }
        $list = Db::name('wx_user_company')->where($map)->limit($show_page,$page_count)->select();
        $count = Db::name('wx_user_company')->where($map)->count();

        $res['data_list'] = count($list) == 0 ? array() : $list;
        $res['page_data']['count'] = $count;
        $res['page_data']['rows_num'] = $page_count;
        $res['page_data']['page'] = $page;
        
        return msg(200,'success',$res);
    }

    /**
     * 删除微信用户公司
     * @param company_id 商户company_id 
     * @param wx_company_id 删除的公司id
	 * @return code 200->成功
	 */
    public function delWxUserComapny($data){
        $company_id = $data['company_id'];
        $wx_company_id = $data['wx_company_id'];

        $del_res = Db::name('wx_user_company')->where(['company_id'=>$company_id,'wx_company_id'=>$wx_company_id])->delete();
        if($del_res){
            return msg(200,'success');
        }else{
            return msg(3001,'删除数据失败');
        }
    }

    /**
     * 添加编辑客户池分组
     * @param company_id 商户company_id 
     * @param group_name 分组名称
     * @param wx_user_group_id 用户分组id 更新时传入
	 * @return code 200->成功
	 */
    public function addCustomerGroup($data){
        $company_id = $data['company_id'];
        $group_name = $data['group_name'];
        $wx_user_group_id = empty($data['wx_user_group_id']) == true ? '' : $data['wx_user_group_id'];

        if(!$wx_user_group_id){
            $wx_user_group_id = Db::name('wx_user_group')->insertGetId([
                'company_id'=>$company_id,
                'group_name'=>$group_name,
            ]);
        }else{
            $wx_user_group_id = Db::name('wx_user_group')->where(['company_id'=>$company_id,'wx_user_group_id'=>$wx_user_group_id])->update([
                'group_name'=>$group_name
            ]);
        }

        if($wx_user_group_id){
            return msg(200,'success',['wx_user_group_id'=>$wx_user_group_id]);
        }else{
            return msg(3001,'插入数据失败');
        }
    }

    /**
     * 删除客户池分组
     * @param wx_user_group_id 删除的分组id
	 * @return code 200->成功
	 */
    public function delCustomerGroup($data){
        $company_id = $data['company_id'];
        $wx_user_group_id = $data['wx_user_group_id'];

        $del_res = Db::name('wx_user_group')->where(['company_id'=>$company_id,'wx_user_group_id'=>$wx_user_group_id])->delete();
        if($del_res){
            return msg(200,'success');
        }else{
            return msg(3001,'删除数据失败');
        }
    }

    /**
     * 获取客户池分组list
     * @param company_id 商户company_id
     * @param group_name 客户池分组名称
	 * @return code 200->成功
	 */
    public function getCustomerGroupList($data){
        $company_id = $data['company_id'];
        $group_name = empty($data['group_name']) == true ? '' : $data['group_name'];

        $map['company_id'] = $company_id;
        if($group_name){
            $map['group_name'] = array('like',"%$group_name%");
        }

        $res = Db::name('wx_user_group')->where($map)->select();

        return msg(200,'success',$res);
    }

    /**
     * 设置微信个性化菜单
     * @param appid 微信公众号id
     * @param comapny_id 商户company_id
     * @param menu_list 菜单数据
     * @param match_rule  菜单匹配规则
	 * @return code 200->成功
	 */
    public function setWxIndividualizationMenu($data){
        $company_id = $data['company_id'];
        $appid = $data['appid'];
        $menu_list = $data['menu_list'];
        $match_rule = $data['match_rule'];

        $token_info = Common::getRefreshToken($appid,$company_id);
        if($token_info['meta']['code'] == 200){
            $refresh_token = $token_info['body']['refresh_token'];
        }else{
            return $token_info;
        }

        $app = new Application(wxOptions());
        $openPlatform = $app->open_platform;
        $menu = $openPlatform->createAuthorizerApplication($appid,$refresh_token)->menu;
        $menu->add($menu_list, $match_rule);

        return msg(200,'success');
    }

    /**
     * 获取个性化菜单数据
     * @param appid 公众号或小程序appid
     * @param company_id 商户company_id
	 * @return code 200->成功
	 */
    public function getWxIndividualizationMenu($data){
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

        return msg(200,'success',$menu->all()['conditionalmenu']);
    }

    /**
     * 删除个性化菜单
     * @param appid 公众号或小程序appid
     * @param company_id 商户company_id
     * @param menuId 菜单id
	 * @return code 200->成功
	 */
    public function delWxIndividualizationMenu($data){
        $company_id = $data['company_id'];
        $appid = $data['appid'];
        $menuId = $data['menuId'];

        $token_info = Common::getRefreshToken($appid,$company_id);
        if($token_info['meta']['code'] == 200){
            $refresh_token = $token_info['body']['refresh_token'];
        }else{
            return $token_info;
        }

        $app = new Application(wxOptions());
        $openPlatform = $app->open_platform;
        $menu = $openPlatform->createAuthorizerApplication($appid,$refresh_token)->menu;

        $menu->destroy($menuId);

        return msg(200,'success');
    }

    /**
     * 获取微信公众号分组
     * @param appid 公众号或小程序appid
     * @param company_id 商户company_id
	 * @return code 200->成功
	 */
    public function getWxGroup($data){
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
        $group = $openPlatform->createAuthorizerApplication($appid,$refresh_token)->user_group;

        $res = $group->lists();

        return msg(200,'success',$res['groups']);
    }

    /**
     * 创建或编辑微信公众号分组
     * @param appid 公众号或小程序appid
     * @param company_id 商户company_id
     * @param name 分组名称
	 * @return code 200->成功
	 */
    public function addWxGroup($data){
        $company_id = $data['company_id'];
        $appid = $data['appid'];
        $name = $data['name'];
        $group_id = empty($data['group_id']) == true ? '' : $data['group_id'];

        $token_info = Common::getRefreshToken($appid,$company_id);
        if($token_info['meta']['code'] == 200){
            $refresh_token = $token_info['body']['refresh_token'];
        }else{
            return $token_info;
        }

        $app = new Application(wxOptions());
        $openPlatform = $app->open_platform;

        try{
            $group = $openPlatform->createAuthorizerApplication($appid,$refresh_token)->user_group;
        }catch (\Exception $e) {
            return msg(3001,$e->getMessage());
        }

        if(!$group_id){
            try{
                $res = $group->create($name);
            }catch (\Exception $e) {
                return msg(3001,$e->getMessage());
            }

            return msg(200,'success',['group_id'=>$res['group']['id']]);
        }else{
            try{
                $group->update($group_id,$name);
            }catch (\Exception $e) {
                return msg(3001,$e->getMessage());
            }
            
            return msg(200,'success');
        }
    }

    /**
     * 删除微信公众号分组
     * @param appid 公众号或小程序appid
     * @param company_id 商户company_id
     * @param group_id 删除的分组id
	 * @return code 200->成功
	 */
    public function delWxGroup($data){
        $appid = $data['appid'];
        $company_id = $data['company_id'];
        $group_id = $data['group_id'];

        $token_info = Common::getRefreshToken($appid,$company_id);
        if($token_info['meta']['code'] == 200){
            $refresh_token = $token_info['body']['refresh_token'];
        }else{
            return $token_info;
        }

        $app = new Application(wxOptions());
        $openPlatform = $app->open_platform;
        try{
            $group = $openPlatform->createAuthorizerApplication($appid,$refresh_token)->user_group;
            $group->delete($group_id);
        }catch (\Exception $e) {
            return msg(3001,$e->getMessage());
        }
        
        return msg(200,'success');
    }

    /**
     * 移动用户到指定微信分组
     * @param appid 公众号或小程序appid
     * @param company_id 商户company_id
     * @param group_id 移到到新的分组id
     * @param openid_list 移动的用户openid list ['openid...','opneid...']
	 * @return code 200->成功
	 */
    public function moveUserWxGroup($data){
        $appid = $data['appid'];
        $company_id = $data['company_id'];
        $group_id = $data['group_id'];
        $openid_list = $data['openid_list'];

        $token_info = Common::getRefreshToken($appid,$company_id);
        if($token_info['meta']['code'] == 200){
            $refresh_token = $token_info['body']['refresh_token'];
        }else{
            return $token_info;
        }

        $app = new Application(wxOptions());
        $openPlatform = $app->open_platform;
        $group = $openPlatform->createAuthorizerApplication($appid,$refresh_token)->user_group;

        $res = $group->moveUsers($openid_list,$group_id);
        if($res['errcode'] == 0){
            foreach($openid_list as $openid){
                Db::name('wx_user')->where(['company_id'=>$company_id,'appid'=>$appid,'openid'=>$openid])->update(['groupid'=>$group_id]);
            }

            return msg(200,'success');
        }else{
            return msg(3001,$res['errmsg']);
        }
    }

    /**
     * 添加群发
     * @param appid 公众号或小程序appid
     * @param company_id 商户id
     * @param type 群发类型 1全部 2按分组 3指定用户
     * @param send_type 发送时效 1立即发送 2定时发送
     * @param send_time 定时群发时间(选传)
     * @param openid_list 发送指定的用户openid list(选传)
     * @param group_id 发送指定的微信分组id(选传)
     * @param send_message_type 群发消息类型 1文字 2图文消息 3图片
     * @param media_id 群发的图文信息id(选传)
     * @param text 群发文字(选传)
	 * @return code 200->成功
	 */
    public function addMassNews($data){
        $time = date('Y-m-d H:i:s');

        $company_id = $data['company_id'];
        $appid = $data['appid'];
        $type = $data['type'];
        $send_type = $data['send_type'];
        $send_time = empty($data['send_time']) == true ? '' : $data['send_time'];
        $openid_list = empty($data['openid_list']) == true ? '' : $data['openid_list'];
        $group_id = empty($data['group_id']) == true ? '' : $data['group_id'];
        $send_message_type = $data['send_message_type'];
        $text = empty($data['text']) == true ? '' : $data['text'];
        $media_id = empty($data['media_id']) == true ? '' : $data['media_id'];

        $news_id = Db::name('mass_news')->insertGetId([
            'company_id' => $company_id,
            'appid' => $appid,
            'type' => $type,
            'send_type' => $send_type,
            'send_time' => $send_time,
            'openid_list' => $openid_list,
            'group_id' => $group_id,
            'send_message_type' => $send_message_type,
            'text' => $text,
            'media_id' => $media_id,
            'add_time' => $time,
        ]);

        if($news_id){
            return msg(200,'success',['news_id'=>$news_id]);
        }else{
            return msg(3001,'插入数据失败');
        }
    }
    
    /**
     * 删除群发消息
     * @param appid 公众号或小程序appid
     * @param news_id 删除的群发id
     * @param company_id 商户company_id
	 * @return code 200->成功
	 */
    public function delMassNews($data){
        $company_id = $data['company_id'];
        $appid = $data['appid'];
        $news_id = $data['news_id'];
    
        $del_res = Db::name('mass_news')->where([
            'news_id' => $news_id,
            'appid' => $appid,
            'company_id' => $company_id
        ])->delete();
    
        if($del_res){
            return msg(200,'success');
        }else{
            return msg(3001,'删除失败');
        }
    }

    /**
     * 获取群发消息列表
     * @param appid 公众号或小程序appid
     * @param company_id 商户company_id
     * @param page 分页参数默认1
	 * @return code 200->成功
	 */
    public function getMassNewsList($appid,$company_id,$page){
        //分页
        $page_count = 16;
        $show_page = ($page - 1) * $page_count;

        $list = Db::name('mass_news')->where(['appid'=>$appid,'company_id'=>$company_id])->limit($show_page,$page_count)->order('add_time desc')->select();
        $count = Db::name('mass_news')->where(['appid'=>$appid,'company_id'=>$company_id])->count();
        
        foreach($list as $k=>$v){
            $list[$k]['app_name'] = Db::name('openweixin_authinfo')->where(['appid'=>$v['appid']])->cache(true,60)->value('nick_name');
        }

        $res['data_list'] = count($list) == 0 ? array() : $list;
        $res['page_data']['count'] = $count;
        $res['page_data']['rows_num'] = $page_count;
        $res['page_data']['page'] = $page;
        
        return msg(200,'success',$res);
    }

    /**
     * 获取用户增减数据(最大时间跨度：7)
     * @param appid 公众号appid
     * @param company_id 商户company_id
     * @param start_date 查询开始日期
     * @param end_date 查询结束日期
	 * @return code 200->成功
	 */
    public function getUserSummary($data){
        $company_id = $data['company_id'];
        $appid = $data['appid'];
        $start_date = $data['start_date'];
        $end_date = $data['end_date'];

        $token_info = Common::getRefreshToken($appid,$company_id);
        if($token_info['meta']['code'] == 200){
            $refresh_token = $token_info['body']['refresh_token'];
        }else{
            return $token_info;
        }

        $app = new Application(wxOptions());
        $openPlatform = $app->open_platform;
        $stats = $openPlatform->createAuthorizerApplication($appid,$refresh_token)->stats;

        try{
            $userSummary = $stats->userSummary($start_date, $end_date);
        }catch (\Exception $e) {
            return msg(3001,$e->getMessage());
        }

        return msg(200,'success',$userSummary['list']);
    }

    /**
     * 获取累计用户数据(最大时间跨度：7)
     * @param appid 公众号appid
     * @param company_id 商户company_id
     * @param start_date 查询开始日期
     * @param end_date 查询结束日期
	 * @return code 200->成功
	 */
    public function getUserCumulate($data){
        $company_id = $data['company_id'];
        $appid = $data['appid'];
        $start_date = $data['start_date'];
        $end_date = $data['end_date'];

        $token_info = Common::getRefreshToken($appid,$company_id);
        if($token_info['meta']['code'] == 200){
            $refresh_token = $token_info['body']['refresh_token'];
        }else{
            return $token_info;
        }

        $app = new Application(wxOptions());
        $openPlatform = $app->open_platform;
        $stats = $openPlatform->createAuthorizerApplication($appid,$refresh_token)->stats;

        try{
            $userCumulate = $stats->userCumulate($start_date, $end_date);
        }catch (\Exception $e) {
            return msg(3001,$e->getMessage());
        }
        
        return msg(200,'success',$userCumulate['list']);
    }

    /**
     * 获取图文群发每日数据(最大时间跨度：1)
     * @param appid 公众号appid
     * @param company_id 商户company_id
     * @param start_date 查询开始日期
     * @param end_date 查询结束日期
	 * @return code 200->成功
	 */
    public function getArticleSummary($data){
        $company_id = $data['company_id'];
        $appid = $data['appid'];
        $start_date = $data['start_date'];
        $end_date = $data['end_date'];

        $token_info = Common::getRefreshToken($appid,$company_id);
        if($token_info['meta']['code'] == 200){
            $refresh_token = $token_info['body']['refresh_token'];
        }else{
            return $token_info;
        }

        $app = new Application(wxOptions());
        $openPlatform = $app->open_platform;
        $stats = $openPlatform->createAuthorizerApplication($appid,$refresh_token)->stats;

        try{
            $articleSummary = $stats->articleSummary($start_date, $end_date);
        }catch (\Exception $e) {
            return msg(3001,$e->getMessage());
        }
        
        return msg(200,'success',$articleSummary['list']);
    }

    /**
     * 获取图文群发总数据(最大时间跨度：1)
     * @param appid 公众号appid
     * @param company_id 商户company_id
     * @param start_date 查询开始日期
     * @param end_date 查询结束日期
	 * @return code 200->成功
	 */
    public function getArticleTotal($data){
        $company_id = $data['company_id'];
        $appid = $data['appid'];
        $start_date = $data['start_date'];
        $end_date = $data['end_date'];

        $token_info = Common::getRefreshToken($appid,$company_id);
        if($token_info['meta']['code'] == 200){
            $refresh_token = $token_info['body']['refresh_token'];
        }else{
            return $token_info;
        }

        $app = new Application(wxOptions());
        $openPlatform = $app->open_platform;
        $stats = $openPlatform->createAuthorizerApplication($appid,$refresh_token)->stats;

        try{
            $articleTotal = $stats->articleTotal($start_date, $end_date);
        }catch (\Exception $e) {
            return msg(3001,$e->getMessage());
        }
        
        return msg(200,'success',$articleTotal['list']);
    }

    /**
     * 获取图文分享转发数据(最大时间跨度：7)
     * @param appid 公众号appid
     * @param company_id 商户company_id
     * @param start_date 查询开始日期
     * @param end_date 查询结束日期
	 * @return code 200->成功
	 */
    public function getUserShareSummary($data){
        $company_id = $data['company_id'];
        $appid = $data['appid'];
        $start_date = $data['start_date'];
        $end_date = $data['end_date'];

        $token_info = Common::getRefreshToken($appid,$company_id);
        if($token_info['meta']['code'] == 200){
            $refresh_token = $token_info['body']['refresh_token'];
        }else{
            return $token_info;
        }

        $app = new Application(wxOptions());
        $openPlatform = $app->open_platform;
        $stats = $openPlatform->createAuthorizerApplication($appid,$refresh_token)->stats;

        try{
            $userShareSummary = $stats->userShareSummary($start_date, $end_date);
        }catch (\Exception $e) {
            return msg(3001,$e->getMessage());
        }
        
        return msg(200,'success',$userShareSummary['list']);
    }

    /**
     * 获取消息发送概况数据(最大时间跨度：7)
     * @param appid 公众号appid
     * @param company_id 商户company_id
     * @param start_date 查询开始日期
     * @param end_date 查询结束日期
	 * @return code 200->成功
	 */
    public function getUpstreamMessageSummary($data){
        $company_id = $data['company_id'];
        $appid = $data['appid'];
        $start_date = $data['start_date'];
        $end_date = $data['end_date'];

        $token_info = Common::getRefreshToken($appid,$company_id);
        if($token_info['meta']['code'] == 200){
            $refresh_token = $token_info['body']['refresh_token'];
        }else{
            return $token_info;
        }

        $app = new Application(wxOptions());
        $openPlatform = $app->open_platform;

        try{
            $stats = $openPlatform->createAuthorizerApplication($appid,$refresh_token)->stats;
            $upstreamMessageSummary = $stats->upstreamMessageSummary($start_date, $end_date);
        }catch (\Exception $e) {
            return msg(3001,$e->getMessage());
        }
        
        return msg(200,'success',$upstreamMessageSummary['list']);
    }

    /**
     * 发送客服信息
     * @param appid 公众号appid
     * @param company_id 商户company_id
     * @param openid 接收用户openid
     * @param message 消息内容
     * @param type 1文字 2图片 3文件 4视频  5声音
	 * @return code 200->成功
	 */
    public function sendMessage($data){
        $company_id = $data['company_id'];
        $appid = $data['appid'];
        $openid = $data['openid'];
        $message = $data['message'];
        $type = $data['type'];

        $token_info = Common::getRefreshToken($appid,$company_id);
        if($token_info['meta']['code'] == 200){
            $refresh_token = $token_info['body']['refresh_token'];
        }else{
            return $token_info;
        }

        $app = new Application(wxOptions());
        $openPlatform = $app->open_platform;

        try{
            //$staff = $openPlatform->createAuthorizerApplication($appid,$refresh_token)->staff;
            $session = $openPlatform->createAuthorizerApplication($appid,$refresh_token)->staff_session;
            $session->create('lyfzkf@6454', $openid);
            
            //$message = new Text(['content' => $message]);
            //$staff->message($message)->to($openid)->send();    
            
            //$staff->message($message)->by('lyfzkf@6092')->to($openid)->send();            

           // $staff->records('2015-06-07', $endTime, $pageIndex, $pageSize);

            //dump($session->get($openid));
            
        }catch (\Exception $e) {
            return msg(3001,$e->getMessage());
        }
    }

    /**
     * 会话接入
     * @param company_id 商户id
     * @param session_id 待接入的会话id
	 * @return code 200->成功
	 */
    public function sessionAccess($company_id,$session_id){
        $session_res = Db::name('message_session')
        ->join('tb_customer_service','tb_customer_service.customer_service_id = tb_message_session.customer_service_id')
        ->where([
            'tb_message_session.company_id' => $company_id,
            'tb_message_session.session_id' => $session_id,
            'tb_message_session.state' => 0,
        ])->find();
        if(!$session_res){
            return msg(3001,'会话不可接入');
        }

        $token_info = Common::getRefreshToken($session_res['appid'],$company_id);
        if($token_info['meta']['code'] == 200){
            $refresh_token = $token_info['body']['refresh_token'];
        }else{
            return $token_info;
        }

        try{
            $app = new Application(wxOptions());
            $openPlatform = $app->open_platform;
            $staff = $openPlatform->createAuthorizerApplication($session_res['appid'],$refresh_token)->staff;

            $message = new Text(['content' => '您好，我是客服'.$session_res['name'].'请问有什么需要帮助吗？']);
            $staff->message($message)->by($session_res['wx_sign'])->to($session_res['customer_wx_openid'])->send();            
        }catch (\Exception $e) {
            return msg(3002,$e->getMessage());
        }

        $update_res = Db::name('message_session')
        ->where([
            'session_id' => $session_id,
            'company_id' => $company_id
        ])
        ->update([
            'state' => 1
        ]);

        if($update_res !== false){
            return msg(200,'success');
        }else{
            return msg(3001,'接入失败');
        }
    }

    /**
     * 获取会话列表
     * @param company_id 商户id
     * @param type 会话类型 -2接待超时关闭 -1会话关闭 0等待接入会话 1会话中
     * @param page 分页参数默认1
	 * @return code 200->成功
	 */
    public function getSessionList($data){
        $company_id = $data['company_id'];
        $type = $data['type'];
        $page = $data['page'];
        $uid = $data['uid'];

        //分页
        $page_count = 16;
        $show_page = ($page - 1) * $page_count;       
        
        $session_res = Db::name('message_session')
        ->where([
            'uid' => $uid,
            'company_id' => $company_id,
            'state' => $type
        ])
        ->limit($show_page,$page_count)
        ->select();

        $count = $count = Db::name('message_session')
        ->where([
            'uid' => $uid,
            'company_id' => $company_id,
            'state' => $type
        ])
        ->count();

        foreach($session_res as $k=>$v){
            $session_res[$k]['nick_name'] = Db::name('openweixin_authinfo')->where(['appid'=>$v['appid']])->cache(true,60)->value('nick_name');
        }

        $res['data_list'] = count($session_res) == 0 ? array() : $session_res;
        $res['page_data']['count'] = $count;
        $res['page_data']['rows_num'] = $page_count;
        $res['page_data']['page'] = $page;
        
        return msg(200,'success',$res);
    }
}