<?php
namespace app\api\logic\v1\we_chat;
use think\Model;
use think\Db;
use EasyWeChat\Foundation\Application;
use app\api\common\Common;
use think\Log;
use EasyWeChat\Message\Article;
use EasyWeChat\Message\Text;
use EasyWeChat\Message\Image;
use EasyWeChat\Message\Voice;
use EasyWeChat\Message\Video;
use EasyWeChat\Message\Material;

//微信后台操作业务类
class WxOperationLogic extends Model {
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

        try{
            $app = new Application(wxOptions());
            $openPlatform = $app->open_platform;
    
            $menu = $openPlatform->createAuthorizerApplication($appid,$refresh_token)->menu;

            $menu_data = $menu->all()['menu']['button'];
        }catch (\Exception $e) {
            return msg(3001,$e->getMessage());
        }

        return msg(200,'success',$menu_data);
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
     * @param company_id 商户company_id
     * @param uid 客服uid
     * @param session_id 会话id
     * @param message 消息内容
     * @param type 1文字 2图片 3声音 4视频 6图文信息素材 7链接
     * @param resources_id 资源id (图片 视频 声音)
     * @param media_id 素材id (图文素材)
     * @param link_url 链接 (链接)
     * @param link_name 链接名称 (链接)
	 * @return code 200->成功
	 */
    public function sendMessage($data){
        $company_id = $data['company_id'];
        $content = empty($data['message']) === true ? '' : $data['message'];
        $type = $data['type'];
        $uid = $data['uid'];
        $session_id = $data['session_id'];
        $media_id = empty($data['media_id']) == true ? '' : $data['media_id'];
        $resources_id = empty($data['resources_id']) == true ? '' : $data['resources_id'];
        $link_url = empty($data['link_url']) == true ? '' : $data['link_url'];
        $link_name = empty($data['link_name']) == true ? '' : $data['link_name'];

        $session_res = Db::name('message_session')
        ->partition('', '', ['type'=>'md5','num'=>config('separate')['message_session']])
        ->where([
            'uid' => $uid,
            'company_id' => $company_id,
            'session_id' => $session_id,
            'state' => 1,
        ])->cache(true,10)->find();

        if(empty($session_res)){
            return msg(3001,'会话不存在');
        }

        $customer_service_res = Db::name('customer_service')->where([
            'customer_service_id' => $session_res['customer_service_id']
        ])->cache(true,60)->find();

        $token_info = Common::getRefreshToken($session_res['appid'],$company_id);
        if($token_info['meta']['code'] == 200){
            $refresh_token = $token_info['body']['refresh_token'];
        }else{
            return $token_info;
        }

        $app = new Application(wxOptions());
        $openPlatform = $app->open_platform;

        try{
            if($type == 2 || $type == 4 || $type == 3){
                $resources_res = Db::name('resources')->where(['resources_id'=>$resources_id])->find();
                
                if(!$resources_res){return msg(3005,'资源不存在');}

                $temporary = $openPlatform->createAuthorizerApplication($session_res['appid'],$refresh_token)->material_temporary;
            }

            switch($type){
                case 1:
                    $message = new Text(['content' => $content]);
                    $data_obj = ['text'=>$content];
                    break;
                case 2:
                    $upload_res = $temporary->uploadImage('..'.$resources_res['resources_route']);
                    $message = new Image(['media_id' => $upload_res['media_id']]);
                    $data_obj = ['file_url'=>$resources_res['resources_route'],'resources_id'=>$resources_res['resources_id']];
                    break;
                case 4:
                    $upload_res = $temporary->uploadVideo('..'.$resources_res['resources_route']);
                    $message = new Video(['media_id' => $upload_res['media_id']]);
                    $data_obj = ['file_url'=>$resources_res['resources_route'],'resources_id'=>$resources_res['resources_id']];
                    break;
                case 3:
                    if($resources_res['mime_type'] == 'audio/x-wav'){
                        $audio_name = md5(uniqid());
                        
                        $amr_file = '..'.$resources_res['resources_route'];
                    
                        $mp3_file = "../uploads/source_material/$audio_name.mp3";
        
                        $command = "/usr/local/bin/ffmpeg -i $amr_file $mp3_file";
        
                        exec($command, $log, $status);
    
                        if(!file_exists($mp3_file)){
                            return msg(3004,'file_error');
                        }
    
                        $upload_res = $temporary->uploadVoice($mp3_file);
                        $message = new Voice(['media_id' => $upload_res['media_id']]);
                        unlink($mp3_file);
                    }else if($resources_res['mime_type'] == 'audio/mpeg'){
                        $upload_res = $temporary->uploadVoice('..'.$resources_res['resources_route']);
                        $message = new Voice(['media_id' => $upload_res['media_id']]);
                    }else{
                        return msg(3008,'File types do not support');
                    }

                    $data_obj = ['file_url'=>$resources_res['resources_route'],'resources_id'=>$resources_res['resources_id']];
                    break;
                case 6:
                    $message = new Material('mpnews', $media_id);
                    $data_obj = ['media_id'=>$media_id];
                    break;
                default:
                    return msg(3006,'type参数错误');
            }

            $staff = $openPlatform->createAuthorizerApplication($session_res['appid'],$refresh_token)->staff;
            $staff->message($message)->by($customer_service_res['wx_sign'])->to($session_res['customer_wx_openid'])->send();
        }catch (\Exception $e) {
            return msg(3001,$e->getMessage());
        }

        Common::addMessagge($session_res['appid'],$session_res['customer_wx_openid'],$session_id,$session_res['customer_service_id'],$session_res['uid'],$type,1,$data_obj);

        return msg(200,'success');
    }

