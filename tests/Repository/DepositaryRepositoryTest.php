<?php

namespace App\Tests\Repository;
use App\Entity\Depositary;
use App\Repository\DepositaryRepository;
use App\Tests\Fixture\DepositaryFixture;
use App\Tests\Fixture\StockFixture;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class DepositaryRepositoryTest extends KernelTestCase // Класс теста, наследует KernelTestCase для интеграционных тестов
{
    private StockFixture $stockFixture; // Фикстура для акций
    private DepositaryFixture $depositaryFixture; // Фикстура для депозитариев
    private DepositaryRepository $depositaryRepository; // Репозиторий, который тестируем
    private ORMExecutor $executor; // Используется для загрузки и очистки фикстур

    // Метод setUp() вызывается перед каждым тестом
    protected function setUp(): void
    {
        $kernel = self::bootKernel(); // Запуск Symfony Kernel

        $this->assertSame('test', $kernel->getEnvironment()); // Проверка, что окружение тестовое

        // Получаем EntityManager из контейнера
        $em = $kernel->getContainer()->get('doctrine.orm.entity_manager');

        // Проверяем, что это действительно EntityManager
        $this->assertInstanceOf(EntityManager::class, $em);

        $loader = new Loader(); // Создаём загрузчик фикстур

        // Добавляем фикстуру StockFixture
        $loader->addFixture($this->stockFixture = new StockFixture());

        // Добавляем фикстуру DepositaryFixture
        $loader->addFixture($this->depositaryFixture = new DepositaryFixture());

        // Создаём ORMExecutor с EntityManager и ORMPurger (очищает таблицы перед загрузкой)
        $this->executor = new ORMExecutor($em, new ORMPurger());

        // Загружаем все добавленные фикстуры в базу
        $this->executor->execute($loader->getFixtures());

        // Получаем репозиторий для сущности Depositary
        $this->depositaryRepository = $em->getRepository(Depositary::class);
    }

    // Метод tearDown() вызывается после каждого теста
    protected function tearDown(): void
    {
        // Очищаем базу данных после теста
        $this->executor->getPurger()->purge();
    }

    // Тест метода removeDepositary
    public function testRemoveDepositary(): void
    {
        // Получаем объект Depositary из фикстуры по ссылке
        $depositary = $this->depositaryFixture->getReference(DepositaryFixture::DEPOSITARY_REFERENCE);

        // Убеждаемся, что это действительно объект класса Depositary
        $this->assertInstanceOf(Depositary::class, $depositary);

        // Сохраняем ID удаляемого объекта
        $id = $depositary->getId();

        // Вызываем метод удаления у репозитория
        $this->depositaryRepository->removeDepositary($depositary);

        // Пытаемся снова найти объект по ID
        $removedDepositary = $this->depositaryRepository->find($id);

        // Проверяем, что объект действительно не найден (т.е. удалён)
        $this->assertNull($removedDepositary, 'The depositary should be removed from the database.');
    }
}
