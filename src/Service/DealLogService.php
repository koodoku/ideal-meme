<?php

namespace App\Service;

use App\Entity\Application;
use App\Entity\DealLog;
use App\Entity\Depositary;
use App\Enums\ActionEnum;
use App\Repository\DealLogRepository;

class DealLogService
{
    public function __construct(
        private readonly DealLogRepository $dealLogRepository
    ) {
    }

    public function registerDealLog(Application $buyApplication, Application $sellApplication): DealLog// метод регестрирует запись о соверш сделке
    {
        if ($buyApplication->getAction() === ActionEnum::SELL) {//Получает тип действия заявки (покупка или продажа).
            //Проверяет, является ли заявка на покупку ($buyApplication) на самом деле заявкой на продажу.
            return $this->registerDealLog($sellApplication, $buyApplication);
            //сли это так, метод вызывает сам себя, поменяв местами заявки на покупку и продажу. Это нужно для того, чтобы всегда обрабатывать 
            //заявку на покупку как $buyApplication, а заявку на продажу как $sellApplication
        }

        $dealLog = (new DealLog())//создает вот объект деаллог
            ->setStock($buyApplication->getStock())//устанавливает ценную бумагу по кот был соверш сделка (из заявки на покупку)
            ->setPrice($buyApplication->getPrice()) // станавливает цену сделки
            ->setBuyPortfolio($buyApplication->getPortfolio())
            ->setSellPortfolio($sellApplication->getPortfolio())
            ->setQuantity($buyApplication->getQuantity()) 
        ;

        $this->dealLogRepository->saveDealLog($dealLog);//Использует репозиторий DealLogRepository для сохранения записи о сделке в базе данных

        return $dealLog;//Возвращает объект DealLog, который был сохранен.


    }

    public function calculateDelta(Depositary $depositary): float
    {
        $sellDealLogs =
            $depositary->getPortfolio()->getSellDealLogs()->filter(
                function (DealLog $sellDealLog) use ($depositary) {
                    return $depositary->getStock()->getId() === $sellDealLog->getStock()->getId();
                }
            );

        $buyDealLogs =
            $depositary->getPortfolio()->getBuyDealLogs()->filter(
                function (DealLog $buyDealLog) use ($depositary) {
                    return $depositary->getStock()->getId() === $buyDealLog->getStock()->getId();
                }
            );

        $latestDealLog = $this->dealLogRepository->findLatestByStock($depositary->getStock());

        $investSum = 0.0;
        $actualQuantity = 0;

        foreach ($buyDealLogs as $buyDealLog) {
            $investSum += $buyDealLog->getQuantity() * $buyDealLog->getPrice();
            $actualQuantity += $buyDealLog->getQuantity();
        }

        foreach ($sellDealLogs as $sellDealLog) {
            $investSum -= $sellDealLog->getQuantity() * $sellDealLog->getPrice();
            $actualQuantity -= $sellDealLog->getQuantity();
        }

        $actualSum = $actualQuantity * ($latestDealLog?->getPrice() ?? 0.0);

        return $actualSum - $investSum;
    }
}