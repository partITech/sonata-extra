# Translate Content Command Documentation

## Command Name

```bash
sonata:extra:translate-content
````

## Description

This command is part of the Sonata Extra Bundle, aimed at facilitating the translation of content for entities across different locales in a multisite setup.

## Usage

```bash
bin/console sonata:extra:translate-content --site=<site_ids> --reference-site=<reference_site_id> --service=<service_admin_name>
```

## Options

- `--help, -h`: Displays help message.
- `--site`: Specifies the site ID(s) for which to translate content. This is a required option.
- `--reference-site`: Specifies the reference site ID used as the source for translations. This is a required option.
- `--service`: Service Name of the admin to be translated. This is a required option. (use `bin/console sonata:admin:list` to get a full list of your availlable admin services)

## Functionality
- Translates the content of the specified entity to different locales based on the site IDs provided.
- Uses a reference site as the source for translations.

```bash
bin/console sonata:extra:translate-content --site=1,2,3 --reference-site=1 --fqcn=Partitech\SonataExtra\Entity\Article
```

## Key Features
- Supports multisite translation.
- Provides a progress bar to monitor the status of translation.
- Includes comprehensive error handling for missing or incorrect options.

## Requirements
- Ensure the appropriate entities and admin classes are set up with multilanguage support.
- Run the necessary Sonata Page commands (`sonata:page:create-site`, etc.) to configure sites.

## Additional Information
- The command includes a progress bar and status updates to inform the user about the ongoing translation process.
- It validates the existence of specified sites, reference sites, and entities to ensure a smooth translation process.
- Error messages and usage instructions are displayed in case of incorrect or missing options.

## Dependencies
- `EntityManagerInterface` for database interactions.
- `ParameterBagInterface` for handling parameter configurations.
- `TranslateObjectService` for executing the translation logic.
- `Pool` for Sonata admin pool services.