<?php
/**
 * Created by PhpStorm.
 * User: nay
 * Date: 9/14/16
 * Time: 8:29 PM
 */

namespace MyanmarCaptcha;


interface CaptchaBuilderInterface
{
    /**
     * Init Captcha Image Builder
     */
    public function build();

    /**
     * Save Captcha Image to disk
     *
     * @param $path
     *
     * @return $this
     */
    public function save($path);

    /**
     * Encoded image data after raw HTTP header is sent. If you are in a
     * Laravel framework environment the method will return a
     * Illuminate\Http\Response with the corresponding header fields already
     * set.
     *
     * @return mixed
     */
    public function response();

    //image
    public function width($width);
    public function height($height);

    //text
    public function textColor($color);
    public function fontSize($size);

    //background
    public function backgroundColor($color);
    public function backgroundImage($path);

    //effects
    public function horizontalLines($line);
    public function verticalLines($lines);
    public function disableDistortion();
    public function invert();
    public function dots($dotsNumber);
    public function getCanvas();
}