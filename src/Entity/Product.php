<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Hateoas\Configuration\Annotation as Hateoas;

/**
 * @ORM\Entity(repositoryClass=ProductRepository::class)
 *
 * @Hateoas\Relation(
 *     "show",
 *     href = @Hateoas\Route(
 *     "show_product",
 *     parameters = { "id" = "expr(object.getId())" },
 *     absolute = true
 *      )
 * )
 *
 * @Hateoas\Relation(
 *     "delete",
 *     href = @Hateoas\Route(
 *     "delete_product",
 *     parameters={ "id" = "expr(object.getId())" },
 *     absolute = true
 *      )
 * )
 *
 * @Hateoas\Relation(
 *     "update",
 *     href = @Hateoas\Route(
 *     "update_product",
 *     parameters={ "id" = "expr(object.getId())" },
 *     absolute = true
 *      )
 * )
 */
class Product
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100)
     * @Assert\NotBlank
     * @Assert\Length(
     *     min = 4,
     *     max = 255,
     *     minMessage = "Le nom du produit est trop court.",
     *     maxMessage = "Le nom du produit est trop long."
     * )
     */
    private $name;

    /**
     * @ORM\Column(type="integer")
     * @Assert\NotBlank
     * @Assert\Positive
     */
    private $price;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank
     * @Assert\Length(
     *     min = 4,
     *     max = 255,
     *     minMessage = "La description est trop courte.",
     *     maxMessage = "La description est trop longue."
     * )
     */
    private $description;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(int $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }
}
