<?php

namespace MyanmarCaptcha;

/**
 * Myanmar Captcha Package
 *
 * @version 1.x
 * @author  Bodawpaya <bodawphaya@gmail.com>
 *
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 */

use Intervention\Image\Image;
use Intervention\Image\ImageManager;
use Colors\RandomColor;

/**
 * Class Captcha
 *
 * @package MyanmarCaptcha\Captcha
 */
class Captcha implements CaptchaBuilderInterface
{
    use CaptchaString;

    /**
     * @var int
     */
    protected $width = 120;

    /**
     * @var int
     */
    protected $height = 40;

    /**
     * @var ImageManager
     */
    protected $imageManager;

    /**
     * @var Image
     */
    protected $mainCanvas;

    /**
     * @var string
     */
    protected $bgColor;

    /**
     * @var bool
     */
    protected $enabledEffects = true;


    /**
     * @var bool
     */
    protected $enabledDistortion = true;

    /**
     * @var bool
     */
    protected $invert = false;

    /**
     * @var string
     */
    protected $bgImage;

    /**
     * @var string
     */
    protected $fontPath;

    /**
     * @var
     */
    protected $textColor;

    /**
     * @var Image
     */
    protected $backgroundCanvas = false;

    /**
     * @var
     */
    protected $horizontalLines = 0;

    /**
     * @var int
     */
    protected $verticalLines = 0;

    /**
     * @var Image
     */
    protected $captchaImage;

    /**
     * @var int
     */
    protected $dots = 1000;

    /**
     * @var int
     */
    protected $fontSize = 22;

    public function __construct()
    {
        $this->imageManager = new ImageManager();
        $this->fontPath = __DIR__ . '/assets/mon3.ttf';
    }

    /**
     * @return $this
     */
    public function build()
    {
        $this->createCanvas();

        // Text
        $this->writeText($this->mainCanvas);

        // Background
        $this->drawBackground($this->backgroundCanvas);

        // Effects

        $this->disortImage($this->mainCanvas);
        $this->drawVerticalLines($this->mainCanvas);
        $this->drawHorizontalLines($this->mainCanvas);
        $this->mergeCanvas($this->backgroundCanvas, $this->mainCanvas);
        $this->captchaImage = $this->mainCanvas;
        $this->invertImage($this->captchaImage);
        return $this;
    }

