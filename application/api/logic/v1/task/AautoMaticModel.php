<?php
namespace app\api\logic\v1\task;
use think\Model;
use think\Db;
use EasyWeChat\Foundation\Application;
use app\api\common\Common;

//自动任务处理
class AautoMaticModel extends Model {
    //任务进度计算
    private function progressCalculation($task_id,$total,$max_count,$num){
        $pull_num = ceil($total/$max_count);

        $value = ($num/$pull_num) * 100;

        $speed_progress= sprintf('%.2f', $value);

        Db::name('task')->where(['task_id'=>$task_id])->update(['speed_progress'=>$speed_progress]);
    }

    //同步微信用户列表
    public function syncWxUserList(){
        set_time_limit(600);

        $add_time = date('Y-m-d H:i:s');

        $task_res = Db::name('task')->where(['task_type'=>1,'state'=>0])->find();
        if(!$task_res){
            return;
        }

        $appid = $task_res['appid'];
        $company_id = $task_res['company_id'];

        Db::name('task')->where(['task_id'=>$task_res['task_id']])->update(['state'=>1]);

        $token_info = Common::getRefreshToken($appid,$company_id);
        if($token_info['meta']['code'] == 200){
            $refresh_token = $token_info['body']['refresh_token'];
        }else{
            return;
        }

        $app = new Application(wxOptions());
        $openPlatform = $app->open_platform;
        $userService  = $openPlatform->createAuthorizerApplication($appid,$refresh_token)->user;
        try{
            $list = $userService->lists();
        }catch (\Exception $e) {
        } 

        if(empty($list['data']['openid'])){
            Db::name('task')->where(['task_id'=>$task_res['task_id']])->update(['state'=>2,'speed_progress'=>100,'handle_end_time'=>date('Y-m-d H:i:s')]);
            return;
        }

        $total = $list['total'];
 
        $pull_num = ceil($total/1000);

        foreach($list['data']['openid'] as $key=>$openid){
            $openid_list[$key]['openid'] = $openid;
            $openid_list[$key]['appid'] = $appid;
            $openid_list[$key]['company_id'] = $company_id;
            $openid_list[$key]['add_time'] = $add_time;
        }
        try{
            Db::name('wx_user')->insertAll($openid_list);
        }catch (\Think\Exception $e) {
        }

        if($pull_num > 1){
            $openid_arr = $list['data']['openid'];

            $next_openid = end($openid_arr);

            for ($i=0; $i<$pull_num; $i++){
                try{
                    $id_list = $userService->lists($next_openid);
                }catch (\Exception $e) {
                }
                
                if(!empty($id_list['data']['openid'])){
                    $wxid_list = $id_list['data']['openid'];
                    $next_openid = end($wxid_list);

                    foreach($id_list['data']['openid'] as $key=>$openid){
                        $openid_arr_data[$key]['openid'] = $openid;
                        $openid_arr_data[$key]['appid'] = $appid;
                        $openid_arr_data[$key]['company_id'] = $company_id;
                        $openid_arr_data[$key]['add_time'] = $add_time;
                    }

                    $this->progressCalculation($task_res['task_id'],$total,1000,$i+1);

                    try{
                        Db::name('wx_user')->insertAll($openid_arr_data);
                    }catch (\Think\Exception $e) {
                        continue;
                    }
                }else{
                    continue;
                }
            }
        }

        Db::name('task')->where(['task_id'=>$task_res['task_id']])->update(['state'=>2,'speed_progress'=>100,'handle_end_time'=>date('Y-m-d H:i:s')]);
    }

    //同步获取更新微信用户详情信息 type 1拉取用户详情信息 2更新用户详情信息
    public function syncWxUserDetails($type){
        $is_sync = $type == 1 ? -1 : 1;

        set_time_limit(600);

        $task_type = $type == 1 ? 2 : 3;

        $task_res = Db::name('task')->where(['task_type'=>$task_type,'state'=>0])->find();
        if(!$task_res){
            return;
        }

        Db::name('task')->where(['task_id'=>$task_res['task_id']])->update(['state'=>1]);

        $appid = $task_res['appid'];
        $company_id = $task_res['company_id'];

        $token_info = Common::getRefreshToken($appid,$company_id);
        if($token_info['meta']['code'] == 200){
            $refresh_token = $token_info['body']['refresh_token'];
        }else{
            return;
        }

        $app = new Application(wxOptions());
        $openPlatform = $app->open_platform;
        $userService  = $openPlatform->createAuthorizerApplication($appid,$refresh_token)->user;

        $total = Db::name('wx_user')->where(['appid'=>$appid,'company_id'=>$company_id,'is_sync'=>$is_sync])->count();
        $wx_user_arr = Db::name('wx_user')->where(['appid'=>$appid,'company_id'=>$company_id,'is_sync'=>$is_sync])->field('openid')->select();
        
        if($total == 0){
            Db::name('task')->where(['task_id'=>$task_res['task_id']])->update(['state'=>2,'speed_progress'=>100,'handle_end_time'=>date('Y-m-d H:i:s')]);
            return;
        }

        $pull_num = 100;

        $page = ceil($total/$pull_num);

        for ($i = 0; $i < $page; $i++) {
            //分页
            $show_page = $i * $pull_num;

            $wx_user_res = array_slice($wx_user_arr,$show_page,$pull_num);

            $openid_list = [];

            foreach($wx_user_res as $k=>$v){
                $openid_list[$k] = $v['openid'];
            }

            try{
                $users = $userService->batchGet($openid_list);
            }catch (\Exception $e) {
            }

            foreach($users['user_info_list'] as $value){
                Db::name('wx_user')->where([
                    'openid' => $value['openid'],
                    'appid' => $appid,
                    'company_id' => $company_id
                ])->update([
                    'nickname' => $value['nickname'],
                    'portrait' => $value['headimgurl'],
                    'gender' => $value['sex'],
                    'city' => $value['city'],
                    'province' => $value['province'],
                    'language' => $value['language'],
                    'country' => $value['country'],
                    'groupid' => $value['groupid'],
                    'subscribe_time' => $value['subscribe_time'],
                    'tagid_list' => $value['tagid_list'],
                    'is_sync' => 1,
                    'unionid' => $value['unionid'],
                    'desc' => $value['remark'],
                    'subscribe' => $value['subscribe'],
                    'update_time' => date('Y-m-d H:i:s'),
                ]);
            }

           $this->progressCalculation($task_res['task_id'],$total,100,$i+1);
        }

        Db::name('task')->where(['task_id'=>$task_res['task_id']])->update(['state'=>2,'speed_progress'=>100,'handle_end_time'=>date('Y-m-d H:i:s')]);
    }
}