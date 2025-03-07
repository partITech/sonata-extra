# Blog Feature Documentation

Enhance your Sonata project with the integrated **blog feature** from the Sonata Extra Bundle. This component provides a straightforward way to manage articles, categories, and tags, complete with SEO-friendly URLs and customizable templates.

> [!TIP]
> Use these instructions along with the SonataPageBundle documentation for a fully featured blog experience in your application.

---

## Core Routes Integration

### Updating Blog Core Routes

Synchronize Sonata Page with blog-specific controllers by running the following command:

```shell
php bin/console sonata:page:update-core-routes --site={id}
```

This operation sets up the following hybrid routes:

- **Blog Category**: `/blog-category/{category}`
- **Blog Tag**: `/blog-tag/{tag}`
- **Blog Article**: `/blog-article/{article}`

These routes are dynamically generated and linked to their respective controllers within the Sonata Extra Bundle.

---

### Route Customization

You can rename or localize the default route patterns while preserving controller linkage by editing the page details in the Sonata admin:

- An event listener on `routing.loader` automatically updates these routes.
- A custom URL field (`custom_url`) lets you define alternate paths.

![Blog_CustomUrl.png](./doc-sonata-extra-images/Blog_CustomUrl.png)  
![Blog_ServiceType.png](./doc-sonata-extra-images/Blog_ServiceType.png)

Use the following template references in your Twig code to generate links:

```twig
{{ path('sonata_extra_blog_category', {'category': category.getSlug()}) }}
{{ path('sonata_extra_blog_tag', {'tag': tag.getSlug()}) }}
{{ path('sonata_extra_blog_article', {'article': article.getSlug()}) }}
```

---

## Templating

Connect your blog output to a dedicated template using the **Blog page type** service. Refer to the SonataPageBundle documentation for in-depth guidance on creating and configuring page services.

---

## Available Template Variables

When using the `Blog page` service, the following variables are made available to your Twig template:

- `content`: The HTML content of your blog.
- `page`: The `SonataPagePage` entity.
- `site`: The `SonataPageSite` entity.

Display the main content in Twig with:

```twig
{{ content|raw }}
```

---

### SEO Parameters

For articles, categories, and tags, the service also injects SEO-related variables:

- `ogTitle`
- `ogDescription`
- `ogImage`  
  Points to the path of the chosen image format. If `og_image` format does not exist, `reference` is used.
- `seoTitle`
- `seoKeyword`
- `seoDescription`

A sample usage in your Twig template:

```twig
{% block sonata_seo_title %}
    {% apply spaceless %}
        <title>{{ seoTitle|default('Titre par défaut') }}</title>
    {% endapply %}
{% endblock %}

{% block sonata_seo_metadatas %}
    {% apply spaceless %}
        {% if seoDescription is defined %}
            <meta name="description" content="{{ seoDescription }}">
        {% endif %}
        {% if seoKeyword is defined %}
            <meta name="keywords" content="{{ seoKeyword }}">
        {% endif %}
    {% endapply %}
{% endblock %}

{% block app_og_metadatas %}
    {% apply spaceless %}
        {% if ogTitle is defined %}
            <meta property="og:title" content="{{ ogTitle }}">
        {% endif %}
        {% if ogDescription is defined %}
            <meta property="og:description" content="{{ ogDescription }}">
        {% endif %}
        {% if ogImage is  defined %}
            <meta property="og:image" content="{{ absolute_url(ogImage) }}">
        {% endif %}
        <meta property="og:type" content="article">
    {% endapply %}
{% endblock %}
```

Then, in your base layout:

```twig
{% block sonata_seo_title %}
    {{ sonata_seo_title() }}
{% endblock %}
{% block sonata_seo_metadatas %}
    {{ sonata_seo_metadatas() }}
{% endblock %}
{% block app_og_metadatas %}
    {{ app_og_metadatas() }}
{% endblock %}
```

---

## Category and Tag Entities

### Sonata Classification Category

Below is an example of how to define a category entity with SEO fields:

