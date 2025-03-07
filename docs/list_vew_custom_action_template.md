# Adding a Custom Action on the List View

This guide explains how to add a **custom button action** for each row in your Sonata Admin **list** view. By creating a dedicated route and controller action, you can easily perform custom operations on selected entities.

---

## 1. Define the Admin with a Custom Controller

Use the `#[AsAdmin]` attribute to specify the `controller` in your admin class:

```php
#[AsAdmin(
    manager_type: 'orm',
    label: 'My Personal Admin',
    model_class: \App\Entity\MyEntity::class,
    controller: \App\Controller\MyEntityAdminController::class,
)]
final class DatasetAdmin extends AbstractAdmin
{
// ...
}
```

---

## 2. Configure the Route

Within your admin class, add a route for the custom action:

```php
final class DatasetAdmin extends AbstractAdmin
{
    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->add('my-custom-route');
    }

    // ...
}
```

---

## 3. Add the Button to Each Row

In the **`configureListFields`** method, add a custom action with a dedicated template (`@PartitechSonataExtra/Admin/CRUD/default_filed_button_action.html.twig`):

```php
protected function configureListFields(ListMapper $list): void
{
    $list
        ->add('id')
        ->add('content')
        ->add(ListMapper::NAME_ACTIONS, null, [
            'actions' => [
            'show' => [],
            'edit' => [],
            'delete' => [],
            'vectorise' => [
                'template' => '@PartitechSonataExtra/Admin/CRUD/default_filed_button_action.html.twig',
                'label' => 'Content',
                'route' => 'my-custom-route',
                'icon_class' => 'fas fa-search',
                'ask_confirmation' => true,
                'confirmation_message' => 'Apply ?'
            ],
        ],
    ]);
}
```

> **Parameters**
> - **`template`**: Points to the Twig file for rendering the button.
> - **`route`**: Ties the button’s action to your defined route (in this case `my-custom-route`).
> - **`icon_class`**: Adds an icon (Font Awesome recommended).
> - **`ask_confirmation`**: If set to `true`, a confirmation dialog appears before executing the action.
> - **`confirmation_message`**: Message displayed in the confirmation dialog.

---

## 4. Implement the Controller Action

In the custom controller (referenced by `controller: \App\Controller\MyEntityAdminController::class`), add your method for processing the selected entity:

```php
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class MyEntityAdminController extends CRUDController
{
public function myCustomRouteAction(Request $request, MyCustomService $myCustomService): RedirectResponse
{
$entity = $this->admin->getSubject();

        if (!$entity) {
            throw $this->createNotFoundException(
                sprintf('Unable to find the entity object with id: %s', $request->get('id'))
            );
        }

        try {
            $myCustomService->myVeryImportantProcess($entity);
            $this->addFlash('sonata_flash_success', 'Process done successfully');
        } catch (\Throwable $e) {
            $this->addFlash('sonata_flash_error', 'Some Error: ' . $e->getMessage());
        }

        return new RedirectResponse(
            $this->admin->generateUrl('list', ['filter' => $this->admin->getFilterParameters()])
        );
    }
}
```

> [!NOTE]
> This example demonstrates a straightforward process: retrieving the entity, running some logic (`myVeryImportantProcess`), and then redirecting back to the list view. Adjust it to your specific needs.

---

## Conclusion

By adding a **route**, a **controller action**, and referencing a **custom button** within `configureListFields()`, you can create any number of **custom actions** for your Sonata Admin entities. This approach keeps your code organized and leverages Sonata’s extensible architecture for customized workflow.
