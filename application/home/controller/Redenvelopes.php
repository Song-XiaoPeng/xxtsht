<?php
namespace app\home\controller;

class Redenvelopes{
    // 领取红包首页
    public function index(){
        
        return view('index', ['name'=>'thinkphp']);
    }
}
