<?php
namespace Partitech\SonataExtra\Block;

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
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Service\Attribute\Required;
use Twig\Environment;
use Partitech\SonataExtra\Service\AssetsHandler;

#[AutoconfigureTag(name: 'sonata.block')]
final class CookieConsentBlockService extends AbstractBlockService implements EditableBlockService
{
    private $assetsHandler;
    #[Required]
    public function autowireDependencies(
        Environment $twig,
        AssetsHandler $assetsHandler
    ): void {
        parent::__construct($twig);
        $this->assetsHandler = $assetsHandler;
    }

    public function execute(BlockContextInterface $blockContext, Response $response = null): Response
    {

        $this->assetsHandler->addCss('/bundles/partitechsonataextra/assets/styles/orejime.css');
        $this->assetsHandler->addJs('/bundles/partitechsonataextra/assets/js/orejime.js', false);

        $settings = $blockContext->getSettings();

        $class = $settings['class'];
        $style_url = $settings['style_url'];
        $template = $settings['template'];
        $config_orejim = $settings['config_orejim'];
        $config_tags = $settings['config_tags'];

        if(!empty($style_url)){
            $this->assetsHandler->addCss($style_url);
        }
        $this->assetsHandler->addJsInline('
        document.addEventListener(\'DOMContentLoaded\', function() {
            window.orejimeConfig = '.$config_orejim.'
            orejime=Orejime.init(window.orejimeConfig);
        });
        ');

        return $this->renderResponse($template, [
            'block' => $blockContext->getBlock(),
            'config_orejim' => $config_orejim,
            'config_tags' => $config_tags,
            'class' => $class,
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
                ['config_orejim', TextareaType::class, [
                    'label' => 'Configuration Orejim',
                    'required' => false,
                    'translation_domain' => 'SonataExtraBundle',
                    'attr' => ['style' => 'min-height: 600px;'],

                ]],
                ['config_tags', TextareaType::class, [
                    'label' => 'Configuration de vos tags',
                    'required' => false,
                    'translation_domain' => 'SonataExtraBundle',
                    'attr' => ['style' => 'min-height: 600px;'],
                ]],
                ['template', TextType::class, [
                    'label' => 'Template',
                    'translation_domain' => 'SonataExtraBundle',
                ]],
                ['style_url', TextType::class, [
                    'label' => 'Style URL',
                    'translation_domain' => 'SonataExtraBundle',
                ]],
                ['class', TextType::class, [
                    'label' => 'CSS Class',
                    'required' => false,
                    'translation_domain' => 'SonataExtraBundle',
                ]],
            ],
            'translation_domain' => 'SonataExtraBundle',
        ]);
    }

    public function validate(ErrorElement $errorElement, BlockInterface $block): void
    {
    }

    public function configureSettings(OptionsResolver $resolver): void
    {
$orejimeConfig = <<<HTML
{
/* see https://github.com/empreinte-digitale/orejime/blob/master/README.md */
    elementID: "orejime",
    appElement: "#app",
    cookieName: "rgpd",
    cookieExpiresAfterDays: 365,
    cookieDomain: location.hostname,
    privacyPolicy: "/politique-des-cookies", // URL of your cookie policy page
    default: true,
    mustConsent: false,
    mustNotice: false,
    lang: navigator.language.substring(0, 2) || "en", // Default language or the one from the browser
    logo: false,
    debug: false,
    
translations: {
fr: {
        consentModal: {
            title: "Paramètres des cookies",
            description: "Ici, vous pouvez voir et personnaliser les informations que nous collectons à votre sujet.",
            privacyPolicy: {
                text: "Lire notre {privacyPolicy}",
                name: "politique de confidentialité", 
            },
        },
        consentNotice: {
            changeDescription: "Il y a eu des changements depuis votre dernière visite, veuillez mettre à jour votre consentement.",
            description: "Nous collectons et traitons vos informations personnelles aux fins suivantes : {purposes}.",
            learnMore: "En savoir plus",
        },
        accept: "Accepter",
        decline: "Refuser",
        acceptAll: "Tout accepter",
        declineAll: "Tout refuser",
        acceptSelected: "Accepter la sélection",
        purposes: {
            analytics: "Analyse et performance",
            security: "Sécurité",
            livechat: "Chat en direct",
            advertisement: "Publicité",
        },
        ok: "OK",
        save: "Enregistrer",
        saveDataProcessingInfo: "Enregistrer les informations sur le traitement des données",
        app: {
            disableAll: {
                title: "Activer/désactiver tout",
                description: "Utilisez ce commutateur pour activer ou désactiver toutes les applications.",
            }
        },
    },
        en: {
            consentModal: {
                title: "Cookie settings",
                description: "Here you can see and customize the information that we collect about you.",
                privacyPolicy: {
                    text: "Read our  {privacyPolicy}",
                    name: "privacy policy",
                },
            },
            consentNotice: {
                changeDescription: "There have been changes since your last visit, please update your consent.",
                description: "We collect and process your personal information for the following purposes: {purposes}.",
                learnMore: "Learn More",
            },
            accept: "Accep",
            decline:"decline",
            acceptAll: "Accept all",
            declineAll: "Decline all",
            acceptSelected: "Accept selected",
            purposes: {
                analytics: "Analytics and performance",
                security: "Security",
                livechat: "Live chat",
                advertisement: "Advertisement",
            },
            ok: "OK",
            save: "Save",
            saveDataProcessingInfo: "Save information about data processing",
            app: {
                disableAll: {
                    title: "Toggle all",
                    description: "Use this switch to enable or disable all apps.",
                }
            },
        },
es: {
    consentModal: {
        title: "Configuración de cookies",
        description: "Aquí puede ver y personalizar la información que recopilamos sobre usted.",
        privacyPolicy: {
            text: "Leer nuestra {privacyPolicy}",
            name: "política de privacidad", 
        },
    },
    consentNotice: {
        changeDescription: "Ha habido cambios desde su última visita, por favor actualice su consentimiento.",
        description: "Recopilamos y procesamos su información personal para los siguientes fines: {purposes}.",
        learnMore: "Aprender más",
    },
    accept: "Aceptar",
    decline: "Rechazar",
    acceptAll: "Aceptar todo",
    declineAll: "Rechazar todo",
    acceptSelected: "Aceptar seleccionado",
    purposes: {
        analytics: "Análisis y rendimiento",
        security: "Seguridad",
        livechat: "Chat en vivo",
        advertisement: "Publicidad",
    },
    ok: "OK",
    save: "Guardar",
    saveDataProcessingInfo: "Guardar información sobre el procesamiento de datos",
    app: {
        disableAll: {
            title: "Activar/desactivar todo",
            description: "Utilice este interruptor para activar o desactivar todas las aplicaciones.",
        }
    },
},
it: {
    consentModal: {
        title: "Impostazioni dei cookie",
        description: "Qui puoi vedere e personalizzare le informazioni che raccogliamo su di te.",
        privacyPolicy: {
            text: "Leggi la nostra {privacyPolicy}",
            name: "politica sulla riservatezza", 
        },
    },
    consentNotice: {
        changeDescription: "Ci sono stati dei cambiamenti dalla tua ultima visita, si prega di aggiornare il tuo consenso.",
        description: "Raccogliamo ed elaboriamo le tue informazioni personali per i seguenti scopi: {purposes}.",
        learnMore: "Per saperne di più",
    },
    accept: "Accettare",
    decline: "Rifiutare",
    acceptAll: "Accetta tutto",
    declineAll: "Rifiuta tutto",
    acceptSelected: "Accetta selezionati",
    purposes: {
        analytics: "Analisi e performance",
        security: "Sicurezza",
        livechat: "Chat dal vivo",
        advertisement: "Pubblicità",
    },
    ok: "OK",
    save: "Salvare",
    saveDataProcessingInfo: "Salva le informazioni sul trattamento dei dati",
    app: {
        disableAll: {
            title: "Attiva/disattiva tutto",
            description: "Usa questo interruttore per attivare o disattivare tutte le applicazioni.",
        }
    },
},
vi: {
    consentModal: {
        title: "Cài đặt cookie",
        description: "Tại đây, bạn có thể xem và tùy chỉnh thông tin mà chúng tôi thu thập về bạn.",
        privacyPolicy: {
            text: "Đọc {privacyPolicy} của chúng tôi",
            name: "chính sách bảo mật", 
        },
    },
    consentNotice: {
        changeDescription: "Đã có thay đổi kể từ lần truy cập cuối cùng của bạn, vui lòng cập nhật sự đồng ý của bạn.",
        description: "Chúng tôi thu thập và xử lý thông tin cá nhân của bạn cho các mục đích sau: {purposes}.",
        learnMore: "Tìm hiểu thêm",
    },
    accept: "Chấp nhận",
    decline: "Từ chối",
    acceptAll: "Chấp nhận tất cả",
    declineAll: "Từ chối tất cả",
    acceptSelected: "Chấp nhận đã chọn",
    purposes: {
        analytics: "Phân tích và hiệu suất",
        security: "An ninh",
        livechat: "Trò chuyện trực tiếp",
        advertisement: "Quảng cáo",
    },
    ok: "OK",
    save: "Lưu",
    saveDataProcessingInfo: "Lưu thông tin về xử lý dữ liệu",
    app: {
        disableAll: {
            title: "Bật/tắt tất cả",
            description: "Sử dụng công tắc này để bật hoặc tắt tất cả các ứng dụng.",
        }
    },
},
    },
    apps: [
        {
            name: "google-tag-manager",
            title: "GoogleTag Manager",
            purposes: ["analytics"],
            cookies: ["ga-cookie"],
            required: false,
            optOut: false,
            default: true,
            onlyOnce: false,
        }
    ],
    categories: [
        {
            name: "analytics",
            title: "Analytics",
            apps: ["google-tag-manager"]
        }
    ]
};

HTML;

$config_tags=<<<HTML
<script 
type="opt-in" 
data-type="application/javascript" 
data-name="google-tag-manager"
>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());

    gtag('config', 'YOUR_GOOGLE_ANALYTICS_ID');
</script>

HTML;
        $resolver->setDefaults([
            'config_orejim' => $orejimeConfig,
            'config_tags' => $config_tags,
            'style_url' => '',
            'class' => null,
            'template' => '@PartitechSonataExtra/Blocks/cookie_consent/default.html.twig',
        ]);
    }

    public function getMetadata(): MetadataInterface
    {
        return new Metadata('RGPD', null, null, 'SonataBlockBundle', [
            'class' => 'fa fa-cookie',
        ]);
    }
}
