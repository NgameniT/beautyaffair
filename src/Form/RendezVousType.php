<?php

namespace App\Form;

use App\Entity\Prestation;
use App\Entity\RendezVous;
use App\Repository\PrestationRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RendezVousType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('prestation', EntityType::class, [
                'class' => Prestation::class,
                'query_builder' => fn(PrestationRepository $r) => $r->createQueryBuilder('p')
                    ->where('p.actif = true')
                    ->join('p.categorie', 'c')
                    ->addSelect('c')
                    ->orderBy('c.nom', 'ASC')
                    ->addOrderBy('p.nom', 'ASC'),
                'choice_label' => fn(Prestation $p) => $p->getCategorie()->getNom()
                    . ' › ' . $p->getNom()
                    . ' — ' . number_format((float) $p->getPrix(), 0, ',', ' ') . ' FCFA'
                    . ' (' . $p->getDuree() . ' min)',
                'group_by'     => fn(Prestation $p) => $p->getCategorie()->getNom(),
                'label'        => 'Prestation',
                'placeholder'  => 'Choisissez une prestation…',
            ])
            ->add('date', DateType::class, [
                'mapped'  => false,
                'widget'  => 'single_text',
                'label'   => 'Date souhaitée',
                'attr'    => ['min' => (new \DateTime('+1 day'))->format('Y-m-d')],
            ])
            ->add('heure', TimeType::class, [
                'mapped'      => false,
                'widget'      => 'single_text',
                'label'       => 'Heure souhaitée',
                'attr'        => ['min' => '08:00', 'max' => '19:30', 'step' => '1800'],
            ])
            ->add('notes', TextareaType::class, [
                'required' => false,
                'label'    => 'Notes (optionnel)',
                'attr'     => ['rows' => 3, 'placeholder' => 'Précisions sur votre coiffure, allergies, etc.'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => RendezVous::class]);
    }
}
