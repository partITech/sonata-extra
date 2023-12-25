# Sonata Extra Bundle:  Firewall

The SonataExtraBundle provides an enhanced set of functionalities for managing firewall rules within the Sonata Admin environment. This bundle allows administrators to create and manage rules for filtering requests based on various criteria like stop words, IP addresses, and User Agents.

## Features

- Firewall Rule Management: Define and manage firewall rules to filter incoming requests.
- Support for Multiple Criteria: Filter requests based on stop words, IP addresses, and User Agents.
- Dynamic Rule Application: Apply rules based on request parameters (GET, POST, Headers).
- Cache Integration: Rules are cached for improved performance with the option to reset the cache on updates.
- Recursive Data Analysis: Handle complex data structures in request parameters.

Activation
Add the firewall listener into your `services.yaml`

```yaml
    sonata-extra.Firewall_Listener:
        class: Partitech\SonataExtra\EventListener\FirewallListener
        tags:
            - { name: kernel.event_listener, event: kernel.request, method: onKernelRequest }

```

Add admin interfaces in your `sonata_admin.yaml` 

```yaml
            firewall:
                icon: fa fa-cogs
                label: Firewall
                items:
                    - Partitech\SonataExtra\Admin\SecFirewallRuleAdmin
                    - Partitech\SonataExtra\Admin\SecStopWordAdmin
                    - Partitech\SonataExtra\Admin\SecIpRuleAdmin
````

## Creating Firewall Rules

Firewall rules can be created and managed through the Sonata Admin dashboard. The following rule types are supported:

- Stop Word: Block requests containing specified keywords.
- IP: Block requests from specified IP addresses.
- User Agent: Block requests from specified User Agents.

## Configuring Rules

Each rule consists of the following components:

- type: The type of rule (e.g., 'stop_word', 'ip', 'user_agent').
- source: The source of the data to analyze (e.g., 'GET', 'POST', 'HEADER').
- parameters: List of values to check against the rule.
- matchMode: Determines if the rule should look for an exact match ('equal') or a partial match ('contain').

## Cache Management

The bundle utilizes caching to enhance performance. The cache is automatically reset whenever a rule is updated, created, or deleted.


