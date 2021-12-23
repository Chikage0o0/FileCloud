<?php

namespace FileCloud\Controllers;

use FileCloud\Config\Config;
use FileCloud\Utils\ConnDB;

class UserController
{


    /**
     * 注册用户，返回用户ID
     * @param string $uname 用户名
     * @param string $password 密码
     * @return int 用户id
     */
    static public function addUser(string $uname, string $password): int
    {

        if (self::isExistUser($uname)) {
            $sql = sprintf("INSERT INTO `user` ( `UName`, `Password`) VALUES ( '%s', '%s');",
                $uname, md5($password . Config::UniqueSecurityCode));
            $result = ConnDB::linkMySql($sql);
            if (!$result) {
                $sql=sprintf("SELECT UID FROM `user` WHERE UName='%s';",$uname);
                $result = ConnDB::linkMySql($sql,"mysqli_fetch_row");
                return $result[0];
            }
        }
        return 0;
    }

    /**
     * 根据用户名查看是否存在用户
     * @param string $Uname 用户名
     * @return bool
     */
    static public function isExistUser(string $Uname):bool{
        $sql = sprintf("SELECT * FROM user where UName='%s';", $Uname);
        $result = ConnDB::linkMySql($sql, "mysqli_fetch_row");
        if ($result == null){
            return true;
        }
        return false;
    }

    /**
     * 删除用户
     * @param string $uname
     * @param string $password
     * @return bool
     */
    static public function delUser(string $uname, string $password): bool
    {
        $sql = sprintf("SELECT UID, UName, Password, RootPath FROM user where UName='%s';", $uname);
        $result = ConnDB::linkMySql($sql, "mysqli_fetch_row");
        if ($result != null && $result[2] == md5($password . Config::UniqueSecurityCode)) {
            $fid = $result[3];
            $sql = sprintf("DELETE FROM `user` WHERE UName='%s';", $uname);
            $result = ConnDB::linkMySql($sql);
            if (!$result) {
                FileController::delFile($fid);
                return true;
            }
        }
        return false;
    }

    /**
     * 修改用户密码
     * @param string $uname 用户名
     * @param string $oldPasswd 旧密码
     * @param string $newPasswd 新密码
     * @return bool
     */
    static public function changePassword(string $uname, string $oldPasswd, string $newPasswd): bool
    {
        $sql = sprintf("SELECT * FROM user where UName='%s';", $uname);
        $result = ConnDB::linkMySql($sql, "mysqli_fetch_row");
        if ($result[2] == md5($oldPasswd . Config::UniqueSecurityCode)) {
            $sql = sprintf("UPDATE `user` SET `Password` = '%s' WHERE `user`.`UName`='%s';"
                , md5($newPasswd . Config::UniqueSecurityCode),$uname);
            $result = ConnDB::linkMySql($sql);
            if (!$result) {
                return true;
            }
        }
        return false;
    }

    static public function logout(){
        
    }
}