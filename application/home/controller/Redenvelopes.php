<?php
namespace app\home\controller;
use think\Db;

class Redenvelopes{
    // 领取红包首页
    public function index(){
        $code = input('get.code');
        
        $str = base64_decode($code);

        $data = json_decode($str,true);

        $arr = Db::name('red_envelopes')->where(['activity_id'=>$data['activity_id']])->cache(true,30)->find();

        $is_receive = Db::name('red_envelopes_id')->where(['red_envelopes_id'=>$data['red_envelopes_id']])->value('is_receive');
        if($is_receive == 1){
            return view('receive',['title'=>$arr['activity_name']]);
        }

        return view('index', ['title'=>$arr['activity_name']]);
    }

    
}
