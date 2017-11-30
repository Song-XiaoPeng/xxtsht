<?php
namespace app\api\logic\v1\message;
use think\Model;
use think\Db;

class CommonModel extends Model {
    /**
     * 设置快捷回复语句
     * @param quick_reply_id 存在则是编辑
	 * @param text 快捷回复语句
	 * @return code 200->成功
	 */
    public function setQuickReplyText($data){
        $quick_reply_id = empty($data['quick_reply_id']) == true ? false : $data['quick_reply_id'];
        $text = $data['text'];
        $uid = $data['uid'];
        $company_id = $data['company_id'];

        if($quick_reply_id){
            $update_res = Db::name('quick_reply')
            ->where([
                'quick_reply_id' => $quick_reply_id,
                'company_id' => $company_id,
                'uid' => $uid
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

        $insert_res = Db::name('quick_reply')
        ->insert([
            'quick_reply_text' => $text,
            'company_id' => $company_id,
            'uid' => $uid,
        ]);

        if($insert_res){
            return msg(200,'success');
        }else{
            return msg(3001,'更新数据失败');
        }
    }

    /**
     * 获取快捷回复语句
     * @param quick_reply_id 存在则是编辑
	 * @param text 快捷回复语句
	 * @return code 200->成功
	 */
    public function getQuickReplyList($company_id,$uid){
        $list = Db::name('quick_reply')->where(['company_id'=>$company_id,'uid'=>$uid])->select();
    
        return msg(200,'success',$list);
    }
}