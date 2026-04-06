<?php

namespace App\Controller;

use App\Entity\BookLoan;
use App\Entity\User;
use App\Repository\BookFavoriteRepository;
use App\Repository\BookLoanRepository;
use App\Repository\EventRegistrationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DashboardController extends AbstractController
{
    #[Route('/mon-compte', name: 'app_dashboard')]
    public function index(BookLoanRepository $bookLoanRepository, EventRegistrationRepository $eventRegistrationRepository, BookFavoriteRepository $bookFavoriteRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        /** @var User $user */
        $user = $this->getUser();

        return $this->render('dashboard/index.html.twig', [
            'user' => $user,
            'activeLoans' => $bookLoanRepository->findActiveByUser($user),
            'eventRegistrations' => $eventRegistrationRepository->findByUser($user),
            'favorites' => $bookFavoriteRepository->findByUser($user),
        ]);
    }

    #[Route('/mon-compte/loans/{id}/return', name: 'app_dashboard_return_loan', methods: ['POST'], requirements: ['id' => '\\d+'])]
    public function returnLoan(Request $request, BookLoan $loan, EntityManagerInterface $entityManager): RedirectResponse
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        /** @var User $currentUser */
        $currentUser = $this->getUser();

        if (!$this->isCsrfTokenValid('return-loan-'.$loan->getId(), (string) $request->request->get('_token'))) {
            $this->addFlash('error', 'La demande de retour est invalide.');

            return $this->redirectToRoute('app_dashboard');
        }

        if ($loan->getUser()?->getId() !== $currentUser?->getId()) {
            throw $this->createAccessDeniedException();
        }

        if (!$loan->isActive()) {
            $this->addFlash('warning', 'Cet emprunt est deja cloture.');

            return $this->redirectToRoute('app_dashboard');
        }

        $loan->markAsReturned();
        $loan->getBook()?->incrementAvailableCopies();
        $entityManager->flush();

        $this->addFlash('success', 'Le retour du livre a ete enregistre.');

        return $this->redirectToRoute('app_dashboard');
    }
}
