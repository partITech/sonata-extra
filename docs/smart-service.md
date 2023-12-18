# Sonata Extra Bundle: Translation API Feature Documentation

The Sonata Extra Bundle provides a powerful feature for automatic translation through its Translation API functionality. This feature allows users to effortlessly translate text between different languages using AI-powered translation providers.

### Understanding Translation API

The Translation API is a feature that leverages AI-powered translation services to provide automatic translation for text. By integrating different translation providers, users can translate text to various languages without manual intervention.

### Configuration

Below is a snapshot of the default configuration for the Translation API feature in the Sonata Extra Bundle:


```yaml
partitech_sonata_extra:
   smart_service:
    translate_on_create_page: true
    translate_on_create_translation: true
    seo_proposal_on_article: true
    default_provider: open_ai
    translation_provider: open_ai
    seo_provider: open_ai
    providers:
      open_ai:
        class: Partitech\SonataExtra\Translation\Provider\OpenAiProvider
        api_key: 'sk-your-api-Key'
        model: 'gpt-3.5-turbo'
        max_token_per_request: 200
```

Explanation of configuration parameters:

- translate_on_create_page: When set to true, enables translation on the creation page.
- translate_on_create_translation: When set to true, enables translation on the translation creation (all the users admin with translation trait).
- seo_proposal_on_article: When set to true, enables SEO proposal (all the users admin with translation trait).
- default_provider: Specifies the default translation provider to be used. Set to null if you don't wish to use the Translation API.
- providers: A list of translation providers. Each provider has its own set of configurations.

- translation_provider: Set the translation to a specific provider
- seo_provider: Set the SEO to a specific provid

Best practice would be to set the key and model in the .env. 
But you are free to set it in the yaml conf file. By default it is configured to use the .env
```yaml
partitech_sonata_extra:
   smart_service:

    providers:
      open_ai:
        api_key: '%open_ai_api_key%'
        model: '%open_ai_api_model%'

```
```dotenv
OPEN_AI_API_KEY="sk-your-api-Key"
OPEN_AI_API_MODEL="gpt-3.5-turbo"
```


### OpenAI Provider Configuration

The default provider is OpenAI. Here's an explanation of the OpenAI provider's configuration parameters:

- class: The class name of the provider implementation.
- api_key: Your OpenAI API key. Find your API key [here](https://platform.openai.com/account/api-keys).
- model: The model to be used for translation. Read more about models and pricing [here](https://openai.com/pricing).
- max_token_per_request: The maximum number of tokens per request.


## Adding a New Provider

Adding a new provider requires two main steps:

1. **Create a Provider Class:**
   - Create a class that implements TranslationProviderInterface.
   - Implement the translate method to handle the translation using your chosen provider.

```php
namespace App\Translation\Provider;

class YourProvider implements TranslationProviderInterface
{
    private $config;
    public function setConfig($config)
    {
        // Your provider's configuration is automatically passed here 
         $this->config=$config;
    }
    
    public function translate(string $text, string $targetLanguage): string
    {
        // Translation logic here
        return $translated_string;
    }

    public function translateArray(array $arrayOfText, string $targetLanguage): array
    {
        // Translation logic here
        
        $translated_array[$key] = [
            'original' => 'original text',
            'translated' => 'translated text',
         ];
        return $translated_array;
    }
}

```

Then you can call translator anywhere

```php
<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Service\Attribute\Required;

use Partitech\SonataExtra\SmartService\SmartServiceProviderFactoryInterface;


class TranslationController extends AbstractController
{

    private SmartServiceProviderFactoryInterface $smartServiceProviderFactory;

    #[Required]
    public function autowireDependencies(
        SmartServiceProviderFactoryInterface $smartServiceProviderFactory
    ):void{
        $this->smartServiceProviderFactory = $smartServiceProviderFactory;
    }


    /**
     * @Route("test/translate", name="translate")
     */
    public function translate(Request $request): Response
    {
         /* you can force the provider */
        $translationProvider = $this->smartServiceProviderFactory->create('open_ai');
         /* or you can use the default one */
        $translationProvider = $this->smartServiceProviderFactory->create();
        
        $text = 'Il fera beau demain';
        $targetLanguage = 'de'; 
        /*
           You can use any locals that the api can understand, depending on the service.
           fr
           fr_FR
        */
        

        $translatedText = $translationProvider->translate($text, $targetLanguage);


        return $this->render('translation/translate.html.twig', [
            'original_text' => $text,
            'translated_text' => $translatedText,
        ]);
    }
}
```
2. **Update Configuration:**
   - Add your new provider to the providers section in the configuration.

```yaml
partitech_sonata_extra:
   smart_service:
    providers:
      your_provider:
        class: App\SmartService\Provider\YourProvider
        # Other provider-specific configuration here
```

By following these steps, you can add and configure new translation providers to extend the Translation API feature's capabilities within the Sonata Extra Bundle.