    /**
     * 会话接入
     * @param company_id 商户id
     * @param session_id 待接入的会话id
	 * @return code 200->成功
	 */
    public function sessionAccess($company_id,$uid,$session_id){
        if(empty($session_id)){
            return msg(3003,'session_id参数为空');
        }

        $session_res = Db::name('message_session')
        ->partition('', '', ['type'=>'md5','num'=>config('separate')['message_session']])
        ->where([
            'company_id' => $company_id,
            'session_id' => $session_id,
            'uid' => $uid,
            'state' => ['in',[0,3]],
        ])->find();
        if(!$session_res){
            return msg(3001,'会话不可接入');
        }

        $customer_service_res = Db::name('customer_service')->where(['customer_service_id'=>$session_res['customer_service_id']])->find();
        if(!$customer_service_res){
            return msg(3003,'客服账号不存在');
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

            $message = new Text(['content' => '您好，我是客服'.$customer_service_res['name'].'请问有什么需要帮助吗？']);
            $staff->message($message)->by($customer_service_res['wx_sign'])->to($session_res['customer_wx_openid'])->send();            
        }catch (\Exception $e) {
            return msg(3002,$e->getMessage());
        }

        $update_res = Db::name('message_session')
        ->partition(['session_id' => $session_id], 'session_id', ['type'=>'md5','num'=>config('separate')['message_session']])
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
     * 获取待接入会话列表
     * @param company_id 商户id
     * @param uid 客服uid
	 * @return code 200->成功
	 */
    public function getSessionList($data){
        set_time_limit(60);

        $company_id = $data['company_id'];
        $uid = $data['uid'];

        $redis = Common::createRedis();
        $redis->select(0); 

        while (true) {
            $arr = $redis->sMembers($company_id);

            $waiting = [];
            $insert_waiting = [];
            $queue_up = [];

            foreach($arr as $k=>$v){
                $session_data = json_decode($v,true);

                $nick_name = Db::name('openweixin_authinfo')->where(['appid'=>$session_data['appid']])->cache(true,60)->value('nick_name');
                
                $extra_data['app_name'] = empty($nick_name) == true ? '来源公众号已解绑' : $nick_name;

                $extra_data['session_frequency'] = Db::name('message_session')->partition('', '', ['type'=>'md5','num'=>config('separate')['message_session']])->where(['customer_wx_openid'=>$session_data['customer_wx_openid'],'company_id'=>$company_id])->cache(true,60)->count();

                $extra_data['invitation_frequency'] = 0;

                if($session_data['uid'] == $uid && $session_data['state'] == 0){
                    array_push($waiting,array_merge($session_data,$extra_data));
                    array_push($insert_waiting,$session_data);
                }else{
                    array_push($queue_up,$session_data);
                }
            }

            if(count($waiting) != 0 || count($queue_up) != 0){
                foreach($insert_waiting as $key=>$value){
                    Db::name('message_session')->partition(['session_id'=>$value['session_id']], 'session_id', ['type'=>'md5','num'=>config('separate')['message_session']])->insert($insert_waiting[$key]);
                }

                foreach($arr as $v){
                    $redis->SREM($company_id,$v);
                }

                return msg(200,'success',['waiting'=>$waiting,'queue_up'=>$queue_up]);
            }

            sleep(2);
        }
    }

    /**
     * 获取会话消息
     * @param company_id 商户company_id
     * @param uid 客服uid
	 * @return code 200->成功
	 */
    public function getMessage($data){
        set_time_limit(60);

        $company_id = $data['company_id'];
        $uid = $data['uid'];

        $redis = Common::createRedis();
        $redis->select(1); 

        while (true) {
            $arr = [];

            $openid_list = Db::name('message_session')
            ->partition('', '', ['type'=>'md5','num'=>config('separate')['message_session']])
            ->where(['uid'=>$uid,'company_id'=>$company_id])
            ->field('customer_wx_openid')
            ->select();

            foreach($openid_list as $k=>$v){
                $raw_data = $redis->zRange($v['customer_wx_openid'], 0, -1);
                if(empty($raw_data)){
                    continue;
                }

                foreach($raw_data as $z=>$c){
                    $content[$z] = json_decode($c,true);
                }

                foreach($content as $i=>$c){
                    if(!empty($c['text'])){
                        $content[$i]['text'] = emoji_decode($c['text']);
                    }else{
                        $content[$i]['text'] = '';
                    }
                }

                Db::name('message_data')
                ->partition(['customer_wx_openid'=>$v['customer_wx_openid']], "customer_wx_openid", ['type'=>'md5','num'=>config('separate')['message_data']])
                ->insertAll($content);

                foreach($content as $i=>$c){
                    if($c['message_type'] == 2){
                        $content[$i]['file_url'] = getWximg($c['file_url']);
                    }
                }

                if($content){
                    $arr[$v['customer_wx_openid']] = $content;
                }

                $redis->del($v['customer_wx_openid']);
            }

            if(count($arr) != 0){
                return msg(200,'success',$arr);
            }

            sleep(2);
        }
    }

    /**
     * 结束会话
     * @param company_id 商户company_id
     * @param uid 客服uid
     * @param session_list 结束的会话id list
	 * @return code 200->成功
	 */
    public function closeSession($data){
        $company_id = $data['company_id'];
        $uid = $data['uid'];
        $session_list = $data['session_list'];
        
        $success_close_session = [];
        $error_close_session = [];

        foreach($session_list as $k=>$v){
            $session_res = Db::name('message_session')
            ->partition('', '', ['type'=>'md5','num'=>config('separate')['message_session']])
            ->where([
                'session_id' => $v,
                'company_id' => $company_id,
                'uid' => $uid,
                'state' => array('in',[0,1]),
            ])->find();

            if(empty($session_res)){
                array_push($error_close_session,$v);
                continue;
            }

            $customer_service_name = $this->getCustomerServiceName($session_res['customer_service_id']);
            if(!$customer_service_name){
                array_push($error_close_session,$v);
                continue;
            }
    
            $this->noticeCloseSession($session_res['appid'],$company_id,$session_res['customer_wx_openid'],$customer_service_name);

            Db::name('message_session')->partition(['session_id' => $v], 'session_id', ['type'=>'md5','num'=>config('separate')['message_session']])->where(['session_id' => $v])->update(['state' => -1]);

            array_push($success_close_session,$v);
        }

        return msg(200,'success',['success_close_session'=>$success_close_session,'error_close_session'=>$error_close_session]);
    }

    //通知会话结束
    private function noticeCloseSession($appid,$company_id,$openid,$customer_service_name){
        try{
            $token_info = Common::getRefreshToken($appid,$company_id);
            if($token_info['meta']['code'] == 200){
                $refresh_token = $token_info['body']['refresh_token'];
            }else{
                return $token_info;
            }
    
            $app = new Application(wxOptions());
            $openPlatform = $app->open_platform;

            $staff = $openPlatform->createAuthorizerApplication($appid,$refresh_token)->staff;
            
            $message = new Text(['content' => '客服'.$customer_service_name.'已结束与您的会话，感谢您的支持！']);
            $staff->message($message)->to($openid)->send();

            return msg(200,'success');
        }catch (\Exception $e) {
            return msg(3001,$e->getMessage());
        }   
    }

    //获取客服名称
    private function getCustomerServiceName($customer_service_id){
        $customer_service_name = Db::name('customer_service')->where(['customer_service_id'=>$customer_service_id])->cache(true,60)->value('name');

        if(!empty($customer_service_name)){
            return $customer_service_name;
        }else{
            return false;
        }
    }

    /**
     * 上传文件
     * @param company_id 商户company_id
     * @param uid 客服uid
     * @param file 文件流字段名称
     * @param resources_type 资源类型 1 im数据文件 2客服头像
	 * @return code 200->成功
	 */
    public function uploadResources($data){
        $company_id = $data['company_id'];
        $uid = $data['uid'];
        $resources_type = empty($data['resources_type']) == true ? 1 : $data['resources_type'];

        $catalog_name = date('Ymd');
        $save_catalog = "../uploads/message/$catalog_name";
        if(!file_exists($save_catalog)){
            mkdir($save_catalog,0766);
            chmod($save_catalog,0766);
        }

        try {
            $storage = new \Upload\Storage\FileSystem($save_catalog);
            $file = new \Upload\File('file', $storage);
        } catch (\Exception $e) {
            return msg(3003,$e->getMessage());
        }

        $new_filename = uniqid();
        $file->setName($new_filename);

        try {
            $file->addValidations(array(
                new \Upload\Validation\Mimetype([
                    'image/png',
                    'image/gif',
                    'image/jpg',
                    'image/jpeg',
                    'image/bmp',
                    'audio/mpeg',
                    'audio/x-wav',
                    'video/x-msvideo',
                    'video/mp4'
                ]),
            
                new \Upload\Validation\Size('10M')
            ));
        } catch (\Exception $e) {
            return msg(3006,$e->getMessage());
        }

        $data = array(
            'name'       => $file->getNameWithExtension(),
            'extension'  => $file->getExtension(),
            'mime'       => $file->getMimetype(),
            'size'       => $file->getSize(),
            'md5'        => $file->getMd5(),
            'dimensions' => $file->getDimensions()
        );

        $resources_res = Db::name('resources')->where(['resources_md5'=>$data['md5'],'company_id'=>$company_id])->find();
        if($resources_res){
            if(substr($resources_res['mime_type'],0,5) == 'image'){
                $url = 'http://'.$_SERVER['HTTP_HOST'].'/api/v1/we_chat/Business/getImg?resources_id='.$resources_res['resources_id'];
            }else{
                $url = 'http://'.$_SERVER['HTTP_HOST'].'/api/v1/we_chat/Business/getFile?resources_id='.$resources_res['resources_id'];
            }

            return msg(200,'messgae',['resources_id'=>$resources_res['resources_id'],'url'=>$url]);
        }

        try {
            $file->upload();

            $resources_id = md5(uniqid());

            Db::name('resources')->insert([
                'resources_id' => $resources_id,
                'resources_md5' => $data['md5'],
                'resources_size' => $data['size'],
                'add_time' => date('Y-m-d H:i:s'),
                'uid' => $uid,
                'company_id' => $company_id,
                'file_suffix_name' => $data['extension'],
                'file_name' => $data['name'],
                'name' => $_FILES['file']['name'],
                'resources_route' => substr($save_catalog,2).'/'.$data['name'],
                'mime_type' => $data['mime'],
                'resources_type' => $resources_type
            ]);

            if(substr($data['mime'],0,5) == 'image'){
                $url = 'http://'.$_SERVER['HTTP_HOST'].'/api/v1/we_chat/Business/getImg?resources_id='.$resources_id;
            }else{
                $url = 'http://'.$_SERVER['HTTP_HOST'].'/api/v1/we_chat/Business/getFile?resources_id='.$resources_id;
            }
            
            return msg(200,'messgae',['resources_id'=>$resources_id,'url'=>$url]);
        } catch (\Exception $e) {
            if(empty($file->getErrors()[0])){
                return msg(3003,$e->getMessage());
            }else{
                return msg(3002,$file->getErrors()[0]);
            }
        }
    }

    public function test(){
        try{
            $redis = Common::createRedis();
            
            $redis->select(0); 
    

            dump($redis->SREM('51454009d703c86c91353f61011ecf2f','{"session_id":"b00e6c0accec38a23063be032508c1d1","customer_service_id":4,"customer_wx_openid":"oZ8DFwU5HOTs0b4g-P_skZ8wgH7g","add_time":"2017-11-28 09:12:29","uid":6454,"appid":"wx88c6052d06eaaf7d","company_id":"51454009d703c86c91353f61011ecf2f","customer_wx_nickname":"\u4e0d\u4ea6\u4e50\u4e4e","customer_wx_portrait":"http:\/\/wx.qlogo.cn\/mmopen\/WAGxibxwib6EVzMQGErVibk1asnkzib3r2GeiclLKm5J5D5mic4VB9n4f8pWBXJ9aI0rW29CLibGUlwwqIIIF3VataBRC7OqhibY7urF\/0","state":0}'));
            //dump($redis->sMembers('51454009d703c86c91353f61011ecf2f'));

           // $res = $redis->sAdd('51454009d703c86c91353f61011ecf2f', '213123123123123123123','214532zzzz21312312');
           // dump($res);
        } catch (\Exception $e) {
            return msg(3001,$e->getMessage());
        }
    }
}