<?php

use FileCloud\Api\Api;

require_once('__autoload.php');

header("Content-type:application/json;charset=utf-8");
if (isset($_REQUEST['method'])){
    $api=new Api();
    call_user_func(array($api,$_REQUEST['method']));
}else{
    echo '{"error":"must have method"}';
}

