<?php
namespace App\Entity;
use Doctrine\ORM\Mapping as ORM;

class Order
{

    private $id;

    private $createdAt;

    private $product;

    public function getId(): ?int
    {
        return $this->id;
    }
    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }
    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }
    public function getProduct(): ?Product
    {
        return $this->product;
    }
    public function setProduct(?Product $product): self
    {
        $this->product = $product;
        return $this;
    }
}
