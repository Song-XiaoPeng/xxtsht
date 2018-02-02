<?php
/**
 * Created by PhpStorm.
 * User: SuperMan
 * Date: 2018/2/2
 * Time: 18:15
 */

namespace app\api\logic\v1\push;

use think\Model;

class CommonApiLogic extends Model
{
    public function backgroundProcess($data)
    {
        $title = $data['title'];
        $description = $data['description'];
        $msgData = $data['msgData'];
        $TargetUser = $data['TargetUser'];
        $targetList = $data['targetList'];

        $url = "http://kfmsg.lyfz.net:8002/BackgroundProcess.aspx?type=sendpushmsg";

        $client = new \GuzzleHttp\Client();
        $request_res = $client->request(
            'POST',
            $url,
            [
                'form_params' => [
                    'title' => $title,
                    'description' => $description,
                    'msgData' => $msgData,
                    'TargetUser' => $TargetUser,
                    'targetList' => $targetList,
                ]
            ]
        );
        return msg(200, $request_res);
    }
}