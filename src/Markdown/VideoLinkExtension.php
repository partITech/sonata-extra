<?php
namespace  Partitech\SonataExtra\Markdown;

use League\CommonMark\Environment\EnvironmentBuilderInterface;
use League\CommonMark\Extension\ExtensionInterface;
use Partitech\SonataExtra\Markdown\VideoLinkParser;
use Partitech\SonataExtra\Markdown\WebMVideoParser;

class VideoLinkExtension implements ExtensionInterface {
    public function register(EnvironmentBuilderInterface $environment): void {
        $environment->addInlineParser(new WebMVideoParser());
    }
}
