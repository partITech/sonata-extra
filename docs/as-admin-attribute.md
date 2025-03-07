# AsAdmin()

The **SonataExtra `AsAdmin()` attribute** allows you to configure your Admin classes quickly, eliminating the need for lengthy YAML configuration. It leverages PHP attributes and autowiring to streamline admin setup.


This approach reduces boilerplate by embedding configuration details directly into your Admin class.

---

## Old-Style Configuration (YAML)

An example of the traditional service configuration:

```yaml
app.admin.article:
    class: App\Admin\MyEntityAdmin
    arguments: [~, App\Entity\MyEntity, ~]
    tags:
        - { name: sonata.admin, manager_type: orm, label: "My Entity Admin" }
    calls:
        - [ setTranslationDomain, [partitech]]
```

> [!NOTE]
> With **autowiring**, the `arguments` section is no longer required, simplifying the setup.

---

## New-Style Configuration (Attribute)

Below is a basic usage of the `AsAdmin()` attribute:

```php
use Partitech\SonataExtra\Attribute\AsAdmin;
use Symfony\Contracts\Service\Attribute\Required;

#[AsAdmin(
    manager_type: 'orm',
    group: 'Admin',
    label: 'My Entity Admin',
    model_class: \Partitech\SonataExtra\Entity\MyEntity::class,
    controller: \Partitech\SonataExtra\Controller\Admin\MyEntityController::class,
    calls: [
        ['setTranslationDomain', ['partitech']],
        ['setMyAttributeCallService'],
    ]
)]
class MyEntityAdmin extends AbstractAdmin
{
    private MyRequiredService $myRequiredService;
    private MyAttributeCallService $myAttributeCallService;

    #[Required]
    public function setMyRequeredService(MyRequiredService $myRequiredService): void
    {
        $this->myRequiredService = $myRequiredService;
    }

    public function setMyAttributeCallService(MyAttributeCallService $myAttributeCallService): void
    {
        $this->myAttributeCallService = $myAttributeCallService;
    }
    
    // ...
}
```

> [!IMPORTANT]
> Use `#[Required]` for autowired dependencies, or leverage the `calls` array in the `AsAdmin` attribute if further initialization is needed.

---

## Registering Admin Classes as Services

Ensure your Admin classes are registered in the service container. For instance:

```yaml
services:
    _defaults:
        autowire: true
        autoconfigure: true

    App\:
        resource: '../src/'
        exclude:
            - '../src/DependencyInjection/'
            - '../src/Entity/'
            - '../src/Kernel.php'
```

Your `src/Admin/` directory should **not** be excluded.

Alternatively, declare each Admin service individually:

```yaml
services:
    App\SecondAdminDirectory\MyGreatAdmin: ~
```

> [!NOTE]
> If you rely on `_instanceof` auto-registration like below, it **will not** work with `AsAdmin()` since it bypasses the extra attribute logic:

```yaml
services:
    _defaults:
        autowire: true
        autoconfigure: true
    _instanceof:
        Sonata\AdminBundle\Admin\AbstractAdmin:
            tags: { name: sonata.admin }
```

---

## Conclusion

The `AsAdmin()` attribute simplifies Admin configuration by embedding key settings directly in your class. With a bit of autowiring and the right service registration, you can drastically reduce your Sonata admin YAML definitions and keep your code more readable and maintainable.
```