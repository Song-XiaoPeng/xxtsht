<?php
namespace app\api\logic\v1\user;
use think\Model;
use think\Db;

class AuthModel extends Model {
    public function login ($data) {

        return msg(200,'success',['aa'=>123]);
    }
}