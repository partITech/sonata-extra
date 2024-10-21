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

        return $this->minifyHtml($html);
    }

    public function getCodeBlocks(): array
    {
        return $this->codeBlocks;
    }

    public function setCodeBlocks($codeBlocks): self
    {
        $this->codeBlocks = $codeBlocks;

        return $this;
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
        if ($this->isHtml($html)) {
            return preg_replace('/\s+/', ' ', $html);
        }

        return $html;

    }

    private function isHtml(string $string): bool {

        $htmlTags = [
            '<div', '<span', '<a', '<body', '<script', '<style',
            '<header', '<footer', '<section', '<article', '<nav',
            '<table', '<tr', '<td', '<th', '<thead', '<tbody', '<tfoot',
            '<ul', '<ol', '<li', '<p', '<h1', '<h2', '<h3', '<h4', '<h5', '<h6',
            '<br', '<hr', '<img', '<iframe', '<form', '<input', '<textarea', '<button',
            '<link', '<meta'
        ];

        foreach ($htmlTags as $tag) {
            if (stripos($string, $tag) !== false) {
                return true;
            }
        }

        return false;
    }

    public function splitHtml(string $text, int $maxLength): array
    {
        if($this->isHtml($text)){
            $dom = new \DOMDocument;
            @$dom->loadHTML(mb_convert_encoding($text, 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

            $fragments = [];
            $currentLength = 0;
            $currentFragment = '';

            foreach ($dom->documentElement->childNodes as $node) {
                $nodeHtml = $dom->saveHTML($node);
                if ($currentLength + strlen($nodeHtml) > $maxLength && $currentFragment !== '') {
                    $fragments[] = $currentFragment;
                    $currentFragment = '';
                    $currentLength = 0;
                }

                $currentFragment .= $nodeHtml;
                $currentLength += strlen($nodeHtml);
            }

            if ($currentFragment !== '') {
                $fragments[] = $currentFragment;
            }


        }else{

            $paragraphs = explode("\r\n", $text);
            $fragments = [];
            $currentFragment = '';

            foreach ($paragraphs as $paragraph) {

                if (strlen($currentFragment) + strlen($paragraph) > $maxLength) {
                    if ($currentFragment !== '') {
                        $fragments[] = $currentFragment;
                        $currentFragment = '';
                    }
                    $currentFragment = $paragraph;
                } else {
                    $currentFragment .= ($currentFragment === '' ? '' : "\r\n") . $paragraph;
                }
            }

            if ($currentFragment !== '') {
                $fragments[] = $currentFragment;
            }
        }

        return $fragments;
    }
}
