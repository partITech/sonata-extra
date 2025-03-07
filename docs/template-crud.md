# CRUD Customization Features

**The Sonata Extra Bundle** offers flexible tools to customize your Sonata CRUD interface. From toggling block titles to embedding custom buttons and search areas, these features enhance both the **UI** and **UX** of the Sonata Admin.

---

## Configuration of Show and Edit Views

### Overriding Default Templates

To utilize the customization features, set your Admin class to use Sonata Extra Bundle templates:

```php
class MyAdmin extends AbstractAdmin
{
  public function configure(): void
  {
    $this->setTemplates([
      'edit' => '@PartitechSonataExtra/Admin/CRUD/edit.html.twig',
      'list' => '@PartitechSonataExtra/Admin/CRUD/list.html.twig',
      'show' => '@PartitechSonataExtra/Admin/CRUD/show.html.twig',
    ]);
  }
}
```

---

## Customizing Block Titles and Borders

You can hide block titles, remove the border, or add custom buttons in the **`show`** and **`edit`** views.

### Field Group Configuration

When configuring field groups, use the following parameters:

- **`show_header`**: Toggles the title bar.
- **`show_header_border`**: Toggles the border around the block.
- **`header_btn`**: An array of buttons to display on the right side of the title.

```php
->with('block1', [
  'class' => 'col-md-8',
  'label' => 'first block',
  'show_header' => true,        // true|false
  'show_header_border' => true, // true|false
  'header_btn' => [
    [
    'url' => '//www.google.com',
    'target' => '_blank',
    'class' => 'btn-danger',
    'label' => 'Google',
    'icon' => 'fa-plus-circle',
    ],
    [
    'url' => '//www.facebook.com',
    'target' => '_blank',
    'class' => 'btn-success',
    'label' => 'Facebook',
    'icon' => 'fa-plus-circle',
    'confirm' => 'Are you sure ?' // optional: displays a modal confirmation
    ],
  ],
])
```

> [!NOTE]
> A variety of **button classes** (e.g., `btn-default`, `btn-primary`, `btn-danger`) and **Font Awesome icons** can be used to style title bar buttons.

---

## Adding a Search List Box

Include a **search_list** array in the field group configuration. The user’s input is appended to the target URL for dynamic filtering.

```php
$url_note_list = $admin->generateUrl('Partitech\SonataCrm\Admin\NoteAdmin.list', ['id' => $id]);

$showMapper->with('block_notes', [
  'class' => 'col-md-12',
  'label' => 'sonata-crm.account.block_notes',
  'search_list' => [
    'label' => 'Rechercher',
    'class' => 'btn-info',
    'icon' => 'fa-search',
    'list' => [
      [
        'url' => $url_note_list . '?filter[subject][value]=',
        'target' => '_self',
        'class' => '',
        'label' => 'sonata-crm.note.subject'
      ],
      [
        'url' => $url_note_list . '?filter[location][value]=',
        'target' => '_self',
        'class' => '',
        'label' => 'sonata-crm.note.location'
      ],
      [
        'url' => $url_note_list . '?filter[description][value]=',
        'target' => '_self',
        'class' => '',
        'label' => 'sonata-crm.note.description'
      ]
    ]
  ]
]);
```

For the search functionality to work, **filters** must be defined in the corresponding Sonata Admin class.

---

## Customizing Navigation Bar Buttons

Use `configureTabMenu` to change the class and icon of top navigation buttons.

```php
protected function configureTabMenu(MenuItemInterface $menu, string $action, AdminInterface $childAdmin = null): void
{
    if (!$childAdmin && !in_array($action, ['edit', 'show'])) {
        return;
    }

    $admin = $this->isChild() ? $this->getParent() : $this;
    $id = $admin->getRequest()->get('id');

    if (!$childAdmin && in_array($action, ['edit', 'show'])) {
        $contacts = $menu->addChild('Contacts', $admin->generateMenuUrl(
            current($this->getChildren())->getCode() . '.list', ['id' => $id]
        ));
        $contacts->setExtras([
            'btn_class' => ('edit' === $action) ? 'btn-warning' : 'btn-info',
            'btn_icon'  => 'fa-edit',
        ]);
    }
}
```

---

## Displaying Raw HTML Fields

