<?php


spl_autoload_register(function ($className) {
    $className = substr($className, strpos($className, "\\"));
    if (is_file('./backend/' . $className . '.php')) {
        require './backend/' . $className . '.php';
    }
});
//$s=new LocalStorage("upload");
//$s->delFile("ebc52cc672fd853493684d5d98155cb8");