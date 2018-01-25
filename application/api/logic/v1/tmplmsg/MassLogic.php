<?php
namespace app\api\logic\v1\tmplmsg;
use think\Model;
use think\Db;
use EasyWeChat\Foundation\Application;
use app\api\common\Common;

class MassLogic extends Model {
    /**
     * 添加群发模板消息任务
     * @param company_id 所属商户company_id
     * @param appid 公众号appid
     * @param template_id 群发的模板id
     * @param template_data 群发模板数据
     * @param template_url 模板跳转链接
     * @param label_list 群发的标签
     * @param type 群发类型 1全部用户 2指定分组
	 * @return code 200->成功
	 */
    public function addMassTmplMsg($data){
        $company_id = $data['company_id'];
        $appid = $data['appid'];
        $template_id = $data['template_id'];
        $template_data = $data['template_data'];
        $template_url = $data['template_url'];
        $label_list = empty($data['label_list']) == true ? [] : $data['label_list'];
        $type = $data['type'];

        if($type == 1){
            $openid_res = Db::name('wx_user')
            ->partition([], "", ['type'=>'md5','num'=>config('separate')['wx_user']])
            ->where(['appid'=>$appid,'company_id'=>$company_id])
            ->field('openid')
            ->select();

            $openid_list = array_column($openid_res, 'openid');
        }else if($type == 2){
            $label_res = Db::name('label')->where(['company_id'=>$company_id,'label_id'=>['in',$label_list]])->field('label_id')->select();
            $label_list = array_column($label_res, 'label_id');

            $tag_res = Db::name('label_tag')->where(['appid'=>$appid,'label_id'=>['in',$label_list]])->field('tag_id')->select();
            $tag_list = array_column($tag_res, 'tag_id');

            $openid_list = [];
            foreach($tag_list as $k=>$tag_id){
                $openid_res = Db::name('wx_user')
                ->partition([], "", ['type'=>'md5','num'=>config('separate')['wx_user']])
                ->where(['appid'=>$appid,'company_id'=>$company_id,'tagid_list'=>['like',"%$tag_id%"]])
                ->field('openid')
                ->select();
                
                $openid_arr = array_column($openid_res, 'openid');

                foreach($openid_arr as $openid){
                    array_push($openid_list, $openid);
                }
            }
        }else{
            return msg(3001,'type参数错误');
        }

        $redis = Common::createRedis();

        //插入redis
        foreach($openid_list as $openid){
            $arr['appid'] = $appid;
            $arr['openid'] = $openid;
            $arr['template_id'] = $template_id;
            $arr['url'] = $template_url;
            $arr['data'] = $template_data;
            $arr['company_id'] = $company_id;

            $str = json_encode($arr);

            $key = $appid.$openid.md5(uniqid());
            $redis->select(config('redis_business')['mass_template']);
            $redis->set($key,$str);
            $redis->expire($key,7200);
        }

        return msg(200,'success');
    }

}