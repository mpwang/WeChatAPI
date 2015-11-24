<?php

require_once('Requests/Requests.php');
Requests::register_autoloader();

/**
 * Created by PhpStorm.
 * User: Bob
 * Date: 14-3-22
 * Time: 下午7:02
 */
//TODO remove Config class
class Config
{
    static public $json;

    static public function static_init()
    {
        // self::$json = json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT']. "/config.json"));
    }

    static public function getAccessTokenFile()
    {
        return self::$json->project->access_token_file;
    }

    static public function getMenu()
    {
        return self::$json->project->menu;
    }

    static public function getAppId()
    {
        return self::$json->dev->appID;
    }

    static public function getAppSecret()
    {
        return self::$json->dev->appsecret;
    }
}

Config::static_init();

/**
 * Class MessageClass
 *
 * @Author Randall Wang
 *
 * Basic message object class
 */
class MessageClass
{
    // original post data
    public $postData;
    // pasred xml object
    public $xmlObj;

    /*
     * Fields for received message
     */
    // request user ID
    public $fromUserName;
    // receiver user ID
    public $toUserName;
    // create time of user request
    public $createTime;
    // message type of user request
    public $msgType;
    // message id of user request
    public $msgId;

    // keyword from user, for text msg
    public $keyword;
    // pic url, for image msg
    public $picUrl;
    // media id, for image/voice/video msg
    public $mediaId;
    // media format, for voice msg
    public $mediaFormat;
    // Thumb of video, for video msg
    public $thumbMediaId;

    // location x, for location msg
    public $loc_x;
    // location y, for location msg
    public $loc_y;
    // location scale, for location msg
    public $loc_scale;
    // location information
    public $loc_label;

    // link title
    public $lnk_title;
    // link description
    public $lnk_desc;
    // link url
    public $lnk_url;

    /*
     * Fields for pushed message
     */
    public $eventType;
    public $eventKey;

    function __construct($postData)
    {
        $this->postData = $postData;
        $xmlObj = simplexml_load_string($postData, 'SimpleXMLElement', LIBXML_NOCDATA);
        $this->xmlObj = $xmlObj;
        // common fields
        $this->fromUserName = $xmlObj->FromUserName;
        $this->toUserName = $xmlObj->ToUserName;
        $this->createTime = $xmlObj->CreateTime;
        $this->msgType = $xmlObj->MsgType;
        $this->msgId = $xmlObj->MsgId;

        // handle fields various among diff message types
        if ($this->msgType == "text") {

            $this->keyword = trim($xmlObj->Content);
        } else if ($this->msgType == "image") {

            $this->picUrl = $xmlObj->PicUrl;
            $this->mediaId = $xmlObj->MediaId;
        } else if ($this->msgType == "voice") {

            $this->mediaId = $xmlObj->MediaId;
            $this->mediaFormat = $xmlObj->Format;
        } else if ($this->msgType == "video") {

            $this->mediaId = $xmlObj->MediaId;
            $this->thumbMediaId = $xmlObj->ThumbMediaId;
        } else if ($this->msgType == "location") {

            $this->loc_x = $xmlObj->Location_X;
            $this->loc_y = $xmlObj->Location_Y;
            $this->loc_scale = $xmlObj->scale;
            $this->loc_label = $xmlObj->label;
        } else if ($this->msgType == "link") {

            $this->lnk_title = $xmlObj->Title;
            $this->lnk_desc = $xmlObj->Description;
            $this->lnk_url = $xmlObj->Url;
        } else if ($this->msgType == "event") {

            $this->eventType = $xmlObj->Event;
            //点击菜单拉取消息时的事件推送或点击菜单跳转链接时的事件推送
            if ($this->eventType == "CLICK" || $this->eventType == "VIEW") {
                $this->eventKey = $xmlObj->EventKey;
            }
        }
    }

    public function isSubscribe()
    {
        return $this->msgType == "event" && $this->eventType == "subscribe" ? true : false;
    }

    public function isUnsubscribe()
    {
        return $this->msgType == "event" && $this->eventType == "unsubscribe" ? true : false;
    }

    public function isText()
    {
        return $this->msgType == "text" ? true : false;
    }

    public function isImage()
    {
        return $this->msgType == 'image' ? true : false;
    }

    public function isLink()
    {
        return $this->msgType == "link" ? true : false;
    }

    public function isMenuClick()
    {
        return $this->msgType == "event" && $this->eventType == "CLICK" ? true : false;
    }

    public function isMenuView()
    {
        return $this->msgType == "event" && $this->eventType == "VIEW" ? true : false;
    }

    //  create text message response
    public function createTextRsp($content)
    {
        $time = time();
        $result = "<xml>
		 <ToUserName><![CDATA[{$this->fromUserName}]]></ToUserName>
		 <FromUserName><![CDATA[{$this->toUserName}]]></FromUserName>
		 <CreateTime>{$time}</CreateTime>
		 <MsgType><![CDATA[text]]></MsgType>
		 <Content><![CDATA[{$content}]]></Content>
		 </xml>";
        return $result;
    }

