<?php

namespace MyanmarCaptcha;

/**
 * Myanmar Captcha Package
 *
 * @version 1.0.0
 * @author  NayZawOo <nayzawoo.me@gmail.com>
 *
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 */

use Colors\RandomColor;
use Intervention\Image\Image;
use Intervention\Image\ImageManager;
use RandomLib\Factory;

/**
 * Class Captcha
 *
 * @property CaptchaStringInterface $captchaString
 * @package MyanmarCaptcha\Captcha
 */
class Captcha implements CaptchaBuilderInterface
{
    protected $width = 120;

    protected $height = 40;

    /**
     * @var ImageManager
     */
    protected $imageManager;

    /**
     * @var Image
     */
    public $mainCanvas;

    protected $bgColor;

    protected $enabledEffects = true;

    protected $enabledDistortion = true;

    protected $invert = false;

    protected $bgImage;

    protected $fontPath;

    protected $textColor;

    /**
     * @var Image
     */
    public $backgroundCanvas;

    protected $horizontalLines = 0;

    protected $verticalLines = 0;

    /**
     * @var Image
     */
    public $captchaImage;

    protected $dots = 1000;

    protected $fontSize = 22;

    /**
     * @var CaptchaStringInterface
     */
    protected $stringGenerator;

    /**
     * @var \RandomLib\Generator
     */
    protected $randomGenerator;

    /**
     * @var Image
     */
    protected $effectCanvas;

    /**
     * @var string
     */
    public $randomColor;

    public $singleColor = false;

    public function __construct(CaptchaStringInterface $stringGenerator)
    {
        $this->fontPath = __DIR__.'/assets/mon3.ttf';
        $this->randomGenerator = (new Factory)->getMediumStrengthGenerator();
        $this->imageManager = new ImageManager();
        $this->randomColor = $this->randomColor();
        $this->captchaString = $stringGenerator;
    }

    /**
     * @return $this
     */
    public function build()
    {
        $this->createCanvas();

        // Text
        $this->renderText($this->mainCanvas);
        $this->distortImage($this->mainCanvas);

        // Background
        $this->drawBackground($this->backgroundCanvas);
        $this->mergeCanvas($this->backgroundCanvas, $this->mainCanvas);

        // Effects
        $this->drawVerticalLines($this->effectCanvas);
        $this->drawHorizontalLines($this->effectCanvas);
        $this->distortImage($this->effectCanvas);
        $this->mergeCanvas($this->mainCanvas, $this->effectCanvas);

        $this->captchaImage = $this->mainCanvas;
        $this->invertImage($this->captchaImage);

        return $this;
    }

    protected function createCanvas()
    {
        $this->mainCanvas = $this->imageManager->canvas($this->width, $this->height);
        $this->backgroundCanvas = $this->imageManager->canvas($this->width, $this->height, $this->bgColor);
        $this->effectCanvas = $this->imageManager->canvas($this->width, $this->height);
    }

    protected function mergeCanvas(Image $background, Image $mask)
    {
        $this->mainCanvas = $background->insert($mask);
    }

    /**
     * @link http://www.codeproject.com/Articles/26595/CAPTCHA-Image-in-PHP
     *
     * @param Image $image
     * @param int   $width
     * @param int   $height
     * @param int   $x_period
     * @param int   $x_amplitude
     *
     * @return $this
     */
    protected function distortImage(Image $image, $width = 0, $height = 0, $x_period = 10, $x_amplitude = 5)
    {
        if ( ! $this->enabledDistortion) {
            return $this;
        }

        $tempImage = $image->getCore();

        $width = $width ? $width : $this->width;
        $height = $height ? $height : $this->height;
        $canvas = $this->imageManager->canvas($width, $height)->getCore();
        $xp = $x_period;
        $k = $this->rand(0, 10);

        for ($a = 0; $a < $width; $a++) {
            imagecopy($canvas, $tempImage, $a - 1, sin($k + $a / $xp) * $x_amplitude, $a, 0, 1, $height);
        }

        $image->setCore($canvas);

        return $this;
    }

    protected function invertImage(Image $image)
    {
        if ($this->invert) {
            $image->invert();
        }

        return $this;
    }

