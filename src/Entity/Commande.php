<?php

namespace App\Entity;

use App\Repository\CommandeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CommandeRepository::class)]
class Commande
{
    const STATUT_EN_ATTENTE = 'en_attente';
    const STATUT_PAYEE      = 'payee';
    const STATUT_EXPEDIEE   = 'expediee';
    const STATUT_LIVREE     = 'livree';
    const STATUT_ANNULEE    = 'annulee';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'commandes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $client = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $total = null;

    #[ORM\Column(length: 20)]
    private string $statut = self::STATUT_EN_ATTENTE;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $adresseLivraison = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $telephone = null;

    /** Référence de transaction PayDunya */
    #[ORM\Column(length: 100, nullable: true)]
    private ?string $referencePaiement = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\OneToMany(targetEntity: LigneCommande::class, mappedBy: 'commande', cascade: ['persist', 'remove'])]
    private Collection $lignes;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->lignes = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }

    public function getClient(): ?User { return $this->client; }
    public function setClient(?User $client): static { $this->client = $client; return $this; }

    public function getTotal(): ?string { return $this->total; }
    public function setTotal(string $total): static { $this->total = $total; return $this; }

    public function getStatut(): string { return $this->statut; }
    public function setStatut(string $statut): static { $this->statut = $statut; return $this; }

    public function getAdresseLivraison(): ?string { return $this->adresseLivraison; }
    public function setAdresseLivraison(?string $adresseLivraison): static { $this->adresseLivraison = $adresseLivraison; return $this; }

    public function getTelephone(): ?string { return $this->telephone; }
    public function setTelephone(?string $telephone): static { $this->telephone = $telephone; return $this; }

    public function getReferencePaiement(): ?string { return $this->referencePaiement; }
    public function setReferencePaiement(?string $ref): static { $this->referencePaiement = $ref; return $this; }

    public function getCreatedAt(): ?\DateTimeInterface { return $this->createdAt; }
    public function setCreatedAt(\DateTimeInterface $createdAt): static { $this->createdAt = $createdAt; return $this; }

    /** @return Collection<int, LigneCommande> */
    public function getLignes(): Collection { return $this->lignes; }

    public function addLigne(LigneCommande $ligne): static
    {
        if (!$this->lignes->contains($ligne)) {
            $this->lignes->add($ligne);
            $ligne->setCommande($this);
        }
        return $this;
    }

    public function removeLigne(LigneCommande $ligne): static
    {
        if ($this->lignes->removeElement($ligne) && $ligne->getCommande() === $this) {
            $ligne->setCommande(null);
        }
        return $this;
    }

    public function calculerTotal(): void
    {
        $total = 0;
        foreach ($this->lignes as $ligne) {
            $total += $ligne->getSousTotal();
        }
        $this->total = (string) $total;
    }
}
