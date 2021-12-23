<?php

namespace FileCloud\Controllers;

use FileCloud\Config\Config;
use FileCloud\Model\LocalStorage;
use FileCloud\Utils\ConnDB;

class UploadController
{

    /**
     * 上传文件到后端<br>
     * TODO：<br>
     * 读取配置，多存储库，负载均衡，异地灾备
     * @param $file
     * @param $fileMD5
     * @param $parent
     * @param $uid
     * @return int
     */
    public static function uploadFile($file, $fileMD5, $parent, $uid): int
    {
        if (!$file["error"]) {
            $storage = new LocalStorage(Config::LocalStoragePath);
            if ($storage->uploadFile($file['tmp_name'], $fileMD5)) {
                self::addFileToDB($fileMD5, $file['name'], $file['size'], $parent, $uid);
                return 0;
            }
        }
        return $file['error'];
    }


    private static function addFileToDB($fileMD5, $fileName, $fileSize, $parent, $uid):void
    {
        $sql = sprintf("INSERT INTO `file` ( `FileMD5`, `FileName`,`FileSize`,`Parent`,`UID`) VALUES
                                                                             ( '%s', '%s','%s', '%s','%s');",
            $fileMD5, $fileName, $fileSize, $parent, $uid);
        ConnDB::linkMySql($sql);
    }

    /**
     * 通过MD5检查系统中是否有该文件，如果有，则添加文件记录
     * @param string $fileMD5 文件MD5
     * @param string $fileName 文件名
     * @param int $parent 父文件夹
     * @param int $uid 用户id
     * @return int 0 存在 1 不存在
     */
    public static function isExistMD5(string $fileMD5,string $fileName,int $parent,int $uid): int
    {
        $sql = sprintf("SELECT FileSize FROM file WHERE FileMD5='%s';", $fileMD5);
        if ($result=ConnDB::linkMySql($sql, "mysqli_fetch_row")) {
            self::addFileToDB($fileMD5, $fileName, $result[0], $parent, $uid);
            return 0;
        }
        return 1;
    }
}