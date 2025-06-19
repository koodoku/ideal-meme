<?php

namespace App\Tests\Fixture;

use App\Entity\Portfolio;
use App\Entity\User;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class PortfolioFixture extends AbstractFixture implements DependentFixtureInterface
{
    public const PORTFOLIO_ADMIN_REFERENCE = 'portfolio-admin'; // Константа для ссылки на портфель администратора
    public const PORTFOLIO_USER_REFERENCE = 'portfolio-user';   // Константа для ссылки на портфель обычного пользователя

    public function load(ObjectManager $manager): void
    {
        $adminPortfolio = new Portfolio(); // Создаем новый объект портфеля для администратора
        $adminPortfolio->setBalance(100);  // Устанавливаем баланс портфеля (например, 100 единиц)
        $adminPortfolio->setFreezeBalance(0); // Устанавливаем замороженный баланс в 0
        // Получаем пользователя-админа из ссылки UserFixture по константе USER_ADMIN_REFERENCE
        $adminPortfolio->setUser($this->getReference(UserFixture::USER_ADMIN_REFERENCE, User::class));

        $manager->persist($adminPortfolio); // Помечаем объект для сохранения в базу

        // Создаем ссылку на этот объект портфеля, чтобы использовать в других фикстурах/тестах
        $this->addReference(self::PORTFOLIO_ADMIN_REFERENCE, $adminPortfolio);

        $userPortfolio = new Portfolio(); // Создаем портфель для обычного пользователя
        $userPortfolio->setBalance(100);  // Баланс портфеля обычного пользователя
        $userPortfolio->setFreezeBalance(0); // Замороженный баланс = 0
        // Получаем пользователя из UserFixture по ссылке USER_USER_REFERENCE
        $userPortfolio->setUser($this->getReference(UserFixture::USER_USER_REFERENCE, User::class));
        $manager->persist($userPortfolio); // Помечаем для сохранения

        // Добавляем ссылку на этот портфель для дальнейшего использования
        $this->addReference(self::PORTFOLIO_USER_REFERENCE, $userPortfolio);

        $manager->flush(); // Сохраняем все изменения в базу данных
    }

    // Метод, указывающий зависимости фикстуры — сначала нужно загрузить UserFixture,
    // так как здесь используется метод getReference для пользователей
    public function getDependencies(): array
    {
        return [UserFixture::class];
    }
}
