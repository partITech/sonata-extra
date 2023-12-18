# Sonata Extra Bundle:  Activity log

## Overview
The Activity Log feature in the Sonata Extra Bundle is designed to monitor and log all activities within the admin site. It provides a comprehensive view of actions taken, resources involved, descriptions of activities, and the users who performed them.

## Features
- **Action Type Display**: Shows the type of action performed (e.g., create, update, delete).
- **Resource Tracking**: Identifies the resource that was affected by the action.
- **Action Description**: Provides a detailed description of the activity.
- **User Identification**: Displays the user who performed the action.
- **Detailed View**: Offers an in-depth view of the modified elements in each activity.
- **Undo Functionality**: Includes a feature to reverse modifications when possible.

## Screens
- List view : 

![Activity_log_index.png](./doc-sonata-extra-images/Activity_log_index.png)

- Detail view : 

![Activity_log_detail.png](./doc-sonata-extra-images/Activity_log_detail.png)

## Enabling the Activity Log
To activate the Activity Log functionality, the following service configuration should be added to your services file:

```yaml
sonata-extra.doctrine_activity_listener:
    class: Partitech\SonataExtra\EventListener\DoctrineActivityListener
    tags:
        - { name: doctrine.event_listener, event: prePersist }
        - { name: doctrine.event_listener, event: preUpdate }
        - { name: doctrine.event_listener, event: preRemove }
        - { name: doctrine.event_listener, event: onFlush }
        - { name: kernel.event_listener, event: sonata.admin.event.batch_action.pre_batch_action, method: onPreBatchAction }
```

## Implementation Details
* The DoctrineActivityListener class is responsible for listening to various Doctrine and Sonata admin events.
* Events such as prePersist, preUpdate, preRemove, and onFlush are tracked to log activities related to entity lifecycle changes.
* The onPreBatchAction method handles logging of batch actions performed in the Sonata admin.
## Usage
Once enabled, the Activity Log feature will automatically start logging activities based on the configured events. The logs can be viewed in the admin interface, where details of each activity are neatly displayed.

## Conclusion
The Activity Log is a powerful feature for monitoring and auditing actions within the Sonata admin interface. It enhances transparency and accountability by providing clear and detailed logs of all activities.