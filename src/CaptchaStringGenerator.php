<?php

namespace MyanmarCaptcha;

use RandomLib\Factory;

class CaptchaStringGenerator implements CaptchaStringGeneratorInterface
{
    protected $question;

    protected $trans = [
        "0" => "၀",
        "1" => "၁",
        "2" => "၂",
        "3" => "၃",
        "4" => "၄",
        "5" => "၅",
        "6" => "၆",
        "7" => "၇",
        "8" => "၈",
        "9" => "၉",
        "*" => "x",
        "/" => "÷",
    ];

    /**
     * @var \RandomLib\Generator
     */
    protected $randomGenerator;

    /**
     * CaptchaString constructor.
     */
    public function __construct()
    {
        $this->randomGenerator = (new Factory)->getHighStrengthGenerator();
        $this->question = $this->generateQuestion();
    }

    /**
     * {@inheritdoc}
     */
    public function getGeneratedQuestion($lang = 'mm', $type = 'string', $end = '=?')
    {
        $question = $this->question.$end;
        if ($lang == 'mm') {
            $question = $this->translateToMyanmar($question);
        }

        if ($type == 'string') {
            return $question;
        }

        return preg_split("//u", $question, -1, PREG_SPLIT_NO_EMPTY);
    }

    /**
     * @return string
     */
    public function generateQuestion()
    {
        $type = $this->generateInt(0, 3);
        switch ($type) {
            case 0:
                // [1-20]+[1-9]
                return $this->generateInt(1, 30)."+".$this->generateInt(1, 9);
            case 1:
                // [1-9]0-[1-9]
                return $this->generateInt(1, 9)."0-".$this->generateInt(1, 8);
            case 2:
                // [10,20,30]*[2-5]
                $items = [ 10, 20, 30 ];

                return $items[array_rand($items)]."*".$this->generateInt(2, 9);
            case 3:
                // 30/[2,3,10,15]
                $items = [ 2, 3, 10, 15 ];

                return "30/".$items[array_rand($items)];
        }
    }

    /**
     * @param $subject
     *
     * @return string
     */
    public function translateToMyanmar($subject)
    {
        return str_replace(array_keys($this->trans), array_values($this->trans), $subject);
    }

    /**
     * @return int
     */
    public function getAnswer()
    {
        return $this->calculate($this->question);
    }

    /**
     * @param $ans
     *
     * @return bool
     */
    public function check($ans)
    {
        return $ans === $this->getAnswer();
    }

    /**
     * Calculate Math String instead of eval()
     *
     * @param $mathString
     *
     * @return float|int
     * @throws \Exception
     */
    protected function calculate($mathString)
    {
        if (preg_match('/(\d+)(?:\s*)([\+\-\*\/])(?:\s*)(\d+)/', $mathString, $matches) !== false) {
            $operator = $matches[2];

            switch ($operator) {
                case '+':
                    $answer = $matches[1] + $matches[3];
                    break;
                case '-':
                    $answer = $matches[1] - $matches[3];
                    break;
                case '*':
                    $answer = $matches[1] * $matches[3];
                    break;
                case '/':
                    $answer = $matches[1] / $matches[3];
                    break;
                default:
                    throw new \Exception("Invalid Math String");
            }

            return $answer;
        }

        throw new \Exception("Invalid Math String");
    }

    protected function generateInt($min, $max)
    {
        return $this->randomGenerator->generateInt($min, $max);
    }
}