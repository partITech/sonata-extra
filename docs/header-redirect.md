# Sonata Extra Bundle:  Header Redirect Manager

# Header Redirect Manager

## Overview
The Header Redirect Tool is designed to manage incoming traffic redirections within your application. It enables the configuration of redirection rules with various HTTP status codes to effectively handle different redirection scenarios.

## Screens
- List view :

![Activity_log_index.png](./doc-sonata-extra-images/header-redirect-list.png)

- Detail view :

![Activity_log_detail.png](./doc-sonata-extra-images/header-redirect-detail.png)

## Supported Redirection Status Codes
- **301 Moved Permanently**: Indicates the resource has been moved to a new URL permanently.
- **302 Found**: Suggests a temporary redirection to a different URL.
- **307 Temporary Redirect**: Indicates the resource has temporarily moved to another URL, with the method and body not being altered.
- **308 Permanent Redirect**: Signals a permanent redirect similar to 301, but with the method and body not being changed.

## Features
- **Redirection Rule Management**: Allows the creation and management of redirection rules.
- **List View**: Displays all configured redirection rules.
- **Flexible Configuration**: Users can specify the incoming URL, domain, and target URL for redirection.
- **Status Code Specification**: Each rule can be configured with one of the supported HTTP status codes to ensure appropriate redirection behavior.

## List View
The list view in the Header Redirect Tool provides a comprehensive overview of all redirection rules. It shows the incoming URL, the domain from which the request originates, the target URL, and the chosen status code for each rule.

## Configuring a Redirection Rule
To configure a new redirection rule, the user needs to provide the following information:
- **Incoming URL**: The URL that needs to be redirected.
- **Domain**: The domain from which the request is coming.
- **Target URL**: The destination URL where the traffic should be redirected.
- **Status Code**: One of the supported HTTP status codes (301, 302, 307, or 308) depending on the redirection requirement.

## Usage
1. Navigate to the Header Redirect Tool interface in your application.
2. To add a new rule, enter the incoming URL, domain, target URL, and select the appropriate status code.
3. Save the configuration to activate the redirection rule.
4. The tool will automatically redirect incoming requests based on the configured rules.

## Conclusion
The Header Redirect Tool offers a robust solution for managing traffic redirection in web applications. Its flexibility in configuring redirection rules with different HTTP status codes makes it an essential tool for website management and SEO optimization.
