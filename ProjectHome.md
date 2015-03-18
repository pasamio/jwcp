# Introducing Working Copy #

## Abstract ##
This is a kind of Subversion for Joomla! live site.
http://docs.joomla.org/Summer_of_Code_2009_Project_Ideas#Working_copy_of_Joomla.21_live_site

## Idea & Benefits ##
Administrators usually work on their live site directly and sometimes they do mistakes as all people do. As a result, the live site gets messed after extension installation/uninstallation process and re-configuration. The idea is to have a working copy of the live site and make changes on it, then, if everything is okay after some testing, you can approve changes and the tool will apply them to your live site.

I would like also to implement some basic features of Subversion into this project e.g. commit/approve, update/synchronize, revert, merge, create patch, apply patch (SVN operations afterwards).

Using this tool, people will do less mistakes on the live site and get less nervous!

## Milestones ##
Creating an API and interface will be needed to complete this project. They both will be developed simultaneously to make able testing from the interface. I will keep the main coding ideas and standards of Joomla! Framework hoping it will be a part of Joomla! 1.6 in future.

During the development process I will assume that the live site (master or parent afterwards) and the working copy (child afterwards) are running on the same versions and configurations of OS/Apache/MySQL/PHP, and the server configuration will stay intact (this tool can be only a testing environment for SERVER RE-CONFIGURATION).

Now I will describe in general what it will be and how easy it will be to work with. Here are some steps administrators can do:

  1. Create as many childs from the master to work on them (admin can create even a grand child)
  1. Modify the child (re-configure, add/edit content, install/uninstall/update extensions) and test (we can have a "spy bot" if necessary on the child to determine made changes easily)
  1. Approve changes to the live site with one of this options:
    1. Create a patch from the child
    1. Apply the patch to the master
    1. Directly approve changes to the master (actually it can do 3.1 then 3.2, just in one step)
  1. View the changes made on the child
  1. Synchronize the child with the parent (when the child is out of date)
  1. Revert the child to the parent state
  1. Merge 2 sites (master-child or child-child) with referential integrity

There are 2 possibilities to make changes on the Joomla! website, which is to change database and/or file system. So there will be 2 types of functions in the API, which will make changes to the database and to the file system.

Working with the file system is the easiest part, because every file has last modified date, which makes easy to determine which file is newer.

Working with the database is much more complicated, because there can be different scenarios with relations.

My goal is to make an API, which will implement SVN operations not only to core tables, but also to 3rd party tables, which can come with 3rd party extensions.

## Future enhancements ##
It is also possible to have a history table (#tablename\_history) for each table in db, which will keep table row versions in it. It will enable versioning of the whole database. Not only the content, but also parameters, module positions, etc. would be versioned. The other thing, which can be done, is to have language tables and keep table row translations in them.

## Timeline ##
~~**April 20 - May 17**: TIME TO SPEAK WITH THE MENTOR~~<br>
<del><b>Week 1 May 18 - 22</b>: Interface and API functions to make a child from master. (1)</del><br>
<del><b>Week 2 May 25 - 29</b>: Interface and API functions to view changes made on child. (4)</del><br>
<del><b>Week 3 June 1 - 5</b>: Interface and API functions to revert the child. (6)</del><br>
<del><b>Week 4 June 8 - 12</b>: Interface and API functions to synchronize the child. (5)</del><br>
<del><b>Week 5 June 15 - 19</b>: Interface and API functions to create a patch. (3.1)</del><br>
<del><b>Week 6 June 22 - 26</b>: Interface and API functions to apply the patch. (3.2, 3.3)</del><br>
<del><b>Week 7 June 29 - July 3</b>: PREPARE FOR THE MID-TERM EVALUATION</del><br>
<del><b>Week 8 July 6 - 10</b>: SUBMITTING THE MID-TERM EVALUATION</del><br>
<del><b>Week 9 July 13 - 17</b>: Interface and API functions to merge 2 sites. (7)</del><br>
<del><b>Week 10 July 20 - 24</b>: RESERVED TIME</del><br>
<del><b>Week 11 July 27 - 31</b>: RESERVED TIME</del><br>
<del><b>Week 12 August 3 - 7</b>: PREPARING FOR THE FINAL EVALUATION, PUTTING EVERYTHING IN THEIR PLACES</del><br>
<del><b>Week 13 August 10 - 14</b>: PENCILS DOWN, SUMMARISING RESULTS, WRITING DOCUMENTATION</del><br>
<del><b>Week 14 August 17 - 21</b>: SUBMITTING THE FINAL EVALUATION</del><br>
<del><b>August 22 - 25</b>: TIME FOR LAST MINUTE DECISIONS</del>

<h2>Webinar</h2>
<wiki:gadget url="http://edo.webmaster.am/data/jwcp_vimeo.xml" border="0" height="450" width="600" />