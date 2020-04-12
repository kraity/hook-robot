<?php

/**
 * Class HOOK_ROBOT
 * author 权那他
 * date 2020/04/09
 * update 2020/04/12
 */
class HOOK_ROBOT
{
    public static $_instance;
    public static $httpApi;
    public static $message;

    /**
     * @param $array
     */
    public static function setApi($array)
    {
        //hookrobot_api build
        self::$httpApi = $array["api"] . "?" . http_build_query($array["query"]);
        self::init();
    }

    /**
     * 初始化
     * 看腾讯hookrobot后续会新加啥
     */
    public static function init()
    {
        //这样是为了兼容后续腾讯hookrobot会增加参数
        self::$message = array(
            "content" => array()
        );
    }

    /**
     * 设置单实例
     * @param HOOK_ROBOT $hook
     */
    public static function set(HOOK_ROBOT $hook)
    {
        self::$_instance = $hook;
    }

    /**
     * 获取单实例
     * @return HOOK_ROBOT
     */
    public static function get()
    {
        if (empty(self::$_instance)) {
            die("Exception: Single instance is not set");
        }
        return self::$_instance;
    }

    /**
     * @param $msg
     * @param int $type 目前是0，可能后续腾讯hookrobot会有新增加
     */
    public static function setMsg($msg, $type = 0)
    {
        self::$message["content"][] = array(
            "type" => $type,
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
        // encode 后，就初始化msg
        self::init();
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, self::$httpApi);
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

// hookrobot  new 对象，set 单实例
$hook = new HOOK_ROBOT();
$hook->setApi(
    array(
        //hookrobot_api
        "api" => "https://app.qun.qq.com/cgi-bin/api/hookrobot_send",
        //这样做是为了后续腾讯hookrobot更新的新参数
        "query" => array(
            // 这里是 key，自己改成自己群hook的key
            "key" => "13256478748454846856496768"
        )
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
// 可以获取hookrobot单实例，类比上面的set
$hook = HOOK_ROBOT::get();
$hook->setMsg("get test 1");
$hook->setMsg("get test 2");
$hook->send();

