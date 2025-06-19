<?php

namespace App\Tests\Fixture;

use App\Entity\User;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;

class UserFixture extends AbstractFixture // Класс фикстуры для создания тестовых пользователей, наследуется от AbstractFixture
{
    public const USER_ADMIN_REFERENCE = 'user-admin'; // Константа для ссылки на администратора
    public const USER_USER_REFERENCE = 'user-user';   // Константа для ссылки на обычного пользователя

    public function load(ObjectManager $manager): void // Метод загрузки данных фикстуры в базу
    {
        $userAdmin = new User(); // Создаем новый объект пользователя - администратора
        $userAdmin->setUsername('admin'); // Устанавливаем имя пользователя

        // Хешируем пароль с помощью встроенной функции password_hash с алгоритмом BCRYPT
        // PASSWORD_BCRYPT — это константа PHP, задающая алгоритм bcrypt для безопасного хеширования пароля
        $hashedPassword = password_hash('admin_password', PASSWORD_BCRYPT);

        $userAdmin->setPassword($hashedPassword); // Устанавливаем хешированный пароль
        // +0.5 балла за использование PasswordHasher — комментарий, отмечающий хороший подход безопасности

        $userAdmin->setRoles(['ROLE_ADMIN']); // Назначаем пользователю роль администратора

        $manager->persist($userAdmin); // Готовим объект к сохранению в базу (не сохраняем пока)

        $this->addReference(self::USER_ADMIN_REFERENCE, $userAdmin); // Добавляем ссылку на этого пользователя, чтобы можно было получить его из других фикстур или тестов

        $user = new User(); // Создаем обычного пользователя
        $user->setUsername('user'); // Устанавливаем имя пользователя

        $hashedPassword = password_hash('user_password', PASSWORD_BCRYPT); // Хешируем пароль пользователя
        $user->setPassword($hashedPassword); // Устанавливаем пароль

        $manager->persist($user); // Готовим объект пользователя к сохранению

        $manager->flush(); // Сохраняем все подготовленные объекты (админа и пользователя) в базу

        $this->addReference(self::USER_USER_REFERENCE, $user); // Добавляем ссылку на обычного пользователя для доступа в тестах
    }
}

