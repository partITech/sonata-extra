# Partitech Sonata Extra Bundle : Cookie Consent Block (GDPR)

## Overview

The Cookie Consent Block is a customizable solution integrated into the Sonata Extra Bundle to manage user consent for cookies in compliance with GDPR regulations. It offers a flexible and user-friendly interface for cookie consent management.

## Screens
![cookie-consent-block.png](./doc-sonata-extra-images/cookie-consent-block.png)
![cookie-consent-block_btn.png](./doc-sonata-extra-images/cookie-consent-block_btn.png)

## Usage

The Cookie Consent Block is designed to be easy to use and integrate. Add the block to your Sonata admin dashboard or any other part of your application where you need to display a cookie consent notice.

## Configuration

### Settings

You can configure the Cookie Consent Block by adjusting the following settings in your block configuration:

- `config_orejim`: JavaScript configuration for the Orejime consent management platform.
- `config_tags`: Custom script tags for analytics or other purposes, compliant with consent choices.
- `template`: Twig template path for rendering the cookie consent UI.
- `style_url`: (Optional) URL to additional CSS styles.
- `class`: (Optional) Custom CSS class for styling the consent block.

### Example Configuration

```php
$resolver->setDefaults([
    'config_orejim' => $orejimeConfig, // Orejime JavaScript configuration
    'config_tags' => $config_tags,     // Custom script tags
    'style_url' => '',                 // Optional additional CSS
    'class' => null,                   // Optional custom CSS class
    'template' => '@PartitechSonataExtra/Blocks/cookie_consent/default.html.twig', // Twig template
]);

```

### Customization

To customize the appearance and behavior of the Cookie Consent Block, modify the Twig template and the CSS styles as needed. You can also adjust the JavaScript configuration (`config_orejim`) for specific consent scenarios.

### Integration with Orejime
The Cookie Consent Block uses [Orejime](https://github.com/empreinte-digitale/orejime) for managing user consents. Ensure you are familiar with Orejime's configuration and usage to effectively use this block.

### Translation and Localization
The block supports multiple languages for GDPR compliance. Configure the translations as per your requirements in the config_orejim setting.