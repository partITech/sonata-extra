<?php

namespace Service\ImportWordpress;

use Partitech\SonataExtra\Service\ImportWordpress\Tools;
use PHPUnit\Framework\TestCase;

class ToolsTest extends TestCase
{
    const FAKE_CONTENT_PATH = '/fakeContents/';
    
    public function replaceImageTest(string $contentPath): void
    {
        $content = $this->getContent($contentPath);
        $linkIndex = json_decode($this->getContent('imageMapping.json'), true);
       
        $result = Tools::replaceImage($content, $linkIndex);
        $pattern = '/<a [^>]*href="[^"]*<[^"]*"[^>]*>/';
        $this->assertDoesNotMatchRegularExpression($pattern, $result);
    }
    
    
    public function testReplaceImage(): void
    {
        $this->replaceImageTest('article_replace_img.html');
        $this->replaceImageTest('article_full.html');
    }
    
    // @todo: to remove
    public function testReplaceImageOnPartitech(): void
    {
        $directory = __DIR__ . self::FAKE_CONTENT_PATH . 'articles';
        $files = scandir($directory);
        foreach ($files as $file) {
            if ($file !== "." && $file !== "..") {
                $this->replaceImageTest('articles/'.$file);
            }
        }
    }
    
    private function getContent(string $file): string
    {
       $path = __DIR__ . self::FAKE_CONTENT_PATH . $file;
       
       return file_get_contents($path);
       
    }
}
