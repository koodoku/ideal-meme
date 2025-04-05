<?php

namespace App\Entity;

use App\Repository\DealLogRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DealLogRepository::class)]
class DealLog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $timestamp = null;

    #[ORM\ManyToOne(inversedBy: 'dealLogs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Stock $stock = null;

    #[ORM\Column]
    private ?float $price = null;

    #[ORM\ManyToOne(inversedBy: 'sellDealLogs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Portfolio $sell_portfolio = null;

    #[ORM\ManyToOne(inversedBy: 'buyDealLogs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Portfolio $buyPortfolio = null;

    public function __construct()
    {
        $this->timestamp = new \DateTimeImmutable('now'); //станавливается текущее время при создании объекта DealLog
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTimestamp(): ?\DateTimeImmutable  //Возвращает значение timestamp
    {
        return $this->timestamp;
    }

    public function setTimestamp(\DateTimeImmutable $timestamp): static //Устанавливает значение timestamp
    {
        $this->timestamp = $timestamp;

        return $this;
    }

    public function getStock(): ?Stock //Возвращает объект Stock, связанный с текущим объектом DealLog
    {
        return $this->stock;
    }

    public function setStock(?Stock $stock): static  //Устанавливает объект Stock, связанный с текущим объектом DealLog
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
        return $this->sell_portfolio;
    }

    public function setSellPortfolio(?Portfolio $sell_portfolio): static
    {
        $this->sell_portfolio = $sell_portfolio;

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
}