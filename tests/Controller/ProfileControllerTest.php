<?php

// Пространство имён для тестов контроллеров
namespace App\Tests\Controller;

// Импорт необходимых сущностей, репозиториев, фикстур и компонентов Symfony
use App\Entity\Portfolio;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Tests\Fixture\PortfolioFixture;
use App\Tests\Fixture\UserFixture;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ProfileControllerTest extends WebTestCase
{
    private static KernelBrowser $client;
    private static ORMExecutor $executor;
    private static PortfolioFixture $portfolioFixture;

    public static function setUpBeforeClass(): void
    {
        self::$client = static::createClient();

        /** @var EntityManagerInterface $em */
        $em = self::$client->getContainer()->get('doctrine.orm.entity_manager');

        $loader = new Loader();
        $loader->addFixture(new UserFixture());
        $loader->addFixture(self::$portfolioFixture = new PortfolioFixture());


        self::$executor = new ORMExecutor($em, new ORMPurger());

        self::$executor->execute($loader->getFixtures());
    }

    public static function tearDownAfterClass(): void
    {
        self::$executor->getPurger()->purge();
        parent::tearDownAfterClass();
    }
    public function testProfile(): void
    {
        /** @var UserRepository $userRepository */
        $userRepository = self::$client->getContainer()->get(UserRepository::class);

        /** @var User $userAdmin */
        $userAdmin = $userRepository->findOneBy(['username' => 'admin']);
        self::$client->loginUser($userAdmin);

        $crawler = self::$client->request('GET', '/profile');
        $this->assertResponseIsSuccessful();

        $this->assertCount(3, $crawler->filter('h1'));
        $this->assertPageTitleSame('User Profile');

        $this->assertAnySelectorTextSame('h1', "User name: {$userAdmin->getUsername()}");

        // Проверка, что есть заголовок со списком портфелей
        $this->assertAnySelectorTextSame('h1', "All portfolios:");

        $adminPortfolio = self::$portfolioFixture->getReference(
            PortfolioFixture::PORTFOLIO_ADMIN_REFERENCE,
            Portfolio::class
        );

        $this->assertSelectorTextSame(
            'span',
            "Portfolio {$adminPortfolio->getId()} has {$adminPortfolio->getBalance()} money and has stocks:"
        );

        $this->assertAnySelectorTextContains('h1', "quantity");
    }

    public function testProfileRequiresAuth(): void
    {
        $client = static::createClient();
        $client->request('GET', '/profile');
        $this->assertResponseRedirects('/login');
    }
}
