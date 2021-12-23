<?php

namespace FileCloud\Controllers;

use FileCloud\Config\Config;
use FileCloud\Model;
use FileCloud\Utils\ConnDB;

class FileController
{
    /**
     * 添加文件夹
     * @param string $folderName 文件夹名
     * @param int $Parent 父文件夹ID
     * @param int $UID 用户ID
     * @return bool
     */
    static public function addFolder(string $folderName, int $Parent, int $UID): bool
    {
        if (!self::isExistFile($folderName, $Parent)) {
            $sql = sprintf("INSERT INTO `file` ( `FileMD5`,`FileName`,`FileSize`, `Parent`,`UID`) 
                    VALUES ( '','%s','0', '%s','%s');", $folderName, $Parent, $UID);
            ConnDB::linkMySql($sql);
            if ($Parent == 1) {
                $fid = self::findFileFID($folderName, $Parent);
                $sql = sprintf("UPDATE user SET RootPath=%s WHERE UID=%s", $fid, $UID);
                ConnDB::linkMySql($sql);
            }
            return true;
        }
        return false;
    }


    /**
     * <p>查询文件FID</p>
     * <p>参数一个时候，填入FID，参数两个时FileName以及Parent</p>
     * @param int $uid 用户id
     * @return int 文件ID
     */
    static public function findUserHome(int $uid): int
    {

        $sql = sprintf("SELECT rootPath FROM user WHERE  UID='%s'", $uid);
        $result = ConnDB::linkMySql($sql, "mysqli_fetch_row");
        if ($result) {
            return $result[0];
        }
        return 0;
    }

    /**
     * <p>查询文件FID</p>
     * <p>参数一个时候，填入FID，参数两个时FileName以及Parent</p>
     * @param string $Name FileName
     * @param int $Parent 父文件夹ID
     * @return int 文件ID
     */
    static private function findFileFID(string $Name, int $Parent): int
    {

        $sql = sprintf("SELECT FID FROM file where FileName='%s' and Parent='%s';", $Name, $Parent);
        $result = ConnDB::linkMySql($sql, "mysqli_fetch_row");
        if ($result) {
            return $result[0];
        }
        return 0;
    }

    /**
     * <p>查询文件是否存在，存在返回true，不存在返回false</p>
     * <p>参数一个时候，填入FID，参数两个时FileName以及Parent</p>
     * @param string $Name FID或FileName
     * @param int $Parent 不填时采用FID查询文件
     * @return int 1 文件夹 2 文件 0 不存在
     */
    static private function isExistFile(string $Name, int $Parent = -1): int
    {
        if ($Parent == -1) {
            $id = $Name;
            $sql = sprintf("SELECT FileMD5  FROM file where FID='%s';", $id);

        } else {
            $sql = sprintf("SELECT FileMD5 FROM file where FileName='%s' and Parent='%s';", $Name, $Parent);
        }
        $result = ConnDB::linkMySql($sql, "mysqli_fetch_row");
        if ($result != null) {
            if ($result[0] == null) {
                return 1;
            } else {
                return 2;
            }
        }
        return 0;
    }

    /**
     * 重命名文件或者文件夹
     * @param int $FID 文件ID
     * @param string $newName 新文件名
     * @return bool
     */
    static public function renameFile(int $FID, string $newName): bool
    {
        if (self::isExistFile($FID)) {
            $sql = sprintf("UPDATE file SET FileName='%s' WHERE FID='%s'", $newName, $FID);
            $result = ConnDB::linkMySql($sql);
            return !$result;
        }
        return false;
    }

    /**
     * 删除文件或者文件夹
     * @param int $id 文件ID
     * @return void
     */
    static public function delFile(int $id): bool
    {
        if ($exist = self::isExistFile($id)) {
            $storage = new Model\LocalStorage(Config::LocalStoragePath);

            //查询要删除的文件列表
            $sql = sprintf("SELECT t1.FileMD5 FROM (SELECT FileMD5,COUNT(FileMD5)as num FROM file 
                        GROUP BY FileMD5) t1 INNER JOIN (SELECT FileMD5,COUNT(FileMD5)as num FROM file 
                        WHERE FIND_IN_SET(FID,get_child_list('%s'))  GROUP BY FileMD5) t2 
                        ON t1.FileMD5=t2.FileMD5 WHERE (t1.num-t2.num)=0;", $id);
            $results = ConnDB::linkMySql($sql, "mysqli_fetch_all", array(MYSQLI_NUM));
            foreach ($results as $result) {
                $storage->delFile($result[0]);
            }
            //删除多余记录
            $sql = sprintf("DELETE FROM file WHERE FID IN (SELECT FID FROM file 
                                    WHERE FIND_IN_SET(FID,get_child_list('%s')));", $id);
            ConnDB::linkMySql($sql);
            return true;

        }
        return false;
    }

    /**判断是否是文件的所有者
     * @param int $fid 文件ID
     * @return false|int|mixed|void|null
     */
    static public function isOwnerOfFile(int $fid)
    {
        if ($uid = AuthController::isLogin()) {
            $sql = sprintf("SELECT FileMD5,FileName FROM file WHERE FID='%s' AND UID='%s' ", $fid, $uid);
            return ConnDB::linkMySql($sql, "mysqli_fetch_assoc");
        }
        return 0;
    }

    /**
     * <p>根据FID查询FID下所有文件，返回文件数组</p>
     * @param int $FID 文件ID
     * @param int $sort 排序方式（未实现）
     * @param int $page 查询页数
     * @param int $limit 单页行数限制
     * @return array  <p>[Parent]=父文件夹id</p>
     * <p>[FID]=当前文件夹id</p>
     * <p>[FileName]=当前文件夹名</p>
     * <p>[Page]=当前页数</p>
     * <p>[Limit]=当前每页行数</p>
     * <p>[MaxPage]=最大页数</p>
     * <p>[Child]=array(FID,FileName,FileMD5,FileSize,Parent)</p>
     */
    static public function showFile(int $FID, int $sort, int $page, int $limit = 10): array
    {
        $sql = sprintf("SELECT Parent,FID,FileName FROM file WHERE FID='%s';", $FID);
        $resultFinal = ConnDB::linkMySql($sql, "mysqli_fetch_assoc");
        $resultFinal["Page"] = $page;
        $resultFinal["Limit"] = $limit;

        $sql = sprintf("SELECT FID,FileName,FileMD5,FileSize FROM file WHERE Parent='%s';", $FID);
        $result = ConnDB::linkMySql($sql, "mysqli_fetch_all", array(MYSQLI_ASSOC));
        $resultFinal["MaxPage"] = ceil(count($result) / $limit);
        $resultFinal["Child"] = @array_chunk($result, $limit)[$page - 1];
        return $resultFinal;
    }
}
