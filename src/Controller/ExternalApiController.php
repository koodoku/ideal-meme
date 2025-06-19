<?php

namespace App\Controller;

use App\Model\Deposit;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Psr\Cache\CacheItemPoolInterface;

class ExternalApiController extends AbstractController//Делает запрос к внешнему API ЦБ РФ,олучает данные о депозитах.
//Кеширует их на 24 часа, чтобы не запрашивать повторно при каждом обращении.Отображает данные через Twig-шаблон external/deposit.html.twig
{
    private HttpClientInterface $client;
    private CacheItemPoolInterface $cache;

    public function __construct(
        HttpClientInterface $client,
        CacheItemPoolInterface $cache
    ) {
        $this->client = $client;
        $this->cache = $cache;
    }

    #[Route('/external/deposit', name: 'app_external_deposit')]
    public function deposit(): Response// метод депозит создает обект кеша (ниже строка)
    {
        $cacheItem = $this->cache->getItem('max_datetime_deposits');
        if (!$cacheItem->isHit()) {
            $currentDate = new \DateTime();//формирует url запроса,Отправляется GET-запрос на API ЦБ РФ , Передаются параметры:y1 и y2 — текущий год (для фильтрации данных за этот год).
            //publicationId, datasetId, measureId — фиксированные параметры, нужные для получения нужного набора данных (ставки по депозитам)
            $response = $this->client->request(
                'GET',
                "https://www.cbr.ru/dataservice/data?y1={$currentDate->format('Y')}&y2={$currentDate->format('Y')}&publicationId=18&datasetId=37&measureId=2"
            );
            $data = $response->toArray();
            $rawData = $data['RawData'];
            $maxDateTime = new \DateTime('@0');//// начальная дата: 1970-01-01
            foreach ($rawData as $item) {
                $date = new \DateTime($item['date']);
                if ($maxDateTime < $date) {
                    $maxDateTime = $date;
                }
            }
            $maxDateTimeData = array_filter($rawData, function ($item) use ($maxDateTime) {
                return $item['date'] === $maxDateTime->format('Y-m-d\\TH:i:s');
            });
            $deposits = [];//Преобразование данных в объекты Deposit,Создаём массив объектов Deposit.Каждый Deposit получает
            //Сырые данные ($data) о ставке.Заголовки (headerData) с описаниями.
            foreach ($maxDateTimeData as $dataRow) {
                $deposits[] = new Deposit($dataRow, $data['headerData']);
            }
            $cacheItem->set($deposits);
            $cacheItem->expiresAfter(3600 * 24);
            $this->cache->save($cacheItem);
        } else {
            $deposits = $cacheItem->get();
        }
        return $this->render('external/deposit.html.twig', [// рендерим шаблон, передаем данные в шаблон
            'deposits' => $deposits,
        ]);
    }
}
//Этот код реализует контроллер Symfony, который обращается к стороннему API (ЦБ РФ) за данными по депозитным
// ставкам и кеширует результат, чтобы не перегружать API и ускорить ответ пользователю