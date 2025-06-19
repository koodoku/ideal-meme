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
    public const ADMIN_APPLICATION_REFERENCE = 'application-admin'; // Константа для ссылки на объект заявки администратора

    public function load(ObjectManager $manager): void
    {
        $application = new Application(); // Создаем новый объект Application (заявка)

        $application->setPrice(1); // Устанавливаем цену заявки — 1
        $application->setQuantity(1); // Устанавливаем количество — 1
        $application->setAction(ActionEnum::SELL); // Устанавливаем действие заявки — продажа (SELL)

        // Устанавливаем портфель заявки, берём ссылку из PortfolioFixture (портфель администратора)
        $application->setPortfolio(
            $this->getReference(PortfolioFixture::PORTFOLIO_ADMIN_REFERENCE, Portfolio::class)
        );

        // Устанавливаем акцию для заявки, берём ссылку из StockFixture
        $application->setStock(
            $this->getReference(StockFixture::STOCK_TEST_REFERENCE, Stock::class)
        );

        $manager->persist($application); // Помечаем заявку для сохранения в базу данных
        $manager->flush(); // Сохраняем изменения в базе

        // Добавляем ссылку на эту заявку, чтобы другие фикстуры или тесты могли её использовать
        $this->addReference(self::ADMIN_APPLICATION_REFERENCE, $application);
    }

    // Указываем зависимости: перед загрузкой этой фикстуры нужно загрузить портфели и акции
    public function getDependencies(): array
    {
        return [PortfolioFixture::class, StockFixture::class];
    }
}

