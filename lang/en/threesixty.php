<?php

$string['threesixty:addinstance'] = 'Can add an instance';
$string['threesixty:deleterespondents'] = 'Delete external respondents from a user\'s list';
$string['threesixty:declinerequest'] = 'Decline request that has been sent to you (only for internal users)';
$string['threesixty:edit'] = 'Change someone\'s personal 360 Degree assessment';
$string['threesixty:feedbackview'] = 'View 360 feedback'; // Added by Kat for Kineo HCA 12/05/10
$string['threesixty:manage'] = 'Manage 360 Degree Tool';
$string['threesixty:participate'] = 'Assess yourself in the 360 Degree Tool';
$string['threesixty:viewrespondents'] = 'View the list of respondents';
$string['threesixty:remindrespondents'] = 'Send a reminder email to external respondents';
$string['threesixty:view'] = 'View 360 Degree Tool information';
$string['threesixty:viewownreports'] = 'View your own 360 Degree Tool reports';
$string['threesixty:viewreports'] = 'View 360 Degree Tool reports for all users';

$string['filter:self'] = 'Self';
$string['filter:average'] = '(<span class="mod-threesixty-overlined">x</span>)';
$string['average'] = 'Average';

$string['edit:carryover'] = 'Carry over skills';
$string['edit:competencies'] = 'Add/edit competencies';
$string['edit:respondents'] = 'View/delete respondents';
$string['edit:scores'] = 'Amend scores';

$string['email:remindersubject'] = 'Reminder to complete a 360 diagnostic activity';
$string['email:reminderbody'] = 'Hello,

This is just a quick reminder to please complete my 360-degree diagnotics
and gap analysis.

To do so, simply click on this link:

  {$a->url}

Thank you,

{$a->userfullname}
';
$string['email:requestsubject'] = 'Invitation to complete a 360 diagnostic activity';
$string['email:requestbody'] = 'Hello,

I would appreciate if you would please complete my 360-degree diagnotics
and gap analysis.

To do so, please click on this link:

  {$a->url}

Thank you,

{$a->userfullname}
';

$string['error:allskillneedascore'] = 'every skill needs a score';
$string['error:cannotaddcompetency'] = 'Could not add new competency';
$string['error:cannotaddskill'] = 'Could not add new skill';
$string['error:cannotdeletecompetency'] = 'Could not delete competency';
$string['error:cannotdeleterespondent'] = 'Could not delete respondent';
$string['error:cannotdeleteskill'] = 'Could not delete skill';
$string['error:cannotinviterespondent'] = 'There was an error while trying to invite this respondent.';
$string['error:cannotsavechanges'] = 'Could not save changes: {$a}';
$string['error:cannotsavescores'] = 'Could not save scores: {$a}';
$string['error:cannotsendreminder'] = 'Could not send the reminder email';
$string['error:cannotupdatecompetency'] = 'Could not update existing competency';
$string['error:cannotupdateskill'] = 'Could not update existing skill';
$string['error:databaseerror'] = 'database error. Contact the site administrator.';
$string['error:formsubmissionerror'] = 'form submission error. Contact the site administrator.';
$string['error:invalidcode'] = 'Invalid response code. Make sure you copied the full link that was emailed to you. If you continue to have problems, please contact the site administrator.';
$string['error:invalidpagenumber'] = 'Invalid page number: no competency to display';
$string['error:invalidreporttype'] = 'Invalid report type: {$a}';
$string['error:nodataforuserx'] = 'No 360 Degree data for {$a}';
$string['error:noscoresyet'] = 'You need to fill in your own scores and submit the form first.';
$string['error:unknownbuttonclicked'] = 'No action associated with the button that was clicked';
$string['error:userxhasnotsubmitted'] = '{$a} has not completed the form yet';
$string['error:time'] = 'There has been a problem retrieving the time completed. Please contact your administrator.';
$string['error:unhashedrespondant'] = 'respondent unhashed; this should not happend; please refer to a programer';
$string['error:invalidcomptency'] = 'Competency id incorrect';

