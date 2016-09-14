<?php

use MyanmarCaptcha\Captcha;

class CaptchaStringTest extends \PHPUnit\Framework\TestCase {

    /**
     * @var Captcha
     */
    private $generator;

    public function setUp() {
        $this->generator = new Captcha();
    }

    public function testGenerateRandomQuestion() {
        for($i = 0; $i < 10; $i++) {
            $generator = $this->generator->generateRandomQuestion();
            $qMM =$generator->getQuestion();
            $qEN = $generator->getQuestion(false);
            $ans = $generator->getAnswer();

            $this->assertGreaterThanOrEqual(5, strlen($qMM), "question should longer than 5");
            $this->assertGreaterThanOrEqual(5, strlen($qEN), "question should longer than 5");
            $this->assertLessThan(strlen($qMM), strlen($qEN), "en string should shorter than mm");
            $this->assertStringEndsWith("=?", $qMM, "question should end with =?");

            // answer
            $this->assertInternalType("integer", $ans);
            $this->assertGreaterThan(0, $ans);

            $this->assertTrue($generator->check($ans));
            $this->assertNotTrue($generator->check(0));

        }
    }
}