    protected function drawBackground(Image $image)
    {
        if ($this->bgColor) {
            $this->backgroundCanvas = $this->imageManager->canvas($this->width, $this->height, $this->bgColor);

            return $this;
        }

        if ($this->bgImage) {
            $this->backgroundCanvas = $this->imageManager->make($this->bgImage)->resize($this->width, $this->height);

            return $this;
        }

        $tempImage = $this->imageManager->canvas($this->width, $this->height, '#FFFFFF')->getCore();

        $pixel_color = imagecolorallocate($tempImage, $this->rand(100, 150), $this->rand(100, 150),
            $this->rand(100, 150));

        for ($i = 0; $i < $this->dots; $i++) {
            imagesetpixel($tempImage, $this->rand() % $this->width, $this->rand() % $this->height, $pixel_color);
        }

        $this->backgroundCanvas = $image->setCore($tempImage);

        return $this;
    }

    protected function drawHorizontalLines(Image $image)
    {
        if ( ! $this->horizontalLines) {
            return $this;
        }
        $tempImage = $image->getCore();

        for ($i = 0; $i < $this->horizontalLines; $i++) {
            if ($this->singleColor) {
                $rgba = $this->hex2rgb($this->randomColor);
                $line_color = imagecolorallocate($tempImage, $rgba[0], $rgba[1], $rgba[2]);
            } else {
                $line_color = imagecolorallocate($tempImage, mt_rand(100, 255), mt_rand(100, 255), mt_rand(100, 255));
            }

            imagesetthickness($tempImage, (mt_rand(5, 30) / 10));
            $x1 = $this->rand(0, $this->width / 3);
            $x2 = $this->rand($this->width / 3, $this->width);
            $y1 = $this->rand(0, $this->height);
            $y2 = $this->rand(0, $this->height);
            imageline($tempImage, $x1, $y1, $x2, $y2, $line_color);
        }

        $image->setCore($tempImage);

        return $this;
    }

    protected function drawVerticalLines(Image $image)
    {
        if ( ! $this->verticalLines) {
            return $this;
        }

        $tempImage = $image->getCore();
        $lineSpace = $this->width / $this->verticalLines;

        for ($i = 0; $i < $this->verticalLines; $i++) {
            if ($this->singleColor) {
                $rgba = $this->hex2rgb($this->randomColor);
                $lineColor = imagecolorallocate($tempImage, $rgba[0], $rgba[1], $rgba[2]);
            } else {
                $lineColor = imagecolorallocate($tempImage, mt_rand(100, 255), mt_rand(100, 255), mt_rand(100, 255));
            }

            imageline($tempImage, $i * $lineSpace, 0, $i * $lineSpace + $lineSpace / 2, $this->height, $lineColor);
            imageline($tempImage, $i * $lineSpace, 0, $i * $lineSpace - $lineSpace / 2, $this->height, $lineColor);
        }

        $image->setCore($tempImage);

        return $this;
    }

    /**
     * @param Image $image
     *
     * @return $this
     */
    protected function renderText(Image $image)
    {
        $phrase = $this->captchaString->getGeneratedQuestion('mm', 'array');
        $length = count($phrase);
        $size = $this->width / $length - $this->rand(0, 2) - 1;
        $box = imagettfbbox($size, 0, $this->fontPath, (pow(10, $length + 1))."");
        $textWidth = $box[2] - $box[0];
        $textHeight = $box[1] - $box[7];
        $x = (($this->width - $textWidth) / 2) + 2;
        $y = ($this->height - $textHeight) / 2 + $size;

        for ($i = 0; $i < $length; $i++) {
            $box = imagettfbbox($size, 0, $this->fontPath, "=");
            $w = $box[2] - $box[0];
            $offset = 0;
            if ($this->enabledEffects) {
                $offset = $this->rand(-1, 1);
            }
            $str = $phrase[$i];
            $image->text($str, $x, $y + $offset, function ($font) {
                $font->file($this->fontPath);
                if ($this->enabledEffects) {
                    $font->size($this->rand($this->fontSize - 1, $this->fontSize + 1));
                } else {
                    $font->size($this->fontSize);
                }
                if ($this->enabledEffects) {
                    $font->angle($this->rand(-10, 10));
                }
                if ($textColor = $this->textColor) {
                    $font->color($textColor);
                } else {
                    if ($this->singleColor) {
                        $font->color($this->randomColor);
                    } else {
                        $font->color($this->randomColor());
                    }
                }
            });
            $x += $w;
        }

        return $this;
    }

