<?php
namespace Partitech\SonataExtra\SmartService\Provider;

use Doctrine\ORM\EntityManagerInterface;
use Sonata\MediaBundle\Entity\MediaManager;
use Sonata\MediaBundle\Provider\ImageProvider;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\Service\Attribute\Required;
use Symfony\Contracts\Cache\ItemInterface;
use Partitech\SonataExtra\SmartService\SmartServiceProviderInterface;
use Partitech\SonataExtra\Service\CodeExtractor;
use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Partitech\SonataExtra\SmartService\TranslationCreateTemplateService;

class OpenAiProvider implements SmartServiceProviderInterface
{
    private const CACHE_TTL = 86400;  // 1 day
    private $client;
    private string $apiKey;
    private string $model;
    private string $max_token_per_request;
    private bool $createTemplate = false;


    private $oldCompletionApi=['babbage-002', 'davinci-002','text-davinci-003', 'text-davinci-002', 'davinci', 'curie', 'babbage', 'ada'];
    private ?LoggerInterface $logger = null;


    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }
    public function setContainer(ContainerInterface $container): void
    {
        $this->container = $container;
    }

    public function setInput(InputInterface $input): void
    {
        $this->input = $input;
    }

    public function setConfig($config)
    {
        $this->api_conf=$config;

        $this->apiKey = $config['api_key'];
        $this->model = $config['model'];
        $this->max_token_per_request = $config['max_token_per_request'];
        $this->client = new Client();

        //test if we are in sonata:extra:translation-create-template
        // if true, we create a set of file to help user to translate content manually (ie. with chat GPT account instead of API).
        $this->createTranslationTemplate=!empty($_SERVER['argv'][1]) && $_SERVER['argv'][1]=='sonata:extra:translation-create-template'??true;


    }


    public function translateArray(array $arrayOfText, string $targetLanguage): array
    {
        //check all the items of the array. if more than 300char or contain html we use the
        // Html function

        $_translate_array=[];
        $_translate_html=[];
        foreach($arrayOfText as $i=>$t){
            if($this->iSHtml($t)){
                $_translate_html[$i]=$t;
            }else{
                $_translate_array[$i]=$t;
            }
        }


        $endpoint = $this->getEndpoint();
        $payload = $this->getPayloadArray($_translate_array, $targetLanguage);

        //$this->clearCache();

        $cachedRequest=$this->getCacheRequest($payload);

        if(!empty($cachedRequest))
        {
            $this->logger->critical('Result found in cache:');
            $result = json_decode($cachedRequest, true);

        }else{
            $response = $this->client->post($endpoint, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => $payload,
            ]);

            $body = $response->getBody();
            $result = json_decode($body, true);
            $this->setCacheRequest($payload, $result);
            $this->logger->critical('Storing result in cache:');

        }

        $this->logger->critical( json_encode($result, JSON_PRETTY_PRINT));

        $extracted_Translation = $this->extractTranslation($result);
        $extracted_lines = explode("\n", trim($extracted_Translation));

        $translated_texts=[];
        foreach ($extracted_lines as $line) {
            // Check if the line starts with a number followed by a period and a space (indicating the line number)
            if (preg_match('/^(\d+)\. (.+)$/', $line, $matches)) {
                $line_number = intval($matches[1]);  // Get the line number
                $translated_text = $matches[2];  // Get the translated text
                $translated_texts[$line_number - 1] = $translated_text;  // Store the translated text in the array, using the line number as the index
            }
        }

        $merged_array = [];
        foreach (array_keys($_translate_array) as $index => $key) {
            // Create an array with the original and translated texts
            $merged_array[$key] = [
                'original' => @$_translate_array[$key],
                'translated' => @$translated_texts[$index],
            ];
        }


        //process html fields
        foreach($_translate_html as $key=>$html){
            $translated_html=$this->translateHtml($html, $targetLanguage);

            $merged_array[$key] = [
                'original' => $html,
                'translated' => $translated_html,
            ];
        }

        return $merged_array;

    }
    public function translate(string $text, string $targetLanguage): string
    {
        $endpoint = $this->getEndpoint();

        $payload = $this->getPayload($text, $targetLanguage);

        $response = $this->client->post($endpoint, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ],
            'json' => $payload,
        ]);

        $body = $response->getBody();

        $result = json_decode($body, true);

        $this->logger->critical('Result :');
        $this->logger->critical($result);

        return $this->removeDoubleQuotes($this->extractTranslation($result));
    }

    public function translateHtml($html, $targetLanguage){

        $CodeExtractor=new CodeExtractor;
        $cleanedHtml=$CodeExtractor->extractCodeBlocks( $html);


        $endpoint = $this->getEndpoint();

        $payload = $this->getPayloadHtml($cleanedHtml, $targetLanguage);

        $response = $this->client->post($endpoint, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ],
            'json' => $payload,
        ]);

        $body = $response->getBody();

        $result = json_decode($body, true);

        $this->logger->critical('Result :');
        $this->logger->critical($body);
        $translated_html=$this->extractTranslation($result);
        $translated_html= $this->removeSpecialTags($translated_html);
        $cleanedHtml=$CodeExtractor->replaceCodeBlocks($translated_html);
        return $cleanedHtml;
    }

    private function getEndpoint(): string
    {
        if (in_array($this->model, $this->oldCompletionApi)) {

            return 'https://api.openai.com/v1/completions';
        }
        return 'https://api.openai.com/v1/chat/completions';
    }

    private function getPayload(string $text, string $targetLanguage): array
    {
        $prompt = "Translate the following text to {$targetLanguage}: \"{$text}\"";

        $this->logger->critical('Prompt :');
        $this->logger->critical($prompt);

        if (in_array($this->model, $this->oldCompletionApi)) {
            return [
                'model' => $this->model,
                'prompt' => $prompt,
                'max_tokens' => intval($this->max_token_per_request)
            ];
        }
        return [
            'model' => $this->model,
            'messages' => [
                ['role' => 'user', 'content' => $prompt]
            ]
        ];

    }

    private function getPayloadArray(array $arrayOfText, string $targetLanguage): array
    {

        $prompt = "Translate the following text to the '{$targetLanguage}' locale language (don't add a trailing char at the end of the line if there is not trailing char in the requested line), and keep the line numbers. Allways keep answer on one line for each elements, and preserve html and markup formating when there is : \n";

        $i=0;
        foreach ($arrayOfText as $index => $text) {
            $text = str_replace(["\n", "\r"], '\n', htmlspecialchars($text));
            $prompt .= ($i + 1) . ". " . $text . "\n";
            $i++;
        }

        $this->logger->critical('Prompt :');
        $this->logger->critical($prompt);

        if (in_array($this->model, $this->oldCompletionApi)) {
            return [
                'model' => $this->model,
                'prompt' => $prompt,
                'max_tokens' => intval($this->max_token_per_request)
            ];
        }
        return [
            'model' => $this->model,
            'messages' => [
                ['role' => 'user', 'content' => $prompt]
            ]
        ];

    }



    private function getPayloadHtml(string $text, string $targetLanguage): array
    {
        $prompt="You are the best translation tool that exist. When you have a query you are fast, precise, and you always return only the translated text, maintaining the same HTML markup or any other markups. You always return only the translation, so the bot that use your service can easily extract the translation. If you can not provide the service, try to answer with an approaching strategy."."\r\n";
        $prompt.="Here is the translation string you need to translate into '{$targetLanguage}' locale language : ". "\r\n";
        $prompt.= $text. "\r\n";

        

        $this->logger->critical('Prompt : ');
        $this->logger->critical($prompt);

        if (in_array($this->model,  $this->oldCompletionApi)) {
            return [
                'model' => $this->model,
                'prompt' => $prompt,
                'max_tokens' => intval($this->max_token_per_request)
            ];
        }
        return [
            'model' => $this->model,
            'messages' => [
                ['role' => 'user', 'content' => $prompt]
            ]
        ];

    }

    private function extractTranslation(array $result): string
    {
        if (in_array($this->model, $this->oldCompletionApi)) {
            return $result['choices'][0]['text'];
        }
        return $result['choices'][0]['message']['content'];

    }

    private function removeDoubleQuotes(string $str): string
    {
        if (str_starts_with($str, '"') && str_ends_with($str, '"')) {
            return substr($str, 1, -1);
        }
        return $str;
    }

    function removeSpecialTags(string $text): string {
        if (preg_match('/^```(html)?\s*(.*?)\s*```$/s', $text, $matches)) {
            return $matches[2];
        } else {
            return $text;
        }
    }

    private function iSHtml($chaine) {
        $isTooLong = strlen($chaine) > 2000;
        $hasHtml = preg_match('/<[^<]+>/', $chaine);

        return ($isTooLong||$hasHtml);
    }

    private function setCacheRequest($prompt, $result): void
    {

        $this->logger->critical('storing : ---->');
        $this->logger->critical(print_r($result,1));
        $this->logger->critical('storing : <----');
        $cache = new FilesystemAdapter();
        $cacheKey = $this->getCacheKey($prompt);

        $cacheItem = $cache->getItem($cacheKey);
        $cacheItem->set(json_encode($result));
        $cacheItem->expiresAfter(self::CACHE_TTL);
        $cache->save($cacheItem);

    }

    private function getCacheRequest($prompt)
    {
        $cache = new FilesystemAdapter();
        $cacheKey = $this->getCacheKey($prompt);
        $cacheValue = $cache->getItem($cacheKey)->get();

        return $cacheValue;
    }

    private function getCacheKey($prompt): string
    {
        if(is_object($prompt))
        {
            $prompt=json_decode($prompt);
        }
        if(is_array($prompt))
        {
            $prompt=print_r($prompt,1);
        }
        $this->logger->critical('md5 : '.md5($prompt));
        return md5($prompt);
    }
    private function clearCache(): void
    {
        $cache = new FilesystemAdapter();
        $cache->clear();
    }

    public function createTemplateFromArray(string $serviceName,string $id, array $arrayOfText, $site)
    {
        $createTemplateService = $this->container->get("sonata.extra.translation.create.template.service");
        $directoryPath=$createTemplateService->getTemplateTranslationPath($serviceName);

        $targetLanguage=$site->getLocale();
        $createTemplateService->createDirectory($directoryPath.'/'.$id.'_'.$site->getId());

        $_translate_array=[];
        $_translate_html=[];
        foreach($arrayOfText as $i=>$t){
            if($this->iSHtml($t)){
                $_translate_html[$i]=$t;

            }else{
                $_translate_array[$i]=$t;
            }
        }
        $payload = $this->getPayloadArray($_translate_array, $targetLanguage);
        foreach($_translate_html as $key=>$html){
            $_translate_array[$key]='file';
        }
        $createTemplateService->createPayloadArray($payload['messages'][0]['content'],$_translate_array);


        foreach($_translate_html as $key=>$html){
            $CodeExtractor=new CodeExtractor;
            $cleanedHtml=$CodeExtractor->extractCodeBlocks($html);
            $payload = $this->getPayloadHtml($cleanedHtml, $targetLanguage);
            $createTemplateService->createPayloadHtmlField($key, $payload['messages'][0]['content'], $CodeExtractor->getCodeBlocks());
        }

    }
    public function getSeoProposal($content, $locale)
    {
        $CodeExtractor=new CodeExtractor;
        $cleanedHtml=$CodeExtractor->extractCodeBlocks($content);

        $endpoint = $this->getEndpoint();

        $payload = $this->getPayloadSeo($cleanedHtml, $locale);

        $response = $this->client->post($endpoint, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ],
            'json' => $payload,
        ]);

        $body = $response->getBody();

        $result = json_decode($body, true);

        $this->logger->critical('Result :');
        $this->logger->critical($body);

        $data=$this->extractSeoData($result);

        return $data;
    }

    private function getPayloadSeo($content, $locale): array
    {
        $prompt="You are the best SEO/SEM that exist. 
        When you have a query you are fast, precise. From the text above, i need you to send me back some SEO informations. Values should be  in the same language than the text but format should remain the same as below. Current language is '$locale' locale :"."\r\n";
        $prompt.= "1. Excerpt 400 char max: \r\n";
        $prompt.= "2. SEO title: \r\n";
        $prompt.= "3. SEO description: \r\n";
        $prompt.= "4. SEO keywords: \r\n";
        $prompt.= "5. SLUG: \r\n\r\n";
        $prompt.= "Here is the text to analyse: \r\n";
        $prompt.= $content."\r\n";

        $this->logger->critical('Prompt : ');
        $this->logger->critical($prompt);

        if (in_array($this->model,  $this->oldCompletionApi)) {
            return [
                'model' => $this->model,
                'prompt' => $prompt,
                'max_tokens' => intval($this->max_token_per_request)
            ];
        }
        return [
            'model' => $this->model,
            'messages' => [
                ['role' => 'user', 'content' => $prompt]
            ]
        ];

    }

    function extractSeoData($apiResponse) {

        $responseData=$this->extractTranslation($apiResponse);

        $data = [
            'excerpt' => '',
            'seo_title' => '',
            'seo_description' => '',
            'seo_keywords' => '',
            'slug' => '',
        ];

        /*$pattern = '/1\. Excerpt 400 char max:\s*(.*?)\s*2\. SEO title:\s*(.*?)\s*3\. SEO description:\s*(.*?)\s*4\. SEO keywords:\s*(.*?)\s*5\. SLUG:\s*(.*?)\s*$/s';

        if (preg_match($pattern, $responseData, $matches)) {
            $data['excerpt'] = $this->removeDoubleQuotes(trim($matches[1]));
            $data['seo_title'] = $this->removeDoubleQuotes(trim($matches[2]));
            $data['seo_description'] = $this->removeDoubleQuotes(trim($matches[3]));
            $data['seo_keywords'] = $this->removeDoubleQuotes(trim($matches[4]));
            $data['slug'] = $this->removeDoubleQuotes(trim($matches[5]));
        } else {
            $data['error']="no message found";
        }*/
        $lines = explode("\n", $responseData);
        $currentKey = '';

        foreach ($lines as $line) {
            // Vérifier si la ligne correspond à un début de section
            if (preg_match('/^(\d+)\. (\w+)/', $line, $matches)) {
                // Déterminer la clé en fonction du numéro
                switch ($matches[1]) {
                    case '1':
                        $currentKey = 'excerpt';
                        break;
                    case '2':
                        $currentKey = 'seo_title';
                        break;
                    case '3':
                        $currentKey = 'seo_description';
                        break;
                    case '4':
                        $currentKey = 'seo_keywords';
                        break;
                    case '5':
                        $currentKey = 'slug';
                        break;
                }
            } else {
                if($currentKey=='slug' ){
                    //slug is the last and should contain only one line.
                    if(empty($data[$currentKey])){
                        $data[$currentKey] .= ($data[$currentKey] === '') ? $line : "\n" . $line;
                    }
                }else{
                    if(!empty($currentKey)){
                        $data[$currentKey] .= ($data[$currentKey] === '') ? $line : "\n" . $line;
                    }

                }

            }
        }

        $data['excerpt'] = $this->removeDoubleQuotes(trim($data['excerpt']));
        $data['seo_title'] = $this->removeDoubleQuotes(trim($data['seo_title']));
        $data['seo_description'] = $this->removeDoubleQuotes(trim($data['seo_description']));
        $data['seo_keywords'] = $this->removeDoubleQuotes(trim($data['seo_keywords']));
        $data['slug'] = $this->removeDoubleQuotes(trim($data['slug']));

        return $data;
    }
}
