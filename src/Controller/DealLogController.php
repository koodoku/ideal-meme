<?php

namespace App\Controller;

use App\Entity\Stock;
use App\Repository\DealLogRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DealLogController extends AbstractController
{
    public function __construct(private readonly DealLogRepository $dealLogRepository)//Внедрение зависимости DealLogRepository в DealLogController для взаимодействия с базой данных и получения логов сделок.
    //readonly - свойство, которое может быть инициализировано только один раз в конструкторе и не может быть изменено после этого.
    {
    }

    #[Route('/deal/log/{id}', name: 'app_deal_log')] //Маршрут для отображения логов сделок по конкретной ценной бумаге (Stock). Параметр {id} будет заменен на идентификатор ценной бумаги.
    //Имя маршрута - app_deal_log.
    public function index(Stock $stock): Response //Метод index принимает объект Stock в качестве параметра и возвращает объект Response.
    //Symfony автоматически извлекает объект Stock из базы данных по переданному идентификатору {id} в маршруте.
    {
        $dealLogs = $this->dealLogRepository->findByStock($stock); //Получает все записи DealLog, связанные с переданной ценной бумагой (Stock), используя метод findByStock() из DealLogRepository.
        //Метод findByStock возвращает массив объектов DealLog, которые были зарегистрированы для данной ценной бумаги.

        return $this->render('deal_log/index.html.twig', [ //Отображает шаблон deal_log/index.html.twig и передает в него массив данных.
            //Шаблон будет использоваться для отображения логов сделок для конкретной ценной бумаги.
            'deal_logs' => $dealLogs, //Передает массив логов сделок в шаблон.
            //Шаблон будет использоваться для отображения логов сделок для конкретной ценной бумаги.
            'stock' => $stock, //Передает объект Stock в шаблон, чтобы отобразить информацию о конкретной ценной бумаге.
            //Шаблон будет использоваться для отображения логов сделок для конкретной ценной бумаги.
        ]);
    }
}

//Определяет, что запрос должен быть обработан методом index контроллера DealLogController.

//Автоматически загружает объект Stock с id = 1 из базы данных.

//Использует репозиторий DealLogRepository, чтобы получить все сделки, связанные с этой ценной бумагой.

//Передает данные в шаблон deal_log/index.html.twig для отображения.