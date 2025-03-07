# Translation API Feature Documentation

The **Sonata Extra Bundle** provides a powerful feature for **automatic translation** via its Translation API. You can integrate AI-powered translation providers (like OpenAI) to enable seamless text translation across multiple languages.

> [!NOTE]
> Customize the translation behavior, such as triggering automatic translations for newly created pages or generating SEO suggestions.

---

## Understanding the Translation API

The Translation API harnesses AI-powered translation services to automatically translate text between languages. You can configure different **translation providers**, each with its own methods and settings, to suit your application's needs.

---

## Configuration

Below is a snapshot of the default configuration for the Translation API in the Sonata Extra Bundle:

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

**Key Parameters**:

- **`translate_on_create_page`**: If `true`, automatically translates newly created pages.
- **`translate_on_create_translation`**: If `true`, automatically translates new entity translations.
- **`seo_proposal_on_article`**: If `true`, proposes SEO-friendly text (using the same translation provider).
- **`default_provider`**: The default provider to use if none is specified. Set to `null` to disable the Translation API.
- **`providers`**: A list of translation providers, each with its own configuration.
- **`translation_provider`**: Specific provider used for translation tasks (e.g., OpenAI).
- **`seo_provider`**: Specific provider used for SEO tasks (can be the same as `translation_provider` or different).

> [!NOTE]
> For best practices, set your API key and model in the `.env` file rather than committing them to YAML.

Example `.env` usage:

```yaml
partitech_sonata_extra:
smart_service:
providers:
open_ai:
api_key: '%open_ai_api_key%'
model: '%open_ai_api_model%'
```

```env
OPEN_AI_API_KEY="sk-your-api-Key"
OPEN_AI_API_MODEL="gpt-3.5-turbo"
```

---

## OpenAI Provider Configuration

The default provider is **OpenAI**, which uses GPT-based models:

- **`class`**: Points to the provider class (`OpenAiProvider`).
- **`api_key`**: Your OpenAI key.
- **`model`**: The GPT model (e.g., `gpt-3.5-turbo`).
- **`max_token_per_request`**: Maximum token limit for a single request.

---

## Adding a New Provider

To integrate another translation provider:

### 1. Create a Provider Class

Implement the `TranslationProviderInterface` and define methods for text and array translations:

```php
namespace App\Translation\Provider;

use Partitech\SonataExtra\Translation\Provider\TranslationProviderInterface;

class YourProvider implements TranslationProviderInterface
{
private $config;

    public function setConfig($config)
    {
        $this->config = $config;
    }

    public function translate(string $text, string $targetLanguage): string
    {
        // Your translation logic
        return $translatedString;
    }

    public function translateArray(array $arrayOfText, string $targetLanguage): array
    {
        // Translate multiple strings
        // Example structure:
        // $translated_array[$key] = [
        //     'original' => 'original text',
        //     'translated' => 'translated text',
        // ];
        return $translated_array;
    }
}
```

### 2. Update the Configuration

Add your new provider to the `providers` list in YAML:

```yaml
partitech_sonata_extra:
smart_service:
providers:
your_provider:
class: App\SmartService\Provider\YourProvider
# Other provider-specific configs
```

> [!NOTE]
> Adjust the **`class`** path to point to your provider’s namespace. You can also include other custom settings relevant to your provider.

---

## Usage Example

You can call the translator from **any** part of your application, such as a custom controller:

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
    ): void {
        $this->smartServiceProviderFactory = $smartServiceProviderFactory;
    }

    /**
     * @Route("/test/translate", name="translate")
     */
    public function translate(Request $request): Response
    {
        // Force the provider (e.g., 'open_ai')
        $translationProvider = $this->smartServiceProviderFactory->create('open_ai');

        // Or use the default one from config
        // $translationProvider = $this->smartServiceProviderFactory->create();

        $text = 'Il fera beau demain';
        $targetLanguage = 'de';

        $translatedText = $translationProvider->translate($text, $targetLanguage);

        return $this->render('translation/translate.html.twig', [
            'original_text' => $text,
            'translated_text' => $translatedText,
        ]);
    }
}
```

In this example:
- The user enters some text and a target language.
- The controller calls `translate()` on the chosen provider, returning the translated string.
- The **TranslationProviderInterface** can handle both single-string and multi-string translations.

---

## Conclusion

The **Translation API** in the Sonata Extra Bundle simplifies integration with AI-based translation services. By leveraging the default **OpenAI** provider or creating your own, you can deliver real-time translations and even SEO suggestions directly within your Sonata-based application.
