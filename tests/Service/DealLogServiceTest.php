<?php

namespace App\Tests;

use App\Entity\Application;
use App\Entity\DealLog;
use App\Entity\Portfolio;
use App\Entity\Stock;
use App\Enums\ActionEnum;
use App\Entity\Depositary;
use App\Repository\DealLogRepository;
use App\Entity\Delta;
use App\Service\DealLogService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Doctrine\Common\Collections\ArrayCollection;


class DealLogServiceTest extends TestCase
{
    private DealLogRepository|MockObject $dealLogRepository;//Объявляется приватное свойство $dealLogRepository, которое будет использоваться как мок-объект (заглушка) для DealLogRepository
    private DealLogService $dealLogService;//Приватное свойство, которое содержит тестируемый сервис DealLogService

    protected function setUp(): void//Метод setUp() вызывается перед каждым тестом. Здесь инициализируются необходимые объекты.
    {
        $this->dealLogRepository = $this->createMock(DealLogRepository::class);//Создаётся мок-объект для DealLogRepository, чтобы можно было контролировать его поведение и изолировать тест от настоящей базы данных.
        $this->dealLogService = new DealLogService($this->dealLogRepository);//Создаётся экземпляр тестируемого сервиса, которому передаётся мок-репозиторий.
    }

    /**
     * @dataProvider provideDealLogService
     */
    public function testRegisterDealLog(//Объявление самого теста. Принимает параметры price и quantity, которые будут подставлены из дата-провайдера.
        float $price,
        int $quantity
    ): void {
        // Arrange
        $buyApplication = $this->createMock(Application::class);//Создаются заглушки для объектов заявки на покупку и продажу
        $sellApplication = $this->createMock(Application::class);

        $stock = $this->createMock(Stock::class);//Моки для сущностей: акция, портфели покупателя и продавца.
        $buyPortfolio = $this->createMock(Portfolio::class);
        $sellPortfolio = $this->createMock(Portfolio::class);

        $buyApplication->method('getAction')->willReturn(ActionEnum::BUY);//Мок настроен так, чтобы при вызове getAction() возвращалось значение BUY
        $buyApplication->method('getStock')->willReturn($stock);//Возвращает объект акции при вызове getStock()
        $buyApplication->method('getPrice')->willReturn($price);//Возвращает переданную цену при вызове getPrice()
        $buyApplication->method('getPortfolio')->willReturn($buyPortfolio);//Возвращает объект портфеля покупателя.
        $buyApplication->method('getQuantity')->willReturn($quantity);//Возвращает количество при вызове getQuantity()

        $sellApplication->method('getPortfolio')->willReturn($sellPortfolio);//Возвращает объект портфеля продавца

        // Ожидаем, что репозиторий получит вызов с объектом DealLog
        $this->dealLogRepository
            ->expects($this->once())//// ожидаем, что метод будет вызван один раз
            ->method('saveDealLog')//// имя метода, который должен быть вызван
            ->with($this->isInstanceOf(DealLog::class));//// проверка, что аргументом будет объект класса DealLog
        /// //Устанавливается ожидание, что метод saveDealLog() будет вызван ровно один раз с объектом DealLog как параметром.

        // Act
        $dealLog = $this->dealLogService->registerDealLog($buyApplication, $sellApplication);//вызывается метод registerDealLog() у сервиса DealLogService с моками заявок на покупку и продажу. Возвращается созданный объект DealLog.

        // Assert
        $this->assertInstanceOf(DealLog::class, $dealLog); //Проверяет, что объект — экземпляр класса,Убеждаемся, что метод действительно вернул объект класса DealLog.
        $this->assertSame($stock, $dealLog->getStock());// Проверяет, что акции совпадают и ссылаются на один и тот же объект(Проверка, что свойство stock в dealLog ссылается на тот же самый объект, что и $stock)
        $this->assertSame($buyPortfolio, $dealLog->getBuyPortfolio());//// Проверка, что портфель покупателя совпадает
        $this->assertSame($sellPortfolio, $dealLog->getSellPortfolio());
        $this->assertEquals($price, $dealLog->getPrice());//Значения сравниваются по содержимому, а не по ссылке — важно при сравнении чисел с плавающей точкой.
        $this->assertEquals($quantity, $dealLog->getQuantity());//проверка, что количество совпадает с переданным в параметрах.

        //этот юнит-тест проверяет правильность создания объекта DealLog сервисом DealLogService, а также что репозиторий вызывается с нужными параметрами. Используются моки (createMock) для полной изоляции тестируемой логики.
    }
    /**
     * @dataProvider provideDeltaCases
     */
    public function testCalculateDelta(
        float $buyPrice,
        int $buyQuantity,
        float $sellPrice,
        int $sellQuantity,
        float $latestPrice,
        float $expectedAbsolute
    ): void {
        $depositary = $this->createMock(Depositary::class);
        $portfolio = $this->createMock(Portfolio::class);
        $stock = $this->createMock(Stock::class);

        $stock->method('getId')->willReturn(1);
        $depositary->method('getPortfolio')->willReturn($portfolio);
        $depositary->method('getStock')->willReturn($stock);

        $buyDealLog = $this->createConfiguredMock(DealLog::class, [
            'getStock' => $stock,
            'getPrice' => $buyPrice,
            'getQuantity' => $buyQuantity,
        ]);
        $sellDealLog = $this->createConfiguredMock(DealLog::class, [
            'getStock' => $stock,
            'getPrice' => $sellPrice,
            'getQuantity' => $sellQuantity,
        ]);
        $latestDealLog = $this->createConfiguredMock(DealLog::class, [
            'getPrice' => $latestPrice
        ]);

        $buyDealLogs = new \Doctrine\Common\Collections\ArrayCollection([$buyDealLog]);
        $sellDealLogs = new \Doctrine\Common\Collections\ArrayCollection([$sellDealLog]);

        $portfolio->method('getBuyDealLogs')->willReturn($buyDealLogs);
        $portfolio->method('getSellDealLogs')->willReturn($sellDealLogs);

        $this->dealLogRepository
            ->method('findLatestByStock')
            ->with($stock)
            ->willReturn($latestDealLog);

        $delta = $this->dealLogService->calculateDelta($depositary);

        $this->assertIsFloat($delta);
        $this->assertEqualsWithDelta($expectedAbsolute, $delta, 0.01);
    }

    public static function provideDealLogService(): array
    {
        return [
            'Тест 1: ' => [100.0, 10],
            'Тест 2:' => [0.0, 0],
            'Тест 3:' => [55.5, 1000],
        ];
    }

    public static function provideDeltaCases(): array//Это data provider для теста testRegisterDealLog. Он позволяет запускать тест несколько раз с разными входными значениями.
    {
        return [
            'standard case' => [
                'buyPrice' => 100.0,
                'buyQuantity' => 5,
                'sellPrice' => 110.0,
                'sellQuantity' => 2,
                'latestPrice' => 120.0,
                'expectedAbsolute' => 80.0,      // actualSum - investSum = 360 - 280
            ],
            'no sales yet' => [
                'buyPrice' => 50.0,
                'buyQuantity' => 10,
                'sellPrice' => 0.0,
                'sellQuantity' => 0,
                'latestPrice' => 60.0,
                'expectedAbsolute' => 100.0,     // (10 * 60) - (10 * 50)
            ],
            'loss case' => [
                'buyPrice' => 200.0,
                'buyQuantity' => 3,
                'sellPrice' => 0.0,
                'sellQuantity' => 0,
                'latestPrice' => 150.0,
                'expectedAbsolute' => -150.0,    // (3 * 150) - (3 * 200)
            ]
        ];
    }

}
