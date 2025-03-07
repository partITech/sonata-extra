# Assets Management

The **SonataExtra Bundle** offers a flexible and powerful way to manage **CSS** and **JavaScript** assets in your Sonata blocks. By only loading assets when they are needed, it optimizes page performance and streamlines resource management.

> [!TIP]
> You can include both external and inline assets, and even group them with custom indexes for advanced configuration.

---

## Overview

**Key advantages**:
- Load assets only for active blocks, **improving** page load times.
- Support for **external** (linked) and **inline** CSS/JS.
- Custom **indexes** for grouping assets.

---

## AssetsHandler Service

At the heart of this feature is the `AssetsHandler` service. It provides methods to add assets in a variety of ways:

- **`addCss()`** – Link an external CSS file.
- **`addJs()`** – Link an external JS file (with an optional `defer` parameter).
- **`addCssInline()`** – Embed inline CSS.
- **`addJsInline()`** – Embed inline JavaScript.

> [!NOTE]
> You can specify an index (e.g., `'default'`) to organize and retrieve these assets in different parts of your application.

---

## Integration Steps

### 1. Inject Dependencies

Add the `AssetsHandler` and `Environment` services to your block service class:

```php
use Partitech\SonataExtra\Service\AssetsHandler;
use Twig\Environment;
use Symfony\Contracts\Service\Attribute\Required;

private $assetsHandler;

#[Required]
public function autowireDependencies(Environment $twig, AssetsHandler $assetsHandler): void
{
    parent::__construct($twig);
        $this->assetsHandler = $assetsHandler;
    }
```

### 2. Add Assets in the `execute()` Method

Within your block’s `execute()` method, call the appropriate methods to register assets:

```php
public function execute(BlockContextInterface $blockContext, Response $response = null)
{
    // External CSS
    $this->assetsHandler->addCss(
        'https://unpkg.com/library@latest/dist/style.css',
        'default'
    );

    // External JS (with defer set to true)
    $this->assetsHandler->addJs(
        'https://unpkg.com/library@latest/dist/script.js',
        true,
        'default'
    );

    // Inline JS
    $this->assetsHandler->addJsInline(
        'console.log(window)',
        true,
        'default'
    );

    // Inline CSS
    $this->assetsHandler->addCssInline(
        '.class { color: red; }',
        'default'
    );

    // Remainder of the method ...
}
```

> [!TIP]
> The second parameter of `addJs()` is a boolean controlling `defer`. Set it to `true` to defer script loading.

---

## Rendering Assets in Twig

Insert the loaded assets in your Twig templates using these functions:

```twig
{{ sonata_extra_get_blocks_css('default')|raw }}
{{ sonata_extra_get_blocks_css_inline('default', true)|raw }}
{{ sonata_extra_get_blocks_js('default')|raw }}
{{ sonata_extra_get_blocks_js_inline('default', true)|raw }}
```

**Parameters**:
- **index** (e.g., `'default'`): Matches the index used in your block code.
- The second parameter in the inline functions (`true` in the example) **compresses** the inline code if desired.

> [!NOTE]
> Ensure you use the `|raw` filter for proper HTML rendering.

---

## Customization

You can define your **own indexes** in `addCss()` or `addJs()` calls, then render them by passing the same index to the Twig functions. This approach provides **granular control** over asset grouping and loading, especially useful for large or multi-faceted applications.

---

## Conclusion

By leveraging the **AssetsHandler service** in the SonataExtra Bundle, you can maintain a clean and **efficient** method for handling your project’s CSS and JavaScript assets. This selective approach ensures that each block only loads the resources it truly requires, leading to better **performance** and a more organized codebase.
