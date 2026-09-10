<?php

namespace App\Form;

use App\Entity\Inscription;
use App\Enum\Classe;
use App\Enum\Preference;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;

/**
 * @extends AbstractType<Inscription>
 */
class InscriptionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('prenom', TextType::class, [
                'label' => 'Prénom',
                'empty_data' => '',
                'attr' => ['placeholder' => 'Léa'],
            ])
            ->add('nom', TextType::class, [
                'label' => 'Nom',
                'empty_data' => '',
                'attr' => ['placeholder' => 'Ton nom'],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'empty_data' => '',
                'attr' => ['placeholder' => 'prenom.nom@etu.umontpellier.fr'],
            ])
            ->add('classe', EnumType::class, [
                'class' => Classe::class,
                'label' => 'Classe',
                'placeholder' => 'Choisis ta classe',
                'choice_label' => fn (Classe $c) => $c->label(),
            ])
            ->add('preference', EnumType::class, [
                'class' => Preference::class,
                'label' => 'Je souhaite…',
                'expanded' => true,
                'choice_label' => fn (Preference $p) => $p->label(),
                'choice_attr' => fn (Preference $p) => [
                    'data-hint' => $p === Preference::PARRAIN
                        ? 'Tu es en 2e ou 3e année'
                        : 'Tu arrives cette année',
                ],
                'constraints' => [new NotNull(message: 'Indique ce que tu recherches.')],
            ])
            ->add('motivation', TextareaType::class, [
                'label' => 'Motivation',
                'required' => false,
                'attr' => [
                    'rows' => 4,
                    'maxlength' => 1000,
                    'placeholder' => 'Ce que tu aimes, ce sur quoi tu peux aider, tes dispos…',
                ],
            ])
            ->add('discord', TextType::class, [
                'label' => 'Discord · optionnel',
                'required' => false,
                'attr' => ['placeholder' => 'lea#0000'],
            ])
            ->add('insta', TextType::class, [
                'label' => 'Instagram · optionnel',
                'required' => false,
                'attr' => ['placeholder' => '@ton_compte'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Inscription::class,
        ]);
    }
}
