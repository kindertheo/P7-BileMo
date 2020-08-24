<?php

namespace App\Entity;

use App\Repository\ClientRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Expose;
use JMS\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Hateoas\Configuration\Annotation as Hateoas;
use JMS\Serializer\Annotation\ExclusionPolicy;
use Hateoas\Configuration\Annotation\Exclusion;

/**
 * @ORM\Entity(repositoryClass=ClientRepository::class)
 * @ExclusionPolicy("all")
 * @Hateoas\Relation(
 *     "update",
 *     href = @Hateoas\Route(
 *     "update_client",
 *     parameters = { "id" = "expr(object.getId())" },
 *     absolute = true
 *      ),
 *     exclusion=@Exclusion(groups="client")
 * )
 *
 * @Hateoas\Relation(
 *     "delete",
 *     href = @Hateoas\Route(
 *     "delete_client",
 *     parameters = { "id" = "expr(object.getId())" },
 *     absolute = true
 *      ),
 *     exclusion=@Exclusion(groups="client")

 * )
 *
 *

 */
class Client
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"client"})
     * @Expose()
     *
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100)
     * @Groups({"client"})
     * @Expose()
     * @Assert\NotBlank
     * @Assert\Length(
     *     min = 4,
     *     max = 100,
     *     minMessage = "Le nom du client est trop court.",
     *     maxMessage = "Le nom du client est trop long."
     * )
     *
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity=User::class, mappedBy="client")
     * @Assert\NotBlank
     */
    private $user;

    public function __construct()
    {
        $this->user = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->name;
    }

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

    /**
     * @return Collection|User[]
     */
    public function getUser(): Collection
    {
        return $this->user;
    }

    public function addUser(User $user): self
    {
        if (!$this->user->contains($user)) {
            $this->user[] = $user;
            $user->setClient($this);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->user->contains($user)) {
            $this->user->removeElement($user);
            // set the owning side to null (unless already changed)
            if ($user->getClient() === $this) {
                $user->setClient(null);
            }
        }

        return $this;
    }
}
