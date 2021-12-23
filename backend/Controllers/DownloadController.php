<?php

namespace FileCloud\Controllers;

use FileCloud\Config\Config;
use FileCloud\Model\LocalStorage;

class DownloadController
{
    /**
     * 根据文件MD5返回下载地址<br>
     * TODO：<br>
     * 多用户下载负载均衡
     * @param string $fileMD5 文件MD5
     * @return void 下载地址
     */
    static public function getDownload(string $fileMD5,$fileName):void
    {
        $storage = new LocalStorage(Config::LocalStoragePath);
        $storage->downloadFile($fileMD5,$fileName);
    }
}