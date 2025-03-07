
# WordPress Importer Command Documentation

## Command Overview

The `sonata-extra:wordpress-importer` command is designed for importing images, categories, posts, and tags from a WordPress site into your application. This command is key for integrating WordPress content with ease.

### Syntax

```bash
sonata-extra:wordpress-importer --url=[WORDPRESS_SITE_URL] --user=[USERNAME] --token="[TOKEN]"
```

#### Parameters

- `--url`: The URL of the WordPress site from which content is to be imported.
- `--user`: Your WordPress username.
- `--token`: The authentication token for API access.

## Generating WordPress Connection Token using REST API

To use the `sonata-extra:wordpress-importer` command, a valid WordPress REST API authentication token is required. Below is the procedure to generate this token.

### Step 1: Log into WordPress Admin Panel

Access the WordPress admin panel by navigating to `https://www.website.com/wp-admin`.

### Step 2: Enable REST API

Make sure that the WordPress REST API is enabled, which is the default setting in the latest WordPress versions.

### Step 3: Create Application Password

1. Navigate to `Users` > `Your Profile`.
2. Find the `Application Passwords` section.
3. Enter a name for the new application password and click `Add New`.
4. Note down the generated password, as it will be used as your token.

### Step 4: Use the Application Password

The generated application password will serve as your token for the `sonata-extra:wordpress-importer` command.

### Step 5: Execute Command with Token

Use the generated application password as the token in your command:

```shell
symfony console sonata-extra:wordpress-importer --url=[WORDPRESS_SITE_URL] --user=[USERNAME] --token="[APPLICATION_PASSWORD]"
```

Replace `[WORDPRESS_SITE_URL]`, `[USERNAME]`, and `[APPLICATION_PASSWORD]` with your actual WordPress site URL, username, and the application password you generated.