$string['report:spiderweb'] = 'Spiderweb Diagram';
$string['report:table'] = 'Gap Analysis';

$string['setting:respondenttypes'] = 'Respondent types';
$string['setting:respondenttypesdefault'] = 'Peer\nColleague\nCustomer\nOther';
$string['setting:respondenttypesdesc'] = 'The list of available respondent types, one per line. <b>Warning: only append entries to the end of this list once users have started using the 360 Degree Tool.</b>';

$string['setting:selftypes'] = 'Self profile types';
$string['setting:selftypesdesc'] = 'The list of available responses that need to be filled out by the participant.';
$string['setting:selftypesdefault'] = 'Self\nJob Profile';

$string['self:responsetype'] = 'Profile';
$string['self:responsecompleted'] = 'Date Completed';
$string['self:responseoptions'] = 'Options';
$string['self:incomplete'] = 'Not Completed';

$string['tab:activity'] = 'Activity';
$string['tab:edit'] = 'Administration';
$string['tab:reports'] = 'Reports';
$string['tab:respondents'] = 'Respondents';
$string['respondents'] = 'Respondents';
$string['tab:requests'] = 'Requests';

$string['validation:emailnotunique'] = 'This email address has already been used.';

$string['threesixty'] = '360 Degree Diagnostics Tool';
$string['modulename'] = '360 Degree Diagnostics Tool';
$string['modulenameplural'] = '360 Degree Diagnostics Tools';
$string['assess'] = 'Assess';
$string['decline'] = 'Decline';
$string['amendme'] = 'Update my self-evaluation';
$string['amend'] = 'Amend self-evaluation';
$string['addnewcompetency'] = 'Add a new competency';
$string['addnewskills'] = 'Add {no} new skills';
$string['adminnotified'] = '<p>Thank you for letting us know. The administrator has been notified.</p>';
$string['applybutton'] = 'Apply';
$string['areyousuredelete'] = 'Are you sure you want to delete this {$a}?';
$string['carryoverheading'] = 'Carry over skills';
$string['carryoverexplanation'] = '<p>Select up to {$a} skills to carry over to the Training Diary:</p>';
$string['carryovernote'] = '<br/><p><i>Note: the order is not important since the skills will be listed in alphabetical order in the Training Diary.</i></p>';
$string['closebutton'] = 'Close';
$string['competency'] = 'competency';
$string['competenciesheading'] = 'Competencies';
$string['competenciescarried'] = 'Number of skills carried over:';
$string['completiondate'] = 'Completion date';
$string['delete'] = 'Delete';
$string['deleteskill'] = 'Delete this skill';
$string['edit'] = 'Edit';
$string['change'] = 'Change';
$string['externaluser'] = 'External User';
$string['feedback'] = 'Feedback';
$string['feedbacks'] = 'Feedbacks';
$string['filters'] = 'Filters';
$string['finishbutton'] = 'Finish';
$string['intro'] = 'Intro';
$string['key'] = 'Key';
$string['makeself'] = 'Perform self-evaluation';
$string['nocompetencies'] = '<p>There are no competencies defined in this activity.</p>';
$string['noscore'] = '(none)';
$string['noskills'] = '<p><b>There are no skills defined for this competency.</b></p>';
$string['norespondents'] = 'No respondents have been entered yet.';
$string['nousers'] = 'No users to display';
$string['nousersfound'] = '<p>None of the users have completed this activity yet. There is no data to display yet.</p>';
$string['notset'] = 'not set';
$string['notapplicable'] = 'N/A';
$string['norequests'] = 'No pending requests';
$string['or'] = 'or';
$string['pluginname'] = 'Threesixty Evaluation';
$string['pluginadministration'] = 'Threesixty Administration';
$string['participant'] = 'Participant';
$string['remindbutton'] = 'Send reminder';
$string['reportforuser'] = 'Report for user: {$a->fullname}';
$string['requestrespondentheading'] = 'Request respondent';
$string['requestrespondentexplanation'] = '<p>Please enter the email address of a person you would like to invite to complete your 360 degree diagnostic activity.</p><p>You need to invite {$a} more respondent(s).</p>';
$string['requiredrespondents'] = 'Number of respondents required:';
$string['respondentsremaining'] = 'Not all respondents have replied yet.';
$string['respondentuserid'] = 'Respondent with user account';
$string['respondenttype'] = 'Respondent type';
$string['respondentwelcome'] = '<p><b>Welcome <tt>{$a}</tt></b></p>';
$string['respondentwarning'] = '<p>If this is not your email address, please <a href="{$a}">click here</a> to notify the site administrator. Thank you!</p>';
$string['respondentinstructions'] = '<p>Thank you for taking part in this profiling exercise.</p><p>Your input should be based on your own impressions of the performance of the person named. Please follow the steps and consider each of the statements, selecting the most appropriate descriptor for the person whose profile you are completing. If you feel unable to answer any particular questions, simply select "N/A" as your answer. On conclusion, you will have the chance to review your answers before saving the profile. Please note that your input will remain confidential.</p>';
$string['respondentindividual'] = 'This profile is for <b>{$a}</b>';
$string['numberrespondents'] = 'Number of Invites Sent';
$string['selecteduser'] = 'Selected user: {$a->fullname} (<a href="{$a->url}">select another</a>)';
$string['selectuser'] = '<p>Please select a user:</p>';
$string['sendemail'] = 'Send email';
$string['selftypes'] = 'Self-Analysis type';
$string['respondenttypes'] = 'Types of respondent';
$string['skill'] = 'Skill';
$string['skillgrade'] = 'Scale used for competencies';
$string['skills'] = 'Skills';
$string['showfeedback'] = 'Feedback';
$string['thankyoumessage'] = '<p>Thank you for assessing this user.</p><p>You may now close this window.</p>';
$string['viewself'] = 'Self-evaluate';
$string['xofy'] = '{$a->page} of {$a->nbpages}';
$string['analyses'] = 'Analyses';
$string['legend:heading'] = 'Scale details:';
$string['legend:level1'] = 'Support Staff Level';
$string['legend:level2'] = 'Junior Management';
$string['legend:level3'] = 'Middle Management';
$string['legend:level4'] = 'Senior Management';
$string['viewentries'] = 'View entries';
$string['view'] = 'View evaluators';
$string['belowaverage'] = 'Below average';
$string['aboveaverage'] = 'Above average';
$string['requiredrespondents_help'] = '# Respondents Required

The number of peers (colleagues, clients, bosses) that are required to fill this assessment before this activity is considered completed.';
$string['showfeedback_help'] = '## Feedback

If this option is enabled, a free-text feedback field will be available while users rate each skill in this competency.';
$string['mods_help'] = '![][1] **360 Degree Diagnostics and Gap Analysis Tool**

<div class="indent">
  This module is designed to implement a 360 degree competency analysis. This method of assessment is also referred to as "multisource feedback." An individual compares his or her level of ability in a predefined list of competencies by rating themselves against a specific scenario. Each scenario highlights a specific skill that makes up the overall competency. This same set of questions is also answered by a set number of colleagues, clients, bosses, etc. in order to get a full (or 360 degree) view of the skill of the individual as perceived by others as well as him or herself.
</div>

 [1]: <?php echo $CFG->wwwroot?>/mod/threesixty/icon.gif';
$string['competenciescarried_help'] = '# Competencies Carried

This field indicates the number of competencies that will be carried from the 360 Degree Diagnostics and Gap Analysis Tool to the Training Diary.

The actual competencies to be carried will be selected by a user with access to the administrative tab of the 360 Degree Diagnostics and Gap Analysis Tool.';

