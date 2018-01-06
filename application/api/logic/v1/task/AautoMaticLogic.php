<?php
namespace app\api\logic\v1\task;
use think\Model;
use think\Db;
use EasyWeChat\Foundation\Application;
use app\api\common\Common;

//自动任务处理
class AautoMaticLogic extends Model {
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
            Db::name('task')->where(['task_id'=>$task_res['task_id']])->update([
                'state'=>-1,
                'speed_progress'=>100,
                'handle_end_time'=>date('Y-m-d H:i:s'),
                'fail_desc'=> $token_info['meta']['message']
            ]);
            return;
        }

        try{
            $app = new Application(wxOptions());
            $openPlatform = $app->open_platform;
            $userService  = $openPlatform->createAuthorizerApplication($appid,$refresh_token)->user;
            $list = $userService->lists();
        }catch (\Exception $e) {
            Db::name('task')->where(['task_id'=>$task_res['task_id']])->update([
                'state'=>-1,
                'speed_progress'=>100,
                'handle_end_time'=>date('Y-m-d H:i:s'),
                'fail_desc'=>$e->getMessage()
            ]);
            return;
        } 

        if(empty($list['data']['openid'])){
            Db::name('task')->where(['task_id'=>$task_res['task_id']])->update(['state'=>2,'speed_progress'=>100,'handle_end_time'=>date('Y-m-d H:i:s')]);
            return;
        }

        $total = $list['total'];
 
        $pull_num = ceil($total/1000);

        foreach($list['data']['openid'] as $key=>$openid){
            $openid_list[$key]['wx_user_id'] = md5(uniqid());
            $openid_list[$key]['openid'] = $openid;
            $openid_list[$key]['appid'] = $appid;
            $openid_list[$key]['company_id'] = $company_id;
            $openid_list[$key]['add_time'] = $add_time;
        }

        $wx_user_id = md5(uniqid());

        foreach($list['data']['openid'] as $openid){
            try{
                Db::name('wx_user')
                ->partition(['company_id'=>$company_id], "company_id", ['type'=>'md5','num'=>config('separate')['wx_user']])
                ->insert([
                    'wx_user_id' => md5(uniqid()),
                    'openid' => $openid,
                    'appid' => $appid,
                    'company_id' => $company_id,
                    'add_time' => $add_time,
                ]);
            }catch (\Think\Exception $e) {
                continue;
            }
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

                    foreach($id_list['data']['openid'] as $openid){
                        try{
                            Db::name('wx_user')
                            ->partition(['company_id'=>$company_id], "company_id", ['type'=>'md5','num'=>config('separate')['wx_user']])
                            ->insert([
                                'wx_user_id' => md5(uniqid()),
                                'openid' => $openid,
                                'appid' => $appid,
                                'company_id' => $company_id,
                                'add_time' => $add_time,
                            ]);
                        }catch (\Think\Exception $e) {
                            continue;
                        }
                    }

                    $this->progressCalculation($task_res['task_id'],$total,1000,$i+1);
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

        set_time_limit(1800);

        $task_type = $type == 1 ? 2 : 3;

        $task_res = Db::name('task')->where(['task_type'=>$task_type,'state'=>0])->find();
        if(!$task_res){
            return;
        }

        $wx_user_id = md5(uniqid());

        Db::name('task')->where(['task_id'=>$task_res['task_id']])->update(['state'=>1]);

        $appid = $task_res['appid'];
        $company_id = $task_res['company_id'];

        $token_info = Common::getRefreshToken($appid,$company_id);
        if($token_info['meta']['code'] == 200){
            $refresh_token = $token_info['body']['refresh_token'];
        }else{
            return;
        }

        try{
            $app = new Application(wxOptions());
            $openPlatform = $app->open_platform;
            $userService  = $openPlatform->createAuthorizerApplication($appid,$refresh_token)->user;
        }catch (\Exception $e) {
            Db::name('task')->where(['task_id'=>$task_res['task_id']])->update([
                'state'=>-1,
                'speed_progress'=>100,
                'handle_end_time'=>date('Y-m-d H:i:s'),
                'fail_desc'=>$e->getMessage()
            ]);
            return;
        } 

        $total = Db::name('wx_user')
        ->partition(['company_id'=>$company_id], "company_id", ['type'=>'md5','num'=>config('separate')['wx_user']])
        ->where(['appid'=>$appid,'company_id'=>$company_id,'is_sync'=>$is_sync])
        ->count();

        $wx_user_arr = Db::name('wx_user')
        ->partition(['company_id'=>$company_id], "company_id", ['type'=>'md5','num'=>config('separate')['wx_user']])
        ->where(['appid'=>$appid,'company_id'=>$company_id,'is_sync'=>$is_sync])
        ->field('openid')
        ->select();
        
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
                Db::name('task')->where(['task_id'=>$task_res['task_id']])->update([
                    'state'=>-1,
                    'speed_progress'=>100,
                    'handle_end_time'=>date('Y-m-d H:i:s'),
                    'fail_desc'=>$e->getMessage()
                ]);
            }

            foreach($users['user_info_list'] as $value){
                $rule = [
                    'type' => 'md5', // 分表方式
                    'num'  => 5    // 分表数量
                ];

                Db::name('wx_user')->partition(['company_id'=>$company_id], "company_id", $rule)
                ->where([
                    'openid' => $value['openid'],
                    'appid' => $appid,
                    'company_id' => $company_id
                ])
                ->update([
                    'nickname' => $value['nickname'],
                    'portrait' => $value['headimgurl'],
                    'gender' => $value['sex'],
                    'city' => $value['city'],
                    'province' => $value['province'],
                    'language' => $value['language'],
                    'country' => $value['country'],
                    'groupid' => $value['groupid'],
                    'subscribe_time' => date("Y-m-d H:i:s",$value['subscribe_time']),
                    'tagid_list' => json_encode($value['tagid_list']),
                    'is_sync' => 1,
                    'unionid' => empty($value['unionid']) == true ? null : $value['unionid'],
                    'desc' => $value['remark'],
                    'subscribe' => $value['subscribe'],
                    'update_time' => date('Y-m-d H:i:s'),
                ]);
            }

           $this->progressCalculation($task_res['task_id'],$total,100,$i+1);
        }

        Db::name('task')->where(['task_id'=>$task_res['task_id']])->update(['state'=>2,'speed_progress'=>100,'handle_end_time'=>date('Y-m-d H:i:s')]);
    }

    //群发消息任务处理
    public function massNews(){
        $time = date('Y-m-d H:i:s');

        $map['state'] = 0;
        $res = Db::name('mass_news')->where($map)->select();

        foreach($res as $item){
            Db::name('mass_news')->where(['news_id'=>$item['news_id']])->update(['state'=>1]);

            //立即发送
            if($item['send_type'] == 1){
                $this->massSendHandle($item);
            }

            //定时发送
            if($item['send_type'] == 2 && strtotime($time) >= strtotime($item['strtotime'])){
                $this->massSendHandle($item);
            }
        }
    }

    //群发发送操作
    private function massSendHandle($item){
        try{
            $app = new Application(wxOptions());
            $openPlatform = $app->open_platform;

            $token_info = Common::getRefreshToken($item['appid'],$item['company_id']);
            if($token_info['meta']['code'] == 200){
                $refresh_token = $token_info['body']['refresh_token'];
            }else{
                return;
            }

            $broadcast  = $openPlatform->createAuthorizerApplication($item['appid'],$refresh_token)->broadcast;

            //按群发类型 1全部 2按分组 3指定用户
            switch($item['type']){
                case 1:
                    if ($item['send_message_type'] == 1) {
                        $broadcast->sendText($item['text']);
                    }

                    if ($item['send_message_type'] == 2) {
                        $broadcast->sendNews($item['media_id']);
                    }

                    if ($item['send_message_type'] == 3) {
                        $broadcast->sendImage($item['media_id']);
                    }
                    break;
                case 2:
                    if ($item['send_message_type'] == 1) {
                        $broadcast->sendText($item['text'], $item['group_id']);
                    }

                    if ($item['send_message_type'] == 2) {
                        $broadcast->sendNews($item['media_id'], $item['group_id']);
                    }

                    if ($item['send_message_type'] == 3) {
                        $broadcast->sendImage($item['media_id'], $item['group_id']);
                    }
                    break;
                case 3:
                    $openid_list = json_decode($item['openid_list'],true);

                    if ($item['send_message_type'] == 1) {
                        $broadcast->sendText($item['text'], $openid_list);
                    }

                    if ($item['send_message_type'] == 2) {
                        $broadcast->sendNews($item['media_id'], $openid_list);
                    }

                    if ($item['send_message_type'] == 3) {
                        $broadcast->sendImage($item['media_id'], $openid_list);
                    }
                    break;
            }

            Db::name('mass_news')->where(['news_id'=>$item['news_id']])->update(['state'=>2]);
        }catch (\Exception $e) {
            Db::name('mass_news')->where(['news_id'=>$item['news_id']])->update(['state'=>-1]);
        }
    }

    //关闭超过2天的排队会话
    public function colseQueuingSession(){
        $redis = Common::createRedis();
            
        $redis->select(2); 

        $company_list = $redis->keys('*');

        foreach($company_list as $company_id){
            try {
                $str_list = $redis->sMembers($company_id);
                foreach($str_list as $str){
                    $arr = json_decode($str,true);

                    $day = distanceDay($arr['add_time']);
                    if($day >= 2){
                        $redis->SREM($company_id, $str);
                    }
                }
            }catch (\Exception $e) {}
        }

        $session_list = Db::name('message_session')
        ->partition([], '', ['type'=>'md5','num'=>config('separate')['message_session']])
        ->where(['state'=>3])
        ->select();

        foreach($session_list as $v){
            $day = distanceDay($v['add_time']);
            if($day >= 2){
                Db::name('message_session')
                ->partition(['session_id'=>$v['session_id']], 'session_id', ['type'=>'md5','num'=>config('separate')['message_session']])
                ->where(['session_id'=>$v['session_id']])
                ->update(['state'=>-3,'close_explain'=>'系统自动关闭']);
            }
        }
    }

    //关闭超时等待中会话
    public function colseWaitingSession(){
        $redis = Common::createRedis();
            
        $redis->select(0); 

        $company_list = $redis->keys('*');

        foreach($company_list as $uid){
            try {
                $str_list = $redis->sMembers($uid);
                foreach($str_list as $str){
                    $arr = json_decode($str,true);

                    $tiem_arr = timediff(date('YmdHis'), $arr['add_time']);
                    $min1 = $tiem_arr['day'] * 24 * 60;
                    $min2 = $tiem_arr['hour'] * 60;
                    $min3 = $tiem_arr['min'];
                    $min = $min1 + $min2 + $min3;

                    $configure_value = Db::name('company_baseinfo')->where(['company_id'=>$arr['company_id'],'configure_key'=>'session_rule'])->cache(true,120)->value('configure_value');
                    if(!empty($configure_value)){
                        $configure_value = json_decode($configure_value,true);
                        $overtime = $configure_value['overtime'];
                    }else{
                        $overtime = 1440;
                    }

                    if($min >= $overtime){
                        $redis->SREM($uid, $str);
                    }
                }
            }catch (\Exception $e) {}
        }

        $session_list = Db::name('message_session')
        ->partition([], '', ['type'=>'md5','num'=>config('separate')['message_session']])
        ->where(['state'=>0])
        ->select();

        foreach($session_list as $v){
            $configure_value = Db::name('company_baseinfo')->where(['company_id'=>$v['company_id'],'configure_key'=>'session_rule'])->cache(true,120)->value('configure_value');  
            if(!empty($configure_value)){
                $configure_value = json_decode($configure_value,true);
                $overtime = $configure_value['overtime'];
            }else{
                $overtime = 1440;
            }

            $tiem_arr = timediff(date('YmdHis'), $v['add_time']);
            $min1 = $tiem_arr['day'] * 24 * 60;
            $min2 = $tiem_arr['hour'] * 60;
            $min3 = $tiem_arr['min'];
            $min = $min1 + $min2 + $min3;

            if($min >= $overtime){
                Db::name('message_session')
                ->partition(['session_id'=>$v['session_id']], 'session_id', ['type'=>'md5','num'=>config('separate')['message_session']])
                ->where(['session_id'=>$v['session_id']])
                ->update(['state'=>-2,'close_explain'=>'系统自动关闭']);
            }
        }
    }

    //关闭无效会话中数据
    public function closeInvalidSession(){
        $session_list = Db::name('message_session')
        ->partition([], '', ['type'=>'md5','num'=>config('separate')['message_session']])
        ->where(['state'=>1])
        ->select();

        foreach($session_list as $v){
            $min1 = differenceMinute($v['receive_message_time']);
            if(!empty($v['send_time'])){
                $min2 = differenceMinute($v['send_time']);
            }else{
                $min2 = 2880;
            }

            if($min1 >= 5760 && $min2 >= 2880){
                Db::name('message_session')
                ->partition(['session_id'=>$v['session_id']], 'session_id', ['type'=>'md5','num'=>config('separate')['message_session']])
                ->where(['session_id'=>$v['session_id']])
                ->update(['state'=>-1,'close_explain'=>'48小时无任何会话系统自动关闭']);
            }
        }
    }

    //删除多余的二维码
    public function delQrCodeFile(){
        $list = Db::name('qrcode_del_list')->select();
    
        foreach($list as $val){
            try {
                deleteAll($val['path']);
            }catch (\Exception $e) {
                continue;
            }

            Db::name('qrcode_del_list')->where(['qrcode_del_id'=>$val['qrcode_del_id']])->delete();
        }
    }
}