<?php

namespace Partitech\SonataExtra\Block;

use Partitech\SonataExtra\Entity\Contact;
use Partitech\SonataExtra\Form\ContactType;
use Partitech\SonataExtra\Repository\ContactRepository;
use Partitech\SonataExtra\Service\ContactBlockMailerService;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Block\Service\AbstractBlockService;
use Sonata\BlockBundle\Block\Service\EditableBlockService;
use Sonata\BlockBundle\Form\Mapper\FormMapper;
use Sonata\BlockBundle\Meta\Metadata;
use Sonata\BlockBundle\Meta\MetadataInterface;
use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\Form\Type\ImmutableArrayType;
use Sonata\Form\Validator\ErrorElement;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;
use ReCaptcha\ReCaptcha;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

#[AutoconfigureTag(name: 'sonata.block')]
final class ContactBlockService extends AbstractBlockService implements EditableBlockService
{
    public function __construct(
        Environment $twig,
        private ParameterBagInterface $parameterBag,
        private readonly TranslatorInterface $translator,
        private readonly RequestStack $requestStack,
        private readonly FormFactoryInterface $formFactory,
        private readonly ContactRepository $contactRepository,
        private readonly ContactBlockMailerService $mailerContact
    ) {
        parent::__construct($twig);
    }

    public function execute(BlockContextInterface $blockContext, Response $response = null): Response|RedirectResponse
    {
        $request = $this->requestStack->getCurrentRequest();
        $settings = $blockContext->getSettings();
        $title=$settings['title'];
        $contact = new Contact();
        $contact->setCreatedAt(new \DateTimeImmutable());
        $form = $this->formFactory->create(ContactType::class, $contact, [
            'settings' => $settings,

        ]);

        $form->handleRequest($request);


        $recaptcha_site_key = $settings['GOOGLE_RECAPTCHA_SITE_KEY'];
        $recaptcha_site_secret = $settings['GOOGLE_RECAPTCHA_SECRET'];
        $recaptchaOn = $settings['GOOGLE_RECAPTCHA'];


        $recaptcha_success=true;
        if ($recaptchaOn && $form->isSubmitted()){
            $recaptcha = new ReCaptcha($recaptcha_site_secret);
            $resp = $recaptcha->verify($request->request->get('g-recaptcha-response'), $request->getClientIp());
            $recaptcha_success=$resp->isSuccess();
        }

        if ($form->isSubmitted()) {
            $formData = $form->getData();

            //honeypot should be empty, otherwise it could be a bot.
            if( empty($form->get('honeypot')->getViewData()) && $recaptcha_success && $form->isValid())
            {
                $this->contactRepository->save($contact, true);
                if ($settings['sendMeACopy'] && !empty($formData->getEmail())) {
                    $this->mailerContact->sendConfirmation($settings, $formData);
                }

                if ($settings['sendTo'] && !empty($settings['sendToAddress'])) {
                    $this->mailerContact->sendAdminConfirmation($settings, $formData);
                }
                $this->requestStack->getSession()->getFlashBag()->add('success', 'Votre message a été envoyé');

                return $this->renderResponse($settings['confirmation_template'], [
                    'confirmation_message' => $settings['confirmation_message'],
                    'confirmation_javascript'=> $settings['confirmation_javascript'],
                ], $response);
            }


        }

        return $this->renderResponse($blockContext->getTemplate(), [
            'block' => $blockContext->getBlock(),
            'title'=> $title,
            'settings' => $blockContext->getSettings(),
            'recaptchaSiteKey'=>!empty($recaptchaOn)?$recaptcha_site_key:false,
            'form' => $form->createView(),
        ], $response);
    }

    public function configureCreateForm(FormMapper $form, BlockInterface $block): void
    {
        $this->configureEditForm($form, $block);
    }

