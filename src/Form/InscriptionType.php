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

class InscriptionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('prenom', TextType::class, [
                'label' => 'Prénom',
            ])
            ->add('nom', TextType::class, [
                'label' => 'Nom',
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
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
                'constraints' => [new NotNull(message: 'Indique ce que tu recherches.')],
            ])
            ->add('motivation', TextareaType::class, [
                'label' => 'Motivation',
                'required' => false,
                'attr' => ['rows' => 4, 'maxlength' => 1000],
            ])
            ->add('discord', TextType::class, [
                'label' => 'Pseudo Discord',
                'required' => false,
            ])
            ->add('insta', TextType::class, [
                'label' => 'Compte Instagram',
                'required' => false,
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
