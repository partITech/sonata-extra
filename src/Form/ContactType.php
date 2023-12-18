<?php

namespace Partitech\SonataExtra\Form;

use Partitech\SonataExtra\Entity\Contact;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Valid;

class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $settings = $options['settings'];

        if ($settings['firstName']) {
            $builder
                ->add('firstName', TextType::class, [
                'label' => $settings['firstNameLabel'],
                'required' => $settings['firstNameRequired'],
            ]);
        }

        if ($settings['lastName']) {
            $builder
                ->add('lastName', TextType::class, [
                    'label' => $settings['lastNameLabel'],
                    'required' => $settings['lastNameRequired'],
                ]);
        }

        if ($settings['companyName']) {
            $builder
                ->add('companyName', TextType::class, [
                    'label' => $settings['companyNameLabel'],
                    'required' => $settings['companyNameRequired'],
                ]);
        }

        if ($settings['address']) {
            $builder
                ->add('address', TextType::class, [
                    'label' => $settings['addressLabel'],
                    'required' => $settings['addressRequired'],
                ]);
        }

        if ($settings['email']) {
            $builder
                ->add('email', EmailType::class, [
                    'label' => $settings['emailLabel'],
                    'required' => $settings['emailRequired'],
                ]);
        }

        if ($settings['phone']) {
            $builder
                ->add('phone', TelType::class, [
                    'label' => $settings['phoneLabel'],
                    'required' => $settings['phoneRequired'],
                ]);
        }

        if ($settings['additionalInformation']) {
            $builder
                ->add('additionalInformation', TextareaType::class, [
                    'label' => $settings['additionalInformationLabel'],
                    'required' => $settings['additionalInformationRequired'],
                    'attr' => ['rows' => 6]]);
        }

        if ($settings['sendMeACopy']) {
            $builder
                ->add('sendMeACopy', CheckboxType::class, [
                    'label' => $settings['sendMeACopyLabel'],
                    'required' => false,
                    'attr' => ['rows' => 6]]);
        }

        $builder->add('submit', SubmitType::class, [
            'label' => $settings['submitLabel'] ?? 'envoyer',
            'attr' => ['class' => 'btn btn-primary'],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Contact::class,
            'settings' => null,
            'constraints' => new Valid(),
            'translation_domain' => 'PartitechSonataExtraBundle'
        ]);
    }
}