    public function configureEditForm(FormMapper $form, BlockInterface $block): void
    {

        $form->add('settings', ImmutableArrayType::class, [
            'keys' => [
               [
                    'GOOGLE_RECAPTCHA',
                    CheckboxType::class,
                    [
                        'label' => 'sonata-extra.block_contact.recaptcha',
                        'translation_domain' => 'PartitechSonataExtraBundle',
                        'required' => false,
                    ],
                ],

                [
                    'GOOGLE_RECAPTCHA_SITE_KEY',
                    TextType::class,
                    [
                        'label' => 'sonata-extra.block_contact.recaptcha_site_key',
                        'translation_domain' => 'PartitechSonataExtraBundle',
                        'required' => false,
                    ],
                ],

                [
                    'GOOGLE_RECAPTCHA_SECRET',
                    TextType::class,
                    [
                        'label' => 'sonata-extra.block_contact.recaptcha_site_secret',
                        'translation_domain' => 'PartitechSonataExtraBundle',
                        'required' => false,
                    ],
                ],


                [
                    'title',
                    TextType::class,
                    [
                        'label' => 'sonata-extra.block_contact.title',
                        'translation_domain' => 'PartitechSonataExtraBundle',
                        'required' => false,
                    ],
                ],
                [
                    'firstNameLabel',
                    TextType::class,
                    [
                        'label' => 'sonata-extra.block_contact.first_name_label',
                        'translation_domain' => 'PartitechSonataExtraBundle',
                        'required' => false,
                    ],
                ],
                [
                    'firstName',
                    CheckboxType::class,
                    [
                        'label' => 'sonata-extra.block_contact.use_field_first_name',
                        'translation_domain' => 'PartitechSonataExtraBundle',
                        'required' => false,
                    ],
                ],
                [
                    'firstNameRequired',
                    CheckboxType::class,
                    [
                        'label' => 'sonata-extra.block_contact.required_field',
                        'translation_domain' => 'PartitechSonataExtraBundle',
                        'required' => false,
                    ],
                ],

                [
                    'lastNameLabel',
                    TextType::class,
                    [
                        'label' => 'sonata-extra.block_contact.last_name_label',
                        'translation_domain' => 'PartitechSonataExtraBundle',
                        'required' => false,
                    ],
                ],
                [
                    'lastName',
                    CheckboxType::class,
                    [
                        'label' => 'sonata-extra.block_contact.use_field_last_name',
                        'translation_domain' => 'PartitechSonataExtraBundle',
                        'required' => false,
                    ],
                ],
                [
                    'lastNameRequired',
                    CheckboxType::class,
                    [
                        'label' => 'sonata-extra.block_contact.required_field',
                        'translation_domain' => 'PartitechSonataExtraBundle',
                        'required' => false,
                    ],
                ],

                [
                    'companyNameLabel',
                    TextType::class,
                    [
                        'label' => 'sonata-extra.block_contact.company_name_label',
                        'translation_domain' => 'PartitechSonataExtraBundle',
                        'required' => false,
                    ],
                ],
                [
                    'companyName',
                    CheckboxType::class,
                    [
                        'label' => 'sonata-extra.block_contact.use_field_company_name',
                        'translation_domain' => 'PartitechSonataExtraBundle',
                        'required' => false,
                    ],
                ],
                [
                    'companyNameRequired',
                    CheckboxType::class,
                    [
                        'label' => 'sonata-extra.block_contact.required_field',
                        'translation_domain' => 'PartitechSonataExtraBundle',
                        'required' => false,
                    ],
                ],

                [
                    'addressLabel',
                    TextType::class,
                    [
                        'label' => 'sonata-extra.block_contact.address_label',
                        'translation_domain' => 'PartitechSonataExtraBundle',
                        'required' => false,
                    ],
                ],
                [
                    'address',
                    CheckboxType::class,
                    [
                        'label' => 'sonata-extra.block_contact.use_field_address',
                        'translation_domain' => 'PartitechSonataExtraBundle',
                        'required' => false,
                    ],
                ],
                [
                    'addressRequired',
                    CheckboxType::class,
                    [
                        'label' => 'sonata-extra.block_contact.required_field',
                        'translation_domain' => 'PartitechSonataExtraBundle',
                        'required' => false,
                    ],
                ],

                [
                    'emailLabel',
                    TextType::class,
                    [
                        'label' => 'sonata-extra.block_contact.email_label',
                        'translation_domain' => 'PartitechSonataExtraBundle',
                        'required' => false,
                    ],
                ],
                [
                    'email',
                    CheckboxType::class,
                    [
                        'label' => 'sonata-extra.block_contact.use_field_email',
                        'translation_domain' => 'PartitechSonataExtraBundle',
                        'required' => false,
                    ],
                ],
                [
                    'emailRequired',
                    CheckboxType::class,
                    [
                        'label' => 'sonata-extra.block_contact.required_field',
                        'translation_domain' => 'PartitechSonataExtraBundle',
                        'required' => false,
                    ],
                ],

                [
                    'phoneLabel',
                    TextType::class,
                    [
                        'label' => 'sonata-extra.block_contact.phone_label',
                        'translation_domain' => 'PartitechSonataExtraBundle',
                        'required' => false,
                    ],
                ],
                [
                    'phone',
                    CheckboxType::class,
                    [
                        'label' => 'sonata-extra.block_contact.use_field_phone',
                        'translation_domain' => 'PartitechSonataExtraBundle',
                        'required' => false,
                    ],
                ],
                [
                    'phoneRequired',
                    CheckboxType::class,
                    [
                        'label' => 'sonata-extra.block_contact.required_field',
                        'translation_domain' => 'PartitechSonataExtraBundle',
                        'required' => false,
                    ],
                ],

                [
                    'additionalInformationLabel',
                    TextType::class,
                    [
                        'label' => 'sonata-extra.block_contact.additional_information_label',
                        'translation_domain' => 'PartitechSonataExtraBundle',
                        'required' => false,
                    ],
                ],
                [
                    'additionalInformation',
                    CheckboxType::class,
                    [
                        'label' => 'sonata-extra.block_contact.use_field_additional_information',
                        'translation_domain' => 'PartitechSonataExtraBundle',
                        'required' => false,
                    ],
                ],
                [
                    'additionalInformationRequired',
                    CheckboxType::class,
                    [
                        'label' => 'sonata-extra.block_contact.required_field',
                        'translation_domain' => 'PartitechSonataExtraBundle',
                        'required' => false,
                    ],
                ],

                [
                    'sendMeACopy',
                    CheckboxType::class,
                    [
                        'label' => 'sonata-extra.block_contact.send_me_a_copy',
                        'translation_domain' => 'PartitechSonataExtraBundle',
                        'required' => false,
                    ],
                ],
                [
                    'sendMeACopyLabel',
                    TextType::class,
                    [
                        'label' => 'sonata-extra.block_contact.send_me_a_copy_label',
                        'translation_domain' => 'PartitechSonataExtraBundle',
                        'required' => false,
                    ],
                ],

                [
                    'sendTo',
                    CheckboxType::class,
                    [
                        'label' => 'sonata-extra.block_contact.send_to_label',
                        'translation_domain' => 'PartitechSonataExtraBundle',
                        'required' => false,
                    ],
                ],
                [
                    'sendToAddress',
                    TextType::class,
                    [
                        'label' => 'sonata-extra.block_contact.send_to',
                        'translation_domain' => 'PartitechSonataExtraBundle',
                        'required' => false,
                    ],
                ],

                [
                    'submitLabel',
                    TextType::class,
                    [
                        'label' => 'sonata-extra.block_contact.submit_label',
                        'translation_domain' => 'PartitechSonataExtraBundle',
                        'required' => false,
                    ],
                ],

                [
                    'mailSubject',
                    TextType::class,
                    [
                        'label' => 'sonata-extra.block_contact.mail_subject',
                        'translation_domain' => 'PartitechSonataExtraBundle',
                        'required' => false,
                    ],
                ],
                [
                    'mailSignature',
                    TextType::class,
                    [
                        'label' => 'sonata-extra.block_contact.mail_signature',
                        'translation_domain' => 'PartitechSonataExtraBundle',
                    ]
                ],
                [
                    'template',
                     TextType::class,
                     [
                        'label' => 'Template',
                        'translation_domain' => 'PartitechSonataExtraBundle',
                    ]
                ],

                [
                    'template_mail_confirmation',
                    TextType::class,
                     [
                        'label' => 'sonata-extra.block_contact.mail_confirmation',
                        'translation_domain' => 'PartitechSonataExtraBundle',
                    ]
                ],
                [
                    'template_mail_admin_notification',
                     TextType::class,
                     [
                        'label' => 'sonata-extra.block_contact.mail_admin_notification',
                        'translation_domain' => 'PartitechSonataExtraBundle',
                    ]
                ],
                
                

                [
                    'confirmation_message',
                    TextareaType::class,
                    [
                        'label' => 'sonata-extra.block_contact.confirmation_message_label',
                        'translation_domain' => 'PartitechSonataExtraBundle',
                        
                            'attr' => [
                                'rows' => 15
                            ],
                        
                    ],
                ],
                
                
                [
                    'confirmation_javascript',
                    TextareaType::class,
                    [
                        'label' => 'sonata-extra.block_contact.confirmation_javascript_label',
                        'translation_domain' => 'PartitechSonataExtraBundle',
                        'required' => false,
                        'attr' => [
                            'rows' => 15
                        ],
                        
                    ]
                ],
                
                [
                    'confirmation_template',
                    TextType::class,
                    [
                        'label' => 'sonata-extra.block_contact.confirmation_template_label',
                        'translation_domain' => 'PartitechSonataExtraBundle',
                    ]
                ],
                
                
            ],
            'translation_domain' => 'PartitechSonataExtraBundle',
        ]);
    }

