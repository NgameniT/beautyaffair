<?php

namespace App\Entity;

use App\Entity\BookLoan;
use App\Repository\BookRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BookRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Book
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    private ?string $author = null;

    #[ORM\Column(length: 120)]
    private ?string $category = null;

    #[ORM\Column(length: 80)]
    private ?string $language = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $imageUrl = null;

    #[ORM\Column(type: 'text')]
    private ?string $summary = null;

    #[ORM\Column(length: 120)]
    private ?string $coverTheme = null;

    #[ORM\Column]
    private int $publishedYear = 2026;

    #[ORM\Column]
    private int $pageCount = 0;

    #[ORM\Column]
    private int $availableCopies = 0;

    #[ORM\Column]
    private bool $featured = false;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $slug = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * @var Collection<int, BookLoan>
     */
    #[ORM\OneToMany(mappedBy: 'book', targetEntity: BookLoan::class, orphanRemoval: true)]
    private Collection $bookLoans;

    /**
     * @var Collection<int, BookFavorite>
     */
    #[ORM\OneToMany(mappedBy: 'book', targetEntity: BookFavorite::class, orphanRemoval: true)]
    private Collection $favorites;

    /**
     * @var Collection<int, BookReview>
     */
    #[ORM\OneToMany(mappedBy: 'book', targetEntity: BookReview::class, orphanRemoval: true)]
    private Collection $reviews;

    public function __construct()
    {
        $this->bookLoans = new ArrayCollection();
        $this->favorites = new ArrayCollection();
        $this->reviews = new ArrayCollection();
    }

    #[ORM\PrePersist]
    public function initializeCreatedAt(): void
    {
        $this->createdAt ??= new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getAuthor(): ?string
    {
        return $this->author;
    }

    public function setAuthor(string $author): static
    {
        $this->author = $author;

        return $this;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(string $category): static
    {
        $this->category = $category;

        return $this;
    }

    public function getLanguage(): ?string
    {
        return $this->language;
    }

    public function setLanguage(string $language): static
    {
        $this->language = $language;

        return $this;
    }

    public function getImageUrl(): ?string
    {
        return $this->imageUrl;
    }

    public function setImageUrl(?string $imageUrl): static
    {
        $this->imageUrl = $imageUrl;

        return $this;
    }

    public function getSummary(): ?string
    {
        return $this->summary;
    }

    public function setSummary(string $summary): static
    {
        $this->summary = $summary;

        return $this;
    }

    public function getCoverTheme(): ?string
    {
        return $this->coverTheme;
    }

    public function setCoverTheme(string $coverTheme): static
    {
        $this->coverTheme = $coverTheme;

        return $this;
    }

    public function getPublishedYear(): int
    {
        return $this->publishedYear;
    }

    public function setPublishedYear(int $publishedYear): static
    {
        $this->publishedYear = $publishedYear;

        return $this;
    }

    public function getPageCount(): int
    {
        return $this->pageCount;
    }

    public function setPageCount(int $pageCount): static
    {
        $this->pageCount = $pageCount;

        return $this;
    }

    public function getAvailableCopies(): int
    {
        return $this->availableCopies;
    }

    public function setAvailableCopies(int $availableCopies): static
    {
        $this->availableCopies = max(0, $availableCopies);

        return $this;
    }

    public function decrementAvailableCopies(): static
    {
        $this->availableCopies = max(0, $this->availableCopies - 1);

        return $this;
    }

    public function incrementAvailableCopies(): static
    {
        ++$this->availableCopies;

        return $this;
    }

    public function isFeatured(): bool
    {
        return $this->featured;
    }

    public function setFeatured(bool $featured): static
    {
        $this->featured = $featured;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * @return Collection<int, BookLoan>
     */
    public function getBookLoans(): Collection
    {
        return $this->bookLoans;
    }

    /**
     * @return Collection<int, BookReview>
     */
    public function getReviews(): Collection
    {
        return $this->reviews;
    }
}
