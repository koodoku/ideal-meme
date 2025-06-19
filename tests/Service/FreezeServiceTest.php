<?php

namespace App\Tests\Service;

use App\Entity\Application;
use App\Entity\Portfolio;
use App\Entity\Stock;
use App\Entity\Depositary;
use App\Enums\ActionEnum;
use App\Service\FreezeService;
use PHPUnit\Framework\TestCase;

class FreezeServiceTest extends TestCase//проверяет, что при заявке на продажу вызывается заморозка соответствующего количества акций в депозитарии.
{
    private FreezeService $freezeService;//Создаём свойство класса, которое будет содержать тестируемый сервис.

    protected function setUp(): void//Метод setUp() автоматически вызывается перед каждым тестом. Здесь создаётся новый экземпляр FreezeService
    {
        $this->freezeService = new FreezeService();
    }

    /**
     * @dataProvider provideFreezeSellCases
     */
    //Аннотация @dataProvider указывает, что метод testFreezeByApplicationSell будет вызван несколько раз — с каждым значением из provideFreezeSellCases().
    public function testFreezeByApplicationSell(int $quantity): void //Основной тест: проверяет, что при подаче заявки на продажу нужное количество замораживается в депозитарии.

    {//Создаём фейковые (мокаемые) объекты: акция, депозитарий, портфель, заявка.
        $stock = $this->createMock(Stock::class);
        $depositary = $this->createMock(Depositary::class);
        $portfolio = $this->createMock(Portfolio::class);
        $application = $this->createMock(Application::class);//Устанавливаем поведение мока: заявка — на продажу.
        //Настраиваем, чтобы Application возвращала нужные данные: акцию, количество и портфель.
        $application->method('getAction')->willReturn(ActionEnum::SELL);
        $application->method('getStock')->willReturn($stock);
        $application->method('getQuantity')->willReturn($quantity);
        $application->method('getPortfolio')->willReturn($portfolio);

        $portfolio->method('getDepositaryByStock')->with($stock)->willReturn($depositary);//Портфель возвращает нужный депозитарий по акции.
        $depositary->expects($this->once())->method('addFreezeQuantity')->with($quantity);//Ожидание: метод addFreezeQuantity($quantity) должен быть вызван один раз — это основная проверка!

        $this->freezeService->freezeByApplication($application);//метод: он должен взять заявку и вызвать addFreezeQuantity() у соответствующего депозитария.
    }

    public static function provideFreezeSellCases(): array//Поставляет тесту разные значения quantity, чтобы проверить заморозку 5 и 10 единиц.
    {
        return [
            'freeze 5 units' => [5],
            'freeze 10 units' => [10],
        ];
    }
//Тест проверяет, что если подана заявка на продажу, то нужное количество акций замораживается в депозитарии. Это гарантирует корректную логику заморозки активов перед продажей.

    /**
     * @dataProvider provideFreezeBuyCases
     */
    public function testFreezeByApplicationBuy(float $total): void//Один тестовый запуск принимает одно значение типа float, соответствующее сумме заявки на покупку (в денежном выражении).
    {
        $portfolio = $this->createMock(Portfolio::class);//Создаём моки для зависимостей — объект портфеля и объект заявки.
        $application = $this->createMock(Application::class);

        $application->method('getAction')->willReturn(ActionEnum::BUY);//Говорим моку: если вызовут $application->getAction(), он вернёт ActionEnum::BUY, т.е. заявка на покупку.
        $application->method('getTotal')->willReturn($total);//При вызове getTotal() — вернётся сумма заявки $tota
        $application->method('getPortfolio')->willReturn($portfolio);//Портфель, с которым связана эта заявка, — это $portfolio.

        $portfolio->expects($this->once())->method('addFreezeBalance')->with($total);//Ожидаем, что один раз будет вызван метод addFreezeBalance() на объекте портфеля, с аргументом $total

        $this->freezeService->freezeByApplication($application);//Вызываем метод, который должен реализовать описанную логику: заморозить баланс при заявке на покупку.
    }

    public static function provideFreezeBuyCases(): array//Эти данные поступают в testFreezeByApplicationBuy. Тест будет запускаться дважды
    {
        return [
            'freeze 1000.0 balance' => [1000.0],
            'freeze 250.5 balance' => [250.5],
        ];
    }
//Когда приходит заявка на покупку, метод freezeByApplication должен:
//Получить total из заявки.
//Найти портфель, к которому она относится.
//Вызвать addFreezeBalance($total) на этом портфеле, чтобы заморозить средства на покупку.
//Если всё это происходит — тест пройден.
    /**
     * @dataProvider provideUpdateSellCases
     */
    //запущен несколько раз с разными парами значений oldQuantity и newQuantity, предоставленными методом provideUpdateSellCases.
    public function testUpdateFreezeByApplicationSell(int $oldQuantity, int $newQuantity): void//Каждый запуск теста проверяет, как метод updateFreezeByApplication отрабатывает при изменении количества в заявке на продажу — со старого значения на новое.
    {
        $stock = $this->createMock(Stock::class);//Создаются моки:
        $depositary = $this->createMock(Depositary::class);
        $portfolio = $this->createMock(Portfolio::class);
        $application = $this->createMock(Application::class);

        $application->method('getAction')->willReturn(ActionEnum::SELL);//Настройка поведения заявки:Говорим моку, что заявка — на продажу.
        $application->method('getStock')->willReturn($stock);//Заявка относится к акции $stock.
        $application->method('getQuantity')->willReturn($newQuantity);//Новое количество, указанное в изменённой заявке — $newQuantity.
        $application->method('getPortfolio')->willReturn($portfolio);//Портфель, к которому принадлежит заявка.

        $portfolio->method('getDepositaryByStock')->with($stock)->willReturn($depositary);//Метод getDepositaryByStock вернёт депозитарий для указанной акции.

        $depositary->expects($this->once())->method('subFreezeQuantity')->with($oldQuantity)->willReturnSelf();//Ожидается, что метод subFreezeQuantity($oldQuantity) будет вызван один раз, и он вернёт сам объект (возможно, для чейнинга).
        $depositary->expects($this->once())->method('addFreezeQuantity')->with($newQuantity);//Также ожидается вызов метода addFreezeQuantity($newQuantity) один раз

        $this->freezeService->updateFreezeByApplication($application, $oldQuantity, $newQuantity);//Вызывается метод, который должен:
        //снять заморозку старого количества, //заморозить новое количество.
    }