    public function validate(ErrorElement $errorElement, BlockInterface $block): void
    {
    }

    public function configureSettings(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'GOOGLE_RECAPTCHA' => false,
            'GOOGLE_RECAPTCHA_SITE_KEY' => "",
            'GOOGLE_RECAPTCHA_SECRET' => "",
            'title' => "Contact us",
            'firstName' => true,
            'firstNameLabel' => $this->translator->trans(
                'sonata-extra.block_contact.first_name',
                [],
                'PartitechSonataExtraBundle'
            ),
            'firstNameRequired' => true,

            'lastName' => true,
            'lastNameLabel' => $this->translator->trans(
                'sonata-extra.block_contact.last_name',
                [],
                'PartitechSonataExtraBundle'
            ),
            'lastNameRequired' => true,

            'companyName' => true,
            'companyNameLabel' => $this->translator->trans(
                'sonata-extra.block_contact.company_name',
                [],
                'PartitechSonataExtraBundle'
            ),
            'companyNameRequired' => false,

            'address' => true,
            'addressLabel' => $this->translator->trans(
                'sonata-extra.block_contact.address',
                [],
                'PartitechSonataExtraBundle'
            ),
            'addressRequired' => false,

            'email' => true,
            'emailLabel' => $this->translator->trans(
                'sonata-extra.block_contact.email',
                [],
                'PartitechSonataExtraBundle'
            ),
            'emailRequired' => true,

            'phone' => true,
            'phoneLabel' => $this->translator->trans(
                'sonata-extra.block_contact.phone',
                [],
                'PartitechSonataExtraBundle'
            ),
            'phoneRequired' => false,

            'additionalInformation' => true,
            'additionalInformationLabel' => $this->translator->trans(
                'sonata-extra.block_contact.additional_information', [],
                'PartitechSonataExtraBundle'
            ),
            'additionalInformationRequired' => false,

            'sendMeACopy' => true,
            'sendMeACopyLabel' => $this->translator->trans(
                'sonata-extra.block_contact.send_me_a_copy_default_message', [],
                'PartitechSonataExtraBundle'
            ),
            'sendTo' => true,
            'sendToAddress' => 'team@company.com',

            'submitLabel' => $this->translator->trans(
                'sonata-extra.block_contact.submitLabel', [],
                'PartitechSonataExtraBundle'
            ),
            'mailSubject' => 'Demande de contact',
            'content' => null,
            'mailSignature'=>'',
            'template' => '@PartitechSonataExtra/Blocks/contact/contact.html.twig',
            

            'template_mail_confirmation' => ContactBlockMailerService::CONFIRMATION_TEMPLATE,
            'template_mail_admin_notification' => ContactBlockMailerService::ADMIN_NOTIFICATION_TEMPLATE,
            
            
            'confirmation_javascript' => $this->translator->trans(
                '', [],
                'PartitechSonataExtraBundle',
            ),
            
            'confirmation_message' => $this->translator->trans(
                'sonata-extra.block_contact.confirmation_message', [],
                'PartitechSonataExtraBundle'
            ),

            'confirmation_template' => '@PartitechSonataExtra/Blocks/contact/confirmation.html.twig',
        ]);
    }

    public function getMetadata(): MetadataInterface
    {
        return new Metadata('Contact', null, null, 'SonataBlockBundle', [
            'class' => 'fa fa-address-book',
        ]);
    }
}
