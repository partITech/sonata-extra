# Adding a custome action on listView 

To simply add a custom button action to a list vew row. 

Create a controller for your admin and register it to your AsAdmin attribute : 


```php
#[AsAdmin(
manager_type: 'orm',
label: 'My personnal admin',
model_class: \App\Entity\MyEntity::class,
controller: \App\Controller\MyEntityAdminController::class,
)]
final class DatasetAdmin extends AbstractAdmin
```

Create your route 
```php
final class DatasetAdmin extends AbstractAdmin{


    protected function configureRoutes(RouteCollectionInterface $collection)
    : void {
        $collection->add('my-custom-route');
    }

```

add your button to every rows 

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

No really needs to explain params. 

In your controller  

```php
class MyEntityAdminController extends CRUDController
{
    public function myCustomRouteAction(Request $request, MyCustomService $myCustomService): RedirectResponse
    {
        $entity = $this->admin->getSubject();

        if (!$entity) {
            throw $this->createNotFoundException(sprintf('Unable to find the entity object with id: %s', $request->get('id')));
        }

        try{
            $myCustomService->myVeryImportantProcess($entity);
            $this->addFlash('sonata_flash_success', 'Process done successfully');
        }catch(\Throwable $e){
            $this->addFlash('sonata_flash_error', 'Some Error: ' . $e->getMessage());
        }

        return new RedirectResponse($this->admin->generateUrl('list', ['filter' => $this->admin->getFilterParameters()]));
    }

}
```
