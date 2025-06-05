<?php

namespace App\Tests\Controller;

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

class StockControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private ORMExecutor $executor;

    protected function setUp(): void
    {
        $this->client = static::createClient();

        /** @var EntityManagerInterface $em */
        $em = $this->client->getContainer()->get('doctrine.orm.entity_manager');

        $loader = new Loader();
        $loader->addFixture(new UserFixture());

        $this->executor = new ORMExecutor($em, new ORMPurger());
        $this->executor->execute($loader->getFixtures());

        /** @var UserRepository $userRepository */
        $userRepository = $this->client->getContainer()->get(UserRepository::class);
        /** @var User $userAdmin */
        $userAdmin = $userRepository->findOneBy(['username' => 'admin']);

        $this->client->loginUser($userAdmin);
    }

    protected function tearDown(): void
    {
        $this->executor->getPurger()->purge();
    }

    public function testNewStock(): void
    {
        $this->client->request('GET', '/stock/new');

        $crawler = $this->client->submitForm('Save', [
            'stock[name]' => 'Admin stock',
            'stock[ticker]' => 'AST'
        ]);

        $this->assertResponseRedirects('/stock');
        $this->client->followRedirect();

        $this->assertResponseIsSuccessful();
    }
}
