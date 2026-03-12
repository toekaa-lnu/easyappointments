# Linnaeus University fork of EasyAppointments

This is the Linnaeus University ("Lnu") Library fork of the Easy!Appointments booking application. This README file is mainly for publishing and describing the changes made by Lnu to the original repository. If you're looking for the original Easy!Appointments README file, it can be found here: [README_ORIG.md](README_ORIG.md).

EasyAppointments is used at Lnu by our text tutors so that students can book a tutoring session. Also our Talking Book support team has expressed interest in using it. Both of these teams have requested adaptations which I have implemented (and are continuing to implement). We have tried to make them configurable so that hopefully others will find them useful too.

Here is a list of changes we have done so far. More detailed instructions will be added as we publish each change to GitLab:

1. **[Improvements to Custom Fields](#1-improvements-to-custom-fields)**
    1. [Configuration and Migration](#11-configuration-and-migration)
    2. [Description of the Changes](#12-description-of-the-changes)
        1. [Configurable Max Number of Custom Fields](#121-configurable-max-number-of-custom-fields)
        2. [Appointment-specific Custom Fields](#122-appointment-specific-custom-fields)
        2. [Translation of Field Labels and Values](#123-translation-of-field-labels-and-values)
        2. [Support for Additional Input Types](#124-support-for-additional-input-types)
        2. [Support for HTML Attributes](#125-support-for-html-attributes)
        2. [Options for Select Drop-down Menus](#126-options-for-select-drop-down-menus)
        2. [Handling Groups of Checkboxes and Radio Buttons](#127-handling-groups-of-checkboxes-and-radio-buttons)
2. [Support for Attached Files](#2-support-for-attached-files)
    1. [Configuration and Migration](#21-configuration-and-migration)
    2. [Description of the Changes](#22-description-of-the-changes)
3. Hide Provider Selection
4. Mark Availability in Calendar View
5. Booking Lead Time
6. Customer Booking Limits
7. Hide Timezone for Customers
8. Appointment Colour by Provider

There are also a [Migration instructions](#migration-instructions). Most of the changes listed above require updates to the application database, and this usually involves renaming the migration script files and then running a migration command.


## Migration instructions

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
> [!CAUTION]
> Unfortunately migrating up/down doesn't tell you which step you are on (unless you try to migrate past the last step), so you need to keep track of this by yourself. **Be careful if you're doing this in a production environment. Each migration down will remove fields from the database (and the data stored in them is lost). If you migrate down too many times, you may accidentally remove fields you didn't intend to, and lose important data from the database.**


## 1. Improvements to Custom Fields

ID of the commit:
```
1e83303519115acf3dd56259dd03c3569b05ea76
```
To open the commit in GitHub, [click here](https://gitlab.com/lnu-ub/easyappointments_fork/-/commit/1e83303519115acf3dd56259dd03c3569b05ea76).


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

ID of the commit:
```
de4dec2da22f68e7f48f7e0bb89cb2065fab83a6
```

To open the commit in GitHub, [click here](https://github.com/alextselegidis/easyappointments/commit/de4dec2da22f68e7f48f7e0bb89cb2065fab83a6).

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

