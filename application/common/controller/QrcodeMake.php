<?php
/**
 * Created by PhpStorm.
 * User: mr.lee
 * Date: 2018/11/6
 * Time: 11:39 AM
 */

namespace app\common\controller;


use Endroid\QrCode\QrCode;
use think\Controller;

class QrcodeMake extends Controller
{
    public function qrcode($uid = "", $url = '', $level = 4, $size = 5)
    {
        $path = $_SERVER['DOCUMENT_ROOT'] . "/qrcode/_" . $uid . '.png';
//        if (file_exists($path)) {
//            return $path;
//        }
        $qrCode = new QrCode();
        $qrCode->setText($url)
            ->setSize(160)//大小
            //->setLabelFontPath(VENDOR_PATH . 'endroid/qrcode/assets/noto_sans.otf')
            ->setErrorCorrectionLevel('high')
            ->setForegroundColor(array('r' => 0, 'g' => 0, 'b' => 0, 'a' => 0))
            ->setBackgroundColor(array('r' => 255, 'g' => 255, 'b' => 255, 'a' => 0));

        header('Content-Type: ' . $qrCode->getContentType());
        $image = $qrCode->writeString();

        file_put_contents($path, $image);

        return $path;


    }


    public function getcodeurl($uid = "", $url = '', $level = 4, $size = 5)
    {
        $path = $_SERVER['DOCUMENT_ROOT'] . "/qrcode/_" . $uid . '.png';
        $qr_return = "/qrcode/_" . $uid . '.png';
        $urls = $this->selfurl();
//        if (file_exists($path)) {
//            return $urls.$qr_return;
//        }
        $qrCode = new QrCode();
        $qrCode->setText($url)
            ->setSize(250)//大小
            //->setLabelFontPath(VENDOR_PATH . 'endroid/qrcode/assets/noto_sans.otf')
            ->setErrorCorrectionLevel('high')
            ->setForegroundColor(array('r' => 0, 'g' => 0, 'b' => 0, 'a' => 0))
            ->setBackgroundColor(array('r' => 255, 'g' => 255, 'b' => 255, 'a' => 0));
        header('Content-Type: ' . $qrCode->getContentType());
        $image = $qrCode->writeString();
        file_put_contents($path, $image);
        return $urls.$qr_return;
    }

    public function getcode($uid = 602, $url = '', $size = 48)
    { 
//        int(1) 
//        string(104) "https://mp.weixin.qq.com/misc/getheadimg?fakeid=ouagxuB4IrUXNgC-3wpc9qB9ZEhY&token=1250496181&lang=zh_CN"
        $qr = $this->qrcode($uid, $url);
        $qr2 = $_SERVER['DOCUMENT_ROOT'] . "/qrcode/" ."new_". $uid . '.png';
//         $qr2 = "http://".$_SERVER['SERVER_NAME']. "/qrcode/" ."new_". $uid . '.png';
        
        $qr_return = "/qrcode/" ."new_". $uid . '.png';
//        if (file_exists($qr2)) {
//            return $this->selfurl() . $qr_return;
//        }
        $img = \think\Image::open('./footer.png'); 
        $img->water($qr, array(321, 100))->save($qr2);
        
        return $this->selfurl() . $qr_return;
    }

    private function selfurl()
    {
        $http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
        return $http_type . $_SERVER['HTTP_HOST'];
    }

}