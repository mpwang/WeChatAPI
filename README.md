# Simple WeChatAPI in PHP

# Usage Example
```php
include_once 'WeChatAPI.php';

public function responseMsg()
{
    $api = new WeChatAPI();
    $postData = $GLOBALS["HTTP_RAW_POST_DATA"];
    if (empty($postData)) {
        if (!empty($_GET["echostr"])) {
            $api->validate();
        } else {
            echo "server works";
        }
    } else {
        $msg = new MessageClass($postData);
        if ($msg->isSubscribe()) {
            // do something
        }
    }
}
```
