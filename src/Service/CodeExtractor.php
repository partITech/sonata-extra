<?php

namespace Partitech\SonataExtra\Service;

class CodeExtractor
{
    private array $codeBlocks = [];
    private int $placeholderCount = 0;

    public function extractCodeBlocks(string $html): string
    {
        $html = $this->extractFigureBlocks($html);
        $html = $this->extractMdCodeBlocks($html);
        $html = $this->extractImagePlaceholders($html);

        // Extraction des blocs de code
        $html = preg_replace_callback(
            '/<pre.*?>.*?<\/pre>/s',
            function ($matches) {
                $codeBlock = $matches[0];
                $placeholder = "{{code_placeholder_" . $this->placeholderCount . "}}";
                $this->codeBlocks[$placeholder] = $codeBlock;
                $this->placeholderCount++;
                return $placeholder;
            },
            $html
        );

        // Minification du reste du HTML
        return $this->minifyHtml($html);
    }

    public function getCodeBlocks(){
        return $this->codeBlocks;
    }

    public function setCodeBlocks($codeBlocks):void
    {
        $this->codeBlocks=$codeBlocks;
    }

    private function extractFigureBlocks(string $html): string
    {
        return preg_replace_callback(
            '/<figure.*?>.*?<\/figure>/s',
            function ($matches) {
                $figureBlock = $matches[0];
                $placeholder = "{{figure_placeholder_" . $this->placeholderCount . "}}";
                $this->codeBlocks[$placeholder] = $figureBlock;
                $this->placeholderCount++;
                return $placeholder;
            },
            $html
        );
    }

    public function extractMdCodeBlocks(string $mdText): string
    {
        return preg_replace_callback(
            '/```(.*?)```/s',
            function ($matches) {
                //dd($matches);
                $codeBlock = $matches[0];
                $placeholder = "{{md_code_placeholder_" . $this->placeholderCount . "}}";
                $this->codeBlocks[$placeholder] = $codeBlock;
                $this->placeholderCount++;
                return $placeholder;
            },
            $mdText
        );
    }

    public function extractImagePlaceholders(string $mdText): string
    {
        return preg_replace_callback(
            '/!\[(.*?)\]\((.*?)\)/',
            function ($matches) {
                $imageMarkdown = $matches[0];
                $placeholder = "{{image_placeholder_" . $this->placeholderCount . "}}";
                $this->codeBlocks[$placeholder] = $imageMarkdown;
                $this->placeholderCount++;
                return $placeholder;
            },
            $mdText
        );
    }
    public function replaceCodeBlocks(string $html): string
    {
        foreach ($this->codeBlocks as $placeholder => $codeBlock) {
            $html = str_replace($placeholder, $codeBlock, $html);
        }
        return $html;
    }

    private function minifyHtml(string $html): string
    {
        // Réduction des espaces blancs dans le HTML
        return preg_replace('/\s+/', ' ', $html);
    }
}