    protected function createCanvas()
    {
        $this->mainCanvas = $this->imageManager->canvas(
            $this->width,
            $this->height
        );

        $this->backgroundCanvas = $this->imageManager->canvas(
            $this->width,
            $this->height,
            $this->bgColor
        );
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
     *
     * @return $this
     */
    protected function disortImage(Image $image, $width = 0, $height = 0)
    {
        if (!$this->enabledDistortion) {
            return $image;
        }

        $x_period = 10;
        $x_amplitude = 5;
        $tempImage = $this->mainCanvas->getCore();
        $width = $width ? $width : $this->width;
        $height = $height ? $height : $this->height;
        $canvas = $this->imageManager->canvas($width, $height)->getCore();
        $xp = $x_period;
        $k = rand(0, 100);

        for ($a = 0; $a < $width; $a++) {
            imagecopy(
                $canvas, $tempImage, $a - 1,
                sin($k + $a / $xp) * $x_amplitude,
                $a, 0, 1, $height
            );
        }

        return $image->setCore($canvas);
    }

    protected function invertImage(Image $image) {
        if ($this->invert) {
            $image->invert();
        }
        return $image;
    }

    protected function drawBackground(Image $image)
    {
        if ($this->bgColor) {
            $this->backgroundCanvas = $this->imageManager->canvas(
                $this->width,
                $this->height,
                $this->bgColor
            );

            return $image;
        }

        if ($this->bgImage) {
            $this->backgroundCanvas = $this->imageManager->make($this->bgImage)
                ->resize(
                    $this->width,
                    $this->height
                );

            return $image;
        }

        $tempImage = $this->imageManager->canvas($this->width, $this->height, '#FFFFFF')->getCore();

        $pixel_color = imagecolorallocate(
            $tempImage, rand(100, 150), rand(100, 150), rand(100, 150)
        );

        for ($i = 0; $i < $this->dots; $i++) {
            imagesetpixel(
                $tempImage, rand() % $this->width, rand() % $this->height,
                $pixel_color
            );
        }

        $this->backgroundCanvas = $image->setCore($tempImage);
        return $this->backgroundCanvas;
    }

    protected function drawHorizontalLines(Image $image)
    {
        if (!$this->horizontalLines) {
            return $image;
        }
        $tempImage = $image->getCore();

        for ($i = 0; $i < $this->horizontalLines; $i++) {
            $line_color = imagecolorallocate(
                $tempImage, rand(100, 200), rand(100, 200), rand(100, 200)
            );
            imageline(
                $tempImage, 0, rand() % $this->height, 200,
                rand() % $this->width, $line_color
            );
        }

        return $image->setCore($tempImage);
    }

    protected function drawVerticalLines(Image $image)
    {
        if (!$this->verticalLines) {
            return $image;
        }

        $tempImage = $image->getCore();
        $lineSpace = $this->width / $this->verticalLines;

        for ($i = 0; $i < $this->verticalLines; $i++) {
            $lineColor = imagecolorallocate(
                $tempImage, rand(100, 200), rand(100, 200), rand(100, 200)
            );

            imageline(
                $tempImage, $i * $lineSpace, 0,
                $i * $lineSpace + $lineSpace /2, $this->height, $lineColor
            );
            imageline(
                $tempImage, $i * $lineSpace, 0,
                $i * $lineSpace - $lineSpace /2, $this->height, $lineColor
            );
        }

        return $image->setCore($tempImage);
    }

    protected function writeText(Image $image)
    {
        $this->generateRandomQuestion();
        $phrase = $this->getQuestionInArray();
        $length = count($phrase);
        $size = $this->width / $length - rand(0, 2) - 1;
        $box = imagettfbbox(
            $size, 0, $this->fontPath, (pow(10, $length + 1)) . ""
        );
        $textWidth = $box[2] - $box[0];
        $textHeight = $box[1] - $box[7];
        $x = (($this->width - $textWidth) / 2) + 2;
        $y = ($this->height - $textHeight) / 2 + $size;

        for ($i = 0; $i < $length; $i++) {
            $box = imagettfbbox($size, 0, $this->fontPath, "=");
            $w = $box[2] - $box[0];
            $offset = 0;
            if ($this->enabledEffects) {
                $offset = rand(-5, 0);
            }
            $str = $this->encodeChar($phrase[$i]);
            $image->text(
                $str,
                $x,
                $y + $offset,
                function ($font) {
                    $font->file($this->fontPath);
                    if ($this->enabledEffects) {
                        $font->size(rand($this->fontSize - 1, $this->fontSize + 1));
                    } else {
                        $font->size($this->fontSize);
                    }
                    if ($this->enabledEffects) {
                        $font->angle(rand(-10, 10));
                    }
                    if ($textColor = $this->textColor) {
                        $font->color($textColor);
                    } else {
                        $font->color($this->randomColor());
                    }
                }
            );
            $x += $w;
        }

        return $image;
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
        return RandomColor::one(
            [
                'luminosity' => 'dark',
                'hue'        => array("red", "green", "purple")
            ]
        );
    }

    protected function validateColor($color)
    {
        if (!preg_match("/^#(?:[0-9a-f]{3}){1,2}$/i", $color)) {
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
        $this->enabledDistortion = !$disable;

        return $this;
    }

    /**
     * @param int $width
     *
     * @return $this
     */
    public function width($width)
    {
        $this->width = (int)$width;

        return $this;
    }

    /**
     * @param int $height
     *
     * @return $this
     */
    public function height($height)
    {
        $this->height = (int)$height;

        return $this;
    }

    /**
     * @param $disable
     *
     * @return $this
     */
    public function disableEffects($disable = true)
    {
        $this->enabledEffects = !$disable;
        $this->textColor("#444444");
        $this->backgroundColor("#FFFFFF");
        $this->horizontalLines(0);
        $this->verticalLines(0);
        $this->disableDistortion();
        $this->dots(0);
        return $this;
    }

    public function getCanvas() {
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
}
