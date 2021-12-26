<?php

namespace FileCloud\Utils;

use FileCloud\Config\Config;

class ConnDB
{


    const DefaultSql = "../data/studentdb.sql"; //默认创建的数据库结构

    static public function linkMySql(string $sql, string $operation = null, array $option = null)
    {
        $link = @mysqli_connect(Config::DB['Host'], Config::DB['UserName'], Config::DB['UserPasswd'], null, Config::DB['Port']);
        if ($link) {
            mysqli_set_charset($link, 'utf8');      // 设置数据库字符集 utf8 非utf-8
            mysqli_select_db($link, Config::DB['DBName']);
            $result = mysqli_query($link, $sql);    // 执行 SQL 语句，并返回结果
            if (!mysqli_errno($link)) {
                if ($operation != null) {      // 根据用户设置的获取数据方式，返回数据
                    if ($option != null) {
                        array_unshift($option, $result);
                    } else {
                        $option = array($result);
                    }
                    /*var_dump($option);*/
                    $data = call_user_func_array($operation, $option);
                } else {
                    $data = null;
                }
            } else {
                exit(mysqli_error($link));
            }
            mysqli_close($link);
            unset($result, $option);
            return $data;
        } else {
            exit('数据库连接失败！');
        }
    }

    //没有数据库的话通过sql导入数据库
    /*    static private function createDB(mysqli $link): void
        {
            mysqli_query($link,
                'CREATE DATABASE IF NOT EXISTS `'.Config::DB['DBName'].'` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
                        USE '.Config::DB['DBName'].';'); //建立数据库

            $sql = file_get_contents(self::DefaultSql);
            $sqlArr = explode(';', $sql);
            foreach ($sqlArr as $value) {
                mysqli_query($link, $value . ';');
            }
        }*/
}