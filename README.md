# tool_uploadexternalcontentresults
[![Build Status](https://travis-ci.org/lushonline/moodle-tool_uploadexternalcontentresults.svg?branch=master)](https://travis-ci.org/lushonline/moodle-tool_uploadexternalcontentresults)

A tool to allow import of completion results for External content activities using a text delimited file.

The External content activity is available here:
[moodle-mod_externalcontent](https://github.com/lushonline/moodle-mod_externalcontent)

The Course and External content activity should have been uploaded using the
[moodle-tool_uploadexternalcontent](https://github.com/lushonline/moodle-tool_uploadexternalcontent) as this correctly configures the Course and Activity ID Number.

The import file contains two columns:
|COLUMN ORDER|COLUMN HEADER|DESCRIPTION|REQUIRED|EXAMPLE|
|---------------|-------------|---------|----------|----------|
|1|COURSE_IDNUMBER|This is the Moodle Course ID Number. The ID number of a course is used when matching the results against the imported course and is not displayed anywhere to the user in Moodle. All course when imported using [moodle-tool_uploadexternalcontent](https://github.com/lushonline/moodle-tool_uploadexternalcontent) have an immutable COURSE_IDNUMBER|Yes|1b49aa30-e719-11e6-9835-f723b46a2688|
|2|USER_USERNAME|This is the Moodle Users username.|Yes|Student|

The import enrols the student into the course, and marks the activity viewed plus if the import set EXTERNAL_MARKCOMPLETEEXTERNALLY=true the activity is also marked as Completed.

The date used for the viewed and completed entry is the date of the import not the date of the completion as recorded in the external system.

- [Installation](#installation)
- [Usage](#usage)

## Installation

---
1. Install the External content activity module.

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
