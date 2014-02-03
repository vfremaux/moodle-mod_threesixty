<?php
/**
* @package mod-threesixty
* @category mod
* @author Valery Fremaux (admin@ethnoinformatique.fr)
*/

/*
* index of functions
function compile_mail_template($template, $infomap, $module = 'scheduler') {
function get_mail_template($virtual, $modulename, $lang = ''){
*/

    /**
    * useful templating functions from an older project of mine, hacked for Moodle
    * @param template the template's file name from $CFG->sitedir
    * @param infomap a hash containing pairs of parm => data to replace in template
    * @return a fully resolved template where all data has been injected
    */
    function threesixty_compile_mail_template($template, $infomap, $lang = '') {

		if (empty($lang)) $lang = current_language();
       
        $notification = implode('', threesixty_get_mail_template($template, $lang));
        foreach($infomap as $aKey => $aValue){
            $notification = str_replace("<%%$aKey%%>", $aValue, $notification);
        }
        return $notification;
    }

    /*
    * resolves and get the content of a Mail template, acoording to the user's current language.
    * @param virtual the virtual mail template name
    * @param module the current module
    * @param lang if default language must be overriden
    * @return string the template's content or false if no template file is available
    */
    function threesixty_get_mail_template($virtual, $lang = ''){
        global $CFG;

        if ($lang == '') {
            $lang = $CFG->lang;
        }
        $templateName = "{$CFG->dirroot}/mod/threesixty/mails/{$lang}/{$virtual}.tpl";
        if (file_exists($templateName))
            return file($templateName);

        debugging("template $templateName not found");
        return array();
    }

/**
 * Sends an e-mail based on a template. 
 * Several template substitution values are automatically filled by this routine.
 *  
 * @uses $CFG 
 * @uses $SITE
 * @param user $recipient A {@link $USER} object describing the recipient
 * @param user $sender A {@link $USER} object describing the sender
 * @param object $course The course that the activity is in. Can be null.
 * @param string $title the identifier for the e-mail subject. 
 *        Value can include one parameter, which will be substituted 
 *        with the course shortname. 
 * @param string $template the virtual mail template name (without "_html" part)
 * @param array $infomap a hash containing pairs of parm => data to replace in template
 * @param string $modulename the current module
 * @param string $lang language to be used, if default language must be overriden
 * @return bool|string Returns "true" if mail was sent OK, "emailstop" if email
 *         was blocked by user and "false" if there was another sort of error.
 */
function threesixty_send_email_from_template($recipient, $sender, $course, $title, $template, $infomap, $modulename , $lang = ''){
    
    global $CFG;
    global $SITE;
    
    $defaultvars = array( 
        'SITE' => $SITE->shortname,
        'SITE_URL' => $CFG->wwwroot,
        'SENDER'  => fullname($sender),
        'RECIPIENT'  => fullname($recipient)
    );
    
    $subjectPrefix = $SITE->shortname;
    
    if ($course) {
        $subjectPrefix = $course->shortname;
        $defaultvars['COURSE_SHORT'] = $course->shortname;
        $defaultvars['COURSE']       = $course->fullname;
        $defaultvars['COURSE_URL']   = $CFG->wwwroot.'/course/view.php?id='.$course->id;
    }
    
    $vars = array_merge($defaultvars,$infomap);
    
    $subject = get_string($title,$modulename,$subjectPrefix);
    $plainMail = compile_mail_template($template,$vars,$modulename);
    $htmlMail = compile_mail_template($template.'_html',$vars,$modulename);
    
    $res = email_to_user ($recipient, $sender, $subject, $plainMail, $htmlMail); 
    
    return $res;
}
