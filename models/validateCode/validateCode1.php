<?php

namespace app\models\validateCode;

use Yii;

//干扰线验证码类
class validateCode1 {

    private $charset = 'abcdefghknpstuwxyzABCDEFHKNPRSTUVXYZ3456789';
    private $code;                           //验证码
    private $codelen = 5;                    //验证码长度
    private $width = 140;                    //宽度
    private $height = 50;                    //高度
    private $img;                            //图形资源句柄
    private $font;                           //指定的字体
    private $fontsize = 34;                  //指定字体大小
    private $fontcolor;                      //指定字体颜色
    private $sessionName = '__verify';

    //构造方法初始化
    public function __construct() {
        //$this->font = dirname(__FILE__) . '/ttfs/' . mt_rand(2,5).'.ttf';
        $this->font = dirname(__FILE__) . '/ttfs/3.ttf';
    }

    //生成随机码
    private function createCode() {
        $_len = strlen($this->charset) - 1;
        for ($i = 0; $i < $this->codelen; $i++) {
            $this->code .= $this->charset[mt_rand(0, $_len)];
        }
    }

    //生成背景
    private function createBg() {
        $this->img = imagecreatetruecolor($this->width, $this->height);
        //$color = imagecolorallocate($this->img, mt_rand(157,255), mt_rand(157,255), mt_rand(157,255));
        $color = imagecolorallocate($this->img, 255, 255, 255);
        imagefilledrectangle($this->img, 0, $this->height, $this->width, 0, $color);
    }

    //生成文字
    private function createFont() {
        $_x = ($this->width - 10) / ($this->codelen * 1.1);
        $this->fontcolor = imagecolorallocate($this->img, mt_rand(0, 157), mt_rand(0, 157), mt_rand(0, 157));
        $_begin = 10;
        for ($i = 0; $i < $this->codelen; $i++) {
            if ($i == 1) {
                imagefilter($this->img, IMG_FILTER_GAUSSIAN_BLUR);
            }
            imagettftext($this->img, $this->fontsize, mt_rand(0, 10), $_x * $i + $_begin, $this->height / 1.4, $this->fontcolor, $this->font, $this->code[$i]);
        }
    }

    //生成线条、雪花
    private function createLine() {
        for ($i = 0; $i < 6; $i++) {
            $color = imagecolorallocate($this->img, mt_rand(0, 156), mt_rand(0, 156), mt_rand(0, 156));
            imageline($this->img, mt_rand(0, $this->width), mt_rand(0, $this->height), mt_rand(0, $this->width), mt_rand(0, $this->height), $color);
        }
        for ($i = 0; $i < 70; $i++) {
            $color = imagecolorallocate($this->img, mt_rand(200, 255), mt_rand(200, 255), mt_rand(200, 255));
            imagestring($this->img, mt_rand(1, 5), mt_rand(0, $this->width), mt_rand(0, $this->height), '*', $color);
        }
    }

    //生成一条背景线
    private function writeLine() {
        $A = mt_rand(1, $this->height / 6);                  // 振幅
        $b = mt_rand(-$this->height / 8, $this->height / 8);   // Y轴方向偏移量
        $f = mt_rand(-$this->height / 8, $this->height / 8);   // X轴方向偏移量
        $T = mt_rand($this->height * 2.5, $this->width * 2);  // 周期
        $w = (2 * M_PI) / $T;

        $px1 = 0;  // 曲线横坐标起始位置
        $px2 = mt_rand($this->width / 2, $this->width * 0.667);  // 曲线横坐标结束位置
        for ($px = $px1; $px <= $px2; $px = $px + 0.9) {
            if ($w != 0) {
                $py = $A * sin($w * $px + $f) + $b + $this->height / 2;  // y = Asin(ωx+φ) + b
                $i = 1;
                while ($i > 0) {

                    imagesetpixel($this->img, $px + $i, $py + $i + 2, $this->fontcolor);
                    imagesetpixel($this->img, $px + $i, $py + $i + 1, $this->fontcolor);
                    imagesetpixel($this->img, $px + $i, $py + $i, $this->fontcolor);  // 这里画像素点比imagettftext和imagestring性能要好很多
                    imagesetpixel($this->img, $px + $i, $py + $i - 1, $this->fontcolor);
                    imagesetpixel($this->img, $px + $i, $py + $i - 2, $this->fontcolor);
                    $i--;
                }
            }
        }

        $A = mt_rand(1, $this->height / 6);                  // 振幅
        $f = mt_rand(-$this->height / 4, $this->height / 4);   // X轴方向偏移量
        $T = mt_rand($this->height * 2.5, $this->width * 2);  // 周期
        $w = (2 * M_PI) / $T;
        $b = $py - $A * sin($w * $px + $f) - $this->height / 2;
        $px1 = $px2;
        $px2 = $this->width;
        for ($px = $px1; $px <= $px2; $px = $px + 0.9) {
            if ($w != 0) {
                $py = $A * sin($w * $px + $f) + $b + $this->height / 2;  // y = Asin(ωx+φ) + b
                $i = 1;
                while ($i > 0) {
                    imagesetpixel($this->img, $px + $i, $py + $i + 2, $this->fontcolor);
                    imagesetpixel($this->img, $px + $i, $py + $i + 1, $this->fontcolor);
                    imagesetpixel($this->img, $px + $i, $py + $i, $this->fontcolor);  // 这里(while)循环画像素点比imagettftext和imagestring用字体大小一次画出（不用这while循环）性能要好很多
                    imagesetpixel($this->img, $px + $i, $py + $i - 1, $this->fontcolor);
                    imagesetpixel($this->img, $px + $i, $py + $i - 2, $this->fontcolor);
                    $i--;
                }
            }
        }
    }

    //输出
    private function outPut() {
        header('Cache-Control: private, max-age=0, no-store, no-cache, must-revalidate');
        header('Cache-Control: post-check=0, pre-check=0', false);
        header('Pragma: no-cache');
        header('Content-type:image/png');
        imagepng($this->img);
        imagedestroy($this->img);
    }

    //对外生成
    public function doimg() {
        $this->clearCode();
        $this->createBg();
        $this->createCode();
        $this->createFont();
        $this->writeLine();
        $this->setCode();
        $this->outPut();
    }

    //设置验证码
    public function setCode() {
        $session = Yii::$app->session;
        $session->open();
        $name = $this->sessionName;
        $session[$name] = strtolower($this->code);
    }

    //获取验证码
    public function getCode() {

        $session = Yii::$app->session;
        $session->open();
        $code = null;

        if (!empty($session[$this->sessionName])) {
            $code = $session[$this->sessionName];
        } else {
            $code = $this->code;
        }
        return $code;
    }

    /**
     * 如果验证码验证失败，则立即清除，即要求验证码立即刷新
     */
    public function clearCode() {
        $session = Yii::$app->session;
        $session->open();
        $name = $this->sessionName;
        unset($session[$name]);
    }

    /**
     * 验证验证码是否正确
     * @param string $input
     * @param string $flag
     * @return boolean
     */
    public function validate($input, $flag = false) {
        $code = $this->getCode();
        $valid = (strtolower($input) == $code) ? true : false;
        if ($valid && $flag) {
            $this->clearCode();
        }
        return $valid;
    }

}
