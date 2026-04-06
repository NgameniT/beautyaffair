<?php

namespace App\Controller;

use App\Entity\EventRegistration;
use App\Entity\LibraryEvent;
use App\Entity\User;
use App\Repository\EventRegistrationRepository;
use App\Repository\LibraryEventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class EventController extends AbstractController
{
    #[Route('/evenements', name: 'app_events')]
    public function index(LibraryEventRepository $eventRepository, EventRegistrationRepository $eventRegistrationRepository): Response
    {
        $events = $eventRepository->findUpcoming();
        $registrationCounts = [];
        $registeredEventIds = [];
        $user = $this->getUser();

        foreach ($events as $event) {
            $registrationCounts[$event->getId()] = $eventRegistrationRepository->countForEvent($event);
        }

        if ($user instanceof User) {
            foreach ($eventRegistrationRepository->findByUser($user) as $registration) {
                if (null !== $registration->getEvent()?->getId()) {
                    $registeredEventIds[] = $registration->getEvent()->getId();
                }
            }
        }

        return $this->render('event/index.html.twig', [
            'events' => $events,
            'registrationCounts' => $registrationCounts,
            'registeredEventIds' => $registeredEventIds,
        ]);
    }

    #[Route('/evenements/{id}/register', name: 'app_events_register', methods: ['POST'], requirements: ['id' => '\\d+'])]
    public function register(Request $request, LibraryEvent $event, EventRegistrationRepository $eventRegistrationRepository, EntityManagerInterface $entityManager): RedirectResponse
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        if (!$this->isCsrfTokenValid('register-event-'.$event->getId(), (string) $request->request->get('_token'))) {
            $this->addFlash('error', 'La demande d\'inscription est invalide.');

            return $this->redirectToRoute('app_events');
        }

        /** @var User $user */
        $user = $this->getUser();

        if ($eventRegistrationRepository->isRegistered($user, $event)) {
            $this->addFlash('warning', 'Vous etes deja inscrit a cet evenement.');

            return $this->redirectToRoute('app_events');
        }

        if ($eventRegistrationRepository->countForEvent($event) >= $event->getCapacity()) {
            $this->addFlash('error', 'Cet evenement est complet.');

            return $this->redirectToRoute('app_events');
        }

        $registration = (new EventRegistration())
            ->setUser($user)
            ->setEvent($event);

        $entityManager->persist($registration);
        $entityManager->flush();

        $this->addFlash('success', 'Votre inscription a bien ete enregistree.');

        return $this->redirectToRoute('app_dashboard');
    }
}
