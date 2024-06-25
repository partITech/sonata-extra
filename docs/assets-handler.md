# SonataExtra Bundle - Assets Management Documentation

# Overview

The SonataExtra bundle introduces a flexible and powerful way to manage CSS and JavaScript assets in Sonata blocks. This feature allows developers to include external CSS and JS files, as well as inline styles and scripts, with ease and efficiency.

## Integration

###  Loading Efficiency: 
The assets (CSS/JS) are loaded into the page only if the corresponding block is being used. This ensures efficient loading and avoids unnecessary overhead, particularly beneficial for performance optimization.

### AssetsHandler Service

The AssetsHandler service is at the core of this feature. It is responsible for handling the addition of CSS and JS assets to your Sonata blocks. This service supports adding both linked and inline assets and allows specifying custom indexes for asset grouping.

### Including Assets in Blocks

To use the AssetsHandler service in your blocks, follow these steps:

1. Inject Dependencies:

Inject the `AssetsHandler` and `Environment` services into your block service using the `autowireDependencies` method.

```php
use Partitech\SonataExtra\Service\AssetsHandler;
use Twig\Environment;
use Symfony\Contracts\Service\Attribute\Required;

private $assetsHandler;

#[Required]
public function autowireDependencies(Environment $twig, AssetsHandler $assetsHandler): void {
    parent::__construct($twig);
    $this->assetsHandler = $assetsHandler;
}
```

2. Add Assets in the execute Method:

Use the `addCss`, `addJs`, `addJsInline`, and `addCssInline` methods to add assets to your block.

```php
public function execute(BlockContextInterface $blockContext, Response $response = null)
{
    // Adding external CSS
    $this->assetsHandler->addCss('https://unpkg.com/library@latest/dist/style.css', 'default');

    // Adding external JS with @bool defer parameter (default false)
    $this->assetsHandler->addJs('https://unpkg.com/library@latest/dist/script.js', true, 'default');

    // Adding inline JS 
    $this->assetsHandler->addJsInline('console.log(window)', true, 'default');

    // Adding inline CSS
    $this->assetsHandler->addCssInline('.class{ }', 'default');

    // ... rest of the execute method ...
}
```

### Rendering Assets in Templates

To render the assets in your Twig templates, use the following Twig functions provided by the SonataExtra bundle:

```php
{{ sonata_extra_get_blocks_css('default')|raw }}
{{ sonata_extra_get_blocks_css_inline('default', true)|raw }}
{{ sonata_extra_get_blocks_js('default')|raw }}
{{ sonata_extra_get_blocks_js_inline('default', true)|raw }}
```

- `'default'` index is used by default if the value is not set, but custom indexes can be specified when developing custom blocks.
- The `|raw` filter is used to ensure the proper rendering of HTML tags.
- You can use compression parameter (bool default false) to render compressed `sonata_extra_get_blocks_css_inline` and `sonata_extra_get_blocks_js_inline`
  

### Customization

Developers can create custom indexes for grouping assets when developing their blocks. This provides flexibility in managing assets across different parts of the application.