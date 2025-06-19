<?php

namespace App\Tests\Fixture;

use App\Entity\Depositary;
use App\Entity\Portfolio;
use App\Entity\Stock;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;

class DepositaryFixture extends ApplicationFixture // Класс фикстуры, загружает тестовые данные для сущности Depositary
{
    public const DEPOSITARY_REFERENCE = 'test_depositary'; // Константа с именем ссылки на объект для использования в других фикстурах/тестах

    public function load(ObjectManager $manager): void // Метод, вызываемый при загрузке фикстур
    {
        $depositary = new Depositary(); // Создаём новый объект депозитария

        // Устанавливаем акцию, получая ссылку на ранее созданный объект из StockFixture
        $depositary->setStock(
            $this->getReference(StockFixture::STOCK_TEST_REFERENCE, Stock::class)
        );

        // Устанавливаем портфель, получая ссылку на ранее созданный объект из PortfolioFixture
        $depositary->setPortfolio(
            $this->getReference(PortfolioFixture::PORTFOLIO_ADMIN_REFERENCE, Portfolio::class)
        );

        $depositary->setQuantity(100); // Устанавливаем общее количество бумаг
        $depositary->setFreezeQuantity(0); // Устанавливаем замороженное количество бумаг как 0

        $manager->persist($depositary); // Отмечаем сущность для сохранения
        $manager->flush(); // Сохраняем сущность в базу данных

        // Добавляем ссылку на созданный объект для использования в других местах (тестах/фикстурах)
        $this->addReference(self::DEPOSITARY_REFERENCE, $depositary);
    }
}