When using a WYSIWYG editor (e.g., CKEditor), you may want to display the rendered HTML in **`show`** mode. Sonata Extra Bundle provides a dedicated template:

```php
protected function configureShowFields(ShowMapper $showMapper): void
{
$showMapper
  ->add('ckeditor_field', null, [
    'label' => 'Field that display HTML',
    'template' => '@PartitechSonataExtra/Admin/CRUD/show_field_html.html.twig',
  ]);
}
```

This template ensures your HTML field is displayed unescaped (i.e., **rendered** rather than plain text).

---

## Appending or Prepending Templates to Page Content

You can insert custom templates before or after the **content header**. This is especially useful for nested interfaces where parent information should be displayed in child views.

**Template Slots**:
- `show_prepend_page_content_header_template`
- `list_prepend_page_content_header_template`
- `edit_prepend_page_content_header_template`
- `show_append_page_content_header_template`
- `list_append_page_content_header_template`
- `edit_append_page_content_header_template`

Configure them in your Admin class:

```php
class YourAdmin extends AbstractAdmin
{
  public function configure(): void
  {
    $this->setTemplates([
      'edit'  => '@PartitechSonataExtra/Admin/CRUD/edit.html.twig',
      'list'  => '@PartitechSonataExtra/Admin/CRUD/list.html.twig',
      'show'  => '@PartitechSonataExtra/Admin/CRUD/show.html.twig',
      'show_append_page_content_header_template' => '@PartitechSonataCrm/Account/extra_header.html.twig',
      'list_append_page_content_header_template' => '@PartitechSonataCrm/Account/extra_header.html.twig',
      'edit_append_page_content_header_template' => '@PartitechSonataCrm/Account/extra_header.html.twig',
    ]);
  }
}
```

**Example Twig Template**:

```twig
{% if admin.isChild() and admin.parent is defined and admin.parent %}
{% set parent_class = admin.parent|get_class %}
{% if parent_class == 'Partitech\\SonataCrm\\Admin\\AccountAdmin' and admin.parent.subject is not null %}
{% set object = admin.parent.subject %}
{% endif %}
{% endif %}

{% if object is defined %}
<section class="invoice" style="margin: 3px 0;border-radius: 4px;">
    <div class="row">
        <div class="col-xs-12">
            <h2 class="page-header">
                <i class="fa fa-globe"></i> {{ object.name }}
                <small class="pull-right">Updated: {{ object.dateModified|date('Y-m-d H:i:s') }}</small>
            </h2>
        </div>
    </div>
    <div class="row invoice-info">
        <div class="col-sm-4 invoice-col">
            <b>Phone</b>: {{ object.officePhone }}<br>
            <b>Website</b>: <a href="{{ object.website }}" target="_blank">{{ object.website }}</a><br>
        </div>
        <div class="col-sm-4 invoice-col">
            <b>Address</b>:<br>
            <address>
                {{ object.billingAddressStreet }}<br>
                {{ object.billingAddressPostalcode }} {{ object.billingAddressCity }} {{ object.billingAddressState }}<br>
                {{ object.billingAddressCountry }}
            </address>
        </div>
        <div class="col-sm-4 invoice-col">
            <b>Info:</b><br>
            {{ object.description|raw }}
        </div>
    </div>
</section>
{% endif %}
```

---

## Preserving Filters in List View

When overriding the **`list.html.twig`** template, you can enable a “preserve filters” feature. This retains the active filters and layout in the user’s session.

### Steps to Activate

1. **Override the List Template**:

```php
public function configure(): void
{
  $this->setTemplates([
    'list' => '@PartitechSonataExtra/Admin/CRUD/list.html.twig',
  ]);
}
```

2. **Enable the Preserve Filter Option**:

```php
class YourAdmin extends AbstractAdmin
{
    public bool $preserveFilters = true;
}
```

> [!NOTE]
> A **Reset** button appears in the top navigation bar, allowing you to clear saved filters.

![template_admin_show_groups_7.png](./doc-sonata-extra-images/template_admin_show_groups_7.png)

---

## Conclusion

These **CRUD customization** features in the Sonata Extra Bundle give you fine-grained control over how your entity fields, blocks, and navigation elements are displayed. By leveraging custom templates, search lists, and button configurations, you can craft a more intuitive and visually appealing Sonata Admin interface tailored to your project’s needs.
