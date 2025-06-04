<?php

namespace App\Tests\Fixture;

use App\Entity\Stock;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;

class StockFixture extends AbstractFixture
{
    public function load(ObjectManager $manager): void
    {
        $testStock = new Stock();
        $testStock->setName('Test stock');
        $testStock->setTicker('TST');

        $manager->persist($testStock);
        $manager->flush();

        $this->addReference('stock-test', $testStock);
    }
}