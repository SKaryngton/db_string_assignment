<?php

namespace App\Entity;

use App\Repository\AnlageRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AnlageRepository::class)]
class Anlage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $lastUploadDate = null;

    #[ORM\Column(nullable: true)]
    private ?int $anlId = null;

    #[ORM\OneToMany(mappedBy: 'anlId', targetEntity: AnlageStringAssignment::class)]
    private Collection $anlageStringAssignments;

    public function __construct()
    {
        $this->anlageStringAssignments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }



    public function getLastUploadDate(): ?\DateTimeInterface
    {
        return $this->lastUploadDate;
    }

    public function setLastUploadDate(?\DateTimeInterface $lastUploadDate): self
    {
        $this->lastUploadDate = $lastUploadDate;

        return $this;
    }
    /**
     * @return Collection<int, AnlageStringAssignment>
     */
    public function getAnlageStringAssignments(): Collection
    {
        return $this->anlageStringAssignments;
    }

    public function addAnlageStringAssignment(AnlageStringAssignment $anlageStringAssignment): static
    {
        if (!$this->anlageStringAssignments->contains($anlageStringAssignment)) {
            $this->anlageStringAssignments->add($anlageStringAssignment);
            $anlageStringAssignment->setAnlId($this);
        }

        return $this;
    }

    public function removeAnlageStringAssignment(AnlageStringAssignment $anlageStringAssignment): static
    {
        if ($this->anlageStringAssignments->removeElement($anlageStringAssignment)) {
            // set the owning side to null (unless already changed)
            if ($anlageStringAssignment->getAnlId() === $this) {
                $anlageStringAssignment->setAnlId(null);
            }
        }

        return $this;
    }

    public function getAnlId(): ?int
    {
        return $this->anlId;
    }

    public function setAnlId(?int $anlId): static
    {
        $this->anlId = $anlId;

        return $this;
    }
}
