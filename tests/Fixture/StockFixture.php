<?php

namespace App\Tests\Fixture;

use App\Entity\Stock;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;

class StockFixture extends AbstractFixture // Класс фикстуры для создания тестовых объектов Stock, наследуется от AbstractFixture
{
    public const STOCK_TEST_REFERENCE = 'stock-test'; // Константа для ссылки на первый тестовый объект Stock
    public const STOCK_ANOTHER_REFERENCE = 'stock-another'; // Константа для ссылки на второй тестовый объект Stock

    public function load(ObjectManager $manager): void // Метод загрузки фикстур в базу данных
    {
        $testStock = new Stock(); // Создаем новый объект Stock для теста
        $testStock->setName('Test stock'); // Устанавливаем имя для тестового Stock
        $testStock->setTicker('TST'); // Устанавливаем тикер (уникальный код акции) для тестового Stock

        $manager->persist($testStock); // Подготавливаем объект к сохранению в базу (но не сохраняем пока)

        $this->addReference(self::STOCK_TEST_REFERENCE, $testStock); // Создаем ссылку на этот объект для последующего использования в тестах или других фикстурах

        $anotherStock = new Stock(); // Создаем еще один объект Stock
        $anotherStock->setName('Another stock'); // Устанавливаем имя для второго Stock
        $anotherStock->setTicker('ANS'); // Устанавливаем тикер для второго Stock

        $this->addReference(self::STOCK_ANOTHER_REFERENCE, $anotherStock); // Добавляем ссылку на второй объект Stock
        $manager->persist($anotherStock); // Подготавливаем второй объект к сохранению

        $manager->flush(); // Сохраняем оба объекта в базу данных
    }
}

