<?php

namespace FileCloud\Controllers;

use FileCloud\Config\Config;
use FileCloud\Utils\ConnDB;

class AuthController
{
    /**
     * 登录用户，成功登录后将uid以及rootPath加入session
     * @param string $user 用户名
     * @param string $passwd 密码
     * @param string $captcha 验证码
     * @return int 0 成功 1 验证码错误 2 不存在用户 3 密码错误
     */
    static public function loginUser(string $user, string $passwd, string $captcha): int
    {

        if (!(isset($_SESSION['captcha'] )&& $captcha == $_SESSION['captcha'])) {
            return 1;
        }
        $sql = sprintf("SELECT UID,Password,RootPath FROM user where UName='%s';", $user);
        $result = ConnDB::linkMySql($sql, "mysqli_fetch_row");
        if ($result == null) {
            return 2;
        }
        if ($result[1] == md5($passwd . Config::UniqueSecurityCode)) {
            //设置相关cookies

            if (!session_id()) {
                session_set_cookie_params(Config::LifeTime);
                session_start();
            }
            $_SESSION['uid'] = $result[0];
            $_SESSION['rootPath'] = $result[2];
            unset($_SESSION['captcha']);
            return 0;
        } else {
            return 3;
        }
    }

    /**
     * 已经登录返回用户id 未登录返回0|false
     * @return int
     */
    static public function isLogin(): int
    {
        if (!session_id()) {
            session_set_cookie_params(Config::LifeTime);
            session_start();
        }
        if (isset($_SESSION['uid'])) {
            return $_SESSION['uid'];
        }
        return 0;
    }
}