<?php

namespace App\Tests\Fixture;

use App\Entity\DealLog;
use App\Entity\Portfolio;
use App\Entity\Stock;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class DealLogFixture extends AbstractFixture implements DependentFixtureInterface
{
    public const OLDER_DEAL_LOG = 'older-deal-log';  // Константа для ссылки на старую запись сделки
    public const NEWER_DEAL_LOG = 'newer-deal-log';  // Константа для ссылки на новую запись сделки

    public function load(ObjectManager $manager): void
    {
        $olderDealLog = new DealLog(); // Создаем новый объект DealLog — старую сделку
        $olderDealLog
            ->setPrice(1) // Устанавливаем цену сделки = 1
            ->setQuantity(1) // Количество акций в сделке = 1
            ->setTimestamp(new \DateTimeImmutable('2025-01-01 00:00:00')) // Время сделки — 1 января 2025
            ->setBuyPortfolio( // Портфель покупателя, берем ссылку из PortfolioFixture для обычного пользователя
                $this->getReference(PortfolioFixture::PORTFOLIO_USER_REFERENCE, Portfolio::class)
            )
            ->setSellPortfolio( // Портфель продавца — из PortfolioFixture для администратора
                $this->getReference(PortfolioFixture::PORTFOLIO_ADMIN_REFERENCE, Portfolio::class)
            )
            ->setStock( // Акция, по которой прошла сделка — ссылка из StockFixture
                $this->getReference(StockFixture::STOCK_TEST_REFERENCE, Stock::class)
            )
        ;

        $manager->persist($olderDealLog); // Подготавливаем объект для сохранения

        $this->addReference(self::OLDER_DEAL_LOG, $olderDealLog); // Сохраняем ссылку на эту сделку

        $newerDealLog = new DealLog(); // Создаем новую сделку
        $newerDealLog
            ->setPrice(2) // Цена сделки = 2
            ->setQuantity(2) // Количество = 2
            ->setTimestamp(new \DateTimeImmutable('2025-01-02 00:00:00')) // Время — 2 января 2025
            ->setBuyPortfolio( // Покупатель — администратор
                $this->getReference(PortfolioFixture::PORTFOLIO_ADMIN_REFERENCE, Portfolio::class)
            )
            ->setSellPortfolio( // Продавец — обычный пользователь
                $this->getReference(PortfolioFixture::PORTFOLIO_USER_REFERENCE, Portfolio::class)
            )
            ->setStock( // Акция та же, что и в первой сделке
                $this->getReference(StockFixture::STOCK_TEST_REFERENCE, Stock::class)
            )
        ;

        $manager->persist($newerDealLog); // Подготавливаем для сохранения
        $manager->flush(); // Сохраняем обе сделки в базу

        $this->addReference(self::NEWER_DEAL_LOG, $newerDealLog); // Добавляем ссылку на новую сделку
    }

    // Указываем зависимости — фикстуры, которые должны быть загружены до этой
    public function getDependencies(): array
    {
        return [StockFixture::class, PortfolioFixture::class]; // Нужно сначала загрузить акции и портфели
    }
}

