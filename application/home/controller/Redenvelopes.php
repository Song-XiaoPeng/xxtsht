<?php
namespace app\home\controller;
use think\Db;
use EasyWeChat\Foundation\Application;
use app\api\common\Common;

class Redenvelopes{
    // 领取红包首页
    public function index(){
        $code = input('get.code');
        
        $str = base64_decode($code);

        $data = json_decode($str,true);

        $arr = Db::name('red_envelopes')->where(['activity_id'=>$data['activity_id']])->cache(true,30)->find();

        $is_receive = Db::name('red_envelopes_id')->where(['red_envelopes_id'=>$data['red_envelopes_id']])->value('is_receive');
        if($is_receive == 1 || $is_receive == 2){
            return view('receive', ['title'=>$arr['activity_name']]);
        }

        return view('index', ['title'=>$arr['activity_name'],'code'=>$code]);
    }

    // 领取红包
    public function receive(){
        $data = input('post.');

        $code = $data['code'];
        $wx_nickname = $data['wx_nickname'];
        $wx_portrait = $data['wx_portrait'];
        $openid = $data['openid'];

        $str = base64_decode($code);

        $data = json_decode($str,true);

        $arr = Db::name('red_envelopes')->where(['activity_id'=>$data['activity_id']])->cache(true,30)->find();

        //判断是否关注
        $wx_user_res = Db::name('wx_user')
        ->partition([], "", ['type'=>'md5','num'=>config('separate')['wx_user']])
        ->where(['openid'=>$openid, 'appid'=>$arr['appid']])
        ->find();
        if(!$wx_user_res){
            return msg(3001, '未关注公众号');
        }     

        //判断是否分享
        $is_share = Db::name('red_envelopes_share')->where(['openid'=>$openid,'appid'=>$arr['appid']])->find();
        if($is_share){
            return msg(3002, '请先分享');
        }

        //判断是否已领取
        $is_receive = Db::name('red_envelopes_id')->where(['red_envelopes_id'=>$data['red_envelopes_id']])->value('is_receive');
        if($is_receive == 1 || $is_receive == 2){
            return msg(3003, '已领取红包');
        }

        // 锁定操作
        Db::name('red_envelopes_id')->where(['red_envelopes_id'=>$data['red_envelopes_id']])->update(['is_receive'=>2]);

        // 判断是随机金额或者固定金额
        if ($arr['amount_type'] == 1) {
            // 派发固定金额操作
            $receive_amount = 0.01;

            
        } else if ($arr['amount_type'] == 2) {
            // 派发随机金额操作
            $receive_amount = 0.01;
            
            
        }

        Db::name('red_envelopes_id')
        ->where(['red_envelopes_id'=>$data['red_envelopes_id']])
        ->update([
            'is_receive' => 1,
            'wx_nickname' => $wx_nickname,
            'wx_portrait' => $wx_portrait,
            'receive_amount' => $receive_amount,
            'openid' => $openid
        ]);

        Db::name('red_envelopes')
        ->where(['activity_id'=>$data['activity_id']])
        ->setInc([
            'already_amount',
            $receive_amount
        ]);

        return msg(200,'success');
    }

    // 设为已分享
    public function setShare(){
        $data = input('put.');
    
        Db::name('red_envelopes_share')->insert([
            'appid' => $data['appid'],
            'openid' => $data['openid'],
            'activity_id' => $data['activity_id'],
            'add_time' => date('Y-m-d H:i:s')
        ]);
    }

    //获取jssdk数据
    public function getJsSdk(){
        $data = input('get.');

        $token_info = Common::getRefreshToken($data['appid'], $data['company_id']);
        if ($token_info['meta']['code'] == 200) {
            $refresh_token = $token_info['body']['refresh_token'];
        } else {
            return $token_info;
        }

        try {
            $app = new Application(wxOptions());
            $openPlatform = $app->open_platform;
            $js = $openPlatform->createAuthorizerApplication($data['appid'],$refresh_token)->js;

            return msg(200,'success', json_decode($js->config(array('onMenuShareQQ', 'onMenuShareWeibo'), true)));
        } catch (\Exception $e) {
            return msg(3001,$e->getMessage());
        }
    }
}
