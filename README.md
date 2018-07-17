
# ClassMarker.com

## API REQUEST SAMPLE PHP / MYSQL CODE

ClassMarker is a secure online Quiz Maker platform for Business and Education for giving exams and assessments.

Our API gives access to downloading Test results to 3rd party systems.

 Use the included files to:
 1. Create database tables,
 1. Connect to ClassMarker and download results available to your API KEY
 1. Insert results into your database
 1. View downloaded results

**Note:** See Directory "example_response_files" to locally host response files to easily request and debug
your application using local files (Handy if your ClassMarker.com account does not have test results saved as yet).

## HOW TO BUILD A QUIZ MAKER

Our API allows you to retrieve your Quiz results directly from ClassMarker.com. You can use this sample PHP/MySQL code with ClassMarker to save you time having to build a quiz maker system.

# Online Documentation
https://www.classmarker.com/online-testing/api/developers



# Getting Started

### STEP 1

 Create a MySQL database and run the SQL to create **5 tables**

 See: **create_classmarker_tables.txt**

 * *classmarker_tests:*  			for tests information (test name and ID)
 * *classmarker_groups:* 			for group information (group name and ID)
 * *classmarker_links:*  			for link information (link name and ID)
 * *classmarker_group_results:* 	for group results (holds test results taken within Groups)
 * *classmarker_link_results:*  	for link results (holds test results taken from Direct links)


### STEP 2

 Add **.php files** to your webserver


### STEP 3

 Move **credentials.php** to a safe location un-accessible from www
 Configure credentials.php to add database access and your support email address.


### STEP 4
 Configure **request_classmarker_results.php** to download results from Links or groups as instructed in the file

 See: https://www.classmarker.com/online-testing/api/developers#explain


### STEP 5
 Run **request_classmarker_results.php** to download results
 **request_classmarker_results.php** - can be called from Browser or Command line


### STEP 6
 View **view_classmarker_results.php** in browser to see downloaded results.


### STEP 7

You can then either
1. Run a **cron job** *hourly* or *daily* (depending on how often tests are taken under your account).
1. Just select **request_classmarker_results.php** from a Browser to download latest results



### Disclaimer  

ClassMarker Pty Ltd accepts no responsibility whatsoever from usage of these API scripts and Classes and shall be held harmless from any and all litigation, liability, and responsibilities.
