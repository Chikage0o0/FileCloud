<?php

namespace FileCloud\Model;

class LocalStorage implements StorageInterface
{
    protected $storePath;

    public function __construct($storePath)
    {
        if (!(is_dir($storePath))) {
            //第三个参数是“true”表示能创建多级目录，iconv防止中文目录乱码
            mkdir(iconv("UTF-8", "GBK", $storePath), 0777, true);
        }
        $this->storePath = $storePath;
    }


    public function downloadFile(string $fileMD5, string $fileName): void
    {
        ob_clean();
        $filepath = sprintf("%s/%s", $this->storePath, $fileMD5);
        $fp = fopen($filepath, "r");

        //取得文件大小
        $file_Size = filesize($filepath);

        header("Content-type:application/octet-stream");
        header("Accept-Ranges:bytes");
        header("Accept-Length:" . $file_Size);
        header("Content-Disposition: attachment; filename=" . $fileName);

        $buffer = 1024;
        $buffer_count = 0;

        while (!feof($fp) && $file_Size - $buffer_count > 0) {
            $data = fread($fp, $buffer);
            $buffer_count += $buffer;
            echo $data;

        }
        fclose($fp);
    }


    public function uploadFile(string $tmpFile, string $fileMD5): bool
    {
        return move_uploaded_file($tmpFile, sprintf("%s/%s", $this->storePath, $fileMD5));
    }

    public function delFile(string $fileMD5): bool
    {
        return @unlink(sprintf("%s/%s", $this->storePath, $fileMD5));
    }

    public function downloadFileUrl(string $fileMD5): string
    {
        return sprintf('{"file":"%s/%s"}', $this->storePath, $fileMD5);
    }
}
