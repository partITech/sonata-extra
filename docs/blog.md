# Sonata Extra Bundle:  Blog Feature Documentation

Enhance your Sonata project with the integrated blog feature from Sonata Extra Bundle.

## Core Routes Integration
### Updating Blog Core Routes

Integrate your site's pages with the blog controller by executing the `update-core-route` command:

```shell
php bin/console sonata:page:update-core-routes --site={id}
```

Executing this command synchronizes the Sonata Page with the following hybrid routes:

- Blog Category  : `/blog-category/{category}`
- Blog Tag : `/blog-tag/{tag}`
- Blog Article : `/blog-article/{article}`

These routes correspond to dynamically generated pages that are linked to their respective controllers.

### Route Customization
Customize default route patterns to localize or modify your URL structure while preserving controller linkage by editing the page details:

- An event listener on routing.loader is automatically added.
- A new route is created with your custom pattern from the custom_url field.

![Blog_CustomUrl.png](./images/Blog_CustomUrl.png)

![Blog_ServiceType.png](./doc-sonata-extra-images/Blog_ServiceType.png)
![Blog_CustomUrl.png](./doc-sonata-extra-images/Blog_CustomUrl.png)


Refer to the following template for generating URL paths within Twig:
```php
{{  path('sonata_extra_blog_category', {'category': category.getSlug()}) }}
{{  path('sonata_extra_blog_tag', {'tag': tag.getSlug()}) }}
{{  path('sonata_extra_blog_article', {'article': article.getSlug()}) }}
```


## Templating

Link your blog's content output to your template configuration by utilizing the `Blog page type`. This page type service is explained in detail within the [SonataPageBundle documentation](https://docs.sonata-project.org/projects/SonataPageBundle/en/5.x/reference/page_services/).

## Available Template Variables

The `Blog page` service provides the following variables to your template for a seamless integration:

![Blog_ServiceType.png](images%2FBlog_ServiceType.png)

- `content` : The HTML content of your blog.
- `page` : The `SonataPagePage` entity instance.
- `site` : The `SonataPageSite` entity instance.

Display your blog content within your Twig template as follows:

```php
{{ content|raw }}
```

For page article, category and tags, the service will also make availlable seo parameters :
- `ogTitle`
- `ogDescription`
- `ogImage` : The image path will be allready calculated. Be sure to have an image format named `og_image`  otherwise `reference` will be used. As social networks have a max width/height, it is not recommended to use the `reference` format.
- `seoTitle`
- `seoKeyword`
- `seoDescription`

In your template you can use it like this

```php
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
and in your base template
```php
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

Category and Tag entity should have the referal fields

- App\Entyty\SonataClassificationCategory.php :

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
- App\Entyty\SonataClassificationTag :
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