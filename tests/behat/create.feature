@tool @tool_uploadexternalcontentresults @_file_upload
Feature: An admin can update results for an external content course using a text delimited file
  In order to update courses using a text delimited file
  As an admin
  I need to be able to upload a text delimited file and navigate through the import process

  Background:
    Given the following "courses" exist:
        | fullname                                        | shortname                                        | idnumber                              | enablecompletion |
        | C1b49aa30-e719-11e6-9835-f723b46a2688 Full Name | C1b49aa30-e719-11e6-9835-f723b46a2688 Short Name | C1b49aa30-e719-11e6-9835-f723b46a2688 | 1                |
    And the following "activities" exist:
        | activity        | name           | intro          | content          | course                                           | idnumber                              | completion | completionview | completionexternally |
        | externalcontent | External Name  | External Intro | External Content | C1b49aa30-e719-11e6-9835-f723b46a2688 Short Name | C1b49aa30-e719-11e6-9835-f723b46a2688 | 2          | 1              | 1                    |
    And the following "users" exist:
      | username | firstname | lastname | email |
      | teacher1 | Teacher1  | Teacher1 | teacher@example.com  |
      | student1 | Student1  | Student1 | student1@example.com |
    And the following "course enrolments" exist:
      | user      | course                                            | role           |
      | teacher1  | C1b49aa30-e719-11e6-9835-f723b46a2688 Short Name  | editingteacher |
    And I log in as "admin"
    And I am on "C1b49aa30-e719-11e6-9835-f723b46a2688 Full Name" course homepage with editing mode on
    And I navigate to "Course completion" in current page administration
    And I expand all fieldsets
    And I click on "External content - External Name" "checkbox"
    And I click on "Save changes" "button"
    And I log out

  @javascript
  Scenario: Update of course viewed and enrol user successful
    Given I log in as "admin"
    And I navigate to "Courses > Upload external content completions" in site administration
    And I upload "admin/tool/uploadexternalcontentresults/tests/fixtures/onecourseresult.csv" file to "CSV file" filemanager
    And I press "Import"
    And I press "Confirm"
    And I should see "Completions added/updated: 1"
    And I log out
    Given I log in as "teacher1"
    And I am on "C1b49aa30-e719-11e6-9835-f723b46a2688 Full Name" course homepage
    And I follow "Participants"
    And I should see "Student1 Student1"
    And I am on "C1b49aa30-e719-11e6-9835-f723b46a2688 Full Name" course homepage
    And "Student1 Student1" user has completed "External Name" activity

  @javascript
  Scenario: Update of course viewed and enrol user skipped as user does not exist
    Given I log in as "admin"
    And I navigate to "Courses > Upload external content completions" in site administration
    And I upload "admin/tool/uploadexternalcontentresults/tests/fixtures/onecourseresult_nouser.csv" file to "CSV file" filemanager
    And I press "Import"
    And I press "Confirm"
    And I should see "User with username student999 does not exist"
    And I should see "Completions skipped: 1"

  @javascript
  Scenario: Update of course viewed and enrol user skipped as user does not exist
    Given I log in as "admin"
    And I navigate to "Courses > Upload external content completions" in site administration
    And I upload "admin/tool/uploadexternalcontentresults/tests/fixtures/onecourseresult_nocourse.csv" file to "CSV file" filemanager
    And I press "Import"
    And I press "Confirm"
    And I should see "Course with idnumber B1b49aa30-e719-11e6-9835-f723b46a2688 does not exist"
    And I should see "Completions skipped: 1"