```php
<?php
namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Sonata\ClassificationBundle\Entity\BaseCategory;
use Partitech\SonataExtra\Traits\EntityTranslationTrait;
use Partitech\SonataExtra\Contract\MediaInterface;

#[ORM\Entity]
#[ORM\Table(name: 'classification__category')]
class SonataClassificationCategory extends BaseCategory
{
    use EntityTranslationTrait;
    #[ORM\Id]
    #[ORM\Column(type: Types::INTEGER)]
    #[ORM\GeneratedValue]
    protected ?int $id = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    #[ORM\ManyToOne(targetEntity: MediaInterface::class, cascade: ["persist"])]
    #[ORM\JoinColumn(name: "featured_image__media_id", referencedColumnName: "id", nullable: true, onDelete: "SET NULL")]
    private $featured_image = null;


    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $seo_title = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $seo_keywords = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $seo_description = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $seo_og_title = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $seo_og_description = null;

    #[ORM\ManyToOne(targetEntity: MediaInterface::class, cascade: ["persist"])]
    #[ORM\JoinColumn(name: "seo_og_image__media_id", referencedColumnName: "id", nullable: true, onDelete: "SET NULL")]
    private $seo_og_image = null;

    public function getFeaturedImage()
    {
        return $this->featured_image;
    }

    public function setFeaturedImage($featured_image): self
    {
        $this->featured_image = $featured_image;

        return $this;
    }

    public function getSeoTitle(): ?string {
        return $this->seo_title;
    }

    public function setSeoTitle(?string $seo_title): self {
        $this->seo_title = $seo_title;
        return $this;
    }

    public function getSeoKeywords(): ?string {
        return $this->seo_keywords;
    }

    public function setSeoKeywords(?string $seo_keywords): self {
        $this->seo_keywords = $seo_keywords;
        return $this;
    }

    public function getSeoDescription(): ?string {
        return $this->seo_description;
    }

    public function setSeoDescription(?string $seo_description): self {
        $this->seo_description = $seo_description;
        return $this;
    }

    public function getSeoOgTitle(): ?string {
        return $this->seo_og_title;
    }

    public function setSeoOgTitle(?string $seo_og_title): self {
        $this->seo_og_title = $seo_og_title;
        return $this;
    }

    public function getSeoOgDescription(): ?string {
        return $this->seo_og_description;
    }

    public function setSeoOgDescription(?string $seo_og_description): self {
        $this->seo_og_description = $seo_og_description;
        return $this;
    }

    public function getSeoOgImage() {
        return $this->seo_og_image;
    }

    public function setSeoOgImage($seo_og_image): self {
        $this->seo_og_image = $seo_og_image;
        return $this;
    }
}
```

---

### Sonata Classification Tag

Similarly, for tags:

```php
<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;
use App\Repository\SonataClassificationTagRepository;
use Sonata\ClassificationBundle\Entity\BaseTag;
use Partitech\SonataExtra\Traits\EntityTranslationTrait;
use Partitech\SonataExtra\Contract\MediaInterface;

#[ORM\Entity(repositoryClass: SonataClassificationTagRepository::class)]
#[ORM\Table(name: 'classification__tag')]
#[ORM\HasLifecycleCallbacks]

class SonataClassificationTag extends BaseTag
{
    use EntityTranslationTrait;

    #[ORM\Id]
    #[ORM\Column(type: Types::INTEGER)]
    #[ORM\GeneratedValue]
    protected ?int $id = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    #[ORM\ManyToOne(targetEntity: MediaInterface::class, cascade: ["persist"])]
    #[ORM\JoinColumn(name: "featured_image__media_id", referencedColumnName: "id", nullable: true, onDelete: "SET NULL")]
    private $featured_image = null;


    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $seo_title = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $seo_keywords = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $seo_description = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $seo_og_title = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $seo_og_description = null;

    #[ORM\ManyToOne(targetEntity: MediaInterface::class, cascade: ["persist"])]
    #[ORM\JoinColumn(name: "seo_og_image__media_id", referencedColumnName: "id", nullable: true, onDelete: "SET NULL")]
    private $seo_og_image = null;

    public function getFeaturedImage()
    {
        return $this->featured_image;
    }

    public function setFeaturedImage($featured_image): self
    {
        $this->featured_image = $featured_image;

        return $this;
    }

    public function getSeoTitle(): ?string {
        return $this->seo_title;
    }

    public function setSeoTitle(?string $seo_title): self {
        $this->seo_title = $seo_title;
        return $this;
    }

    public function getSeoKeywords(): ?string {
        return $this->seo_keywords;
    }

    public function setSeoKeywords(?string $seo_keywords): self {
        $this->seo_keywords = $seo_keywords;
        return $this;
    }

    public function getSeoDescription(): ?string {
        return $this->seo_description;
    }

    public function setSeoDescription(?string $seo_description): self {
        $this->seo_description = $seo_description;
        return $this;
    }

    public function getSeoOgTitle(): ?string {
        return $this->seo_og_title;
    }

    public function setSeoOgTitle(?string $seo_og_title): self {
        $this->seo_og_title = $seo_og_title;
        return $this;
    }

    public function getSeoOgDescription(): ?string {
        return $this->seo_og_description;
    }

    public function setSeoOgDescription(?string $seo_og_description): self {
        $this->seo_og_description = $seo_og_description;
        return $this;
    }

    public function getSeoOgImage() {
        return $this->seo_og_image;
    }

    public function setSeoOgImage($seo_og_image): self {
        $this->seo_og_image = $seo_og_image;
        return $this;
    }
}
```

---

> [!IMPORTANT]
> Ensure each entity’s SEO fields are updated to reflect the content you want to display in meta tags or OG properties. This allows for rich previews on social platforms and optimized search engine visibility.
