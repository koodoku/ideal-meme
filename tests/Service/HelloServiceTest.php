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
        $helloObject = $this->createMock(Hello::class);//мок
        $helloObject
            ->expects($this->once())
            ->method('getLuckyNumber') //Метод getLuckyNumber() мока Hello настраивается, чтобы вернуть ожидаемое число
            ->willReturn($expectedLuckyNumber)
        ;

        $this->helloRepository//мок репозитория
            ->expects($this->once())
            ->method('createLuckyNumber')
            ->willReturn($helloObject)//Этот мок говорит: если сервис вызовет createLuckyNumber(), верни заранее подготовленный Hello с нужным lucky number.
        ;
        // Act (Действие)
        $actualLuckyNumber = $this->helloService->generateLuckyNumber();//Здесь выполняется одно конкретное действие — вызов метода generateLuckyNumber() у сервиса.

        //Assert (Проверка) //Проверяется, что результат вызова generateLuckyNumber() совпадает с ожидаемым значением.
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