    //  create music message response TODO
    public function createMusicRsp($title, $desc, $musicUrl, $hQMusicUrl)
    {
        $time = time();
        $result = "<xml>
		 <ToUserName><![CDATA[{$this->fromUserName}]]></ToUserName>
		 <FromUserName><![CDATA[{$this->toUserName}]]></FromUserName>
		 <CreateTime>{$time}</CreateTime>
		 <MsgType><![CDATA[music]]></MsgType>
		 <Music>
		 <Title><![CDATA[{$title}]]></Title>
		 <Description><![CDATA[{$desc}]]></Description>
		 <MusicUrl><![CDATA[{$musicUrl}]]></MusicUrl>
		 <HQMusicUrl><![CDATA[{$hQMusicUrl}]]></HQMusicUrl>
		 </Music>
		 </xml>";
        return $result;
    }

    // create rich text message response
    public function createNewsRsp($itemsArray)
    {
        $articles = "";
        foreach ($itemsArray as $item) {
            $articles = $articles . $item;
        }
        $articlesCount = count($itemsArray);

        $time = time();
        $result = "<xml>
        <ToUserName><![CDATA[{$this->fromUserName}]]></ToUserName>
        <FromUserName><![CDATA[{$this->toUserName}]]></FromUserName>
        <CreateTime>{$time}</CreateTime>
        <MsgType><![CDATA[news]]></MsgType>
        <ArticleCount>{$articlesCount}</ArticleCount>
        <Articles>{$articles}</Articles>
        </xml>";
        return $result;
    }

    public function createNewsItem($title, $desc, $picPath, $url)
    {
        $result = "<item>
        <Title><![CDATA[{$title}]]></Title>
        <Description><![CDATA[{$desc}]]></Description>
        <PicUrl><![CDATA[{$picPath}]]></PicUrl>
        <Url><![CDATA[$url]]></Url>
        </item>";
        return $result;
    }
}

class WeChatAPI
{
    public function validate($token)
    {
        $echoStr = $_GET["echostr"];
        if ($this->checkSignature($token)) {
            echo $echoStr;
            exit;
        }
    }

    private function checkSignature($token)
    {
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];

        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode($tmpArr);
        $tmpStr = sha1($tmpStr);

        if ($tmpStr == $signature) {
            return true;
        } else {
            return false;
        }
    }

    private function isGoodCall($rspJson)
    {
        $good = empty($rspJson->errcode) || $rspJson->errcode == 0 ? true : false;
        if (!$good) {
            sae_debug(json_encode($rspJson));
        }
        return $good;
    }

    public function getAccessToken()
    {
        $cache_file = $_SERVER['DOCUMENT_ROOT'] . Config::getAccessTokenFile();
        // caching time, in seconds
        // 48 hours
        $cache_life = '7200';
        // returns FALSE if file does not exist
        $filemtime = @filemtime($cache_file);
        if (!$filemtime or (time() - $filemtime >= $cache_life)) {
            // request access token
            $appID = Config::getAppId();
            $appsecret = Config::getAppSecret();
            $requestUrl = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$appID}&secret={$appsecret}";
            sae_debug("getAccessToken try {$requestUrl}");
            $response = Requests::get($requestUrl);
            $rspJson = json_decode($response->body);
            if ($this->isGoodCall($rspJson)) {
                $accessToken = $rspJson->access_token;
                file_put_contents($cache_file, $accessToken);
            }
        } else {
            // use cached access token
            $accessToken = file_get_contents($cache_file);
        }
        return $accessToken;
    }

    public function menuCreate()
    {
        $accessToken = $this->getAccessToken();
        $requestUrl = "https://api.weixin.qq.com/cgi-bin/menu/create?access_token={$accessToken}";
        $menuJson = file_get_contents($_SERVER['DOCUMENT_ROOT'] . Config::getMenu());
        sae_debug("menuCreate try {$requestUrl}");
        $response = Requests::post($requestUrl, array(), $menuJson);
        $rspJson = json_decode($response->body);
        return $this->isGoodCall($rspJson);
    }

    public function  menuQUery()
    {
        $accessToken = $this->getAccessToken();
        $requestUrl = "https://api.weixin.qq.com/cgi-bin/menu/get?access_token={$accessToken}";
        sae_debug("menuQUery try {$requestUrl}");
        $response = Requests::get($requestUrl);
        $rspJson = json_decode($response->body);
        if ($this->isGoodCall($rspJson)) {
            return $response->body;
        }
    }

    public function menuDelete()
    {
        $accessToken = $this->getAccessToken();
        $requestUrl = "https://api.weixin.qq.com/cgi-bin/menu/delete?access_token={$accessToken}";
        sae_debug("menuDelete try {$requestUrl}");
        $response = Requests::post($requestUrl);
        $rspJson = json_decode($response->body);
        return $this->isGoodCall($rspJson);
    }

    public function getUserBaseInfo($openID)
    {
        $accessToken = $this->getAccessToken();
        $requestUrl = "https://api.weixin.qq.com/cgi-bin/user/info?access_token={$accessToken}&openid={$openID}&lang=zh_CN";
        sae_debug("getUserBaseInfo try {$requestUrl}");
        $response = Requests::post($requestUrl);
        $rspJson = json_decode($response->body);
        if ($this->isGoodCall($rspJson)) {
            return $response->body;
        }
    }


}
