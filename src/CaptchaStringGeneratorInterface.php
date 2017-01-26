<?php

namespace MyanmarCaptcha;

interface CaptchaStringGeneratorInterface
{
    public function generateQuestion();

    /**
     * @param string $lang
     * @param string $type ['string', 'array']
     * @param string $end
     *
     * @return array|string
     */
    public function getGeneratedQuestion($lang = 'mm', $type = 'string', $end = '=?');

    /**
     * @return int
     */
    public function getAnswer();

    /**
     * @param $ans
     *
     * @return bool
     */
    public function check($ans);
}