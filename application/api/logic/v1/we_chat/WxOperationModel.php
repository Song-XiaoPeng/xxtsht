<?php
namespace app\api\logic\v1\we_chat;
use think\Model;
use think\Db;
use EasyWeChat\Foundation\Application;
use app\api\common\Common;
use think\Log;
use EasyWeChat\Message\Article;

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
     * 发布微信图文素材
     * @param company_id 商户company_id
     * @param appid 公众号appid
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
            'title' => 'xxx',
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
}