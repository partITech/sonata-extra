## SonataExtra AsAdmin() attribute

Thanks to php attributes; exit the old 15kms long services.yaml config file with a quick per class configuration.

old type was :

```yaml
app.admin.article:
       class: App\Admin\MyEntityAdmin
       arguments: [~, App\Entity\MyEntity, ~]
       tags:
           - { name: sonata.admin, manager_type: orm, label: "My Entity Admin" }
       calls:
           - [ setTranslationDomain, [partitech]]
```

Thanks to autowiring "Arguments" is no more needed. 



here is a basic AsAdmin() configuration : 

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
    
    .....
}
```



If you need some more services in your Admin class, user the #[required] method annotation to mirror the construct autowiring, or set calls in the AsAdmin() attribute.

**Be aware that your Admins Classes have to be in the Services registration in your yaml file s either**

- globally like  :

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

Your src/Admin/ directory must not be exclude.

- one line Service declaration : 

  ```yaml
  App\SecondAdminDirectory\MyGreatAdmin: ~
  ```

  

If you use fancy automatic declaration based on Instance like this, this will not work: 

```yaml
services:
  _defaults:
    autowire: true
    autoconfigure: true
  _instanceof:
    Sonata\AdminBundle\Admin\AbstractAdmin:
      tags: { name: sonata.admin }
```

