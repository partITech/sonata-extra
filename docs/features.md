* # Partitech Sonata Extra Bundle : Features
* 
* ### AsAdmin() php attribute configuration
* 
* - Add another configuration type directly in the Admin class
* - Documentation : [as-admin-attribute.md](./as-admin-attribute.md)
* - Code Preview: ![AsAdmin.png](./doc-sonata-extra-images/AsAdmin.png)
* 
* ### Activity Log in Admin
* The Activity Log feature in the Sonata Extra Bundle is designed to monitor and log all activities within the admin site. It provides a comprehensive view of actions taken, resources involved, descriptions of activities, and the users who performed them.
* 
* - **Action Type Display**: Shows the type of action performed (e.g., create, update, delete).
* - **Resource Tracking**: Identifies the resource that was affected by the action.
* - **Action Description**: Provides a detailed description of the activity.
* - **User Identification**: Displays the user who performed the action.
* - **Detailed View**: Offers an in-depth view of the modified elements in each activity.
* - **Undo Functionality**: Includes a feature to reverse modifications when possible.
* 
* - List view :
* 
* ![Activity_log_index.png](./doc-sonata-extra-images/Activity_log_index.png)
* 
* - Detail view :
* 
* ![Activity_log_detail.png](./doc-sonata-extra-images/Activity_log_detail.png)
* 
* ### Activity Approval
* The SonataExtra Approval Workflow is designed to log all actions within the admin site, providing a comprehensive overview of activities, resources involved, descriptions, and users. This feature ensures that actions are logged but not applied immediately. Instead, they require validation by a user with the `ROLE_APPROVE` permission.
* 
* - **Action Logging**: Logs every action but does not apply changes instantly.
* - **Role-Based Approval**: Actions need approval from a user with `ROLE_APPROVE`.
* - **Pending Modifications Alert**: A red notification badge in the admin interface alerts administrators of modifications awaiting approval.
* - **Detailed Action View**: Displays action type, resource, description, user, and date in the list view.
* - **Approval and Detail Buttons**: Allows administrators to approve modifications or view detailed information.
* - **Purge Functionality**: An option to purge pending modifications.
* - **Detailed Modification View**: Shows impacted fields and values for each action.
* 
* - Documentation : [approval-workflow.md](./approval-workflow.md)
* - Editor action :
* ![approval_editor_action.png](./doc-sonata-extra-images/approval_editor_action.png)
* - Admin notification :
* ![approval_admin_notification.png](./doc-sonata-extra-images/approval_admin_notification.png)
* - List view :
* ![approval_admin_list.png](./doc-sonata-extra-images/approval_admin_list.png)
* - Detail view :
* ![approval_admin_detail.png](doc-sonata-extra-images/approval_admin_detail.png)
* 
* ### ASSETS MANAGEMENT
* flexible and powerful way to manage CSS and JavaScript assets in Sonata blocks. This feature allows developers to include external CSS and JS files, as well as inline styles and scripts, with ease and efficiency.
* - Documentation : [assets-handler.md](./assets-handler.md)
* ```
* {{ sonata_extra_get_blocks_css('default')|raw }}
* {{ sonata_extra_get_blocks_css_inline('default', true)|raw }}
* {{ sonata_extra_get_blocks_js('default')|raw }}
* {{ sonata_extra_get_blocks_js_inline('default', true)|raw }}
* ```
* ### Blog
* Enhance your Sonata project with the integrated blog feature from Sonata Extra Bundle.
* - Documentation : [blog.md](blog.md)
* - Multilanguage custom urls :
* ![Blog_CustomUrl.png](./doc-sonata-extra-images/Blog_CustomUrl.png)
* - Blog service type :
* ![Blog_ServiceType.png](./doc-sonata-extra-images/Blog_ServiceType.png)
* 
* ### Content Security Policy
* 
* Content Security Policy (CSP) is a crucial security feature in web development, helping to prevent various types of attacks, such as Cross-Site Scripting (XSS) and data injection attacks. The Sonata-Extra bundle for Symfony provides an efficient way to implement and manage CSP in your application.
* - Documentation : [content-security-policy.md](./content-security-policy.md)
* - Code Preview:
* 
* ![config_security_policy.png](./doc-sonata-extra-images/config_security_policy.png)
* - Rendered header:
* 
* ![result_security_policy.png](./doc-sonata-extra-images/result_security_policy.png)
* 
* ### Header Redirect Manager
* Manage all your header redirections
* - Documentation : [header-redirect.md](header-redirect.md)
* - List view :
* ![Activity_log_index.png](./doc-sonata-extra-images/header-redirect-list.png)
* - Detail view :
* ![Activity_log_detail.png](./doc-sonata-extra-images/header-redirect-detail.png)
* 
* ### Language-switcher
* The Sonata Extra Bundle provides a feature-rich language switcher compatible with hybrid pages.
* - Documentation : [language-switcher.md](language-switcher.md)
* - Front view :
* ![Activity_log_index.png](./doc-sonata-extra-images/language_switcher.png)
* 
* ### multilang on SonataPageBundle
* The Sonata Extra Bundle enhances the SonataPageBundle by offering comprehensive multisite and multilanguage management capabilities. This feature allows tracking and switching between page translations in the front-end and supports a flexible multisite strategy.
* - Documentation : [multilanguage-support-for-sonata-page.md](multilanguage-support-for-sonata-page.md)
* - List view :
* ![Activity_log_index.png](./doc-sonata-extra-images/page_multilangue_list.png)
* - Detail view :
* ![Activity_log_detail.png](./doc-sonata-extra-images/page_multilangue_view.png)
* 
* 
* ### Multilanguage Support for User Admins
* 
* Multilanguage support for admin interfaces. It comprises a trait for admin classes to manage multilanguage interfaces, and a trait for entities to create necessary fields.
* The implementation will add icons for the locales `from sonata_page` sites, enabling linkages with the records and managing all site languages.
* - Documentation : [multilanguage-support-for-users-admins.md](multilanguage-support-for-users-admins.md)
* - List view of the translation in the selected language site
* ![Multilanguage_edit.png](./doc-sonata-extra-images/Multilanguage_edit.png)
* - Edit view of the translation in the selected language site
* ![Multilanguage_list.png](./doc-sonata-extra-images/Multilanguage_list.png)
* - Create a tranlsation from a local patern
* ![Multilanguage_create_translation.png](./doc-sonata-extra-images/Multilanguage_create_translation.png)
* 
* ### Firewall
* Provides an enhanced set of functionalities for managing firewall rules within the Sonata Admin environment. This bundle allows administrators to create and manage rules for filtering requests based on various criteria like stop words, IP addresses, and User Agents.
* - Documentation : [firewall.md](./firewall.md)
* 
* ### Integration with PrestaSitemapBundle
* Our application automatically integrates with the "PrestaSitemapBundle" to generate a `sitemap.xml` file. This feature encompasses all image assets, article modules, and content pages within the application. The process is entirely automated and includes support for multilingual links.
* - Documentation : [sitemap.md](./sitemap.md)
* 
* ## Smart services (AI-powered services)
* ### Translation
* The Sonata Extra Bundle provides a powerful feature for automatic translation through its [Smart service](smart-service.md) functionality. This feature allows users to effortlessly translate text between different languages using AI-powered translation providers.
* - Documentation : [smart-service.md](./smart-service.md)
* ### Translation CMD
* A command to automate the translation of a selected language for an admin object.
* - Documentation : [translation-cmd.md](./translation-cmd.md)
* ### Translation template
* - Documentation : [translation-cmd-create-template.md](./translation-cmd-create-template.md)
* 
* ## WORDPRESS-IMPORT
* The sonata-extra:wordpress-importer command is designed for importing images, categories, posts, and tags from a WordPress site into your application. This command is key for integrating WordPress content with ease.
* - Documentation : [wordPress-import.md](./wordPress-import.md)
* - Import cmd :
* ![wordpress_import.png](./doc-sonata-extra-images/wordpress_import.png)
* 
* ## Blocks
* ### Cookie Consent Block (GDPR)
* The Cookie Consent Block is a customizable solution integrated into the Sonata Extra Bundle to manage user consent for cookies in compliance with GDPR regulations. It offers a flexible and user-friendly interface for cookie consent management.
* - Documentation : [cookie-consent-block.md](./cookie-consent-block.md)
* ![cookie-consent-block.png](./doc-sonata-extra-images/cookie-consent-block.png)
* ![cookie-consent-block_btn.png](./doc-sonata-extra-images/cookie-consent-block_btn.png)
* 
* ## Form types
* ### GUTENBERG EDITOR
* Integrating the Gutenberg editor into Sonata involves using the 'Automatic Isolated block editor' library. This guide details the steps needed to install and configure the Gutenberg editor in your Sonata application.
* - Documentation : [form-type-gutenberg.md](./form-type-gutenberg.md)
* ![Gutenberg_FormType_1.png](./doc-sonata-extra-images/Gutenberg_FormType_1.png)
* ![Gutenberg_FormType_2.png](./doc-sonata-extra-images/Gutenberg_FormType_2.png)
* ![Gutenberg_FormType_3.png](./doc-sonata-extra-images/Gutenberg_FormType_3.png)
* ![Gutenberg_FormType_4.png](./doc-sonata-extra-images/Gutenberg_FormType_4.png)
* ![Gutenberg_FormType_5.png](./doc-sonata-extra-images/Gutenberg_FormType_5.png)
* ![Gutenberg_FormType_6.png](./doc-sonata-extra-images/Gutenberg_FormType_6.png)
* ![Gutenberg_FormType_7.png](./doc-sonata-extra-images/Gutenberg_FormType_7.png)
* 
* ### Slider Manager with Sonata Page Block
* - **Preview**: ![Slider Manager Preview](./doc-sonata-extra-images/slider-manager-preview.png)
* - Add sliders to Sonata Page with customizable blocks
* 
* ### FAQ Manager with Sonata Page Block
* - Easily integrate FAQs into your Sonata Pages
* - **FAQ list**: ![faq_list.png](./doc-sonata-extra-images/faq_list.png)
* - **Questions**: ![faq_questions.png](./doc-sonata-extra-images/faq_questions.png)
* - **Block**: ![faq_block.png](./doc-sonata-extra-images/faq_block.png)
* - **Page edition**: ![faq_page.png](./doc-sonata-extra-images/faq_page.png)
* 
* 
* 
* 
* ### Sonata Page Block Manager
* - Hide undesired blocks in Sonata Page block manager
* - **Preview**: ![Block Manager Preview](./doc-sonata-extra-images/block-manager-preview.png)
* 
* 
* ### Article Manager with Gutenberg Editor
* - Write articles using the Gutenberg editor
* - **Gutemberg editor**: ![article_editor_gutemberg.png](./doc-sonata-extra-images/article_editor_gutemberg.png)
* - **Editor selector**: ![article_editor_selector.png](./doc-sonata-extra-images/article_editor_selector.png)
* 
* 
* ### Sonata Admin FormTypes
* - Additional FormTypes for Sonata Admin
* - **Gutemberg Editor**: ![article_editor_gutemberg.png](./doc-sonata-extra-images/article_editor_gutemberg.png)
* - **Markdown editor**: ![Markdown_editor.png](./doc-sonata-extra-images/Markdown_editor.png)
* 
* ### Gutenberg Editor with Advanced Features
* - WordPress's Gutenberg editor with file upload, custom patterns, and SonataMedia gallery
* - Editor view :![Gutenberg Preview](./doc-sonata-extra-images/Gutenberg_FormType_7.png)
* - Blocks selection : ![Gutenberg Preview](./doc-sonata-extra-images/Gutenberg_FormType_1.png)
* - Patterns selection : ![Gutenberg Preview](./doc-sonata-extra-images/Gutenberg_FormType_5.png)
* - Media library : ![Gutenberg Preview](./doc-sonata-extra-images/Gutenberg_FormType_3.png)
* - Media library expended view: ![Gutenberg Preview](./doc-sonata-extra-images/Gutenberg_FormType_4.png)
* 
* 
* 
* ### CKEditor Gallery View
* 
* - View galleries in CKEditor with improved UI
* - Select media  :![Gutenberg Preview](./doc-sonata-extra-images/CkEditor_2.png)
* - Media brwoser window : ![Gutenberg Preview](./doc-sonata-extra-images/CkEditor_1.png)
* - **Preview**: ![CKEditor Gallery View Preview](./doc-sonata-extra-images/CkEditor_2.png)
* 
* ### Automatic WordPress Importer
* - **Code Preview**: ![Wordpress_import.png](./doc-sonata-extra-images/wordpress_import.png)
* - documentation : [wordPress_import.md](WordPress_import.md)
* 
* 
* 
* ---