    /**
     * @param $path
     *
     * @return $this
     */
    public function save($path)
    {
        $this->mainCanvas->save($path);

        return $this;
    }

    /**
     * Generate a random dark color
     *
     * @return string
     */
    protected function randomColor()
    {
        return RandomColor::one([
            'luminosity' => 'dark',
            'hue'        => [ "red", "green", "purple" ]
        ]);
    }

    protected function validateColor($color)
    {
        if ( ! preg_match("/^#(?:[0-9a-f]{3}){1,2}$/i", $color)) {
            throw new \Exception("Invalid Hex Color");
        }
    }

    /**
     * ======================================================
     * =================== Public ===========================
     * ======================================================
     */

    /**
     * @return $this
     */
    public function invert()
    {
        $this->invert = true;;

        return $this;
    }

    /**
     * @param string $color
     *
     * @return $this
     * @throws \Exception
     */
    public function textColor($color)
    {
        $this->validateColor($color);
        $this->textColor = $color;

        return $this;
    }

    /**
     * @param string $color
     *
     * @return $this
     */
    public function backgroundColor($color)
    {
        $this->validateColor($color);
        $this->bgColor = $color;

        return $this;
    }

    /**
     * @param $path
     *
     * @return $this
     */
    public function backgroundImage($path)
    {
        $this->bgImage = $path;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function verticalLines($lines = 50)
    {
        $this->verticalLines = $lines;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function response($imageType = 'jpg', $quality = 100)
    {
        return $this->captchaImage->response($imageType, $quality);
    }

    public function horizontalLines($lines = 3)
    {
        $this->horizontalLines = $lines;

        return $this;
    }

    /**
     * @param boolean $disable
     *
     * @return $this
     */
    public function disableDistortion($disable = true)
    {
        $this->enabledDistortion = ! $disable;

        return $this;
    }

    /**
     * @param int $width
     *
     * @return $this
     */
    public function width($width)
    {
        $this->width = (int) $width;

        return $this;
    }

    /**
     * @param int $height
     *
     * @return $this
     */
    public function height($height)
    {
        $this->height = (int) $height;

        return $this;
    }

    /**
     * @param $disable
     *
     * @return $this
     */
    public function disableEffects($disable = true)
    {
        $this->enabledEffects = ! $disable;
        $this->textColor("#444444");
        $this->backgroundColor("#FFFFFF");
        $this->horizontalLines(0);
        $this->verticalLines(0);
        $this->disableDistortion();
        $this->dots(0);

        return $this;
    }

    public function getCanvas()
    {
        return $this->captchaImage;
    }

    public function dots($dotsNumber)
    {
        $this->dots = (int) $dotsNumber;

        return $this;
    }

    /**
     * @param int $fontSize
     *
     * @return $this
     */
    public function fontSize($fontSize)
    {
        $this->fontSize = $fontSize;

        return $this;
    }

    /**
     * @param $fontPath
     *
     * @return $this
     */
    public function fontPath($fontPath)
    {
        $this->fontPath = $fontPath;

        return $this;
    }

    /**
     * @param $ans
     *
     * @return bool
     */
    public function check($ans)
    {
        return $this->captchaString->check($ans);
    }

    /**
     * @return int
     */
    public function getAnswer()
    {
        return $this->captchaString->getAnswer();
    }

    /**
     * Generate Height Quality Random Integer
     *
     * @param $min
     * @param $max
     *
     * @return int
     */
    protected function rand($min = 0, $max = 1)
    {
        return $this->randomGenerator->generateInt($min, $max);
    }

    protected function hex2rgb($hex)
    {
        $hex = str_replace("#", "", $hex);

        if (strlen($hex) == 3) {
            $r = hexdec(substr($hex, 0, 1).substr($hex, 0, 1));
            $g = hexdec(substr($hex, 1, 1).substr($hex, 1, 1));
            $b = hexdec(substr($hex, 2, 1).substr($hex, 2, 1));
        } else {
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
        }
        $rgb = [ $r, $g, $b ];

        return $rgb; // returns an array with the rgb values
    }
}
