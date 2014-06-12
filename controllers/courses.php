<?php

require "StudipMobileAuthenticatedController.php";
require dirname(__FILE__) . "/../models/course.php";

use Studip\Mobile\Course;

/**
 *    get the courses and all combined stuff, like files and
 *    members ...
 *    @author Nils Bussmann - nbussman@uos.de
 */
class CoursesController extends StudipMobileAuthenticatedController
{
    function index_action()
    {
        // get current semester
        $this->semester = \SemesterData::GetSemesterArray();
        // get all courses
        $this->courses  = Course::findAllByUser($this->currentUser()->id);
    }

    function list_files_action($id = NULL)
    {
        // is the user in the course?
        $this->course = Course::find($id);

        if (!Course::isReadable($id, $this->currentUser()->id)) {
            throw new Trails_Exception(403);
        }

        // give seminarId to the view
        $this->seminar_id = $id;
        // get files as array and give it to the view
        $this->files = Course::find_files($id, $this->currentUser()->id);

        // mark as visited
        object_set_visit($id, 'documents');
    }

    function show_action( $id = NULL )
    {
        // get specific course
        $this->course = Course::find($id);
        // get specific ressources for the course
        $this->resources = Course::getResources($this->course);

        //exception if course is not readable
        if (!$this->course) {
            throw new Trails_Exception(404);
        }

        if (!Course::isReadable($id, $this->currentUser()->id)) {
            throw new Trails_Exception(403);
        }

        $this->next_dates = array();
        $termine = SeminarDB::getNextDate($id);
        foreach ($termine["termin"] as $termin) {
            $this->next_dates[] = new SingleDate($termin);
        }
        if ($termine["ex_termin"]) {
            $this->next_dates[] = new SingleDate($termine["ex_termin"]);
        }
    }

    function show_map_action($id)
    {
        // get specific course object
        $this->course = Course::find($id);
        // get destinations of the course
        $this->resources = Course::getResources($this->course);

        if (!Course::isReadable($id, $this->currentUser()->id)) {
            throw new Trails_Exception(403);
        }
    }

    function dropfiles_action( $id = NULL )
    {

        if (!StudipMobile::DROPBOX_ENABLED) {
            throw new Trails_Exception(400);
        }

        if (!Course::isReadable($id, $this->currentUser()->id)) {
            throw new Trails_Exception(403);
        }

        //generate the callbacklink
        $call_back_link =  "http://".$_SERVER['HTTP_HOST'].$this->url_for("courses/dropfiles", htmlReady($id));
        // give seminar id to the view
        $this->seminar_id = $id;
        // get files to sync width the userers dropbox
        // the view starts the upload via ajax
        $this->files = Course::find_files($id, $this->currentUser()->id);
        // give user_id t the view
        $this->user_id = $this->currentUser()->id;
        // start the sync prozess
        $this->dropCom = Course::connectToDropbox( $this->currentUser()->id, $call_back_link );
    }

    /*
     *  this controller function is called by the view via ajax
     *  the user should be connected to dropbox
     */
    function upload_action( $fileid )
    {
        if (!StudipMobile::DROPBOX_ENABLED) {
            throw new Trails_Exception(400);
        }

        // try to upload a specific file to the users dropboxs
        $this->upload_info = Course::dropboxUpload($fileid);
    }

    /*
     *  this controller makes sure that the folder structure
     *  of the specific course is correctly mapped in the
     *  users dropbox.
     *  if not: make the structure
     */
    function createDropboxFolder_action( $semId )
    {
        if (!StudipMobile::DROPBOX_ENABLED) {
            throw new Trails_Exception(400);
        }

        $this->createdFolderInfo = Course::createDropboxFolders( $semId );
    }


    const MEMBER_THRESHOLD = 40;

    /*
     *  give an array width all members to the view
     */
    function show_members_action($semId)
    {
        if (!Course::isReadable($semId, $this->currentUser()->id)) {
            throw new Trails_Exception(403);
        }
        $this->course = Course::find($semId);

        $count = $this->course->countMembers($semId);

        if (Request::submitted("deep") || $count <= self::MEMBER_THRESHOLD) {
            $this->members = Course::getMembers($semId);
        }
    }

    /*
     *  drops all files of all the courses to the users dropbox
     *  !! not implemented right now
     */
    function dropAll_action()
    {

        // TODO (mlunzena) clear up this method
        throw new Trails_Exception(500, "Not implemented.");

        if (!StudipMobile::DROPBOX_ENABLED) {
            throw new Trails_Exception(400);
        }

        $call_back_link         = $_SERVER['HTTP_HOST'].$this->url_for("courses/dropfiles", htmlReady($id) );
        $this->files            = Course::findAllFiles( $this->currentUser()->id );
        $this->user_id          = $this->currentUser()->id;
        $this->courses          = Course::findAllByUser($this->currentUser()->id);
        $this->dropCom          = Course::connectToDropbox( $this->currentUser()->id, $call_back_link );
    }
}
