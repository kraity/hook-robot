<?php

/**
 * Class HOOK_ROBOT
 * author 权那他
 * date 2020/04/09
 */
class HOOK_ROBOT
{
    public static $_instance;
    public static $hookrobot_api;
    public static $message;


    /**
     * @param $array
     */
    public static function setApi($array)
    {
        self::$hookrobot_api = $array["hookrobot_api"] . "?" . http_build_query(array("key" => $array["key"]));
        self::$message = array(
            "content" => array()
        );
    }

    /**
     * @param HOOK_ROBOT $hook
     */
    public static function set(HOOK_ROBOT $hook)
    {
        self::$_instance = $hook;
    }

    /**
     * @return HOOK_ROBOT
     */
    public static function get()
    {
        if (empty(self::$_instance)) {
            die("Exception");
        }
        return self::$_instance;
    }

    /**
     * @param $msg
     */
    public static function setMsg($msg)
    {
        self::$message["content"][] = array(
            "type" => 0,
            "data" => $msg
        );
    }

    /**
     * 发送
     * 第一个是http代号，测试出一直是500
     * 第二个是api返回的内容，现在一直是null
     * @return array
     */
    public static function send()
    {
        $json = json_encode(self::$message);
        // encode 后，就清空msg
        self::$message = array(
            "content" => array()
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, self::$hookrobot_api);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER,
            array(
                'Content-Type: application/json; charset=utf-8',
                'Content-Length: ' . strlen($json)
            )
        );
        ob_start();
        curl_exec($ch);
        $return_content = ob_get_contents();
        ob_end_clean();
        return array(
            curl_getinfo($ch, CURLINFO_HTTP_CODE),
            $return_content
        );
    }
}

//  new 对象，set 单实例
$hook = new HOOK_ROBOT();
$hook->setApi(
    array(
        "hookrobot_api" => "https://app.qun.qq.com/cgi-bin/api/hookrobot_send",
        // 这里是 key，自己改成自己群hook的key
        "key" => "13256478748454846856496768"
    )
);
HOOK_ROBOT::set($hook);

//调用方法 1

//这里添加消息内容  可以多次调用
HOOK_ROBOT::setMsg("test 1");
HOOK_ROBOT::setMsg("test 2");

// 最后，添加消息完后，发送
HOOK_ROBOT::send();

//或则 打印返回的内容
var_dump(HOOK_ROBOT::send());


//调用方法 2
// 可以获取单实例，类比上面的set
$hook = HOOK_ROBOT::get();
$hook->setMsg("get test 1");
$hook->setMsg("get test 2");
$hook->send();

