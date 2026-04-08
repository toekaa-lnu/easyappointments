# Linnaeus University fork of EasyAppointments

This is the Linnaeus University ("Lnu") Library fork of the Easy!Appointments booking application. This README file is mainly for publishing and describing the changes made by Lnu to the original repository. If you're looking for the original Easy!Appointments README file, it can be found here: [README_ORIG.md](README_ORIG.md).

EasyAppointments is used at Lnu by our text tutors so that students can book a tutoring session. Also our talking book support team has expressed interest in using it. Both of these teams have requested adaptations which I have implemented (and are continuing to implement). We have tried to make them configurable so that hopefully others will find them useful too.

Here is a list of changes we have done so far. More detailed instructions will be added as we publish each change to GitLab:

1. **[Improvements to Custom Fields](#1-improvements-to-custom-fields)**
    1. [Configuration and Migration](#11-configuration-and-migration)
    2. [Description of the Changes](#12-description-of-the-changes)
        1. [Configurable Max Number of Custom Fields](#121-configurable-max-number-of-custom-fields)
        2. [Appointment-specific Custom Fields](#122-appointment-specific-custom-fields)
        3. [Translation of Field Labels and Values](#123-translation-of-field-labels-and-values)
        4. [Support for Additional Input Types](#124-support-for-additional-input-types)
        5. [Support for HTML Attributes](#125-support-for-html-attributes)
        6. [Options for Select Drop-down Menus](#126-options-for-select-drop-down-menus)
        7. [Handling Groups of Checkboxes and Radio Buttons](#127-handling-groups-of-checkboxes-and-radio-buttons)
2. **[Support for Attached Files](#2-support-for-attached-files)**
    1. [Configuration and Migration](#21-configuration-and-migration)
    2. [Description of the Changes](#22-description-of-the-changes)
3. **[Hide Provider Selection](#3-hide-provider-selection)**
    1. [Configuration and Migration](#31-configuration-and-migration)
    2. [Description of the Changes](#32-description-of-the-changes)
4. **[Unit selection for booking advance timeout](#4-unit-selection-for-booking-advance-timeout)**
    1. [Configuration and Migration](#41-configuration-and-migration)
    2. [Description of the Changes](#42-description-of-the-changes)
5. **[Cooldown period for services](#5-cooldown-period-for-services)**
    1. [Configuration and Migration](#51-configuration-and-migration)
    2. [Description of the Changes](#52-description-of-the-changes)
6. **[Hide Timezone from Customers](#6-hide-timezone-from-customers)**
    1. [Configuration and Migration](#61-configuration-and-migration)
    2. [Description of the Changes](#62-description-of-the-changes)
7. **[Customer Booking Limits](#7-customer-booking-limits)**
    1. [Configuration and Migration](#71-configuration-and-migration)
    2. [Description of the Changes](#72-description-of-the-changes)
8. **[Availability Marking in Calendar View](#8-availability-marking-in-calendar-view)**
    1. [Configuration and Migration](#81-configuration-and-migration)
    2. [Description of the Changes](#82-description-of-the-changes)
9. **[Providers Can Access All Bookings](#9-providers-can-access-all-bookings)**
    1. [Configuration and Migration](#91-configuration-and-migration)
    2. [Description of the Changes](#92-description-of-the-changes)
10. Appointment Colour by Provider in Calendar View
11. Custom message on booking service selection page
12. Adaptive booking UI layout
13. Extra note for booking when no time is available
14. Booking service selection page shows services in current language first
15. Custom link on booking confirmation page
16. Configurable booking step order

Please see the [Merge and Migration instructions](#merge-and-migration-instructions) about how to take these changes into use in your own build.



## Merge and Migration instructions

The usual disclaimer is that this code is provided as-is. To use it is at your own choice and at your own risk. We cannot not held responsible for any damage or loss of data resulting of the use of this code. Having said that, the code is working fine for us and we are not aware of any bugs. Our goal for publishing this code is to let others benefit from the work we have done, just like we have benefitted from being able to use the original EasyAppointments code.

You are free to take this code into use in your own build and improve and adapt it to your own purposes as you wish. Feel free to ask questions if you run into problems but be aware that we may not be able to or have time to help, as we have other duties and priorities as part of our daily work.

Like the original EasyAppointments, this code is licensed under [GPL v3.0](https://www.gnu.org/licenses/gpl-3.0.en.html)  and content under [CC BY 3.0](https://creativecommons.org/licenses/by/3.0/).

### Code Merge

There are a few different ways to take these changes into use in your own EasyAppointments installation.

The fastest way is to add this repository as a remote to your EasyAppointments git repository, fetch the changes in this repository and then cherrypick the commit(s) for the change that you want to include in your repository. You can find the commit SHA in the description for each change. 

```bash
git remote add lnu https://github.com/toekaa-lnu/easyappointments.git
git fetch lnu
git cherry-pick <commit-sha>
```

Another way to is just to look at the commits in GitHub and see the difference in the code for each included file, and then re-implement the changes in your own EasyAppointments code. This is maybe initially more time-consuming, but you'll gain a better understaning of the changes and will be able to solve any possible code conflict better, than just doing a merge/cherry-pick with git.

### Database Migration

Most of the changes require updates to the EasyAppointments application database, and this usually involves renaming the migration script files and then running a migration command.

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


## 1. Improvements to Custom Fields

SHA of the main commit:
```
1e83303519115acf3dd56259dd03c3569b05ea76
```
SHA of bug fix commit:
```
32a8daf05fe9c310b98ed41102857261ed766183
```

To open these commits in GitHub, click here for [the main commit](https://gitlab.com/lnu-ub/easyappointments_fork/-/commit/1e83303519115acf3dd56259dd03c3569b05ea76) and the [bug fix](https://github.com/toekaa-lnu/easyappointments/commit/32a8daf05fe9c310b98ed41102857261ed766183) commit.


### 1.1. Configuration and Migration

This commit requires some updates to the database, so you need to do the migration.

However, even before you do that, double check the configuration about the maximum number of custom fields that you want to have. The default is 5 both for existing customer-specific custom fields and new appointment-specific custom fields, but if you think that you may need more (now or in the future), you can update it to something higher. This just sets a maximum limit: each custom field can be enabled/disabled in the UI so you don't have to have them all active at the same time. If you're happy with the default values, you don't need to add anything.

To change the defaults, add the following lines the `config.php` file in the root folder (and change the value 5 to your max limit): 
```php
const MAX_CUSTOM_FIELDS = 5;
const MAX_APPOINTMENT_CUSTOM_FIELDS = 5;
```

The migration scripts read this value in order to add the preferred number of fields to the database tables, so that's why they need to be set before running the scripts.

After that, you're ready to proceed with the migration. This commit includes four migration scripts (in the `application/migrations/` folder) to update the database to support the improvements to the custom fields: 
```
061_update_custom_fields_columns_to_users_table.php
062_update_custom_field_rows_to_settings_table.php
063_add_appt_custom_field_columns_to_appointments_table.php
064_insert_appt_custom_field_rows_to_settings_table.php
```

See the [Migration instructions](#migration-instructions) how to handle these files and do the migration.


### 1.2 Description of the changes

These are the changes in this commit:
1.2.1. [Configurable max number of custom fields](#1-2-1-configurable-max-number-of-custom-fields)
1.2.2. [Appointment-specific custom fields](#1-2-2-appointment-specific-custom-fields)
1.2.3. [Translation of field labels and values](#1-2-3-translation-of-field-labels-and-values)
1.2.4. [Support for additional input types](#1-2-4-support-for-additional-input-types)
1.2.5. [Support for HTML attributes](#1-2-5-support-for-html-attributes)
1.2.6. [Options for select drop-down menus](#1-2-6-options-for-select-drop-down-menus)
1.2.7. [Handling groups of checkboxes and radio buttons](#1-2-7-handling-groups-of-checkboxes-and-radio-buttons)

#### 1.2.1. Configurable Max Number of Custom Fields

The number of custom fields is now configurable in the `config.php` file (in the root folder).
```php
  const MAX_CUSTOM_FIELDS = 5;
```
This value affects the existing custom fields, where the maximum number has so far been 5, but you can change it to something higher if you think that you may need more custom fields at some point.

If you change this, make sure you do it **before** running the migration command to update the database. See [Migration](#1-1-migration) for more info.

These custom fields are customer-specific, and stored in the `ea_users` table in the Easy!Appointments database. This means that they are unique to each user, and shared between all the same users appointments. This is fine for fields such as *address* and *age* which are properties of the user, however can't be used for custom fields that need to be different for each booking.

#### 1.2.2. Appointment-specific Custom Fields 

Since the existing custom fields are updated every time the same user makes a booking, there is sometimes a need for appointment-specific custom fields. These are stored in the `ea_appointments` table in the Easy!Appointments database, so they are unique for each booking. This makes it possible to add fields such as *number of participants* or *appointment notes*.

Like the customer-specific custom fields, the max number of appointment-specific custom fields is also configurable in the `config.php` file (in the root folder).
```php
  const MAX_APPOINTMENT_CUSTOM_FIELDS = 5;
```
Make sure you change this **before** running the migration command to update the database. See [Migration](#1-1-migration) for more info.

The new appointment-specific custom fields will appear in the *Admin > Settings > Booking Settings* UI, with the same functionality as the existing custom fields.

#### 1.2.3. Translation of Field Labels and Values

You can still write plain text as the label of the custom fields as before, but you can now also type in an ID defined in the translation files. This makes it possible to have the label translated to different languages in the booking UI.

Let's assume that you have the following entries in the language translation files.

```php
# In application/language/english/translations_lang.php:
$lang['label_age'] = 'Age';

# In application/language/swedish/translations_lang.php:
$lang['label_age'] = 'Ålder';
```

You can now type in `label_age` as the label in a custom field. When the booking UI is in English the label will say *Age* and when in Swedish it will say *Ålder*.

#### 1.2.4. Support for Additional Input Types

The existing custom fields are limited to just `text` inputs types. Now you can add an optional configration block to make it possible to use other types of input fields. The configuration block is added to the label field, after the label text itself. For example, you can type the following into the label field:
```json
label_age {"type":"number"}
```
Here the label text is `label_age` and the configuration block `{"type":"number"}`. If you have some experience in coding you may recognize the configuration block as a JSON structure, but that's not really important. The important bits to know are that the configuration starts with a curly brace `{` and ends with a curly brace `}`, and in between those curly braces it has a key-value pair (both key and value are in quotation marks `"`, with a colon `:` between them). In the example above the key is `type` and value is `number`, which means that the type of this custom field should be number.

The `number` type is one of the standard HTML input types. When using this type the input to restricted to only digits. The custom field will also have up/down arrows so that the value can be changed with the mouse.

This is just one of many types that can be used. The following HTML input types are currently supported:
* `checkbox` (see [Handling groups of checkboxes and radio buttons](6-handling-groups-of-checkboxes-and-radio-buttons))
* `color`
* `date`
* `email`
* `month`
* `number` (see [Support for HTML attributes](#5-support-for-html-attributes))
* `password`
* `radio` (see [Handling groups of checkboxes and radio buttons](6-handling-groups-of-checkboxes-and-radio-buttons))
* `range` (see [Support for HTML attributes](#5-support-for-html-attributes))
* `tel`
* `text`
* `time`
* `url`
* `week`

In addition to the HTML input types, the following form elements can also be given as `type`:
* `textarea` (see [Support for HTML attributes](#5-support-for-html-attributes))
* `select` (see [Options for select drop-down menus](#6-options-for-select-drop-down-menus))

#### 1.2.5. Support for HTML Attributes

The configuration is not limited to just specifying the `type` of the custom field. You can have other key/value pairs in the same configuration, just separate them with a comma `,`. For example, you can define a placeholder for text fields, or default values for number fields. See the descriptions and examples below.

With the `attributes` key you can directly enter the attributes for the HTML input element. This is useful for example when setting min/max values for number inputs, or number of lines for a textarea.

```json
// To set limits and initial value for a number input:
label_age {"type":"number","attributes":"min=18 max=99 value=18"}

// To set a number of rows for a textarea, and the maximum number of characters entered:
label_profession {"type":"textarea","attributes":"rows=4 maxLength=500"}
```

The `"attributes"` key can be used to enter more or less anything you want directly as attributes in the HTML element. **Be careful not to enter anything that might break the HTML code, such as a `>` tag ending characters or similar.**

One HTML attribute that has its own key/value pair is the `placeholder`. It is used to show a prompt or initial value in a `text` or `textarea` custom field, and will automatically disappear when the user starts typing into the field.

The reason for having `placeholder` as its own key/value pair in the configration block is to make it possible to use a translation ID as the placeholder and then get it translated to different languages in the UI. Of course you can use it just with plain text too, if you don't need any translations.

Here are some examples: 
```json
// With ID:s from translation files both for the label and the placeholder:
label_profession {"type":"text","placeholder":"placeholder_profession"}

// With plain text instead of ID:s, if you don't need any translations:
Your Profession {"type":"text","placeholder":"Please enter your current profession"}

// With ID:s and using "textarea" type instead of "text":
label_profession {"type":"textarea","placeholder":"placeholder_profession"}
```
You'll of course need to have these ID:s defined in the translation file (eg. `application/language/english/translations_lang.php`)

#### 1.2.6. Options for Select Drop-down Menus

When the `type` of the custom field is `select`, a drop down menu is created with a number of options to choose from. The options are created automatically based on the translation ID used as the label.

Given the following entries in a translation file:
```php
# In eg. application/language/english/translations_lang.php:
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
The ID `custom_field_pulldown_menu` (with the translation *Pulldown menu custom field*) is used as the label. This ID is then used as a base ID used for the options. The options are the automatically created based on other related ID:s defined in the translation file:

1. If there is an ID with `_prompt` added to the end of the base ID, it is used as the initial value in the menu, but it is not selectable. It is just used for prompting the user to select a value from the menu. If there is no such ID, the first option is used instead as the initial value.

2. The actual options are created in the similar way. The ID of the first option has `_1` added to the end of the base ID, the second option `_2` and so on. The number of options is automatically detected based on the ID:s defined in the translation files.

    If the configuration includes "sort":"true", the list of options is sorted according to the translations of the current language.

3. Finally, if there is an ID with '_last' at the end of the base ID, it is added as the last item (regardless of the sorting of the other options). This is convenient to use for options such as *Other* or *Don't know*.

Note that using translation ID:s is required for this to work, so you can't just enter plain text as the label of a `select` custom field.

#### 1.2.7. Handling Groups of Checkboxes and Radio Buttons

Handling a group of checkboxes and radio buttons is similar to how options for a select drop-down menu are handled.

Say that you have the following entries in the translation file:
```php
# In eg. application/language/english/translations_lang.php:
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

Just like with [options for select drop-down menus](#6-options-for-select-drop-down-menus), the number of checkboxes and radio buttons is automatically detected based on the ID:s in the translation files. Use the ID for the custom field label as a base ID, and then add `_1`, `_2`, `_3` (and so on) to the end of the base ID to define the items. There is a `_last` item too, but no `_prompt` item (not really needed, as all the items are visible immediately).


## 2. Support for Attached Files

SHA of the main commit:
```
de4dec2da22f68e7f48f7e0bb89cb2065fab83a6
```

SHA of the bug fix commit:
```
fe04ec19f995a98dac586bc807489a72f440e249
```

To open the commits in GitHub, click [here](https://github.com/alextselegidis/easyappointments/commit/de4dec2da22f68e7f48f7e0bb89cb2065fab83a6) for the main commit, and [here](https://github.com/alextselegidis/easyappointments/commit/fe04ec19f995a98dac586bc807489a72f440e249)for the bug fix commit.

### 2.1. Configuration and Migration

After merging this commit to your build, you need to migrate the application database to support the new functionality.

However, before going ahead with the migration, you may want to check the configuration related to attached files. In the `config.php` file in the root folder you can set the following values:
```php
const MAX_ATTACHED_FILES = 5;
const ATTACHED_FILES_MAX_SIZE = 8000000; // Max size for one file in bytes
const ATTACHED_FILES_ALLOWED_TYPES = '.doc,.docx,.xml,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document';
const ATTACHED_FILES_ALLOWED_TYPES_HINT = 'attached_files_user_allowed_types_hint';
```
These are just default values -- an administrator can change these later in the Booking Settings UI in the Admin panel.

* `MAX_ATTACHED_FILES` is an upper limit, so an administrator can't set it to something higher (only lower).
* `ATTACHED_FILES_MAX_SIZE` is the max size of one file in bytes. Note that the PHP system also has its own `upload_max_filesize` configuration in the `php.ini` file.
* `ATTACHED_FILES_ALLOWED_TYPES` is a comma-separated list of file extensions and mime types. It is used both in the file picker in the frontend and in the backend when uploading the file.
* `ATTACHED_FILES_ALLOWED_TYPES` is a text shown to the user in the booking form, and also as an error message if the file type is not one of the allowed types. It can be both a plain text string, or an ID in the language translation files.

If you don't set these, default values are used.

The default value for `ATTACHED_FILES_ALLOWED_TYPES`, is `attached_files_user_allowed_types_hint`, which is defined in the `application/language/english/translations_lang.php` language file:
```php
$lang['attached_files_user_allowed_types_hint'] = "Only Microsoft Word files (.doc, .docx) are accepted.";
```
Depending on what types of files you want to allow to be uploaded, you may want to change this to something more suitable.

After double-checking the configuration and language files, you can proceed with the migration. This is done with two migration script files added to this commit:
```
065_add_attached_files_column_to_appointments_table.php
066_insert_attached_files_rows_to_settings_table.php
```

See the [Migration instructions](#migration-instructions) how to handle the migration.

### 2.3 Description of the Changes

This commit adds support for attaching files to the booking. A number of settings are added to Admin > Booking Settings, that an administrator can use to:
1. Activate/deactivate support for attached files
2. Set the maximum number of attached files
3. Set the maximum size for an attached file
4. Set the allowed file types
5. Set the text shown to the user in the booking form about allowed file types (the same text is used in the error message if an unallowed file type is uploaded). This can be an ID into the translation files (under `application/language/*/translations_lang.php`), or simply a plain text string if you don't need any translations.

The customer can add up to the maximum number of files when creating a booking. When updating an existing booking, the customer can attach new files and discard previously attached (provided that the total max number is not exceeded). The provider can add and discard attached files in a similar way when adding or editing a booking in the calendar view in the admin panel.

The emails sent to the customer after creating or editing a booking includes information about the attached files (the actual files are not attached).


## 3. Hide Provider Selection

SHA of the main commit:
```
6827050704896c109612840ef96012e68f82b5b1
```

SHA of the bug fix commit:
```
841aa8e3b7d5fc1b465805687f5ab81733bf6ba8
```

To open these commits in GitHub, click [here](https://github.com/alextselegidis/easyappointments/commit/6827050704896c109612840ef96012e68f82b5b1) for the main commit and [here](https://github.com/alextselegidis/easyappointments/commit/841aa8e3b7d5fc1b465805687f5ab81733bf6ba8) for the bug fix commit.

This commit is built on top of some functionality added in **[Support for Attached Files](#2-support-for-attached-files)** (support for subsettings in Booking Settings), so you'll need to add the commit(s) from that change too.

### 3.1. Configuration and Migration

After merging this commit to your build, you need to migrate the application database to support the new functionality.

This is done with a migration script file added to this commit. This file can be found in the `application/migrations` folder:
```
067_insert_hide_providers_rows_to_settings_table.php
```

See the [Migration instructions](#migration-instructions) how to handle the migration.

### 3.2 Description of the Changes

This commit adds two new settings in Admin > Settings > Booking Settings. These settings are related to hiding the provider selection from the booking wizard, and are implemented as subsettings under the existing 'Any Provider' setting:
* **Hide provider selection**. Activating this setting will hide the "Provider" selection completely from the booking wizard. The effect is the same as always selecting the "Any provider" setting if there's multiple providers for a service, or the one and only provider if only one is available. The mail sent to the customer includes the name of the selected provider.
* **Provider selection method**. A new algorithm is added for selecting the provider when "Any provider" is selected. The legacy one (now selectable as 'Available on date" in the Settings UI) was only looking at the date of the new booking, and selected the provider that had the most available periods on that date. The new algorithm ('Available around booking') also looks at dates around the new booking, and selects the provider with the longest availability around the new booking. This works better when providers only have at most one or two available timeslots per day.

> Fo a more detailed description, the new 'Available around booking' algorithm uses the following steps to select a provider:
> 1. It gets a list of providers available for the selected date and time.
> 2. For each of these providers, it chooses their existing appointment which is closest in time to the new booking.
> 3. Among all these closest appointments, it finds the provider having the appointment furthest away from the new booking.
>
> The goal is to distribute the bookings among the providers as evenly as possible over time. Of course the algorithm is only used when there actually are multiple providers available for a booked timeslot.


## 4. Unit selection for booking advance timeout

SHA of the main commit:
```
6e3bd489863901ee4597d0e2cc236bd0b62ebde3
```

SHA of the bug fix commit:
```
4f0b8a74f327bec1e519929d2101adfe6a087e38
```

To open these commits in GitHub, click [here](https://github.com/alextselegidis/easyappointments/commit/6e3bd489863901ee4597d0e2cc236bd0b62ebde3) for the main commit and [here](https://github.com/alextselegidis/easyappointments/commit/4f0b8a74f327bec1e519929d2101adfe6a087e38) for the bug fix commit.


### 4.1. Configuration and Migration

After merging this commit to your build, you need to migrate the application database to support the new functionality.

This is done with a migration script file added to this commit. This file can be found in the `application/migrations` folder:
```
069_insert_cooldown_column_to_services_table.php
```

See the [Migration instructions](#migration-instructions) how to handle the migration.

### 4.2 Description of the Changes

This commit adds a new setting in Admin > Settings > Business Logic. The setting is an improvement to the existing "Allow Rescheduling/Cancellation Before" setting, so that now also the unit for the timeout can be specified:
* minutes
* hours
* days
* weekdays

Earlier the timeout could only be specified in minutes.


## 5. Cooldown period for services

SHA of the commit:
```
89a56da0c98b52824c7574fb9e526c5a444a13c6
```

To open this commit in GitHub, click [here](https://github.com/alextselegidis/easyappointments/commit/89a56da0c98b52824c7574fb9e526c5a444a13c6).


### 5.1. Configuration and Migration

After merging this commit to your build, you need to migrate the application database to support the new functionality.

This is done with a migration script file added to this commit. This file can be found in the `application/migrations` folder:
```
069_insert_book_advance_timeout_unit_rows_to_settings_table.php
```

See the [Migration instructions](#migration-instructions) how to handle the migration.

### 5.2 Description of the Changes

This commit adds a new "Cooldown" setting for each service under Admin > Services. This is meant as a period of extra time after the appointment, where the provider can wrap up the meeting (make notes, have a cup of coffee, go to toilet...). The cooldown period is automatically added to the duration of the booking, but not communicated to the customer. Customers are not able to book another appointment during the cooldown period.

For example, when the customer books a service with a 30 minute duration and 15 minute cooldown, the duration communicated to the customer is 30 minutes (in the booking confirmation screen and in the confirmation email), but the appointment occupies a 45-minute slot in the calendar. 


## 6. Hide Timezone from Customers

SHA of the commit:
```
e602d892d05aa9f321725ec8555afdc85b8ae0c1
```

To open this commit in GitHub, click [here](https://github.com/alextselegidis/easyappointments/commit/e602d892d05aa9f321725ec8555afdc85b8ae0c1).


### 6.1. Configuration and Migration

After merging this commit to your build, you need to migrate the application database to support the new functionality.

This is done with a migration script file added to this commit. This file can be found in the `application/migrations` folder:
```
070_insert_hide_timezone_row_to_settings_table.php
```

See the [Migration instructions](#migration-instructions) how to handle the migration.

### 6.2 Description of the Changes

This commit adds a new "Hide Customer Timezone" setting under Admin > Booking Settings. When activated, the customer is no longer able to change (or even see) the timezone selection. Instead, the Default Timezone (as defined under Admin > General Settings is always used).

The timezone is hidden from the date/time selection and confirmation steps in the booking, as well as the email sent out for saved and deleted appointments. Providers are still able to view and change their timezone in the setting, and when accessing bookings via the calendar page.


## 7. Customer Booking Limits

SHA of the commit:
```
f904c6143e23437ff967204622c1723f773e03ce
```

SHA of the bug fix commit:
```
5f8d51835b0279734f038a00206e6ae2dc976e1e
```

To open these commits in GitHub, click [here](https://github.com/alextselegidis/easyappointments/commit/f904c6143e23437ff967204622c1723f773e03ce) for the main commit and [here](https://github.com/alextselegidis/easyappointments/commit/5f8d51835b0279734f038a00206e6ae2dc976e1e) for the bug fix commit.

### 7.1. Configuration and Migration

After merging this commit to your build, you need to migrate the application database to support the new functionality.

This is done with a migration script file added to this commit. This file can be found in the `application/migrations` folder:
```
071_insert_customer_booking_limit_rows_to_settings_table.php
```

See the [Migration instructions](#migration-instructions) how to handle the migration.

### 7.2 Description of the Changes

This commit adds new "Customer Booking Limit" setting under Admin > Business Logic. The new settings control how many appointments each customer is allowed to book during a configurable time period.

* **Maximum number of appointments** controls the total number of appointments (past, present and future) that a customer is allowed to make during the selected time period. These appointments can be for different services.
* **Active bookings per service** controls how many active (ongoing and future) bookings a customer can have for each service, during the selected time period.
* **Time period for customer booking limits** controls what time period the above two setting are applied to. You can set it to one of the following:
  * Day
  * Week
  * Month
  * Half-year (with the periods January..June and July..December)
  * Calendar year (with the period January..December)
  * School year (with the period July..June)

The limits are reset at the start of each new time period, so if you choose eg. "Day" as the time period, then the customer is allowed to book the selected number of appointments each day.

In EasyAppointments, a customer is identified by the email address.


## 8. Availability Marking in Calendar View

SHA of the commit:
```
5636bb8c006c6c4fc9066f5237dca1bd7ae3d728
```

To open this commit in GitHub, click [here](https://github.com/alextselegidis/easyappointments/commit/5636bb8c006c6c4fc9066f5237dca1bd7ae3d728).

### 8.1. Configuration and Migration

No special configuration and migration needed for this commit.

### 8.2 Description of the Changes

This commit adds the possibility to mark "Availability" in the Admin > Calendar view, in the same way that "Unavailability" or "Appointment" is marked. Just use the mouse to select a timespan and select "Availability" in the popup that appears. A Working Plan Exception is created behind the scenes, with the selected timespan marked as available. It is possible to select multiple timespans and any existing Working Plan Exception is adjusted accordingly, with breaks added between availabilities as needed.

This works best for for providers who want to use a totally closed calendar as a starting point and just add available slots manually.


## 9. Providers Can Access All Bookings

SHA of the commit:
```
6c88a389e3aea93465d20cc9961b6a9fc7156214
```

To open this commit in GitHub, click [here](https://github.com/alextselegidis/easyappointments/commit/6c88a389e3aea93465d20cc9961b6a9fc7156214).

### 9.1. Configuration and Migration

After merging this commit to your build, you need to migrate the application database to support the new functionality.

This is done with a migration script file added to this commit. This file can be found in the `application/migrations` folder:
```
072_insert_provider_permission_all_bookings_row_to_settings_table.php
```

See the [Migration instructions](#migration-instructions) how to handle the migration.

### 9.2 Description of the Changes

This commit adds a new "Providers Can Access All Bookings" setting under Admin > Booking Settings. When this setting is enabled, providers can access (view, edit and delete) also each other's bookings in the calendar view. By default, providers are only able to access their own bookings.

This can be handy eg. in case of sickness, when another provider needs to take over an existing booking.