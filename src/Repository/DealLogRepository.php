<?php
//отвечает за взаимодействие с базой данных: выполнение запросов, сохранение, обновление и удаление данных
namespace App\Repository;

use App\Entity\DealLog;
use App\Entity\Stock;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DealLog>
 */
class DealLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry) //вызов конструктора родительского класса ServiceEntityRepository с передачей ему объекта ManagerRegistry и класса DealLog
    {
        parent::__construct($registry, DealLog::class);
    }

    public function saveDealLog(DealLog $dealLog): void //сохраняет объект DealLog в базе данных
    {
        $this->getEntityManager()->persist($dealLog);  //Говорит Doctrine, что объект $dealLog должен быть сохранен в базе данных (добавляет его в очередь на сохранение).
        $this->getEntityManager()->flush(); //Выполняет все запросы к базе данных, включая сохранение $dealLog.
    }

    /**  //Этот метод возвращает все записи DealLog, связанные с конкретной ценной бумагой
     * @param Stock $stock
     * @return array<DealLog>
     */
    public function findByStock(Stock $stock): array //Метод findByStock принимает объект Stock в качестве параметра и возвращает массив объектов DealLog, связанных с этой ценной бумагой.
    {
        return $this->createQueryBuilder('d') //Создает новый объект QueryBuilder, который позволяет строить запросы к базе данных.
            ->where('d.stock = :stock')  //Добавляет условие к запросу, чтобы выбрать только те записи DealLog, которые связаны с переданной ценной бумагой.
            ->setParameter('stock', $stock)  //Устанавливает значение параметра :stock в запросе, чтобы он соответствовал переданному объекту Stock.
            ->getQuery()  //Создает объект запроса к базе данных.
            ->getResult()  //Выполняет запрос и возвращает  результат в виде массива объектов DealLog).
        ;
    }
    public function findLatestByStock(Stock $stock): ?DealLog
    {
        return $this->createQueryBuilder('d')
            ->where('d.stock = :stock')
            ->setParameter('stock', $stock)
            ->orderBy('d.timestamp', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

}

//Репозиторий DealLogRepository предоставляет методы для работы с данными о сделках (DealLog).

//Метод saveDealLog используется для сохранения новой записи о сделке в базе данных.

//Метод findByStock используется для получения всех сделок, связанных с конкретной ценной бумагой (Stock).
