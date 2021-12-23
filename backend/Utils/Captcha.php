<?php

namespace FileCloud\Utils;


class Captcha
{
    private $key;
    private $image;

    public function __construct(string $key = null, bool $random_color = false)
    {
        if (is_null($key)) {
            $this->key = rand(10000, 99999);
        } else {
            $this->key = $key;
        }

        $width = 48;
        $height = 20;

        if (strlen($this->key) > 5) {
            $this->key = substr($this->key, 0, 16);
            $count_max = strlen($this->key) - 5;
            for ($i = 0; $i < $count_max; $i++) {
                $width += 9;
            }
        }

        $this->image = @imagecreate($width, $height) or die("请检查PHP是否有GD扩展");

        if ($random_color) {
            $this->getRandomColor(0, 127);
            $text_color = $this->getRandomColor(128, 255);
        } else {
            imagecolorallocate($this->image, 200, 200, 200);
            $text_color = imagecolorallocate($this->image, 255, 0, 0);
        }

        imagestring($this->image, 8, 2, 2, $this->key, $text_color);
    }

// 获取验证码
    public function getKey(): string
    {
        return $this->key;
    }

// 获取验证码图片
    public function getImg(): string
    {
        ob_start();
        imagepng($this->image);
        $rawImageBytes = ob_get_clean();
        return "data:image/jpeg;base64," . base64_encode($rawImageBytes);
    }

    private function getRandomColor(int $range_min = 0, int $range_max = 255)
    {
        return imagecolorallocate($this->image, rand($range_min, $range_max), rand($range_min, $range_max), rand($range_min, $range_max));
    }
}
