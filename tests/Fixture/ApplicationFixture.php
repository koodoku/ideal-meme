<?php

namespace App\Tests\Fixture;

use App\Entity\Application;
use App\Entity\Portfolio;
use App\Entity\Stock;
use App\Enums\ActionEnum;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ApplicationFixture extends AbstractFixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $application = new Application();
        $application->setPrice(1);
        $application->setQuantity(1);
        $application->setAction(ActionEnum::SELL);
        $application->setPortfolio(
            $this->getReference('portfolio-admin', Portfolio::class)
        );
        $application->setStock(
            $this->getReference('stock-test', Stock::class)
        );

        $manager->persist($application);
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [PortfolioFixture::class, StockFixture::class];
    }
}