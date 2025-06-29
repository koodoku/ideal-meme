<?php

namespace App\Tests\Controller;

use App\DataFixtures\AppFixtures;
use App\Entity\Application;
use App\Entity\Stock;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Tests\Fixture\ApplicationFixture;
use App\Tests\Fixture\StockFixture;
use App\Tests\Fixture\PortfolioFixture;
use App\Tests\Fixture\UserFixture;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;


class ApplicationControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private ORMExecutor $executor;
    private EntityManagerInterface $em;

    protected function setUp(): void
    {
        $this->client = static::createClient();

        $this->em = $this->client->getContainer()->get('doctrine.orm.entity_manager');

        $loader = new Loader();// Создаём загрузчик фикстур
        $loader->addFixture(new AppFixtures()); // Общие данные приложения(ДЛЯ ЧЕГО?)
        $loader->addFixture(new ApplicationFixture()); // Фикстуры заявок
        $loader->addFixture(new UserFixture()); // Фикстуры пользователей
        $loader->addFixture(new StockFixture()); // Фикстуры акций
        $loader->addFixture(new PortfolioFixture()); // Фикстуры портфелей

        $this->executor = new ORMExecutor($this->em, new ORMPurger()); // Создаём исполнитель фикстур с очисткой базы перед загрузкой
        $this->executor->execute($loader->getFixtures());  // Загружаем все добавленные фикстуры в тестовую БД


        /** @var UserRepository $userRepository */ //// Получаем репозиторий пользователей
        $userRepository = $this->client->getContainer()->get(UserRepository::class);
        /** @var User $userAdmin */ // // Получаем пользователя с логином "admin" из БД
        $userAdmin = $userRepository->findOneBy(['username' => 'admin']);

        $this->client->loginUser($userAdmin); // Логиним пользователя в тестовом клиенте (имитация аутентификации)
    }

    protected function tearDown(): void // Метод, запускающийся после каждого теста(ЗАЧЕМ)
    {
        parent::tearDown();// Вызываем базовую реализацию(ЧТО ЗА БАЗОВАЯ РЕАЛИЗАЦИЯ)
        $this->executor->getPurger()->purge(); // Очищаем БД, чтобы каждый тест был независим
    }

    public function testIndex(): void //Тестирование главной страницы заявок (/application)
    {
        $this->client->request('GET', '/application'); // Отправляем GET-запрос на страницу заявок
        $this->assertResponseIsSuccessful(); // Проверяем, что ответ успешен (HTTP 200)
        $this->assertSelectorExists('table'); // Проверяем, что в HTML есть таблица(КАКАЯ ТАБЛИЦА)
    }

    public function testGlass(): void // Тестирование страницы стакана заявок по конкретной акции
    { // Получаем ссылку на фикстурную акцию
        /** @var Stock $stock */
        $stock = $this->executor->getReferenceRepository()->getReference(StockFixture::STOCK_TEST_REFERENCE);

        $this->client->request('GET', '/application/glass/' . $stock->getId());// Отправляем GET-запрос на страницу стакана для этой акции
        $this->assertResponseIsSuccessful(); // Проверяем, что ответ успешен
        $this->assertSelectorTextContains('h1', $stock->getName()); // Проверяем, что заголовок страницы содержит название акции
    }
}
