<?php

namespace App\Tests\Repository;

use App\Entity\Hello;
use App\Repository\HelloRepository;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;


class HelloRepositoryTest extends KernelTestCase // Класс для функционального тестирования репозитория Hello, использующий Symfony KernelTestCase
{
    private EntityManager $em; // EntityManager для работы с базой данных
    private HelloRepository $helloRepository; // Тестируемый репозиторий
    private ORMExecutor $executor; // Executor для загрузки и очистки фикстур

    protected function setUp(): void // Метод, выполняемый перед каждым тестом
    {
        $kernel = self::bootKernel(); // Запускаем Symfony-кернел (ядро приложения)

        $this->assertSame('test', $kernel->getEnvironment()); // Проверяем, что используется тестовое окружение

        // Получаем EntityManager из контейнера Symfony
        $em = $kernel->getContainer()->get('doctrine.orm.entity_manager');
        $this->assertInstanceOf(EntityManager::class, $em); // Убеждаемся, что это действительно EntityManager

        // Создаём ORMExecutor с очищающим пургером — для работы с тестовыми данными
        $this->executor = new ORMExecutor($em, new ORMPurger());

        // Получаем репозиторий Hello для тестирования
        $this->helloRepository = $em->getRepository(Hello::class);
    }

    protected function tearDown(): void // Метод, выполняемый после каждого теста
    {
        $this->executor->getPurger()->purge(); // Очищаем базу данных после теста
    }

    public function testCreateLuckyNumber(): void // Тестирует метод создания записи с "счастливым числом"
    {
        $number = '777'; // Пример "счастливого числа" для записи

        // Создаём объект Hello с указанным числом через тестируемый метод
        $hello = $this->helloRepository->createLuckyNumber($number);

        $this->assertInstanceOf(Hello::class, $hello); // Проверяем, что возвращённый объект — экземпляр Hello
        $this->assertNotNull($hello->getId()); // Проверяем, что у объекта есть ID (значит, он сохранён)
        $this->assertEquals($number, $hello->getLuckyNumber()); // Проверяем, что число установлено корректно

        // Проверяем, что объект действительно сохранён в базе данных
        $found = $this->helloRepository->find($hello->getId());
        $this->assertNotNull($found); // Объект найден
        $this->assertEquals($number, $found->getLuckyNumber()); // Проверяем число
    }
}
