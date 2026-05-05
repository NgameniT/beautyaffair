<?php

namespace App\Controller;

use App\Service\EmailService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ContactController extends AbstractController
{
    #[Route('/contact', name: 'app_contact', methods: ['GET', 'POST'])]
    public function index(Request $request, EmailService $email): Response
    {
        if ($request->isMethod('POST')) {
            $nom       = trim($request->request->get('nom', ''));
            $prenom    = trim($request->request->get('prenom', ''));
            $mail      = trim($request->request->get('email', ''));
            $telephone = trim($request->request->get('telephone', '')) ?: null;
            $sujet     = trim($request->request->get('sujet', 'Autre'));
            $message   = trim($request->request->get('message', ''));

            if ($nom && $mail && $message) {
                try {
                    $email->sendContactMessage(
                        $prenom ? "$prenom $nom" : $nom,
                        $mail,
                        $sujet ?: 'Autre',
                        $message,
                        $telephone,
                    );
                    $this->addFlash('success', 'Votre message a bien été envoyé. Nous vous répondrons dans les 24h.');
                } catch (\Throwable) {
                    $this->addFlash('danger', 'Une erreur est survenue. Veuillez réessayer ou nous contacter par téléphone.');
                }
            } else {
                $this->addFlash('danger', 'Veuillez remplir tous les champs obligatoires.');
            }

            return $this->redirectToRoute('app_contact');
        }

        return $this->render('contact/index.html.twig');
    }
}
