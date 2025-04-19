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
        $this->helloRepository = $this->createMock(HelloRepository::class);// Создание мок-объекта репозитория HelloRepository для тестирования
        //Mock-объект позволяет имитировать поведение реального объекта, что полезно для изоляции тестируемого кода от зависимостей.
        $this->helloService = new HelloService(
            $this->helloRepository
        );
    }
    /**
     * @dataProvider provideLuckyNumbers
     */

    public function testGenerateLuckyNumber(string $expectedLuckyNumber):void
    {
        $expectedLuckyNumber = '1234';//метод expected возвращает строку 
        $helloObject = $this->createMock(Hello::class); 
        $helloObject //
            ->expects($this->once())
            ->method('getLuckyNumber')
            ->willReturn($expectedLuckyNumber)
        ;
        $this->helloRepository //
            ->expects($this->once()) 
            ->method('createLuckyNumber')
            ->willReturn($helloObject)
        ;


        $this->assertEquals( // Проверка на то что соответствует ожидаемому значению
            $expectedLuckyNumber,
            $this->helloService->generateLuckyNumber()
        );
    }
    public static function provideLuckyNumbers(): array
    {
        return [
            'Один'=> ['1'],
            'Два'=> ['2'],
            'Три'=> ['3'],
        ];
    }
}
