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
        set_time_limit(1800);

        $add_time = date('Y-m-d H:i:s');

        $task_res = Db::name('task')->where(['task_type'=>1,'state'=>1])->find();
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
        $list = $userService->lists();

        if(empty($list['data']['openid'])){
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
                $id_list = $userService->lists($next_openid);

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

        Db::name('task')->where(['task_id'=>$task_res['task_id']])->update(['state'=>2,'speed_progress'=>100]);
    }
}