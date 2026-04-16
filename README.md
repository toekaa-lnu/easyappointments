# Linnaeus University fork of EasyAppointments

This is the Linnaeus University ("Lnu") Library fork of the Easy!Appointments booking application. This README file is mainly for publishing and describing the changes made by Lnu to the original repository. If you're looking for the original Easy!Appointments README file, it can be found here: [README_ORIG.md](README_ORIG.md).

EasyAppointments is currently used at Lnu by our academic writing tutors so that students can book a tutoring session for academic writing, presentation skills or study skills in Swedish and English. Our talking book support team are also planning to take it into use. We have implemented many changes requested by these teams, and the purpose of this README is to document those changes so that others may benefit of what we have done. Hopefully someone else will find these useful too.

Here is a list of changes we have done so far:

1. **[Improvements to Custom Fields](#1-improvements-to-custom-fields)**
2. **[Support for Attached Files](#2-support-for-attached-files)**
3. **[Hide Provider Selection](#3-hide-provider-selection)**
4. **[Unit selection for booking advance timeout](#4-unit-selection-for-booking-advance-timeout)**
5. **[Cooldown period for services](#5-cooldown-period-for-services)**
6. **[Hide Timezone from Customers](#6-hide-timezone-from-customers)**
7. **[Customer Booking Limits](#7-customer-booking-limits)**
8. **[Availability Marking in Calendar View](#8-availability-marking-in-calendar-view)**
9. **[Providers Can Access All Bookings](#9-providers-can-access-all-bookings)**
10. **[Provider Colour in Appointments](#10-provider-colour-in-appointments)**
11. **[Services in Current Language First](#11-services-in-current-language-first)**
12. **[Custom messages during booking](#12-custom-messages-during-booking)**

These changes are implemented on top of EasyAppointments 1.5.2, but can probably be integrated with any version from 1.5 or later. Just follow the *How to Add This to Your Build* section under each topic and it should be fine.

There are some generic [General Merge and Migration instructions](#general-merge-and-migration-instructions) too but they're probably a bit confusing if you just read them without any context of what you're merging and migrating.



## 1. Improvements to Custom Fields


We have made many improvements to the basic custom fields implementation in EasyAppointments. The custom fields are the extra fields that you can add to the customer information form, normally on the third page of the booking process. They are configured under *Admin > Settings > Booking Settings*. These are the improvements we have made:

* **[Configurable max number of custom fields](#11-configurable-max-number-of-custom-fields)** : You can have more than the usual five custom fields, should you need them.
* **[Appointment-specific custom fields](#12-appointment-specific-custom-fields)** : The existing custom fields are user-specific, and updated every time the same user makes a new appointment. This is a whole new set of appointment-specific custom fields, so that different appointments from the same user can have their own values.
* **[Translation of field labels and values](#13-translation-of-field-labels-and-values)** : This adds translation support to the custom fields. You can use ID:s in the language translation tables instead of just plain text.
* **[Support for additional input types](#14-support-for-additional-input-types)** : The standard custom fields supported only text. Now you can have numbers, drop down menus, checkboxes, radio buttons and so on.
* **[Support for HTML attributes](#15-support-for-html-attributes)** : You can specify HTML attributes for the custom fields, such as min/max values for numbers, and placeholders for text.
* **[Options for select drop-down menus](#16-options-for-select-drop-down-menus)** : Options for the the drop down menus (HTML "select" element) can be configured in a flexible way using the language translation files.
* **[Handling groups of checkboxes and radio buttons](#17-handling-groups-of-checkboxes-and-radio-buttons)** : Groups of checkboxes and radio buttons can be handled in a similar way to the drop down menus.

There is some configuration involved when taking this change into use so please also read the **[How to Add This to Your Build](#18-how-to-add-this-to-your-build)** section.


### 1.1. Configurable Max Number of Custom Fields

The number of custom fields is now configurable in the `config.php` file (in the root folder).
```php
  const MAX_CUSTOM_FIELDS = 5;
```
This value affects the existing custom fields, where the maximum number has so far been 5, but you can change it to something higher if you think that you may need more custom fields at some point.

If you change this, make sure you do it **before** running the migration command to update the database tables. See **[How to Add This to Your Build](#18-how-to-add-this-to-your-build)** for more info.

These custom fields are customer-specific, and stored in the `ea_users` table in the Easy!Appointments database. This means that they are unique to each user, and shared between all the same users appointments. This is fine for fields such as *address* and *age* which are properties of the user, however can't be used for custom fields that need to be different for each booking.


### 1.2. Appointment-specific Custom Fields 

Since the existing custom fields are updated every time the a user makes a new booking, there is sometimes a need for appointment-specific custom fields. These are stored in the `ea_appointments` table in the Easy!Appointments database, so they are unique for each booking. This makes it possible to add fields such as *number of participants* or *appointment notes*.

Like the customer-specific custom fields, the max number of appointment-specific custom fields is also configurable in the `config.php` file (in the root folder).
```php
  const MAX_APPOINTMENT_CUSTOM_FIELDS = 5;
```
Make sure you change this **before** running the migration command to update the database tables. See **[How to Add This to Your Build](#18-how-to-add-this-to-your-build)** for more info.

The new appointment-specific custom fields will appear in the *Admin > Settings > Booking Settings* UI, with the same functionality as the existing custom fields.


### 1.3. Translation of Field Labels and Values

You can still write plain text as the label of the custom fields as before, but you can now also type in an ID defined in the translation files. This makes it possible to have the label translated to different languages in the booking UI.

Let's assume that you have the following entries in the language translation files.

```php
# In application/language/english/translations_lang.php:
$lang['label_age'] = 'Age';

# In application/language/swedish/translations_lang.php:
$lang['label_age'] = 'Ålder';
```

You can now type in `label_age` as the label in a custom field:
```json
label_age
```

When the booking UI is in English the label will say *Age* and when in Swedish it will say *Ålder*.


### 1.4. Support for Additional Input Types

The existing custom fields are limited to just `text` inputs types. Now you can add an optional configration block to make it possible to use other types of input fields. The configuration block is added to the label field, after the label text itself. For example, you can type the following into the label field:
```json
label_age {"type":"number"}
```
Here the label text is `label_age` and the configuration block `{"type":"number"}`. If you have some experience in coding you may recognize the configuration block as a JSON structure, but that's not really important. The important bits to know are that the configuration starts with a curly brace `{` and ends with a curly brace `}`, and in between those curly braces it has a key-value pair (both key and value are in quotation marks `"`, with a colon `:` between them). In the example above the key is `type` and value is `number`, which means that the type of this custom field should be number.

The `number` type is one of the standard HTML input types. When using this type the input to restricted to only digits. The custom field will also have up/down arrows so that the value can be changed with the mouse.

This is just one of many types that can be used. The following HTML input types are currently supported:
* `checkbox` (see [Handling groups of checkboxes and radio buttons](#17-handling-groups-of-checkboxes-and-radio-buttons))
* `color`
* `date`
* `email`
* `month`
* `number` (see [Support for HTML attributes](#15-support-for-html-attributes))
* `password`
* `radio` (see [Handling groups of checkboxes and radio buttons](#17-handling-groups-of-checkboxes-and-radio-buttons))
* `range` (see [Support for HTML attributes](#15-support-for-html-attributes))
* `tel`
* `text`
* `time`
* `url`
* `week`

In addition to the HTML input types, the following form elements can also be given as `type`:
* `textarea` (see [Support for HTML attributes](#15-support-for-html-attributes))
* `select` (see [Options for select drop-down menus](#16-options-for-select-drop-down-menus))


### 1.5. Support for HTML Attributes

The configuration is not limited to just specifying the `type` of the custom field. You can have other key/value pairs in the same configuration, just separate them with a comma `,`. For example, you can define a placeholder for text fields, or default values for number fields. See the descriptions and examples below.

With the `attributes` key you can directly enter the attributes for the HTML input element. This is useful for example when setting min/max values for number inputs, or number of rows for a textarea.

For example, to set limits and initial value for a `number` input, you can enter this into the label field:
```json
label_age {"type":"number","attributes":"min=18 max=99 value=18"}
```

To set a number of rows for a `textarea`, and the maximum number of characters entered:
```json
label_profession {"type":"textarea","attributes":"rows=4 maxLength=500"}
```

The `attributes` key can be used to enter more or less anything you want directly as attributes in the HTML element. **Be careful not to enter anything that might break the HTML code, such as a `>` tag ending character.**

One HTML attribute that has its own key/value pair is the `placeholder`. It is used to show a prompt or initial value in a `text` or `textarea` custom field, and will automatically disappear when the user starts typing into the field.

The reason for having `placeholder` as its own key/value pair in the configration block is to make it possible to use a translation ID as the placeholder and then get it translated to different languages in the UI. Of course you can use it just with plain text too, if you don't need any translations.

For example, to define the label and the `placeholder` for a `text` type using plain text:
```json
Your Profession {"type":"text","placeholder":"Please enter your current profession"}
```

To use translation ID:s instead of plain text, and `textarea` type instead of `text`:
```json
label_profession {"type":"textarea","placeholder":"placeholder_profession"}
```

For the latter example to work, you of course need to have the ID `placeholder_profession` defined in the translation file. For example, you can have the following entry in `application/language/english/translations_lang.php`:
```php
$lang['placeholder_profession'] = 'Please enter your current profession';
```


### 1.6. Options for Select Drop-down Menus

When the `type` of the custom field is `select`, a drop down menu is created with a number of options to choose from. The options are created automatically based on the translation ID used as the label.

Given the following entries in a translation file (eg. `application/language/english/translations_lang.php`):
```php
$lang['custom_field_pulldown_menu'] = 'Pulldown menu custom field';
$lang['custom_field_pulldown_menu_prompt'] = 'Select option from menu...';
$lang['custom_field_pulldown_menu_1'] = 'First option';
$lang['custom_field_pulldown_menu_2'] = 'Second option';
$lang['custom_field_pulldown_menu_3'] = 'Third option';
$lang['custom_field_pulldown_menu_last'] = 'Other';
```

A dropdown menu custom field can then be defined as follows:
```json
custom_field_pulldown_menu {"type":"select", "sort":"false"}
```
The ID `custom_field_pulldown_menu` (with the translation *Pulldown menu custom field*) is used as the label. This ID is then used as a base ID for the options. The options are automatically created based on other ID:s having the same beginning as the base ID:

1. If there is an ID with `_prompt` added to the base ID, it is used as the initial value in the menu, but it is not selectable. It is just used for prompting the user to select a value from the menu. If there is no such ID, the first option is used instead as the initial value.

2. The actual options are created in the similar way. The ID of the first option has `_1` added to the base ID, the second option `_2` and so on. The number of options is automatically detected based on the ID:s defined in the translation files.

    If the configuration includes `"sort":"true"`, the list of options is sorted according to the translations of the current language.

3. Finally, if there is an ID with `_last` at the end of the base ID, it is added as the last item (regardless of the sorting of the other options). This is convenient to use for options such as *Other* or *Don't know*.

Note that using translation ID:s is required for this to work, so you can't just enter plain text as the label of a `select` custom field.


### 1.7. Handling Groups of Checkboxes and Radio Buttons

Handling a group of checkboxes and radio buttons is similar to how options for a select drop-down menu are handled.

Say that you have the following entries in a translation file (eg. `application/language/english/translations_lang.php`):
```php
$lang['custom_field_fruit'] = 'Fruit';
$lang['custom_field_fruit_1'] = 'Banana';
$lang['custom_field_fruit_2'] = 'Apple';
$lang['custom_field_fruit_3'] = 'Pear';
$lang['custom_field_fruit_last'] = 'None of the above';
```

A group of radio buttons can then be defined as a custom field as follows:
```json
custom_field_fruit {"type":"radio", "sort":"true"}
```

A group of checkboxes can be defined in almost the same way:
```json
custom_field_fruit {"type":"checkbox", "sort":"true"}
```

The difference between radio buttons and checkboxes is that you can select several checkboxes in a group while only one radio button in a group can be selected.

Just like with [options for select drop-down menus](#16-options-for-select-drop-down-menus), the number of checkboxes and radio buttons is automatically detected based on the ID:s in the translation files. Use the ID for the custom field label as a base ID, and then add `_1`, `_2`, `_3` (and so on) to the end of the base ID to define the items. There is a `_last` item too, but no `_prompt` item (not really needed, as all the items are visible at all times).


### 1.8. How to Add This to Your Build

First of all you need to merge the commits of this change to your build. You can do this with the following commands:
```bash
git cherry-pick 1e83303519115acf3dd56259dd03c3569b05ea76
git cherry-pick 32a8daf05fe9c310b98ed41102857261ed766183
```

If you want to look at the code changes in these commits, they can be found here:
* Main commit: [1e83303519115acf3dd56259dd03c3569b05ea76](https://gitlab.com/lnu-ub/easyappointments_fork/-/commit/1e83303519115acf3dd56259dd03c3569b05ea76)
* Bug fix commit: [32a8daf05fe9c310b98ed41102857261ed766183](https://github.com/toekaa-lnu/easyappointments/commit/32a8daf05fe9c310b98ed41102857261ed766183)


Updates are required to the database tables, so you need to run some migration scripts. 

However, before running the scripts, think about the maximum number of custom fields that you want to have. The default is 5 both for existing customer-specific custom fields and new appointment-specific custom fields, but if you think that you may need more (now or in the future), you can update it to something higher. This just sets a maximum limit: each custom field can be enabled/disabled in the UI so you don't have to have them all active at the same time. If you're happy with the default values, you don't need to add anything.

To change the defaults, add the following lines the `config.php` file in the root folder (and change the value 5 to your chosen max limit): 
```php
const MAX_CUSTOM_FIELDS = 5;
const MAX_APPOINTMENT_CUSTOM_FIELDS = 5;
```

The migration scripts read this value in order to add the preferred number of fields to the database tables, so that's why they need to be set before running the scripts.

After that, you're ready to proceed with the migration. This commit includes four migration script files (in the `application/migrations/` folder) to update the database to support the improvements to the custom fields: 
```
061_update_custom_fields_columns_to_users_table.php
062_update_custom_field_rows_to_settings_table.php
063_add_appt_custom_field_columns_to_appointments_table.php
064_insert_appt_custom_field_rows_to_settings_table.php
```

If you are not starting with EasyAppointments version 1.5.2, or have added some other commits to your build, chances are that you may need to rename these files so that the script files in the `application/migrations/` folder are numbered in sequence, without any gaps or overlapping numbers.

After doing that you can run the following command in the root folder of your build, to update the database tables:
```bash
php index.php console migrate
```

For more detailed information, see the [General Merge and Migration instructions](#general-merge-and-migration-instructions).




## 2. Support for Attached Files

This improvement makes it possible for customers to attach files to the booking.

A number of settings are added to *Admin > Settings > Booking Settings* to configure this feature:
* **Attached Files** : This is the main switch to toggle support for attached files on and off. When activated, the following subsettings become available:
* **Maximum number of files** : The customer can add up to the maximum number of files when creating a booking. When updating an existing booking, the customer can attach new files and discard previously attached (provided that the total max number is not exceeded). The provider can add and discard attached files in a similar way when adding or editing a booking in the calendar view in the admin panel.
* **Maximum Size of a File (bytes)** : Set the maximum size for one attached file. The check is done in the backend when confirming the booking.
* **Allowed File Types** : This is a comma-separated list of file extensions and mime types, and it defines the types of files that the customer is allowed to attach. The check is done with extension when attaching the file in the frontend. Another (more reliable) check is done with mime types in the backend when confirming the booking. The backend check is converting the file extensions to mime types too. 
* **Description of Allowed File Types to Customer** : Here you can set the text shown to the user in the booking form about allowed file types. The same text is also used as part of the error message if an unallowed file type is attached. This can be an ID into the translation files (under `application/language/*/translations_lang.php`), or simply a plain text string if you don't need any translations.

The emails that are sent after creating or editing a booking include information about the attached files, but the actual files themselves are not attached.

There is some configuration involved when taking this change into use so please also read the **[How to Add This to Your Build](#21-how-to-add-this-to-your-build)** section below.

### 2.1. How to Add This to Your Build

First of all you need to merge the commits of this change to your build. You can do this with the following commands:
```bash
git cherry-pick de4dec2da22f68e7f48f7e0bb89cb2065fab83a6
git cherry-pick fe04ec19f995a98dac586bc807489a72f440e249
```

If you want to look at the code changes in these commits, they can be found here:
* Main commit: [de4dec2da22f68e7f48f7e0bb89cb2065fab83a6](https://github.com/alextselegidis/easyappointments/commit/de4dec2da22f68e7f48f7e0bb89cb2065fab83a6)
* Bug fix commit: [fe04ec19f995a98dac586bc807489a72f440e249](https://github.com/alextselegidis/easyappointments/commit/fe04ec19f995a98dac586bc807489a72f440e249)


Updates are required to the database tables, so you need to run some migration scripts. 

However, before running the scripts, you may want to check the configuration related to attached files. In the `config.php` file in the root folder you can set the following values: 
```php
const MAX_ATTACHED_FILES = 5;
const ATTACHED_FILES_MAX_SIZE = 8000000; // Max size for one file in bytes
const ATTACHED_FILES_ALLOWED_TYPES = '.doc,.docx,.xml,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document';
const ATTACHED_FILES_ALLOWED_TYPES_HINT = 'attached_files_user_allowed_types_hint';
```
These are just default values -- an administrator can change these later under *Admin > Settings > Booking Settings*.

* `MAX_ATTACHED_FILES` is an upper limit, so an administrator can't set it to something higher (only lower).
* `ATTACHED_FILES_MAX_SIZE` is the max size of one file in bytes. Note that the PHP system also has its own `upload_max_filesize` configuration in the `php.ini` file.
* `ATTACHED_FILES_ALLOWED_TYPES` is a comma-separated list of file extensions and mime types. It is used both in the file picker in the frontend and in the backend when uploading the file.
* `ATTACHED_FILES_ALLOWED_TYPES_HINT` is a text shown to the user in the booking form, and also as an error message if the file type is not one of the allowed types. It can be both a plain text string, or an ID in the language translation files.

If you don't set these, default values are used.

The `attached_files_user_allowed_types_hint` is defined in the `application/language/english/translations_lang.php` language file:
```php
$lang['attached_files_user_allowed_types_hint'] = "Only Microsoft Word files (.doc, .docx) are accepted.";
```
If you change the allowed file extensions and mime types, you may want to change this translation too so that it matches the allowed types.

After that, you're ready to proceed with the migration. The main commit includes two migration script files (in the `application/migrations/` folder) to update the database to support the improvements to the attached files: 
```
065_add_attached_files_column_to_appointments_table.php
066_insert_attached_files_rows_to_settings_table.php
```

If you are not starting with EasyAppointments version 1.5.2, or have added some other commits to your build, chances are that you may need to rename these files so that the script files in the `application/migrations/` folder are numbered in sequence, without any gaps or overlapping numbers.

After doing that you can run the following command in the root folder of your build, to update the database tables:
```bash
php index.php console migrate
```

For more detailed information, see the [General Merge and Migration instructions](#general-merge-and-migration-instructions).



## 3. Hide Provider Selection

This change makes it possible to hide the provider selection from customers in the booking UI, so that only the service needs to be selected.

This can be configured with two new settings under *Admin > Settings > Booking Settings*, added as subsettings under the existing *Any Provider* setting:
* **Hide provider selection** : Activating this setting will hide the *Provider* selection completely from the booking wizard. The effect is the same as always selecting the *Any provider* setting if there's multiple providers for a service (or the one and only provider if only one is available).
* **Provider selection method** : A new algorithm is added for selecting the provider when *Any provider* is selected. The legacy one (now selectable as *Available on date* in the Settings UI) was only looking at the date of the new booking, and selected the provider that had the most available periods on that date. The new algorithm *Available around booking* also looks at dates around the new booking, and selects the provider with the longest availability around the new booking. This works better when providers only have at most one or two available timeslots per day.

When the booking is confirmed, a provider is selected by the algorithm and assigned to the appointment. The e-mail sent to the customer includes the name of the selected provider.

> For a more detailed description, the new *Available around booking* algorithm uses the following steps to select a provider:
> 1. It gets a list of providers available for the selected date and time.
> 2. For each of these providers, it chooses their existing appointment which is closest in time to the new booking.
> 3. Among all these closest appointments, it finds the provider having the appointment furthest away from the new booking.
>
> The goal is to distribute the bookings among the providers as evenly as possible over time. Of course the algorithm is only used when there actually are multiple providers available for a booked timeslot.


### 3.1. How to Add This to Your Build

First of all you need to merge the commits of this change to your build. You can do this with the following commands:
```bash
git cherry-pick 6827050704896c109612840ef96012e68f82b5b1
git cherry-pick 841aa8e3b7d5fc1b465805687f5ab81733bf6ba8
```

If you want to look at the code changes in these commits, they can be found here:
* Main commit: [6827050704896c109612840ef96012e68f82b5b1](https://github.com/alextselegidis/easyappointments/commit/6827050704896c109612840ef96012e68f82b5b1)
* Bug fix commit: [841aa8e3b7d5fc1b465805687f5ab81733bf6ba8](https://github.com/alextselegidis/easyappointments/commit/841aa8e3b7d5fc1b465805687f5ab81733bf6ba8)

These commits are built on top of some functionality added in **[Support for Attached Files](#2-support-for-attached-files)** (support for subsettings in *Admin > Settings > Booking Settings*), so you'll need to add the commit(s) from that change too.

After merging these commits to your build, you can proceed with the migration. The main commit includes a migration script file (in the `application/migrations/` folder) to make the needed changes to the database tables:
```
067_insert_hide_providers_rows_to_settings_table.php
```

If you are not starting with EasyAppointments version 1.5.2, or have added some other commits to your build, chances are that you may need to rename this file so that the script files in the `application/migrations/` folder are numbered in sequence, without any gaps or overlapping numbers.

After doing that you can run the following command in the root folder of your build, to update the database tables:
```bash
php index.php console migrate
```

For more detailed information, see the [General Merge and Migration instructions](#general-merge-and-migration-instructions).



## 4. Unit Selection for Booking Advance Timeout

When configuring the booking advance timeout (aka. booking lead time), a time unit can now also be specified. The existing implementation only supported minutes, but now eg. weekdays can be used.

A new **Booking lead time unit** setting is added in *Admin > Settings > Business Logic*, under the existing *Allow Rescheduling/Cancellation Before* setting. Now it is possible to select the time unit from the following options:
* minutes
* hours
* days
* weekdays


### 4.1. How to Add This to Your Build

First of all you need to merge the commits of this change to your build. You can do this with the following commands:
```bash
git cherry-pick 6e3bd489863901ee4597d0e2cc236bd0b62ebde3
git cherry-pick 4f0b8a74f327bec1e519929d2101adfe6a087e38
```

If you want to look at the code changes in these commits, they can be found here:
* Main commit: [6e3bd489863901ee4597d0e2cc236bd0b62ebde3](https://github.com/alextselegidis/easyappointments/commit/6e3bd489863901ee4597d0e2cc236bd0b62ebde3)
* Bug fix commit: [4f0b8a74f327bec1e519929d2101adfe6a087e38](https://github.com/alextselegidis/easyappointments/commit/4f0b8a74f327bec1e519929d2101adfe6a087e38)

After merging these commits to your build, you can proceed with the migration. This commit includes a migration script file (in the `application/migrations/` folder) to make the needed changes to the database tables:
```
068_insert_book_advance_timeout_unit_rows_to_settings_table.php
```

If you are not starting with EasyAppointments version 1.5.2, or have added some other commits to your build, chances are that you may need to rename these files so that the script files in the `application/migrations/` folder are numbered in sequence, without any gaps or overlapping numbers.

After doing that you can run the following command in the root folder of your build, to update the database tables:
```bash
php index.php console migrate
```

For more detailed information, see the [General Merge and Migration instructions](#general-merge-and-migration-instructions).



## 5. Cooldown period for services

A new **Cooldown** setting is added for each service under *Admin > Services*. The cooldown is an extra period of time right after the appointment, where the provider can wrap up the meeting (make notes, have a cup of coffee, go to toilet...). The cooldown period is automatically added to the duration of the booking, but not communicated to the customer. Customers are not able to book another appointment during the cooldown period.

For example, when the customer books a service with a 30 minute duration and 15 minute cooldown, the duration communicated to the customer is 30 minutes (in the booking UI service selection and confirmation screens, and in the confirmation email), but the appointment occupies a 45-minute timeslot in the calendar. 


### 5.1. How to Add This to Your Build

First of all you need to merge the commit of this change to your build. You can do this with the following command:
```bash
git cherry-pick 89a56da0c98b52824c7574fb9e526c5a444a13c6
```

If you want to look at the code changes in this commit, they can be found here:
* Main commit: [89a56da0c98b52824c7574fb9e526c5a444a13c6](https://github.com/alextselegidis/easyappointments/commit/89a56da0c98b52824c7574fb9e526c5a444a13c6)

After merging these commits to your build, you can proceed with the migration. This commit includes a migration script file (in the `application/migrations/` folder) to make the needed changes to the database tables:
```
069_insert_cooldown_column_to_services_table.php
```

If you are not starting with EasyAppointments version 1.5.2, or have added some other commits to your build, chances are that you may need to rename these files so that the script files in the `application/migrations/` folder are numbered in sequence, without any gaps or overlapping numbers.

After doing that you can run the following command in the root folder of your build, to update the database tables:
```bash
php index.php console migrate
```

For more detailed information, see the [General Merge and Migration instructions](#general-merge-and-migration-instructions).



## 6. Hide Timezone from Customers

A new setting **Hide Customer Timezone** is added under *Admin > Settings > Booking Settings*. When activated, customers are no longer able to change (or even see) the timezone selection in the booking UI. Instead, the *Default Timezone* (as defined under *Admin > Settings > General Settings*) is always used for new bookings.

The timezone is hidden from the date/time selection and confirmation steps in the booking UI, as well as the email sent out for saved and deleted appointments. Providers are still able to view (and change) their timezone under *Users > Providers*, and when accessing bookings via the calendar page.


### 6.1. How to Add This to Your Build

First of all you need to merge the commit of this change to your build. You can do this with the following command:
```bash
git cherry-pick e602d892d05aa9f321725ec8555afdc85b8ae0c1
```

If you want to look at the code changes in this commit, they can be found here:
* Main commit: [e602d892d05aa9f321725ec8555afdc85b8ae0c1](https://github.com/alextselegidis/easyappointments/commit/e602d892d05aa9f321725ec8555afdc85b8ae0c1)

After merging this commit to your build, you can proceed with the migration. This commit includes a migration script file (in the `application/migrations/` folder) to make the needed changes to the database tables:
```
070_insert_hide_timezone_row_to_settings_table.php
```

If you are not starting with EasyAppointments version 1.5.2, or have added some other commits to your build, chances are that you may need to rename these files so that the script files in the `application/migrations/` folder are numbered in sequence, without any gaps or overlapping numbers.

After doing that you can run the following command in the root folder of your build, to update the database tables:
```bash
php index.php console migrate
```

For more detailed information, see the [General Merge and Migration instructions](#general-merge-and-migration-instructions).



## 7. Customer Booking Limits

With this change it is possible to set limits to how many bookings each customer can make.

Two new *Customer Booking Limit* settings are added under *Admin > Settings > Business Logic*. The new settings control how many appointments each customer is allowed to book during a time period. There is also a setting for selecting the time unit.

The new settings are:
* **Maximum number of appointments** : Controls the total number of appointments (past, present and future) that a customer is allowed to make during the selected time period. These appointments can be for different services.
* **Active bookings per service** : Controls how many active (ongoing and future) bookings a customer can have for each service, during the selected time period.
* **Time period for customer booking limits** controls what time period is used for the above two settings. You can set it to one of the following:
  * Day
  * Week
  * Month
  * Half-year (with two distinct periods January..June and July..December)
  * Calendar year (with the period January..December)
  * School year (with the period July..June)

The limits are reset at the start of each new time period, so if you choose eg. *Day* as the time period, then the customer is allowed to book the selected number of appointments each day.

In EasyAppointments, a customer is identified by the email address.


### 7.1. How to Add This to Your Build

First of all you need to merge the commits of this change to your build. You can do this with the following commands:

```bash
git cherry-pick f904c6143e23437ff967204622c1723f773e03ce
git cherry-pick 5f8d51835b0279734f038a00206e6ae2dc976e1e
```

If you want to look at the code changes in these commits, they can be found here:
* Main commit: [f904c6143e23437ff967204622c1723f773e03ce](https://github.com/alextselegidis/easyappointments/commit/f904c6143e23437ff967204622c1723f773e03ce)
* Bug fix commit: [5f8d51835b0279734f038a00206e6ae2dc976e1e](https://github.com/alextselegidis/easyappointments/commit/5f8d51835b0279734f038a00206e6ae2dc976e1e)

After merging these commits to your build, you can proceed with the migration. The main commit includes a migration script file (in the `application/migrations/` folder) to make the needed changes to the database tables:
```
071_insert_customer_booking_limit_rows_to_settings_table.php
```

If you are not starting with EasyAppointments version 1.5.2, or have added some other commits to your build, chances are that you may need to rename these files so that the script files in the `application/migrations/` folder are numbered in sequence, without any gaps or overlapping numbers.

After doing that you can run the following command in the root folder of your build, to update the database tables:
```bash
php index.php console migrate
```

For more detailed information, see the [General Merge and Migration instructions](#general-merge-and-migration-instructions).



## 8. Availability Marking in Calendar View

This improvement adds the possibility to mark *Availability* in the *Admin > Calendar*, in the same way that *Unavailability* or *Appointment* is marked. Just use the mouse to select a timespan and select *Availability* in the popup that appears.

A *Working Plan Exception* is created behind the scenes, with the selected timespan marked as available. It is possible to select multiple timespans and any existing Working Plan Exception is adjusted accordingly, with breaks added between availabilities as needed.

This works best for for providers who want to have a totally closed calendar as a starting point and then just add available slots manually.


### 8.1. How to Add This to Your Build

To merge the commit of this change to your build, use the following command:

```bash
git cherry-pick 5636bb8c006c6c4fc9066f5237dca1bd7ae3d728
```

If you want to look at the code changes in this commit, they can be found here:
* Main commit: [5636bb8c006c6c4fc9066f5237dca1bd7ae3d728](https://github.com/alextselegidis/easyappointments/commit/5636bb8c006c6c4fc9066f5237dca1bd7ae3d728)

For more detailed information, see the [General Merge and Migration instructions](#general-merge-and-migration-instructions).



## 9. Providers Can Access All Bookings

A new setting **Providers Can Access All Bookings** is added under *Admin > Settings > Booking Settings*. When this setting is enabled, providers can access (view, edit and delete) also each other's bookings in the calendar view. By default, providers are only able to access their own bookings.

This is handy eg. in case of sickness, when one provider needs to take over an existing booking assigned to another provider.


### 9.1. How to Add This to Your Build

First of all you need to merge the commit of this change to your build. You can do this with the following command:

```bash
git cherry-pick 6c88a389e3aea93465d20cc9961b6a9fc7156214
```

If you want to look at the code changes in this commit, they can be found here:
* Main commit: [6c88a389e3aea93465d20cc9961b6a9fc7156214](https://github.com/alextselegidis/easyappointments/commit/6c88a389e3aea93465d20cc9961b6a9fc7156214)

After merging this commit to your build, you can proceed with the migration. This commit includes a migration script file (in the `application/migrations/` folder) to make the needed changes to the database tables:
```
072_insert_provider_permission_all_bookings_row_to_settings_table.php
```

If you are not starting with EasyAppointments version 1.5.2, or have added some other commits to your build, chances are that you may need to rename these files so that the script files in the `application/migrations/` folder are numbered in sequence, without any gaps or overlapping numbers.

After doing that you can run the following command in the root folder of your build, to update the database tables:
```bash
php index.php console migrate
```

For more detailed information, see the [General Merge and Migration instructions](#general-merge-and-migration-instructions).



## 10. Provider Colour in Appointments

A new **Color** setting is added for each provider. Each appointment in the calendar view will then be marked with the colour of the provider. The service/appointment colour is still shown as the event background colour, but the provider colour is indicated with a vertical column on the right edge of the appointment. This helps to quickly identify which appointments belong to which providers.

If the provider colour is set as "#e3e3e3" (the last, light-gray, colour in the selector), then the provider colour is not marked on the appointment.


### 10.1. How to Add This to Your Build

First of all you need to merge the commit of this change to your build. You can do this with the following command:

```bash
git cherry-pick b703441bcc2a08288d06200e9013e251c7a406aa
```

If you want to look at the code changes in this commit, they can be found here:
* Main commit: [b703441bcc2a08288d06200e9013e251c7a406aa](https://github.com/alextselegidis/easyappointments/commit/b703441bcc2a08288d06200e9013e251c7a406aa)

After merging this commit to your build, you can proceed with the migration. This commit includes a migration script file (in the `application/migrations/` folder) to make the needed changes to the database tables:
```
073_add_color_column_to_users_table.php
```

If you are not starting with EasyAppointments version 1.5.2, or have added some other commits to your build, chances are that you may need to rename these files so that the script files in the `application/migrations/` folder are numbered in sequence, without any gaps or overlapping numbers.

After doing that you can run the following command in the root folder of your build, to update the database tables:
```bash
php index.php console migrate
```

For more detailed information, see the [General Merge and Migration instructions](#general-merge-and-migration-instructions).



## 11. Services in Current Language First

This improvement modifies the service selection in the booking UI so that it is possible to show the services in the current language on the top of the list. For example, a customer using the booking UI in English would see the services offered in English first, and then services offered in other languages further down. This will make services relevant to the customer easier to access.

For this to work, the service categories need to be setup so that they match the language names. If you want to use service categories for some other purpose, then this solution will not work for you.

A new setting **Services in Current Language Shown First** is added under *Admin > Settings > Booking Settings* for activating/deactivating this feature.


### 11.1. How to Add This to Your Build

First of all you need to merge the commit of this change to your build. You can do this with the following command:

```bash
git cherry-pick 7f3c2860e69311f595cc21613e77fd695bf8c59a
```

If you want to look at the code changes in this commit, they can be found here:
* Main commit: [7f3c2860e69311f595cc21613e77fd695bf8c59a](https://github.com/alextselegidis/easyappointments/commit/7f3c2860e69311f595cc21613e77fd695bf8c59a)

After merging this commit to your build, you can proceed with the migration. This commit includes a migration script file (in the `application/migrations/` folder) to make the needed changes to the database tables:
```
074_insert_current_language_services_first_table.php
```

If you are not starting with EasyAppointments version 1.5.2, or have added some other commits to your build, chances are that you may need to rename these files so that the script files in the `application/migrations/` folder are numbered in sequence, without any gaps or overlapping numbers.

After doing that you can run the following command in the root folder of your build, to update the database tables:
```bash
php index.php console migrate
```

For more detailed information, see the [General Merge and Migration instructions](#general-merge-and-migration-instructions).



## 12. Custom Messages during Booking

Some custom messages can now be shown to the customer during the booking process. There are two custom messages and a custom link:
* **Message on Service/Provider Page**  
This is a message shown on the service/provider selection page (normally the first step of the booking process). It is shown in its own frame with a background colour that makes it stand out from the rest if the page. At Linnaeus University, we use it for informing students with reading/writing difficulties about contacting a special teacher.
* **Message on Date/Time Unavailability**  
This is an extra message shown when the current month has no available time slots. At Linnaeus University we use it to inform students that there migt not be any available timeslots att all and encourage them to check back at a later time.
* **Link on Confirmation Page**  
This is a custom link that is shown on the confirmation page informing the customer that the booking is successful. It replaces the two buttons that are usually shown on that page ("Go to Booking Page" and "Add to Google Calendar"). At Linnaeus University we use it for directing the student to the Academic Skills Centre homepage.
* **Confirmation Page Link Text**  
This is the text displayed for the link "Link on Confirmation Page".

A new setting **Custom Booking Messages/Links** is added under *Admin > Settings > Booking Settings*. When the setting is deactivated, all custom messages are deactivated. If the setting is activated, each message can be activated individually by entering an ID from the language translation files into the text field.

The message is shown only if the ID is defined in the translation file matching the current user language. This way, you can choose to show the messages only in selected languages. If the text field for each setting is left empty, then the message is not shown for any language. Because of this, plain text is not supported for these messages, only language translation ID:s.


### 12.1. How to Add This to Your Build

First of all you need to merge the commit of this change to your build. You can do this with the following command:

```bash
git cherry-pick 07081a67b5a2fba9519d95d6f66af86c03d4fb55
```

If you want to look at the code changes in this commit, they can be found here:
* Main commit: [07081a67b5a2fba9519d95d6f66af86c03d4fb55](https://github.com/alextselegidis/easyappointments/commit/07081a67b5a2fba9519d95d6f66af86c03d4fb55)

After merging this commit to your build, you can proceed with the migration. This commit includes a migration script file (in the `application/migrations/` folder) to make the needed changes to the database tables:
```
075_insert_booking_custom_message_rows_to_settings_table.php
```

If you are not starting with EasyAppointments version 1.5.2, or have added some other commits to your build, chances are that you may need to rename these files so that the script files in the `application/migrations/` folder are numbered in sequence, without any gaps or overlapping numbers.

After doing that you can run the following command in the root folder of your build, to update the database tables:
```bash
php index.php console migrate
```

For more detailed information, see the [General Merge and Migration instructions](#general-merge-and-migration-instructions).



## General Merge and Migration instructions

This code is provided as-is. Use it at your own choice and at your own risk. Having said that, the code is working fine for us and we are not aware of any bugs. This code is published in order to let others benefit from the work we have done, just like we have benefitted from being able to use the original EasyAppointments code.

You are free to take this code into use in your own build and improve and adapt it to your own purposes as you wish. Feel free to ask questions if you run into problems but be aware that we may not always be able to or have time to help.

Like the original EasyAppointments, this code is licensed under [GPL v3.0](https://www.gnu.org/licenses/gpl-3.0.en.html) and content under [CC BY 3.0](https://creativecommons.org/licenses/by/3.0/).

### Code Merge

There are a few different ways to take these changes into use in your own EasyAppointments installation.

1. The fastest way is to add this repository as a remote to your EasyAppointments git repository, fetch the changes in this repository and then cherry-pick the commit(s) for the change that you want to include. Add this repository with these commands: 
    ```bash
    git remote add lnu https://github.com/toekaa-lnu/easyappointments.git
    git fetch lnu
    ```

    Then repeat the `git cherry-pick ...' line for each commit that you want to merge:
    ```
    git cherry-pick <commit-sha>
    git cherry-pick <commit-sha>
    [...]
    git cherry-pick <commit-sha>
    ```
    You can find the commit SHA(s) in the *How to Add This to Your Build* section under each change. 

2. Another way is just to look at the commits in GitHub and see the difference in the code for each included file, and then re-implement the changes in your own EasyAppointments installation. This is maybe initially more time-consuming, but you'll gain a better understaning of the changes and will be able to solve any possible code conflict better, than just doing a merge/cherry-pick with git.


### Database Migration

Most of the changes require updates to the EasyAppointments application database, and this may involve renaming the migration script files and then running a migration command.

After merging a commit to your build, check the `application/migrations/` folder for any new migration script files added to the commit. These files might look eg. like this:
```
065_add_attached_files_column_to_appointments_table.php
066_insert_attached_files_rows_to_settings_table.php
```

Notice the 3-digit numbers prefixed to the file names. The migration script files must be named so that the numbers are in order, without any missing or overlapping numbers. Depending on your current installation (your EasyAppointments version, and possible other commits added on top of that) the new script files might not fit in with your existing script files. If necessary, rename the new migration script files to change the number in the beginning of the files. The new files should be the last ones in the sequence, and make sure that there are no overlaps or gaps in the numbering.

After you have double-checked (and possibly fixed) the migration script numbering, you can run the migration command to update the database to the latest version. Run this in the root folder of the application:
```bash
php index.php console migrate
```

> [!TIP]
> Undoing/Redoing the Migration
>
> Should you want to undo/redo the migration (eg. if a database update is dependent of some configration value that you want to change), it is possible to migrate own and then back up, one step/script at the time. In that case you can first migrate down until you reach the migration script that you want to undo, change a value in `config.php`, and then migrate up until and including the last migration script to redo the changes (but now with the new configuration value):
> ```bash
> # Migrate four steps down
> php index.php console migrate down
> php index.php console migrate down
> php index.php console migrate down
> php index.php console migrate down
>
> # Change a configuration value, eg. MAX_CUSTOM_FIELDS or MAX_APPOINTMENT_CUSTOM_FIELDS
>
> # Migrate four steps up
> php index.php console migrate up
> php index.php console migrate up
> php index.php console migrate up
> php index.php console migrate up
> ```
>
> CAUTION: Unfortunately migrating up/down doesn't tell you which step you are on (unless you try to migrate past the last step), so you need to keep track of this by yourself. **Be careful if you're doing this in a production environment. Each migration down will remove fields from the database (and the data stored in them is lost). If you migrate down too many times, you may accidentally remove fields you didn't intend to, and lose important data from the database.**

