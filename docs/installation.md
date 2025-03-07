# Installation

Below are step-by-step instructions to install and configure the **Partitech Sonata Extra Bundle** in your Symfony application. Make sure your environment meets the minimal requirements for Sonata and Symfony before proceeding.

> [!TIP]
> For more detailed guidance, refer to the official documentation and the project's GitHub repository.

---

## Installing the Bundle

Run the following Composer command to add the bundle to your project:

```bash
composer require partitech/sonata-extra
```

Once installed, register the bundle in your `bundles.php` file:

```php
Partitech\SonataExtra\PartitechSonataExtraBundle::class => ['all' => true],
```

---

## Configuring Sonata Front-End Assets

### Sonata Page Front-End

To include the required front-end assets for **Sonata Page**, add the following blocks in your Sonata Page templates:

```twig
{% block sonata_page_stylesheets %}
<link rel="stylesheet" href="{{ asset('bundles/sonatapage/frontend.css') }}" media="all">
<link rel="stylesheet" href="{{ asset('bundles/partitechsonataextra/assets/styles/admin.css') }}" media="all">
{% endblock %}

{% block sonata_page_javascripts %}
<script src="{{ asset('bundles/partitechsonataextra/assets/admin.js') }}"></script>
{% endblock %}
```

### Sonata Admin Assets

To include the additional JavaScript and CSS resources for your **Sonata Admin**, update your `config/packages/sonata_admin.yaml` with entries like the following:

```yaml
sonata_admin:
assets:
extra_javascripts:
- js/runroom_sortable_init.js
- js/jquery-ui.min.js
- js/menu-admin.js
- bundles/fosckeditor/ckeditor.js
- bundles/partitechsonataextra/assets/styles/admin.css

        extra_stylesheets:
            - bundles/sonatatranslation/css/sonata-translation.css
            - bundles/partitechsonataextra/assets/admin.js
            - css/admin.css
            - build/admin.css
```

> [!NOTE]
> Adjust these paths according to your project structure and the location of your assets.

---

## Running Asset Commands

Install or update any missing assets and dependencies:

```shell
symfony console sonata:extra:install-gutenberg
symfony console ckeditor:install --tag=4.19.0
symfony console asset:install
```

---

## Sonata Initialization Commands

Run the usual Sonata initialization commands, plus an additional route fix to enable full **multilanguage** support:

```shell
symfony console sonata:page:create-site
symfony console sonata:page:update-core-routes
symfony console sonata:extra:page-fix-route
symfony console sonata:page:create-snapshots
```

---

## Overriding Translations

To override or customize the translations provided by **Sonata Extra**, copy them into your own translation files. For example:

```shell
mkdir translations
touch translations/SonataExtraBundle.fr.yaml
cat vendor/partitech/sonata-extra/translations/messages.fr.yaml > translations/SonataExtraBundle.fr.yaml
```

> [!TIP]
> You can adjust the file name and language code to suit your project's localization needs.

---

## Forcing Redirection After Admin Login

If you want to force a specific domain or path redirection after an admin logs in, use the custom **AuthenticationSuccessHandler**:

```yaml
security:
firewalls:
admin:
form_login:
success_handler: Partitech\SonataExtra\Handler\AuthenticationSuccessHandler
```

This configuration ensures that, upon successful login, the user is redirected to the desired route or domain.

---

## Configuring Sonata Extra Routes

Finally, enable the default **Sonata Extra** routes by adding them to your `config/routes/sonata_extra.yaml`:

```yaml
partitech_sonata_extra_bundle.routes:
resource: '@PartitechSonataExtraBundle/config/routes.yaml'
```

---

> [!IMPORTANT]
> Once everything is set up, verify that your routes, assets, and translations function as expected. Refer to the official documentation for troubleshooting and advanced configuration tips.
