<?php

namespace App\Tests\Service;

use App\Entity\Hello;
use App\Repository\HelloRepository;
use App\Service\HelloService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class HelloServiceTest extends TestCase
{
    private HelloRepository|MockObject $helloRepository;
    private HelloService $helloService;
    protected function setUp(): void
    {
        $this->helloRepository = $this->createMock(HelloRepository::class);

        $this->helloService = new HelloService(
            $this->helloRepository
        );
    }


    /**
     * @dataProvider provideLuckyNumbers
     */
    public function testGenerateLuckyNumber(string $expectedLuckyNumber): void

    {// Arrange (Подготовка)
        $helloObject = $this->createMock(Hello::class);
        $helloObject
            ->expects($this->once())
            ->method('getLuckyNumber')
            ->willReturn($expectedLuckyNumber)
        ;

        $this->helloRepository//мок репозитория
            ->expects($this->once())
            ->method('createLuckyNumber')
            ->willReturn($helloObject)
        ;
        // Act (Действие)
        $actualLuckyNumber = $this->helloService->generateLuckyNumber();

        //Assert (Проверка)
        $this->assertEquals(
            $expectedLuckyNumber,
            $actualLuckyNumber
        );
    }

    public static function provideLuckyNumbers(): array
    {
        return [
            'Один' => ['1'],
            'Два' => ['2'],
            'Три' => ['3']
        ];
    }
}

//public function testSumOfTwoNumbers()
//{
//    // Arrange (Подготовка)
//    $calculator = new Calculator();
//    $firstNumber = 3;
//    $secondNumber = 4;
//    $expectedResult = 7;
//
//    // Act (Действие)
//    $actualResult = $calculator->sum($firstNumber, $secondNumber);
//
//    // Assert (Проверка)
//    $this->assertEquals($expectedResult, $actualResult);
//}
