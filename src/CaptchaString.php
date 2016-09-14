<?php
/**
 * Created by PhpStorm.
 * User: nay
 * Date: 9/14/16
 * Time: 5:52 PM
 */

namespace MyanmarCaptcha;


trait CaptchaString
{

    protected $question;

    protected $answer;

    protected $questionInMM;

    protected $ends = "=?";

    protected $trans
        = [
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

    public function setQuestion($question, $inmyanmar = false)
    {
        if ($inmyanmar) {
            $this->questionInMM = $question;

            return;
        }
        $this->question = $question;
    }

    public function getQuestion($translated = true)
    {
        if (!$this->question) {
             throw new \Exception("Question is not generated");
        }

        if ($translated) {
            return $this->questionInMM . $this->ends;
        }

        return $this->question . $this->ends;
    }

    public function getQuestionInArray()
    {
        return preg_split("//u", $this->getQuestion(), -1, PREG_SPLIT_NO_EMPTY);
    }

    /**
     * @param mixed $answer
     */
    public function setAnswer($answer)
    {
        $this->answer = $answer;
    }

    public function getAnswer()
    {
        return $this->answer;
    }

    public function generateRandomQuestion()
    {
        $type = rand(0, 3);
        $question = '';
        switch ($type) {
            case 0:
                // [1-20]+[1-9]
                $question = rand(1, 20) . "+" . rand(1, 9);
                break;
            case 1:
                // [1-9]0-[1-9]
                $question = rand(1, 9) . "0-" . rand(1, 8);
                break;
            case 2:
                // 20*[2-9]
                $question = "20*" . rand(2, 9);
                break;
            case 3:
                // 30*[2-6]
                $question = "30*" . rand(2, 6);
                break;

        }

        $this->setQuestion($question);
        $questionInMM = $this->translateToMyanmar($question);
        $this->setQuestion($questionInMM, true);
        $answer = $this->calculateQuestion($question);
        $this->setAnswer($answer);

        return $this;
    }

    protected function calculateQuestion($question)
    {
        $answer = null;
        if (preg_match(
                '/(\d+)(?:\s*)([\+\-\*\/])(?:\s*)(\d+)/', $question, $matches
            ) !== false
        ) {
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
            }
        }
        if ($answer === null) {
            throw new \Exception("Calculation Error!");
        }

        return $answer;
    }

    protected function translateToMyanmar($subject)
    {
        return str_replace(
            array_keys($this->trans), array_values($this->trans), $subject
        );
    }

    public function check($answer)
    {
        // TODO
        if ($answer === $this->answer) {
            return true;
        }

        return false;
    }

    /**
     * @param string $ends
     */
    public function setEnds($ends = "=?")
    {
        $this->ends = $ends;
    }

    protected function encodeChar($text)
    {
        return mb_convert_encoding($text, "HTML-ENTITIES", "UTF-8");
    }
}