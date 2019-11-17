# airtable-updater
A Wordpress plugin that automates updating the Wikitongues website (or any Wordpress website) from Airtable.

## Description

* Define workflows to add and update posts on the site from Airtable views
* Run workflows in one click or set a schedule (using cron)
* Add and update posts from a CSV file

## Installation

1. Copy this folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress

## Usage

* In wp-admin, navigate to Airtable in the sidebar
* Enter all the Airtable connection parameters, set a schedule if desired, and click "Save"
* From the dropdown at the top, click "New Workflow" to add a new workflow (i.e. for a different table)
* Use the dropdown to navigate between saved workflows
* Click "Update" in the right column to run any workflow at any time
* Upload a CSV file to add and update posts at any time

## Assumptions

To function properly, the Airtable view (or CSV file) should have the following columns:

* A unique ID field, input in the workflow settings
* `post_title`: Title of the post
* `post_status`: A valid Wordpress [post status](https://wordpress.org/support/article/post-status/). `publish` will publish the post immediately. 
* `post_type`: One of the post types defined in the theme.
* All custom fields for the post type.
* For post object fields, the column should contain the `post_title`'s of referenced posts. The posts must already exist in the Wordpress database.