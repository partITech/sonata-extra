<?php
    
    namespace Partitech\SonataExtra\Service\ImportWordpress;
    
    use DOMDocument;
    use DOMXPath;
    
    class Tools
    {
        public static function replaceImage(string $htmlContent, array $urlIndex)
        : string {
            $dom = new DOMDocument();
            @$dom->loadHTML(mb_convert_encoding($htmlContent, 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
            
            
            $images = $dom->getElementsByTagName('img');
            foreach ($images as $image) {
                // Vérifier si l'image a un attribut srcset
                if ($image->hasAttribute('srcset')) {
                    $currentSrc = $image->getAttribute('src');
                    if(isset($urlIndex[$currentSrc])){
                        $replacementFragment = $dom->createDocumentFragment();
                        @$replacementFragment->appendXML($urlIndex[$currentSrc]['twig']);
                        // Remplacer l'image par la chaîne de remplacement
                        $image->parentNode->replaceChild($replacementFragment->cloneNode(TRUE), $image);
                    }
                    
                    
                }
            }
            $htmlContent = $dom->saveHTML();
            
            // let's replace simple src's
            foreach ($urlIndex as $oldUrl => $newUrl) {
                if(str_contains($htmlContent, $oldUrl)){
                    $htmlContent = str_replace($oldUrl, $newUrl['url'], $htmlContent);
                }
            }
            
            return $htmlContent;
        }
    }