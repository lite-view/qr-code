<?php
/**
 * composer require bacon/bacon-qr-code
 */

namespace LiteView\QrCode;

use BaconQrCode\Common\ErrorCorrectionLevel;
use BaconQrCode\Encoder\Encoder;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\ImagickImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class QrCodeGenerator
{
    private $writer;
    private $QR;

    public function __construct($size = 400, $margin = 2)
    {
        $renderer = new ImageRenderer(
            new RendererStyle($size, $margin),
            new ImagickImageBackEnd()
        );

        $this->writer = new Writer($renderer);
    }

    public function generate($info)
    {
        $qr_string = $this->writer->writeString(
            $info,
            Encoder::DEFAULT_BYTE_MODE_ECODING,
            ErrorCorrectionLevel::H()
        );
        $this->QR = $qr_string; //二维码数据
        return $this;
    }

    public function addLogo($logo, $ratio = 5)
    {
        $QR = imagecreatefromstring($this->QR);
        $logo = imagecreatefromstring(file_get_contents($logo));
        //二维码宽高
        $QR_width = imagesx($QR);
        $QR_height = imagesy($QR);

        //logo宽高
        $logo_width = imagesx($logo);
        $logo_height = imagesy($logo);

        // Scale logo to fit in the QR Code
        $square = $QR_width / $ratio; //嵌入的logo为正方形，正方形的边长

        $dst_x = ($QR_width - $square) / 2;//logo 在 qr 中的x坐标
        $dst_y = $dst_x;                   //logo 在 qr 中的y坐标

        $src_x = 0;//logo 平铺时的x坐标，一般为0，否则会被挤开
        $src_y = 0;//logo 平铺时的y坐标，一般为0，否则会被挤开

        $dst_w = $square;//正方形 logo 的宽度
        $dst_h = $square;//正方形 logo 的高度

        $src_w = $logo_width;// 载入logo 原图的宽度，小于原图：图像缺失，大于原图：图象有黑块
        $src_h = $logo_height;//载入logo 原图的高度，小于原图：图像缺失，大于原图：图象有黑块

        // imageCopyMerge ,$QR 新建的图片,$logo 需要载入的图片
        imagecopyresampled($QR, $logo, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h);

        //$qr_string = imagepng($QR);
        ob_start();
        imagepng($QR);
        $qr_string = ob_get_clean();

        //释放资源
        imagedestroy($QR);
        imagedestroy($logo);
        $this->QR = $qr_string;
        return $this;
    }

    public function getB64($prefix = 'data:image/png;base64,')
    {
        return $prefix . base64_encode($this->QR);
    }

    public function getString($header = false)
    {
        if ($header) {
            header('Content-type:image/png');
        }
        return $this->QR;
    }
}
