<?php

namespace App\Service;

use App\Repository\HelloRepository;

class HelloService
{
    private const MIN_LUCKY_NUMBER = 1;
    private const MAX_LUCKY_NUMBER = 3;

    public function __construct(
        private readonly HelloRepository $helloRepository
    ) {}

    public function generateLuckyNumber(): string
    {
        $number = rand(self::MIN_LUCKY_NUMBER, self::MAX_LUCKY_NUMBER);

        if ($number % 2 === 0) {
            $luckNumber = $this->helloRepository->createLuckyNumber($number . ' EVEN');
        } else {
            $luckNumber = $this->helloRepository->createLuckyNumber($number . ' ODD');
        }

        return $luckNumber->getLuckyNumber();
    }
}
