# tool_uploadexternalcontentresults
![Moodle Plugin CI](https://github.com/lushonline/moodle-tool_uploadexternalcontentresults/workflows/Moodle%20Plugin%20CI/badge.svg?branch=master)


- [Installation](#installation)
- [Usage](#usage)

A tool to allow import of completion results for External content activities using a text delimited file.

The External content activity is available here:
[moodle-mod_externalcontent](https://github.com/lushonline/moodle-mod_externalcontent)

The External content activity is identified by matching the [COURSE_IDNUMBER](https://github.com/lushonline/moodle-tool_uploadexternalcontentresults/wiki/Format-of-Import-File) in the import file against the External Content Activity Item [ID Number](https://docs.moodle.org/310/en/Common_module_settings#ID_number)

The import process does the following:
- enrols the student into the course, if they are not already enrolled
- marks the activity viewed
- if [USER_COMPLETED=1](https://github.com/lushonline/moodle-tool_uploadexternalcontentresults/wiki/Format-of-Import-File) and the activity is configured as Completable Externally it will be marked as Complete
- if optional score is included a grade is added for the activity.

The way Moodle handles teh viewed and completion process it is the date of the import that is used, you cannot import the date of the completion from the external system.


## Installation

---
1. Install the External content activity module v2023031400 or later.

   ```sh
   git clone https://github.com/lushonline/moodle-mod_externalcontent.git mod/externalcontent
   ```

   Or install via the Moodle plugin directory:

   https://moodle.org/plugins/mod_externalcontent

2. Install the uploadexternalcontent tool to support upload of courses.

   ```sh
   git clone https://github.com/lushonline/moodle-tool_uploadexternalcontent.git admin/tool/uploadexternalcontent
   ```

   Or install via the Moodle plugin directory:

   https://moodle.org/plugins/tool_uploadexternalcontent

3. Install this tool to support upload of results.

   ```sh
   git clone https://github.com/lushonline/moodle-tool_uploadexternalcontentresults.git admin/tool/uploadexternalcontentresults
   ```

   Or install via the Moodle plugin directory:

    https://moodle.org/plugins/tool_uploadexternalcontentresults

4. Then run the Moodle upgrade

This plugin requires no configuration.

## Usage

For more information see the [Wiki Pages](https://github.com/lushonline/moodle-tool_uploadexternalcontentresults/wiki)

## Acknowledgements

This was inspired in part by the great work of Frédéric Massart and Piers harding on the core [admin\tool\uploadcourse](https://github.com/moodle/moodle/tree/master/admin/tool/uploadcourse)
