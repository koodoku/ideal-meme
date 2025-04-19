<?php
//хранение инф о совершенных сделках
namespace App\Entity;

use App\Repository\DealLogRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DealLogRepository::class)]//связываеть сущность с репозиторием для выполнения зарпосов к
class DealLog//потом свойства котрые предств полня в бд
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $timestamp = null;

    #[ORM\ManyToOne(inversedBy: 'dealLogs')] //связь
    #[ORM\JoinColumn(nullable: false)]
    private ?Stock $stock = null;

    #[ORM\Column]
    private ?float $price = null;

    #[ORM\ManyToOne(inversedBy: 'sellDealLogs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Portfolio $sellPortfolio = null;

    #[ORM\ManyToOne(inversedBy: 'buyDealLogs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Portfolio $buyPortfolio = null;

    #[ORM\Column]
    private ?int $quantity = null;

    public function __construct()
    {
        $this->timestamp = new \DateTimeImmutable('now');
    }
//потом Для каждого свойства класса определены геттеры (методы 
//для получения значения) и сеттеры (методы для установки значения).
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTimestamp(): ?\DateTimeImmutable 
    {
        return $this->timestamp;
    }

    public function setTimestamp(\DateTimeImmutable $timestamp): static
    {
        $this->timestamp = $timestamp;

        return $this;
    }

    public function getStock(): ?Stock//Возвращает связанный объект Stock
    {
        return $this->stock;
    }

    public function setStock(?Stock $stock): static// Устанавливает связь с объектом Stock
    {
        $this->stock = $stock;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getSellPortfolio(): ?Portfolio
    {
        return $this->sellPortfolio;
    }

    public function setSellPortfolio(?Portfolio $sellPortfolio): static
    {
        $this->sellPortfolio = $sellPortfolio;

        return $this;
    }

    public function getBuyPortfolio(): ?Portfolio
    {
        return $this->buyPortfolio;
    }

    public function setBuyPortfolio(?Portfolio $buyPortfolio): static
    {
        $this->buyPortfolio = $buyPortfolio;

        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): static
    {
        $this->quantity = $quantity;

        return $this;
    }
}
//Когда происходит сделка, создается объект DealLog, заполняется данными (время, цена, ценная бумага) и сохраняется в базе данных через репозиторий.

//Связь с Stock позволяет отслеживать, по какой ценной бумаге была совершена сделка.

//Время сделки (timestamp) автоматически устанавливается при создании объекта.