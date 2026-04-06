<?php

namespace App\Controller;

use App\Entity\Book;
use App\Entity\BookReview;
use App\Entity\User;
use App\Form\BookType;
use App\Form\UserType;
use App\Repository\BookLoanRepository;
use App\Repository\BookRepository;
use App\Repository\BookReviewRepository;
use App\Repository\UserRepository;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class BackOfficeController extends AbstractController
{
    #[Route('/admin', name: 'app_admin_dashboard')]
    public function adminDashboard(BookRepository $bookRepository, BookLoanRepository $bookLoanRepository, BookReviewRepository $bookReviewRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        return $this->render('backoffice/dashboard.html.twig', [
            'title' => 'Administration',
            'booksCount' => count($bookRepository->findAll()),
            'activeLoansCount' => $bookLoanRepository->countActive(),
            'pendingReservationsCount' => $bookLoanRepository->countPendingReservations(),
            'pendingReviewsCount' => count($bookReviewRepository->findPendingModeration()),
            'isAdmin' => true,
        ]);
    }

    #[Route('/bibliothecaire', name: 'app_librarian_dashboard')]
    public function librarianDashboard(BookRepository $bookRepository, BookLoanRepository $bookLoanRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_LIBRARIAN');

        return $this->render('backoffice/dashboard.html.twig', [
            'title' => 'Espace bibliothecaire',
            'booksCount' => count($bookRepository->findAll()),
            'activeLoansCount' => $bookLoanRepository->countActive(),
            'pendingReservationsCount' => $bookLoanRepository->countPendingReservations(),
            'pendingReviewsCount' => null,
            'isAdmin' => false,
        ]);
    }

    #[Route('/admin/catalogue', name: 'app_admin_books')]
    public function adminBooks(BookRepository $bookRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        return $this->render('backoffice/books/index.html.twig', [
            'books' => $bookRepository->findForCatalogue(null, null),
            'newRoute' => 'app_admin_books_new',
            'editRoute' => 'app_admin_books_edit',
            'deleteRoute' => 'app_admin_books_delete',
            'dashboardRoute' => 'app_admin_dashboard',
            'title' => 'Catalogue admin',
        ]);
    }

    #[Route('/bibliothecaire/catalogue', name: 'app_librarian_books')]
    public function librarianBooks(BookRepository $bookRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_LIBRARIAN');

        return $this->render('backoffice/books/index.html.twig', [
            'books' => $bookRepository->findForCatalogue(null, null),
            'newRoute' => 'app_librarian_books_new',
            'editRoute' => 'app_librarian_books_edit',
            'deleteRoute' => 'app_librarian_books_delete',
            'dashboardRoute' => 'app_librarian_dashboard',
            'title' => 'Catalogue bibliothecaire',
        ]);
    }

    #[Route('/admin/catalogue/new', name: 'app_admin_books_new')]
    public function adminBooksNew(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        return $this->handleBookForm($request, $entityManager, new Book(), 'app_admin_books', 'app_admin_dashboard');
    }

    #[Route('/admin/catalogue/{id}/edit', name: 'app_admin_books_edit', requirements: ['id' => '\\d+'])]
    public function adminBooksEdit(Request $request, Book $book, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        return $this->handleBookForm($request, $entityManager, $book, 'app_admin_books', 'app_admin_dashboard');
    }

    #[Route('/bibliothecaire/catalogue/new', name: 'app_librarian_books_new')]
    public function librarianBooksNew(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_LIBRARIAN');

        return $this->handleBookForm($request, $entityManager, new Book(), 'app_librarian_books', 'app_librarian_dashboard');
    }

    #[Route('/bibliothecaire/catalogue/{id}/edit', name: 'app_librarian_books_edit', requirements: ['id' => '\\d+'])]
    public function librarianBooksEdit(Request $request, Book $book, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_LIBRARIAN');

        return $this->handleBookForm($request, $entityManager, $book, 'app_librarian_books', 'app_librarian_dashboard');
    }

    #[Route('/admin/reservations/historique', name: 'app_admin_reservations_history')]
    public function adminReservationsHistory(BookLoanRepository $bookLoanRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        return $this->render('backoffice/reservations/history.html.twig', [
            'title' => 'Historique des reservations',
            'dashboardRoute' => 'app_admin_dashboard',
            'loans' => $bookLoanRepository->findHistory(),
        ]);
    }

    #[Route('/bibliothecaire/reservations/historique', name: 'app_librarian_reservations_history')]
    public function librarianReservationsHistory(BookLoanRepository $bookLoanRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_LIBRARIAN');

        return $this->render('backoffice/reservations/history.html.twig', [
            'title' => 'Historique des reservations',
            'dashboardRoute' => 'app_librarian_dashboard',
            'loans' => $bookLoanRepository->findHistory(),
        ]);
    }

    #[Route('/admin/moderation/commentaires', name: 'app_admin_review_moderation')]
    public function moderation(BookReviewRepository $bookReviewRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        return $this->render('backoffice/moderation/reviews.html.twig', [
            'reviews' => $bookReviewRepository->findPendingModeration(),
        ]);
    }

    #[Route('/admin/moderation/commentaires/{id}/approve', name: 'app_admin_review_approve', methods: ['POST'], requirements: ['id' => '\\d+'])]
    public function approveReview(Request $request, BookReview $review, EntityManagerInterface $entityManager): RedirectResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if ($this->isCsrfTokenValid('approve-review-'.$review->getId(), (string) $request->request->get('_token'))) {
            $review->setIsApproved(true);
            $entityManager->flush();
            $this->addFlash('success', 'Commentaire approuve.');
        }

        return $this->redirectToRoute('app_admin_review_moderation');
    }

    #[Route('/admin/moderation/commentaires/{id}/reject', name: 'app_admin_review_reject', methods: ['POST'], requirements: ['id' => '\\d+'])]
    public function rejectReview(Request $request, BookReview $review, EntityManagerInterface $entityManager): RedirectResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if ($this->isCsrfTokenValid('reject-review-'.$review->getId(), (string) $request->request->get('_token'))) {
            $entityManager->remove($review);
            $entityManager->flush();
            $this->addFlash('success', 'Commentaire supprime.');
        }

        return $this->redirectToRoute('app_admin_review_moderation');
    }

    #[Route('/admin/users', name: 'app_admin_users')]
    public function users(UserRepository $userRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        return $this->render('backoffice/users/index.html.twig', [
            'users' => $userRepository->findBy([], ['email' => 'ASC']),
        ]);
    }

    #[Route('/admin/users/{id}/role', name: 'app_admin_user_role', methods: ['POST'], requirements: ['id' => '\\d+'])]
    public function updateRole(Request $request, User $user, EntityManagerInterface $entityManager): RedirectResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if (!$this->isCsrfTokenValid('update-role-'.$user->getId(), (string) $request->request->get('_token'))) {
            return $this->redirectToRoute('app_admin_users');
        }

        $role = (string) $request->request->get('role', 'ROLE_USER');
        $allowedRoles = ['ROLE_USER', 'ROLE_LIBRARIAN', 'ROLE_ADMIN'];

        if (in_array($role, $allowedRoles, true)) {
            $user->setRoles([$role]);
            $entityManager->flush();
            $this->addFlash('success', 'Role mis a jour.');
        }

        return $this->redirectToRoute('app_admin_users');
    }

    #[Route('/admin/users/new', name: 'app_admin_users_new')]
    public function adminUsersNew(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $hasher, UserRepository $userRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $email = (string) $form->get('email')->getData();
            if ('' !== $email && null !== $userRepository->findOneBy(['email' => $email])) {
                $form->get('email')->addError(new FormError('Cet email est deja utilise.'));
            }
        }

        if ($form->isSubmitted() && $form->isValid()) {
            if ($plainPassword = $form->get('plainPassword')->getData()) {
                $user->setPassword($hasher->hashPassword($user, $plainPassword));
            }
            $user->setIsVerified(true);
            $user->setRoles($form->get('roles')->getData() ? [$form->get('roles')->getData()] : ['ROLE_USER']);
            $entityManager->persist($user);
            $entityManager->flush();
            $this->addFlash('success', 'Utilisateur ajoute.');
            return $this->redirectToRoute('app_admin_users');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addFlash('error', 'Le formulaire contient des erreurs. Veuillez verifier les champs.');
        }

        return $this->render('backoffice/users/form.html.twig', [
            'form' => $form,
            'isEdition' => false,
        ]);
    }

    #[Route('/admin/users/{id}/edit', name: 'app_admin_users_edit', requirements: ['id' => '\\d+'])]
    public function adminUsersEdit(Request $request, User $user, EntityManagerInterface $entityManager, UserPasswordHasherInterface $hasher): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->has('plainPassword') && $plainPassword = $form->get('plainPassword')->getData()) {
                $user->setPassword($hasher->hashPassword($user, $plainPassword));
            }
            $user->setRoles($form->get('roles')->getData() ? [$form->get('roles')->getData()] : ['ROLE_USER']);
            $entityManager->flush();
            $this->addFlash('success', 'Utilisateur mis a jour.');
            return $this->redirectToRoute('app_admin_users');
        }

        return $this->render('backoffice/users/form.html.twig', [
            'form' => $form,
            'isEdition' => true,
        ]);
    }

    #[Route('/admin/users/{id}/delete', name: 'app_admin_users_delete', methods: ['POST'], requirements: ['id' => '\\d+'])]
    public function adminUsersDelete(Request $request, User $user, EntityManagerInterface $entityManager): RedirectResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if (!$this->isCsrfTokenValid('delete-user-'.$user->getId(), (string) $request->request->get('_token'))) {
            return $this->redirectToRoute('app_admin_users');
        }

        $entityManager->remove($user);
        $entityManager->flush();
        $this->addFlash('success', 'Utilisateur supprime.');

        return $this->redirectToRoute('app_admin_users');
    }

    #[Route('/admin/catalogue/{id}/delete', name: 'app_admin_books_delete', methods: ['POST'], requirements: ['id' => '\\d+'])]
    public function adminBooksDelete(Request $request, Book $book, EntityManagerInterface $entityManager): RedirectResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if (!$this->isCsrfTokenValid('delete-book-'.$book->getId(), (string) $request->request->get('_token'))) {
            return $this->redirectToRoute('app_admin_books');
        }

        $entityManager->remove($book);
        $entityManager->flush();
        $this->addFlash('success', 'Ouvrage supprime.');

        return $this->redirectToRoute('app_admin_books');
    }

    #[Route('/bibliothecaire/catalogue/{id}/delete', name: 'app_librarian_books_delete', methods: ['POST'], requirements: ['id' => '\\d+'])]
    public function librarianBooksDelete(Request $request, Book $book, EntityManagerInterface $entityManager): RedirectResponse
    {
        $this->denyAccessUnlessGranted('ROLE_LIBRARIAN');

        if (!$this->isCsrfTokenValid('delete-book-'.$book->getId(), (string) $request->request->get('_token'))) {
            return $this->redirectToRoute('app_librarian_books');
        }

        $entityManager->remove($book);
        $entityManager->flush();
        $this->addFlash('success', 'Ouvrage supprime.');

        return $this->redirectToRoute('app_librarian_books');
    }

    private function handleBookForm(Request $request, EntityManagerInterface $entityManager, Book $book, string $redirectRoute, string $dashboardRoute): Response
    {
        $isEdition = null !== $book->getId();
        $form = $this->createForm(BookType::class, $book);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($book);
            $entityManager->flush();

            $this->addFlash('success', $isEdition ? 'Ouvrage mis a jour.' : 'Ouvrage ajoute.');

            return $this->redirectToRoute($redirectRoute);
        }

        return $this->render('backoffice/books/form.html.twig', [
            'form' => $form,
            'dashboardRoute' => $dashboardRoute,
            'redirectRoute' => $redirectRoute,
            'isEdition' => $isEdition,
        ]);
    }
}
