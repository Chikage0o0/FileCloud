<?php

namespace FileCloud\Model;

interface StorageInterface
{
    /**
     * 返回下载地址，不经过PHP代理
     * @param string $fileMD5 文件MD5
     * @return string 下载地址
     */
    public function downloadFileUrl(string $fileMD5):string;

    /**
     * 通过流提供文件下载，经过PHP代理
     * @param string $fileMD5 文件MD5
     * @param string $fileName 文件名
     * @return void
     */
    public function downloadFile(string $fileMD5,string $fileName):void;
    /**
     * 将文件上传至指定位置
     * @param string $tmpFile 临时文件
     * @param string $fileMD5 文件MD5
     */
    public function uploadFile(string $tmpFile,string $fileMD5):bool;

    /**
     * 通过文件MD5删除指定文件
     * @param string $fileMD5 文件MD5
     */
    public function delFile(string $fileMD5):bool;


}