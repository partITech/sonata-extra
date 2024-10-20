# Sonata Extra Bundle :  Migrating Your Site from WordPress


This guide provides a step-by-step process to migrate your WordPress site content to the Sonata Extra Bundle.

## Step 1: Importing Content

Begin by importing your WordPress content using the following command


```shell

bin/console sonata-extra:wordpress-importer --url https://www.domain.com --user wordpressUser --token "s2df5 df5df1 qsd8f yu65u wxc56 uj16 eret9"

```

For detailed instructions on this command, refer to WordPress Importer Command. [WordPress importer command](../wordPress-import.md) 

After executing this command, your categories, tags, and articles from WordPress should be imported successfully.

## Step 2: Viewing Articles

You can now view your articles using the blog routes provided by Sonata.

## Step 3: Translation Setup
To translate content into multiple languages, follow these steps:

1. **Configure Your OpenAI Account**:

Refer to the [Translation API Documentation](translation-api.md) for configuring your OpenAI account. Alternatively, you can use your custom provider or opt not to use a translation API.

2. **API Configuration**:

Update the Sonata Extra Bundle configuration to include your translation API settings:


```yaml
partitech_sonata_extra:
  smart_service:
    translate_on_create_page: true
    translate_on_create_translation: true
    seo_proposal_on_article: true
    default_provider: open_ai
    providers:
      open_ai:
        class: Partitech\SonataExtra\Translation\Provider\OpenAiProvider
        api_key: '%open_ai_api_key%'
        model: '%open_ai_api_model%'
        max_token_per_request: 200

```


Set `open_ai_api_key` and open_ai_api_model in your `.env` file.

Choosing the Model:
For complex languages, `gpt-4` is recommended. For English, `gpt-3.5-turbo` should suffice. Note that `GPT-3` will be faster and more cost-effective.

Enable Automatic Translation:
Add the `#[Translatable]` attribute and import `Partitech\SonataExtra\Attribute\Translatable` in your entity properties for automatic translation.

## Step 4: Translating Content

Execute the following commands to translate your content (you may want to add your own services):

```shell

bin/console sonata:extra:translate-content --site=2,3,4,5 --reference-site=1 --service="sonata.classification.admin.category"
bin/console sonata:extra:translate-content --site=2,3,4,5 --reference-site=1 --service="sonata.classification.admin.tag"
bin/console sonata:extra:translate-content --site=2,3,4,5 --reference-site=1 --service="Partitech\SonataExtra\Admin\ArticleAdmin"
bin/console sonata:extra:translate-content --site=2,3,4,5 --reference-site=1 --service="Partitech\SonataExtra\Admin\EditorAdmin"
bin/console sonata:extra:translate-content --site=2,3,4,5 --reference-site=1 --service="sonata.page.admin.page"
```
**Note:** If the translation process freezes, simply restart the process, and it will resume from where it left off.


# Step 5: Menu Configuration

If you haven't configured your menu yet, it's advised to duplicate the default menu first.

1. **Duplicate the Menu**: Manually duplicate the default menu. Note that the tree structure will not be preserved and must be rearranged manually.

2. **Fixing Menu URLs**:
3. 
Run the following command to correct the URLs of menu items:

```shell
bin/console sonata:extra:fix-menu-url --menu=6
```
This command will adjust the URLs to match the localized pages in SonataPage.

## Step 6: Creating Snapshots

Finally, create snapshots of your pages with the command:

```shell

bin/console sonata:page:create-snapshots

```

