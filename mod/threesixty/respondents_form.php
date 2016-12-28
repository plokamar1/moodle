<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.'); // It must be included from a Moodle page.
}

global $CFG;

require_once($CFG->dirroot.'/lib/formslib.php');

class mod_threesity_respondents_form extends moodleform {

    public function definition() {

        $mform =& $this->_form;
        $typelist = $this->_customdata['typelist'];
        $remaininginvitations = $this->_customdata['remaininginvitations'];

        $mform->addElement('hidden', 'a', $this->_customdata['a']);
        $mform->setType('a', PARAM_INT);
        $mform->addElement('hidden', 'userid', $this->_customdata['userid']);
        $mform->setType('userid', PARAM_INT);

        $mform->addElement('header', 'requestrespondent', get_string('requestrespondentheading', 'threesixty'));
        $mform->addElement('html', get_string('requestrespondentexplanation', 'threesixty', $remaininginvitations));

        //var_dump(get_string('requestrespondentheading'));

        $mform->addElement('text', 'email', get_string('email'), array('size' => 40));
        $mform->setType('email', PARAM_NOTAGS);
        $mform->addRule('email', get_string('invalidemail'), 'email');

        /* 
        $options = array(
          'showsuggestions' => true,
          'placeholder' => "Write the name of enrolled user...",
          'casesensitive' => false
        );
        $mform->addElement('text','name', get_string('searchenrolled','threesixty'),$options);
        $mform->setType('text', PARAM_RAW);
        */
        global $COURSE;
        $courseid = $COURSE->id;//Getting current course id

        $userlist = get_enrolled_users( context_course::instance($courseid));//Getting the course's enrolled users objects
        $enrolledUsers = array();
        foreach($userlist as $user){
           $enrolledUsers[$user->email] = $user->lastname .' '. $user->firstname.' '.$user->email;
        }


        $mform->addElement('select', 'sEmail', get_string('invite:enrolled','threesixty'), $enrolledUsers);
        $mform->setType('sEmail', PARAM_RAW);
        $mform->getElement('sEmail')->setMultiple(true);


        $mform->addElement('select', 'type', get_string('respondenttype', 'threesixty'), $typelist);
        $mform->setType('type', PARAM_INT);


        $mform->addElement('html', '<br/><br/>');
        $mform->addElement('submit', 'send', get_string('sendemail', 'threesixty'));
    }

    public function validation($data, $files) {
        global $DB;
        $errors = parent::validation($data, $files);
        $analysisid = $this->_customdata['analysisid'];
        $mailList = array();

        if(array_key_exists('sEmail',$data)){
            foreach($data['sEmail'] as $sEmail){
                $mailList[] = $sEmail;
            } 
        }

        if($data['email'] != ''){
            $mailList[] = strtolower($data['email']);
        }

        $getRecords = $DB->get_records('threesixty_respondent',array('analysisid' => $analysisid),null,'email');//return database with the emails as keys where analysisid is the current
        $dbMails = array_keys($getRecords);//get the keys as array indexes
        $mailList = array_diff($mailList, $dbMails);//get to the mailList the mails that arent already in the database

        foreach($mailList as $email){
            if ($DB->get_field('threesixty_respondent', 'id', array('analysisid' => $analysisid, 'email'=> $email))) {
                $errors['email'] = get_string('validation:emailnotunique', 'threesixty');
            }
        }
//////////////////////////////////////////////////////////////////////////
/*
        ob_start();
        var_dump($email);
        $varsDat = ob_get_clean();
        $fp = fopen("textfile1.txt", "w");
        fwrite($fp, $varsDat);
        fclose($fp);
*/
///////////////////////////////////////////////////////////////////////////

        return $errors;
    }
}
