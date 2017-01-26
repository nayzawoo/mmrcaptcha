<?php

use MyanmarCaptcha\CaptchaStringGenerator;

class CaptchaStringTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \MyanmarCaptcha\Captcha
     */
    protected $captcha;

    /**
     * @var CaptchaStringGenerator
     */
    private $generator;

    public function setUp()
    {
        $this->generator = new CaptchaStringGenerator;
        $this->captcha = new \MyanmarCaptcha\Captcha($this->generator);
    }

    public function testGenerateRandomQuestion()
    {
        for ($i = 0; $i < 10; $i++) {
            $question = $this->generator->getGeneratedQuestion();
            $answer = $this->generator->getAnswer();
            $this->assertGreaterThanOrEqual(4, strlen($question), "question should longer than 4");
            $this->assertStringEndsWith("=?", $question, "question should end with =?");

            // answer
            $this->assertInternalType("integer", $answer);
            $this->assertInternalType("integer", $this->captcha->getAnswer());
            $this->assertGreaterThan(0, $answer);
            $this->assertGreaterThan(0, $this->captcha->getAnswer());

            $this->assertTrue($this->generator->check($answer));
            $this->assertTrue($this->captcha->check($answer));
            $this->assertFalse($this->generator->check(0));
            $this->assertFalse($this->captcha->check(0));
            $this->assertFalse($this->generator->check(true));
        }
    }
}