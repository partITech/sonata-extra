<?php
namespace  Partitech\SonataExtra\Markdown;

use League\CommonMark\Parser\Inline\InlineParserInterface;
use League\CommonMark\Parser\Inline\InlineParserMatch;
use League\CommonMark\Extension\CommonMark\Node\Inline\HtmlInline;
use League\CommonMark\Parser\InlineParserContext;

class WebMVideoParser implements InlineParserInterface {
    public function getMatchDefinition(): InlineParserMatch {
        return InlineParserMatch::regex('\\/uploads\\/media\\/.*\\.webm');

    }

    public function parse(InlineParserContext $inlineContext): bool {
        $cursor = $inlineContext->getCursor();
        $previousState = $cursor->saveState();

        $url = $cursor->match('/\/uploads\/media\/.*\.webm/');

        if (empty($url)) {
            $cursor->restoreState($previousState);
            return false;
        }
        $videoHtml = sprintf('
        <figure class="video_container" >
              <video controls="true" allowfullscreen="true">
                <source src="%s" type="video/webm">
              </video>
        </figure>
        ', htmlspecialchars($url, ENT_QUOTES, 'UTF-8'));
        $inlineContext->getContainer()->appendChild(new HtmlInline($videoHtml));


        return true;
    }
}
