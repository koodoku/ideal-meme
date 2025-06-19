<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class HelloControllerTest extends WebTestCase
{
    public function testRootRedirectsToLogin(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');
        $this->assertResponseRedirects('/login');
    }

    public function testHello(): void
    {
        $client = static::createClient();
        $client->request('GET', '/hello');
        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('Hello World!', $client->getResponse()->getContent());
    }

    /**
     * @dataProvider provideHelloNames
     */
    public function testHelloName(string $name): void
    {
        $client = static::createClient();
        $client->request('GET', "/hello/$name");
        $this->assertResponseIsSuccessful();
        $this->assertEquals("Hello $name", $client->getResponse()->getContent());
    }

    public function testLuckyNumber(): void
    {
        $client = static::createClient();
        // Получаем lucky number через сервис (или подставляем невалидный)
        $client->request('GET', '/hello/lucky/999999');
        $this->assertResponseIsSuccessful();
        $this->assertEquals('Fail', $client->getResponse()->getContent());
    }

    public static function provideHelloNames(): array
    {
       return [
           'Первое имя' => ['Иван'],
           'Второе имя' => ['Петр'],
           'Третья имя' => ['Steve'],
           'Четвертое имя' => ['Abacaba'],
       ];
    }
}
