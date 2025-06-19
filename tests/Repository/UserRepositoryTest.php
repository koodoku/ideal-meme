<?php

namespace App\Repository\Tests;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Tests\Fixture\UserFixture;
use Doctrine\ORM\EntityManager;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UserRepositoryTest extends KernelTestCase // Тестовый класс для репозитория User, расширяет KernelTestCase Symfony
{
    private UserFixture $userFixture; // Фикстура для загрузки тестовых пользователей
    private UserRepository $userRepository; // Репозиторий User, который мы тестируем
    private ORMExecutor $executor; // ORMExecutor для управления загрузкой и очисткой фикстур

    protected function setUp(): void // Метод, выполняемый перед каждым тестом
    {
        $kernel = self::bootKernel(); // Запускаем Symfony Kernel (ядро приложения)
        $this->assertSame('test', $kernel->getEnvironment()); // Проверяем, что мы в тестовом окружении

        // Получаем EntityManager из контейнера Symfony
        $em = $kernel->getContainer()->get('doctrine.orm.entity_manager');
        $this->assertInstanceOf(EntityManager::class, $em); // Проверяем, что действительно получили EntityManager

        // Создаем загрузчик фикстур и добавляем фикстуру пользователей
        $loader = new Loader();
        $loader->addFixture($this->userFixture = new UserFixture());

        // Создаем ORMExecutor с пургером для управления фикстурами
        $this->executor = new ORMExecutor($em, new ORMPurger());

        // Выполняем загрузку фикстур (создаем тестовые данные в БД)
        $this->executor->execute($loader->getFixtures());

        // Получаем репозиторий User для последующего тестирования
        $this->userRepository = $em->getRepository(User::class);
    }

    protected function tearDown(): void // Метод, выполняемый после каждого теста
    {
        $this->executor->getPurger()->purge(); // Очищаем базу данных, удаляя все тестовые данные
    }

    public function testUpgradePassword(): void // Тест метода обновления пароля пользователя
    {
        // Получаем ссылку на тестового пользователя из фикстуры по заранее заданному референсу
        $user = $this->executor->getReferenceRepository()->getReference(UserFixture::USER_USER_REFERENCE);
        $this->assertInstanceOf(User::class, $user); // Проверяем, что получили объект User

        $newPassword = 'new_password'; // Новый пароль для обновления

        // Вызываем метод репозитория для обновления пароля пользователя
        $this->userRepository->upgradePassword($user, $newPassword);

        // Загружаем обновленного пользователя из базы данных
        $updatedUser = $this->userRepository->find($user->getId());
        $this->assertNotNull($updatedUser); // Проверяем, что пользователь существует
        $this->assertEquals($newPassword, $updatedUser->getPassword()); // Проверяем, что пароль обновился корректно
    }
}

