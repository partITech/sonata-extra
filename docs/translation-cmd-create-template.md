# Sonata Extra Bundle:  Using the creation and import of translations command.


## Overview

PartitechSonataExtraBundle provides a convenient way to manage translations in Sonata Admin. This bundle includes two primary commands: `sonata:extra:translation-create-template` and `sonata:extra:translation-import-template`. These commands facilitate the creation and import of translations, optimizing the process and integrating with Chat GPT for efficient translation handling.


## Commands

### sonata:extra:translation-create-template

#### Description

This command prepares your content for translation. It creates a directory structure with files containing the text to be translated.

#### Usage
```
bin/console sonata:extra:translation-create-template --site=[SITE_ID] --reference-site=[REFERENCE_SITE_ID] --service="[ADMIN_SERVICE]"

```

- `--site`: The site ID for which translations are to be created.
- `--reference-site`: The reference site ID.
- `--service`: The admin service for which translations are needed.


#### Output

The command generates a set of directories and files under `var/cache/translation/YourService/`. 
For each item ID, you'll find files like:

- `payloadArray.txt`: Contains sentences for translation.
- `payloadArray_translated.json`: For storing the translated content.
- Other files for handling structured content like HTML.









### sonata:extra:translation-import-template

#### Description

This command imports the translated content back into your database, reconstructing the HTML structure.

#### Usage
After translating the content, run:
```
bin/console sonata:extra:translation-import-template --service="[ADMIN_SERVICE]"
```

### Process Overview

1. Deactivate Automatic Translation: First, ensure that automatic translation is turned off.
2. Create Translation Template: Use the sonata:extra:translation-create-template command to generate translation files.
3. Translate Content: Submit the content in payloadArray.txt to the Chat GPT interface or a professional translator. Fill the translated content into payloadArray_translated.json.
4. Handle Structured Content: For HTML fields, use the field_content_payload.txt for translation and update field_content_translation.txt accordingly.
5. Import Translations: Run the sonata:extra:translation-import-template command to update your content with the new translations.

### Example
```
# Creating translation templates
bin/console sonata:extra:translation-create-template --site=5 --reference-site=2 --service="Partitech\SonataExtra\Admin\ArticleAdmin"

# Importing translated content
bin/console sonata:extra:translation-import-template --service="Partitech\SonataExtra\Admin\ArticleAdmin"
```


## Handling Structured Content Like HTML

When dealing with translations for text fields containing structured content (e.g., HTML), the `PartitechSonataExtraBundle` provides a sophisticated method to ensure that the structure and formatting are preserved. This is especially important for content that includes HTML tags, code snippets, images, and other embedded media.

### Understanding the File Structure
The command `sonata:extra:translation-create-template` generates several files to assist with this process:
1. `field_content_code_blocks.json`: This file contains 'code blocks' or sections of the HTML content that should not be translated. These could be actual code snippets, embedded media, or other non-text elements within your HTML content.
2. `field_content_payload.txt`: This file includes the text from your HTML content that requires translation. Before creating this file, the bundle extracts non-translatable elements (like those in field_content_code_blocks.json) and replaces them with placeholders. This extraction ensures that only the necessary text is translated, optimizing both the translation quality and cost.
3. `field_content_translation.txt`: After translating the content in `field_content_payload.txt`, the translated text should be placed in this file.

### The Translation Process
Here’s a detailed look at how the translation process works for structured content:

1. Extract Non-Translatable Elements: The bundle identifies and extracts parts of the HTML content that don't need translation (e.g., code blocks, images) and stores them in field_content_code_blocks.json. These elements are replaced with placeholders in the HTML text.
2. Translate Text Content: The translatable text, now free of non-text elements, is placed in field_content_payload.txt. This text should be submitted for translation.
3. Reinsert Translated Text: Once translated, the new text is inserted into field_content_translation.txt.
4. Reconstruct HTML Content: During the import process (sonata:extra:translation-import-template), the bundle reassembles the HTML content. It combines the translated text from field_content_translation.txt with the non-translatable elements from field_content_code_blocks.json, replacing the placeholders with their original content.


## Conclusion

This functionality simplifies the translation process in Sonata Admin, integrating seamlessly with external translation tools and ensuring efficient content management.



