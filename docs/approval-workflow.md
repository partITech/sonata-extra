# Sonata Extra Bundle: Approval workflow


## Overview
The SonataExtra Approval Workflow is designed to log all actions within the admin site, providing a comprehensive overview of activities, resources involved, descriptions, and users. This feature ensures that actions are logged but not applied immediately. Instead, they require validation by a user with the `ROLE_APPROVE` permission.

## Features
- **Action Logging**: Logs every action but does not apply changes instantly.
- **Role-Based Approval**: Actions need approval from a user with `ROLE_APPROVE`.
- **Pending Modifications Alert**: A red notification badge in the admin interface alerts administrators of modifications awaiting approval.
- **Detailed Action View**: Displays action type, resource, description, user, and date in the list view.
- **Approval and Detail Buttons**: Allows administrators to approve modifications or view detailed information.
- **Purge Functionality**: An option to purge pending modifications.
- **Detailed Modification View**: Shows impacted fields and values for each action.

## Screens

- Editor action :
![approval_editor_action.png](./doc-sonata-extra-images/approval_editor_action.png)

- Admin notification :
![approval_admin_notification.png](./doc-sonata-extra-images/approval_admin_notification.png)

- List view :
![approval_admin_list.png](./doc-sonata-extra-images/approval_admin_list.png)

- Detail view :
![approval_admin_detail.png](./doc-sonata-extra-images/approval_admin_detail.png)

## Configuration Steps

### Add EventListeners
```yaml
    sonata-extra.doctrine_activity_listener:
        class: Partitech\SonataExtra\EventListener\DoctrineActivityListener
        tags:
            - { name: doctrine.event_listener, event: prePersist }
            - { name: doctrine.event_listener, event: preUpdate }
            - { name: doctrine.event_listener, event: preRemove }
            - { name: doctrine.event_listener, event: onFlush }
            - { name: kernel.event_listener, event: sonata.admin.event.batch_action.pre_batch_action, method: onPreBatchAction }



    sonata-extra.configure_menu_listener:
        class: Partitech\SonataExtra\EventListener\ConfigureMenuListener
        tags:
            - { name: kernel.event_listener, event: sonata.admin.event.configure.menu.sidebar, method: onMenuConfigure }
        arguments:
            - "@service_container"
            - "@doctrine.orm.entity_manager"
            - "@request_stack"
            - "@security.authorization_checker"
```
### Configure entity exclusion
You can exclude any entities from the workflow.
By default you should exclude your SonataMediaMedia and User entities.
```yaml
parameters:

  sonata_approve_excluded_entities:
    - 'App\Entity\BackofficeUser'
    - 'App\Entity\SonataMediaMedia'
    - 'Partitech\SonataExtra\Entity\Slider'
```

### Configure Menu
By default the approval menu is dynamically inserted in the root of the menu with a visual red alert
To prevent double display when the menu group is open, you can list the menu items that will hide the root alert.

```yaml
parameters:
  sonata_approve_menu:
    - 'admin_app_approval'
    - 'admin_app_adminactivitylog'
    - 'admin_app_sonatamediamedia'
    - 'admin_app_backofficeuser'
    - 'admin_app_sonatapagesite'
```

### Configure Roles
Any users that don't have ROLE_APPROVE will be in the workflow rule.
If you want to get the admin activity log without the approval workflow, just add ROLE_APPROVE to your default admin user.

```yaml
security:
  role_hierarchy:
        ROLE_EDITOR:
            - ROLE_USER
            - ROLE_SONATA_ADMIN
            - ROLE_ADMIN_USERADMIN_ALL

            - ROLE_ADMIN_ADMIN_ACTIVITY_LOG_ALL
            - ROLE_ADMIN_ADMIN_APPROVAL_LOG_ALL
            - ROLE_ADMIN_USERADMIN_ALL
            - ROLE_ADMIN_REDIRECTION_ALL
            - ROLE_SONATA_EXTRA_ADMIN_SLIDER_ALL
            - ROLE_SONATA_EXTRA_ADMIN_SLIDER_SLIDES_ALL
            - ROLE_SONATA_EXTRA_ADMIN_FAQ_CATEGORY_ALL
            - ROLE_SONATA_EXTRA_ADMIN_FAQ_QUESTION_ALL

            - ROLE_SONATA_USER_ADMIN_USER_ALL

            - ROLE_SONATA_MEDIA_ADMIN_MEDIA_ALL
            - ROLE_SONATA_MEDIA_ADMIN_GALLERY_ALL
            - ROLE_SONATA_MEDIA_ADMIN_GALLERY_ITEM_ALL

            - ROLE_SONATA_PAGE_ADMIN_PAGE_ALL
            - ROLE_SONATA_PAGE_ADMIN_BLOCK_ALL
            - ROLE_SONATA_PAGE_ADMIN_SHARED_BLOCK_ALL
            - ROLE_SONATA_PAGE_ADMIN_SNAPSHOT_ALL
            - ROLE_SONATA_PAGE_ADMIN_SITE_ALL

            - ROLE_PRODIGIOUS_SONATA_MENU_ADMIN_MENU_ALL
            - ROLE_PRODIGIOUS_SONATA_MENU_ADMIN_MENU_ITEM_ALL

        ROLE_ADMIN:
            - ROLE_USER
            - ROLE_SONATA_ADMIN
            - ROLE_APPROVE
            - ROLE_EDITOR
            - ROLE_ADMIN_ADMIN_ACTIVITY_LOG_ALL
            - ROLE_ADMIN_ADMIN_APPROVAL_LOG_ALL
            -
        ROLE_SUPER_ADMIN: [ROLE_ADMIN, ROLE_ALLOWED_TO_SWITCH]
```

## Usage and Workflow

- **Non-Approved Users**: Users without `ROLE_APPROVE` can perform actions, but these will be logged and await approval.
- **Approval Required**: A notification in the 'Pending Modifications' tool alerts administrators to actions requiring approval.
- **Approval Process**: Administrators with **ROLE_APPROVE** can view details and either approve or reject modifications.
- **Purge Option**: Administrators can use the purge button to clear pending modifications.

## Conclusion
The SonataExtra Approval Workflow enhances control and security by ensuring that all modifications are logged and require approval by authorized personnel. This feature is crucial for maintaining integrity and accountability within the admin interface.