<?php

namespace App\Service;

use App\Entity\Application;
use App\Entity\DealLog;
use App\Enums\ActionEnum;
use App\Repository\DealLogRepository;

class DealLogService
{
    public function __construct( 
        private readonly DealLogRepository $dealLogRepository 
    ) {
    }

    public function registerDealLog(Application $buyApplication, Application $sellApplication): DealLog //Метод registerDealLog принимает два объекта Application (buyApplication и sellApplication) и регистрирует сделку между ними, создавая объект DealLog.
    
    {
        if ($buyApplication->getAction() === ActionEnum::SELL) { //Проверяет, является ли действие buyApplication продажей (ActionEnum::SELL).
            // Если да, то вызывает рекурсивно метод registerDealLog с аргументами sellApplication и buyApplication, чтобы зарегистрировать сделку в обратном порядке.
            return $this->registerDealLog($sellApplication, $buyApplication);
        }

        $dealLog = (new DealLog()) //Создает новый объект DealLog, который будет содержать информацию о сделке.
            ->setStock($buyApplication->getStock()) //Устанавливает акцию (ценную бумагу) для сделки, используя метод setStock() объекта DealLog.
            ->setPrice($buyApplication->getPrice())
            ->setBuyPortfolio($buyApplication->getPortfolio()) //Устанавливает цену сделки, используя метод setPrice() объекта DealLog.
            ->setSellPortfolio($sellApplication->getPortfolio())  
        ;

        $this->dealLogRepository->saveDealLog($dealLog); //Сохраняет объект DealLog в базе данных, используя метод saveDealLog() объекта DealLogRepository.

        return $dealLog;
    }
}

//Сервис DealLogService используется для регистрации логов о совершенных сделках.

//Он принимает две заявки (Application): на покупку и на продажу.

//Проверяет, какая из заявок является заявкой на покупку, а какая — на продажу.

//Создает запись о сделке (DealLog), используя данные из заявки на покупку.

//Сохраняет запись в базе данных через репозиторий DealLogRepository.