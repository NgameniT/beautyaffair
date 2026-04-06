<?php

namespace App\Controller;

use App\Entity\Book;
use App\Entity\BookFavorite;
use App\Entity\BookLoan;
use App\Entity\BookReview;
use App\Entity\User;
use App\Repository\BookFavoriteRepository;
use App\Repository\BookLoanRepository;
use App\Repository\BookRepository;
use App\Repository\BookReviewRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CatalogueController extends AbstractController
{
    #[Route('/catalogue', name: 'app_catalogue')]
    public function index(Request $request, BookRepository $bookRepository, BookLoanRepository $bookLoanRepository): Response
    {
        $query = trim((string) $request->query->get('q', ''));
        $category = trim((string) $request->query->get('category', ''));
        $user = $this->getUser();
        $activeLoanIds = [];

        if ($user instanceof User) {
            foreach ($bookLoanRepository->findActiveByUser($user) as $loan) {
                if (null !== $loan->getBook()?->getId()) {
                    $activeLoanIds[] = $loan->getBook()->getId();
                }
            }
        }

        return $this->render('catalogue/index.html.twig', [
            'books' => $bookRepository->findForCatalogue($query ?: null, $category ?: null),
            'categories' => $bookRepository->findCategories(),
            'currentQuery' => $query,
            'currentCategory' => $category,
            'activeLoanIds' => $activeLoanIds,
        ]);
    }

    #[Route('/catalogue/{id}', name: 'app_catalogue_show', requirements: ['id' => '\\d+'])]
    public function show(Book $book, BookLoanRepository $bookLoanRepository, BookFavoriteRepository $bookFavoriteRepository, BookReviewRepository $bookReviewRepository): Response
    {
        $hasActiveLoan = false;
        $isFavorite = false;
        $userReview = null;
        $user = $this->getUser();

        if ($user instanceof User) {
            $hasActiveLoan = $bookLoanRepository->hasActiveLoan($user, $book);
            $isFavorite = $bookFavoriteRepository->isFavorite($user, $book);
            $userReview = $bookReviewRepository->findOneByUserAndBook($user, $book);
        }

        return $this->render('catalogue/show.html.twig', [
            'book' => $book,
            'hasActiveLoan' => $hasActiveLoan,
            'isFavorite' => $isFavorite,
            'reviews' => $bookReviewRepository->findApprovedByBook($book),
            'averageRating' => $bookReviewRepository->averageRating($book),
            'userReview' => $userReview,
        ]);
    }

    #[Route('/catalogue/{id}/borrow', name: 'app_catalogue_borrow', methods: ['POST'], requirements: ['id' => '\\d+'])]
    public function borrow(Request $request, Book $book, BookLoanRepository $bookLoanRepository, EntityManagerInterface $entityManager): RedirectResponse
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        if (!$this->isCsrfTokenValid('borrow-book-'.$book->getId(), (string) $request->request->get('_token'))) {
            $this->addFlash('error', 'La requete de reservation est invalide.');

            return $this->redirectToRoute('app_catalogue_show', ['id' => $book->getId()]);
        }

        /** @var User $user */
        $user = $this->getUser();

        if ($bookLoanRepository->hasActiveLoan($user, $book)) {
            $this->addFlash('warning', 'Vous avez deja ce livre dans vos emprunts en cours.');

            return $this->redirectToRoute('app_dashboard');
        }

        if ($book->getAvailableCopies() < 1) {
            $this->addFlash('error', 'Aucun exemplaire n\'est disponible pour le moment.');

            return $this->redirectToRoute('app_catalogue_show', ['id' => $book->getId()]);
        }

        $loan = (new BookLoan())
            ->setUser($user)
            ->setBook($book);

        $scheduledFor = trim((string) $request->request->get('scheduled_for', ''));
        if ('' !== $scheduledFor) {
            try {
                $plannedDate = new \DateTimeImmutable($scheduledFor);
                if ($plannedDate < new \DateTimeImmutable('today')) {
                    $this->addFlash('error', 'La date de reservation doit etre aujourd\'hui ou plus tard.');

                    return $this->redirectToRoute('app_catalogue_show', ['id' => $book->getId()]);
                }

                $loan->setScheduledFor($plannedDate);
            } catch (\Exception) {
                $this->addFlash('error', 'La date de reservation est invalide.');

                return $this->redirectToRoute('app_catalogue_show', ['id' => $book->getId()]);
            }
        }

        $book->decrementAvailableCopies();

        $entityManager->persist($loan);
        $entityManager->flush();

        $this->addFlash('success', 'Le livre a ete ajoute a vos emprunts.');

        return $this->redirectToRoute('app_dashboard');
    }

    #[Route('/catalogue/{id}/favorite', name: 'app_catalogue_favorite', methods: ['POST'], requirements: ['id' => '\\d+'])]
    public function favorite(Request $request, Book $book, BookFavoriteRepository $bookFavoriteRepository, EntityManagerInterface $entityManager): RedirectResponse
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        if (!$this->isCsrfTokenValid('favorite-book-'.$book->getId(), (string) $request->request->get('_token'))) {
            $this->addFlash('error', 'La requete de favoris est invalide.');

            return $this->redirectToRoute('app_catalogue_show', ['id' => $book->getId()]);
        }

        /** @var User $user */
        $user = $this->getUser();

        if (!$bookFavoriteRepository->isFavorite($user, $book)) {
            $entityManager->persist((new BookFavorite())->setUser($user)->setBook($book));
            $entityManager->flush();
            $this->addFlash('success', 'Livre ajoute aux favoris.');
        }

        return $this->redirectToRoute('app_catalogue_show', ['id' => $book->getId()]);
    }

    #[Route('/catalogue/{id}/unfavorite', name: 'app_catalogue_unfavorite', methods: ['POST'], requirements: ['id' => '\\d+'])]
    public function unfavorite(Request $request, Book $book, BookFavoriteRepository $bookFavoriteRepository, EntityManagerInterface $entityManager): RedirectResponse
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        if (!$this->isCsrfTokenValid('unfavorite-book-'.$book->getId(), (string) $request->request->get('_token'))) {
            $this->addFlash('error', 'La requete de suppression des favoris est invalide.');

            return $this->redirectToRoute('app_catalogue_show', ['id' => $book->getId()]);
        }

        /** @var User $user */
        $user = $this->getUser();

        $favorite = $bookFavoriteRepository->findOneBy(['user' => $user, 'book' => $book]);
        if ($favorite instanceof BookFavorite) {
            $entityManager->remove($favorite);
            $entityManager->flush();
            $this->addFlash('success', 'Livre retire des favoris.');
        }

        return $this->redirectToRoute('app_catalogue_show', ['id' => $book->getId()]);
    }

    #[Route('/catalogue/{id}/review', name: 'app_catalogue_review', methods: ['POST'], requirements: ['id' => '\\d+'])]
    public function review(Request $request, Book $book, BookReviewRepository $bookReviewRepository, EntityManagerInterface $entityManager): RedirectResponse
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        if (!$this->isCsrfTokenValid('review-book-'.$book->getId(), (string) $request->request->get('_token'))) {
            $this->addFlash('error', 'La soumission de l\'avis est invalide.');

            return $this->redirectToRoute('app_catalogue_show', ['id' => $book->getId()]);
        }

        $rating = (int) $request->request->get('rating', 0);
        $comment = trim((string) $request->request->get('comment', ''));

        if ($rating < 1 || $rating > 5 || '' === $comment) {
            $this->addFlash('error', 'Votre avis doit contenir un commentaire et une note entre 1 et 5.');

            return $this->redirectToRoute('app_catalogue_show', ['id' => $book->getId()]);
        }

        /** @var User $user */
        $user = $this->getUser();

        $review = $bookReviewRepository->findOneByUserAndBook($user, $book) ?? (new BookReview())->setUser($user)->setBook($book);
        $review
            ->setRating($rating)
            ->setComment($comment)
            ->setIsApproved(false);

        $entityManager->persist($review);
        $entityManager->flush();

        $this->addFlash('success', 'Votre avis a ete enregistre et sera visible apres moderation.');

        return $this->redirectToRoute('app_catalogue_show', ['id' => $book->getId()]);
    }
}
