# Linnaeus University fork of EasyAppointments

This is the Linnaeus University ("Lnu") Library fork of the Easy!Appointments booking application. This README file is mainly for publishing and describing the changes made by Lnu to the original repository. If you're looking for the original Easy!Appointments README file, it can be found here: [README_ORIG.md](README_ORIG.md).

EasyAppointments is used at Lnu by our text tutors so that students can book a tutoring session. Also our Talking Book support team has expressed interest in using it. Both of these teams have requested adaptations which we have implemented (and are continuing to implement). We have tried to make them configurable so that hopefully others will find them useful too.

Here is a list of changes we have done so far. More detailed instructions will be added as we publish each change to GitLab:

* [Improvements to Custom Fields](#improvements-to-custom-fields)
* Support for Attached Files
* Hide Provider Selection
* Mark Availability in Calendar View
* Booking Lead Time
* Customer Booking Limits
* Hide Timezone for Customers
* Appointment Colour by Provider


## Improvements to Custom Fields

ID of the commit:
```
1e83303519115acf3dd56259dd03c3569b05ea76
```
To open the commit in GitHub, [click here](https://gitlab.com/lnu-ub/easyappointments_fork/-/commit/1e83303519115acf3dd56259dd03c3569b05ea76).

### Configuration and Migration

After merging the commit to your build, you need to migrate the database to the new version. But before that, check the following:

1. **Define the maximum number of custom fields that you may want to have**

    Define `MAX_CUSTOM_FIELDS` and `MAX_APPOINTMENT_CUSTOM_FIELDS` to the max number of customer-specific and appointment-specific custom fields that you may want to have in your booking form. Define these values in the `config.php` file in the root folder.
    ```
     const MAX_CUSTOM_FIELDS = 5;
     const MAX_APPOINTMENT_CUSTOM_FIELDS = 5;
    ```
    These values are just maximum limits, you don't need to have them all active at the same time. But if you want more than the standard 5, set these to something higher. If you don't define these values at all, the standard 5 is used for both.

2. **Double check the migration script files**

    To be able to update the number of custom fields to an existing installation, four migration script files are added to this commit:
    ```
    061_update_custom_fields_columns_to_users_table.php
    062_update_custom_field_rows_to_settings_table.php
    063_add_appt_custom_field_columns_to_appointments_table.php
    064_insert_appt_custom_field_rows_to_settings_table.php
    ```
    These files are stored in the `application/migrations/` folder.

    These files will add the necessary fields to the application database tables. in Easy!Appointments, the migration files must be numbered in order, with no missing or overlapping numbers. This commit is made on top of Easy!Appointments version 1.5.2 (the latest version as of this Writing), where the last migration script has the number 060, so these new scripts are therefore numbered 061...064. However, if you have upgraded to a later version (or included other changes), you may need to rename them. Just make sure these new script files are the last ones in the sequence, without any missing or overlapping numbers.

3. **Run the migration command**

    After defining the max values, and checking the numbering of the migration scripts, you can run the migration command to update the database to the latest version. Run this in the root folder of the application:
    ```bash
    php index.php console migrate
    ```

    Should you change your mind about the max values after migrating, it is possible to migrate down and then back up, one step/script at the time. In that case you can first migrate down four times, change the max values in `config.php`, and then migrate up four times to redo the changes (but now with the new max values):
    ```bash
    # Migrate four steps down
    php index.php console migrate down
    php index.php console migrate down
    php index.php console migrate down
    php index.php console migrate down

    # Change MAX_CUSTOM_FIELDS and MAX_APPOINTMENT_CUSTOM_FIELDS

    # Migrate four steps up
    php index.php console migrate up
    php index.php console migrate up
    php index.php console migrate up
    php index.php console migrate up
    ```
    Unfortunately these scripts don't tell you which step you are on (unless you try to migrate past the last step), so you need to keep track of this by yourself. **Be careful if you're doing this in a production installation. If you migrate down too many times, you might accidentally remove fields from the database and lose the data stored in them.**

### Description of the changes

These are the changes in this commit:
1. [Configurable max number of custom fields](#1-configurable-max-number-of-custom-fields)
2. [Appointment-specific custom fields](#2-appointment-specific-custom-fields)
3. [Translation of field labels and values](#3-translation-of-field-labels-and-values)
4. [Support for additional input types](#4-support-for-additional-input-types)
5. [Support for HTML attributes](#5-support-for-html-attributes)
6. [Options for select drop-down menus](#6-options-for-select-drop-down-menus)
7. [Handling groups of checkboxes and radio buttons]()

#### 1. Configurable max number of custom fields

The number of custom fields is now configurable in the `config.php` file (in the root folder).
```
  const MAX_CUSTOM_FIELDS = 5;
```
This value affects the existing custom fields, where the maximum number has so far been 5, but you can change it to something higher if you think that you may need more custom fields at some point.

If you change this, make sure you do it **before** running the migration command to update the database. See [Configuration and Migration](#configuration-and-migration) for more info.

These custom fields are customer-specific, and stored in the `ea_users` table in the Easy!Appointments database. This means that they are unique to each user, and shared between all the same users appointments. This is fine for fields such as *address* and *age* which are properties of the user, however can't be used for custom fields that need to be different for each booking.

#### 2. Appointment-specific custom fields 

Since the existing custom fields are updated every time the same user makes a booking, there is sometimes a need for appointment-specific custom fields. These are stored in the `ea_appointments` table in the Easy!Appointments database, so they are unique for each booking. This makes it possible to add fields such as *number of participants* or *appointment notes*.

Like the customer-specific custom fields, the max number of appointment-specific custom fields is also configurable in the `config.php` file (in the root folder).
```
  const MAX_APPOINTMENT_CUSTOM_FIELDS = 5;
```
Make sure you change this **before** running the migration command to update the database. See [Configuration and Migration](#configuration-and-migration) for more info.

The new appointment-specific custom fields will appear in the *Admin > Settings > Booking Settings* UI, with the same functionality as the existing custom fields.

#### 3. Translation of field labels and values

You can still write plain text as the label of the custom fields as before, but you can now also type in an ID defined in the translation files. This makes it possible to have the label translated to different languages in the booking UI.

Let's assume that you have the following entries in the language translation files.

```bash
# In application/language/english/translations_lang.php:
$lang['label_age'] = 'Age';

# In application/language/swedish/translations_lang.php:
$lang['label_age'] = 'Ålder';
```

You can now type in `label_age` as the label in a custom field. When the booking UI is in English the label will say *Age* and when in Swedish it will say *Ålder*.

#### 4. Support for additional input types

The existing custom fields are limited to just `text` inputs types. Now you can add an optional configration block to make it possible to use other types of input fields. The configuration block is added to the label field, after the label text itself. For example, you can type the following into the label field:
```bash
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

#### 5. Support for HTML attributes

The configuration is not limited to just specifying the `type` of the custom field. You can have other key/value pairs in the same configuration, just separate them with a comma `,`. For example, you can define a placeholder for text fields, or default values for number fields. See the descriptions and examples below.

With the `attributes` key you can directly enter the attributes for the HTML input element. This is useful for example when setting min/max values for number inputs, or number of lines for a textarea.

```bash
# To set limits and initial value for a number input:
label_age {"type":"number","attributes":"min=18 max=99 value=18"}

# To set a number of rows for a textarea, and the maximum number of characters entered:
label_profession {"type":"textarea","attributes":"rows=4 maxLength=500"}
```

The `"attributes"` key can be used to enter more or less anything you want directly as attributes in the HTML element. **Be careful not to enter anything that might break the HTML code, such as a `>` tag ending characters or similar.**

One HTML attribute that has its own key/value pair is the `placeholder`. It is used to show a prompt or initial value in a `text` or `textarea` custom field, and will automatically disappear when the user starts typing into the field.

The reason for having `placeholder` as its own key/value pair in the configration block is to make it possible to use a translation ID as the placeholder and then get it translated to different languages in the UI. Of course you can use it just with plain text too, if you don't need any translations.

Here are some examples: 
```bash
# With ID:s from translation files both for the label and the placeholder:
label_profession {"type":"text","placeholder":"placeholder_profession"}

# With plain text instead of ID:s, if you don't need any translations:
Your Profession {"type":"text","placeholder":"Please enter your current profession"}

# With ID:s and using "textarea" type instead of "text":
label_profession {"type":"textarea","placeholder":"placeholder_profession"}
```
You'll of course need to have these ID:s defined in the translation file (eg. `application/language/english/translations_lang.php`)

#### 6. Options for select drop-down menus

When the `type` of the custom field is `select`, a drop down menu is created with a number of options to choose from. The options are created automatically based on the translation ID used as the label.

Given the following entries in a translation file:
```bash
# In eg. application/language/english/translations_lang.php:
$lang['custom_field_pulldown_menu'] = 'Pulldown menu custom field';
$lang['custom_field_pulldown_menu_prompt'] = 'Select option from menu...';
$lang['custom_field_pulldown_menu_1'] = 'First option';
$lang['custom_field_pulldown_menu_2'] = 'Second option';
$lang['custom_field_pulldown_menu_3'] = 'Third option';
$lang['custom_field_pulldown_menu_last'] = 'Other';
```
A dropdown menu custom field can then be defined as follows:
```
custom_field_pulldown_menu {"type":"select", "sort":"false"}
```
The ID `custom_field_pulldown_menu` (with the translation *Pulldown menu custom field*) is used as the label. This ID is then used as a base ID used for the options. The options are the automatically created based on other related ID:s defined in the translation file:

1. If there is an ID with `_prompt` added to the end of the base ID, it is used as the initial value in the menu, but it is not selectable. It is just used for prompting the user to select a value from the menu. If there is no such ID, the first option is used instead as the initial value.

2. The actual options are created in the similar way. The ID of the first option has `_1` added to the end of the base ID, the second option `_2` and so on. The number of options is automatically detected based on the ID:s defined in the translation files.

    If the configuration includes "sort":"true", the list of options is sorted according to the translations of the current language.

3. Finally, if there is an ID with '_last' at the end of the base ID, it is added as the last item (regardless of the sorting of the other options). This is convenient to use for options such as *Other* or *Don't know*.

Note that using translation ID:s is required for this to work, so you can't just enter plain text as the label of a `select` custom field.

#### 6. Handling groups of checkboxes and radio buttons

Handling a group of checkboxes and radio buttons is similar to how options for a select drop-down menu are handled.

Say that you have the following entries in the translation file:
```bash
# In eg. application/language/english/translations_lang.php:
$lang['custom_field_fruit'] = 'Fruit';
$lang['custom_field_fruit_1'] = 'Banana';
$lang['custom_field_fruit_2'] = 'Apple';
$lang['custom_field_fruit_3'] = 'Pear';
$lang['custom_field_fruit_last'] = 'None of the above';
```

A group of radio buttons can then be defined as a custom field as follows:
```
custom_field_fruit {"type":"radio", "sort":"true"}
```

A group of checkboxes can be defined in almost the same way:
```
custom_field_fruit {"type":"checkbox", "sort":"true"}
```

The difference between radio buttons and checkboxes is that you can select several checkboxes in a group while only one radio button in a group can be selected.

Just like with [options for select drop-down menus](#6-options-for-select-drop-down-menus), the number of checkboxes and radio buttons is automatically detected based on the ID:s in the translation files. Use the ID for the custom field label as a base ID, and then add `_1`, `_2`, `_3` (and so on) to the end of the base ID to define the items. There is a `_last` item too, but no `_prompt` item (not really needed, as all the items are visible immediately).
