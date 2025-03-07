# For User Admins

The **Sonata Extra Bundle** provides an easy way to manage multilingual content in your admin interfaces. It offers traits for both admin classes and entities to handle language-specific fields, creating a seamless workflow for translations within the Sonata environment.

> [!NOTE]
> All locales come from the `sonata_page` configuration, allowing records to be linked and managed across different sites and languages.

---
##  List view of the translation in the selected language site  
![Multilanguage_edit.png](./doc-sonata-extra-images/Multilanguage_edit.png)

##  Edit view of the translation in the selected language site
![Multilanguage_list.png](./doc-sonata-extra-images/Multilanguage_list.png)

## Create a translation from a local pattern
![Multilanguage_create_translation.png](./doc-sonata-extra-images/Multilanguage_create_translation.png)

---

## Implementation Example for a Simple Admin

### Entity File

Use the `EntityTranslationTrait` to add multilingual fields:

```php
namespace App\Entity;

use App\Repository\SimpleTestRepository;
use Doctrine\ORM\Mapping as ORM;
use Partitech\SonataExtra\Traits\EntityTranslationTrait;

#[ORM\Entity(repositoryClass: SimpleTestRepository::class)]
class SimpleTest
{
    use EntityTranslationTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // ...
}
```

---

### Admin File

Include `AdminTranslationTrait` in your Admin class. The `#[AsAdmin]` attribute simplifies Sonata Admin configuration:

```php
declare(strict_types=1);

namespace App\Admin;

use Partitech\SonataExtra\Attribute\AsAdmin;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Partitech\SonataExtra\Controller\Admin\TranslationController;
use Partitech\SonataExtra\Traits\AdminTranslationTrait;

#[AsAdmin(
    manager_type: 'orm',
    label: 'Simple Entity',
    model_class: \App\Entity\SimpleTest::class,
)]
final class SimpleTestAdmin extends AbstractAdmin
{
    use AdminTranslationTrait;

    // ...
}
```

> [!NOTE]
> After adding these traits, run the following commands to update the schema and clear the cache:

```shell
bin/console doctrine:schema:update --force
bin/console cache:clear
```

---

### Using the Controller Trait

If you already have a custom controller, add the `ControllerTranslationTrait`:

```php
class MenuController extends Controller
{
    use \Partitech\SonataExtra\Traits\ControllerTranslationTrait;
}
```

You can also call the **create translation** action via the extended TranslationController:

```php
use \Partitech\SonataExtra\Controller\Admin\TranslationController;

class MenuController extends Controller
{
    use \Partitech\SonataExtra\Traits\ControllerTranslationTrait;

    private $TranslationController;

    #[Required]
    public function autowireDependencies(
        TranslationController $TranslationController
    ): void {
        $this->TranslationController = $TranslationController;
    }

    public function createTranslationAction($id, $from_site, $to_site, $fqcn): Response
    {
        return $this->TranslationController->createTranslationAction($id, $from_site, $to_site, $fqcn);
    }
}
```

---

## Admin Configuration

The trait includes a `configureTrait()` method. If you have a custom `configure()` method in your admin, remember to call `configureTrait()`:

```php
#[AsAdmin(
    manager_type: 'orm',
    group: 'Admin',
    label: 'Test',
    model_class: \App\Entity\Test::class
)]
final class TestAdmin extends AbstractAdmin
{
    use AdminTranslationTrait;

    protected function configure(): void
    {
        $this->configureTrait();
        $this->setTemplate('edit', '@PartitechSonataMenu/CRUD/edit.html.twig');
    }
}
```

---

## Admin Route Configuration

If you override `configureRoutes`, ensure you call `configureTraitRoutes` first:

```php
#[AsAdmin(
    manager_type: 'orm',
    group: 'Admin',
    label: 'Test',
    model_class: \App\Entity\Test::class
)]
final class TestAdmin extends AbstractAdmin
{
    use AdminTranslationTrait;

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $this->configureTraitRoutes($collection);
        $collection->remove('show');
        // ...
    }
}
```

> [!IMPORTANT]
> This step is only necessary if you override the `configureRoutes` method in your admin class.

---

## Automatic Translation Compatibility

The multilingual feature is compatible with the **automatic translation** tool from Sonata Extra.
Add the `#[Translatable]` attribute to fields you wish to translate automatically:

```php
use Partitech\SonataExtra\Attribute\Translatable;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Translatable]
    private ?string $description = null;
```
> [!NOTE]
> For complex relationships, ensure the entity and any child entities also include the necessary traits and the `#[Translatable]` attribute where needed.

### Handling Slug Fields

During cloning, if the slug remains unchanged, it will automatically prefix the slug with the locale to maintain uniqueness:

```php
if (method_exists($clonedObject, 'setSlug') && $clonedObject->getSlug() == $object->getSlug()) {
    $slugger = new AsciiSlugger();
    $slug = $slugger->slug($this->site->getLocale().'-'.$clonedObject->getSlug())->lower();
    $clonedObject->setSlug($slug);
}
```

---

## Sonata Classification Multilang Integration

For **Sonata Classification** (Tag and Category entities), use the bundle’s forked admin and add the `EntityTranslationTrait` to your custom entities:

```php
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
}
```

Make sure to reference the **Partitech Sonata Extra** admin classes instead of the default Sonata Classification admins:

```yaml
sonata_admin:
# ...
    cms:
    icon: fa fa-pencil
    label: CMS
    keep_open: true
    items:
        - Partitech\SonataExtra\Admin\ArticleAdmin
        - sonata.page.admin.page
        - sonata.page.admin.shared_block
        - Partitech\SonataExtra\Admin\SliderAdmin
        - Partitech\SonataExtra\Admin\FaqCategoryAdmin
        - Partitech\SonataExtra\Admin\TagAdmin
        - Partitech\SonataExtra\Admin\CategoryAdmin
```

> [!CAUTION]
> Using the default Sonata Classification Admin for tags and categories may break multilingual features. Always use the admin classes provided by Sonata Extra.

---

## Conclusion

By adding these traits and configurations, you enable powerful **multilanguage support** for user admins in Sonata. The **Sonata Extra Bundle** streamlines translations, content cloning, and slug management, ensuring a smooth experience when managing records across multiple locales.
