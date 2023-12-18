# Partitech Sonata Extra Bundle : Installation


## Installation

```sh
composer require partitech/sonata-extra
```

### Register the bundle in your `bundles.php`:
```php
Partitech\SonataExtra\PartitechSonataExtraBundle::class => ['all' => true],
```

### Intall assets for sonata page frontend

```sh
{% block sonata_page_stylesheets %}
    <link rel="stylesheet" href="{{ asset('bundles/sonatapage/frontend.css') }}" media="all">
    <link rel="stylesheet" href="{{ asset('bundles/partitechsonataextra/assets/styles/admin.css') }}" media="all">
{% endblock %}

{% block sonata_page_javascripts %}
    <script src="{{ asset('bundles/partitechsonataextra/assets/admin.js') }}"></script>
{% endblock %}

```

### Install assets for admin
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

### Run assets commands
```shell
symfony console sonata:extra:install-gutenberg
symfony console ckeditor:install --tag=4.19.0
symfony console asset:install
```
### Run all Sonata initialization commands + Sonata fix routes (will add default parameters to enable multilanguage support)
```shell
symfony console sonata:page:create-site
symfony console sonata:page:update-core-routes
symfony console sonata:extra:page-fix-route
symfony console sonata:page:create-snapshots

```
Translations can be overridden in your project using the Symfony translation hierarchy like this:
```shell
mkdir translations
touch translations/SonataExtraBundle.fr.yaml
cat vendor/partitech/sonata-extra/translations/messages.fr.yaml > translations/SonataExtraBundle.fr.yaml
```

You can force the redirection of the user to your default site domain/relative path by using the AuthenticationSuccessHandler in `security.yaml`:
```yaml
    firewalls:
        admin:
            form_login:
                success_handler: Partitech\SonataExtra\Handler\AuthenticationSuccessHandler
```