    public static function provideUpdateSellCases(): array//Метод передаёт пары: старое и новое количество акций для обновления заявки.
    {
        return [
            'update from 5 to 8 units' => [5, 8],
            'update from 10 to 4 units' => [10, 4],
        ];
    }
//Он проверяет корректную перезапись замороженного количества акций при изменении заявки на продажу:
//Сначала "размораживается" старое количество.
//Потом замораживается новое.
//Иными словами, заморозка должна быть приведена к актуальному количеству, отражающему последнюю версию заявки.
    /**
     * @dataProvider provideUpdateBuyCases
     */
    public function testUpdateFreezeByApplicationBuy(int $oldQuantity, float $oldPrice, float $newTotal): void
    {
        // Создание моков портфеля и заявки
        $portfolio = $this->createMock(Portfolio::class);
        $application = $this->createMock(Application::class);

        // Настройка поведения заявки: это покупка, вернуть newTotal и связанный портфель
        $application->method('getAction')->willReturn(ActionEnum::BUY);
        $application->method('getTotal')->willReturn($newTotal);
        $application->method('getPortfolio')->willReturn($portfolio);

        // Ожидание: сначала размораживаем старую сумму (oldQuantity * oldPrice)
        $portfolio->expects($this->once())
            ->method('subFreezeBalance')
            ->with($oldPrice * $oldQuantity)
            ->willReturnSelf();

        // Ожидание: замораживаем новую сумму (newTotal)
        $portfolio->expects($this->once())
            ->method('addFreezeBalance')
            ->with($newTotal);

        // Вызов метода обновления заморозки по заявке на покупку
        $this->freezeService->updateFreezeByApplication($application, $oldQuantity, $oldPrice);
    }

// Провайдер тестов обновления заморозки для покупок
    public static function provideUpdateBuyCases(): array
    {
        return [
            'update from 2x100 to 300' => [2, 100.0, 300.0], // было 200, стало 300
            'update from 4x50 to 280' => [4, 50.0, 280.0],   // было 200, стало 280
        ];
    }

    /**
     * @dataProvider provideUnfreezeSellCases
     */
    public function testUnfreezeByApplicationSell(int $quantity): void
    {
        // Моки: акция, депозитарий, портфель, заявка
        $stock = $this->createMock(Stock::class);
        $depositary = $this->createMock(Depositary::class);
        $portfolio = $this->createMock(Portfolio::class);
        $application = $this->createMock(Application::class);

        // Настройка: это продажа, вернуть нужные объекты
        $application->method('getAction')->willReturn(ActionEnum::SELL);
        $application->method('getStock')->willReturn($stock);
        $application->method('getQuantity')->willReturn($quantity);
        $application->method('getPortfolio')->willReturn($portfolio);

        // Настройка: получаем депозитарий по акции
        $portfolio->method('getDepositaryByStock')->with($stock)->willReturn($depositary);

        // Проверка: должен быть вызван subFreezeQuantity на нужное количество
        $depositary->expects($this->once())->method('subFreezeQuantity')->with($quantity);

        // Вызов метода разморозки по заявке на продажу
        $this->freezeService->unfreezeByApplication($application);
    }

// Провайдер тестов разморозки количества по заявке на продажу
    public static function provideUnfreezeSellCases(): array
    {
        return [
            'unfreeze 3 units' => [3],
            'unfreeze 7 units' => [7],
        ];
    }

    /**
     * @dataProvider provideUnfreezeBuyCases
     */
    public function testUnfreezeByApplicationBuy(float $total): void
    {
        // Моки: портфель и заявка
        $portfolio = $this->createMock(Portfolio::class);
        $application = $this->createMock(Application::class);

        // Настройка: это покупка, вернуть общую сумму и портфель
        $application->method('getAction')->willReturn(ActionEnum::BUY);
        $application->method('getTotal')->willReturn($total);
        $application->method('getPortfolio')->willReturn($portfolio);

        // Проверка: должен быть вызван subFreezeBalance с total
        $portfolio->expects($this->once())->method('subFreezeBalance')->with($total);

        // Вызов метода разморозки по заявке на покупку
        $this->freezeService->unfreezeByApplication($application);
    }

// Провайдер тестов разморозки баланса по заявке на покупку
    public static function provideUnfreezeBuyCases(): array
    {
        return [
            'unfreeze 500.0' => [500.0],
            'unfreeze 750.5' => [750.5],
        ];
    }
}
