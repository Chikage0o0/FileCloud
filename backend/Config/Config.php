<?php

namespace FileCloud\Config;

class Config
{
    const DB = array(       //数据库连接信息
        'Host' => 'localhost',
        'UserName' => 'root',
        'UserPasswd' => '',
        'DBName' => 'filecloud',
        'DBPort' => '3306'
    );
    const UniqueSecurityCode = "fdkkll";  //请随意输入，用于密码拼接加密验证
    const LifeTime = 24 * 3600; //cookies过期时间
    const LocalStoragePath="c:/upload"; //本地存储位置
}