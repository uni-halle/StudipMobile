<?php

namespace Studip\Mobile;

require_once("resource.php");

require_once('Dropbox/autoload.php');
require_once("dropboxCom.php");

class Course {

    # TODO (mlunzena) remove these
    var $consumerKey     = '5wty9mf06gcuco0';
    var $consumerSecret  = 'hveok3hllw48hji';

    function __construct($id)
    {
        $seminar = new \Seminar($id);
        //if ($seminar->isVisible()) {
            $this->delegate = $seminar;
        //}
    }

    function __get($key)
    {
        return $this->delegate->$key;
    }

    function getUndecoratedData()
    {
        return $this->delegate->getUndecoratedData();
    }

    function isAuthorized($user_id)
    {
        return true;
    }


    static function findAllByUser($user_id)
    {
        foreach (\SemesterData::GetSemesterArray() as $value){
            if (isset($value['beginn']) && $value['beginn']) {
                $sem_start_times[] = (int) $value['beginn'];
            }
        }

        # these 2 queries are safe
        $sem_number_sql = sprintf('INTERVAL(start_time,%s)', join(',', $sem_start_times));
        $sem_number_end_sql = sprintf('IF(duration_time=-1,-1,INTERVAL(start_time+duration_time,%s))',
                                      join(',', $sem_start_times));

        $query = "SELECT seminare.VeranstaltungsNummer AS sem_nr,
                         schedule_seminare.color AS color,
                         seminare.Name, seminare.Seminar_id,
                         seminare.status as sem_status,
                         seminar_user.status, seminar_user.gruppe,
                         seminare.chdate, seminare.visible,
                         admission_binding,
                         modules,IFNULL(visitdate,0) as visitdate,
                         admission_prelim,
                         {$sem_number_sql} as sem_number,
                         {$sem_number_end_sql} as sem_number_end
                  FROM seminar_user
                  LEFT JOIN seminare  USING (Seminar_id)
                  LEFT JOIN schedule_seminare      ON (schedule_seminare.user_id=:user_id
                        AND schedule_seminare.seminar_id=seminare.Seminar_id)
                  LEFT JOIN object_user_visits ouv ON (ouv.object_id=seminar_user.Seminar_id
                        AND ouv.user_id = :user_id AND ouv.type='sem')
                  WHERE seminar_user.user_id = :user_id GROUP BY seminare.Seminar_id";

        $stmt = \DBManager::get()->prepare($query);
        $stmt->execute(compact("user_id"));

        return $stmt->fetchAll();
    }

    function countMembers($id)
    {
        //$query = "SELECT COUNT(*) FROM seminar_user WHERE Seminar_id = ? AND status IN ('autor', 'user') AND visible = 'yes'";
        $query = "SELECT COUNT(*) FROM seminar_user WHERE Seminar_id = ? AND visible = 'yes'";
        $stmt = \DBManager::get()->prepare($query);
        $stmt->execute(array($id));
        return $stmt->fetchColumn();
    }

    function getMembers($id)
    {
        $query = "SELECT seminar_user.Seminar_id, seminar_user.user_id, seminar_user.visible,
                  seminar_user.status, auth_user_md5.Vorname, auth_user_md5.Nachname, user_info.title_front
                  FROM   seminar_user
                  JOIN   auth_user_md5 ON auth_user_md5.user_id = seminar_user.user_id
                  JOIN   user_info     ON auth_user_md5.user_id = user_info.user_id
                  WHERE  seminar_user.visible = 'yes' AND seminar_user.Seminar_id = ?
                  ORDER BY FIELD(seminar_user.status, 'dozent','tutor' ,'autor', 'user'), auth_user_md5.Nachname";
        $stmt = \DBManager::get()->prepare($query);
        $stmt->execute(array($id));
        return $stmt->fetchAll();
    }


    static function find($id)
    {
        return new Course($id);
    }

    function isReadable($id, $user_id)
    {
        $courses = Course::findAllByUser($user_id);
        foreach ($courses AS $course) {
            if ($course["Seminar_id"] == $id) {
                return true;
            }
        }
        return false;
    }


/* /////////////////// */
/* RESSOURCE MANAGEMENT */
/* /////////////////// */


    static function getResources($course)
    {
        return Resource::getResources($course);
    }


/* ////////////////////// */
/*     Dropbox MANAGEMENT */
/* ////////////////////// */

    static function get_token($user_id)
    {
        $query ="SELECT *
                 FROM dropbox_tokens
                 WHERE dropbox_tokens.user_id = '$user_id'
                 ";
        $stmt = \DBManager::get()->query($query);
        return $stmt->fetchAll();
    }

/* /////////////////// */
/*     FILE MANAGEMENT */
/* /////////////////// */


    static function find_files($id, $user_id)
    {
        $db = \DBManager::get();
        $query ="SELECT *
                 FROM dokumente
                 WHERE seminar_id =  '$id'
                 ORDER BY chdate DESC
                 LIMIT 0,30";

        $result = $db->query($query);
        $files = array();
        foreach ($result as $row) {
            // getLink
            $link = $row['url'];
            if ($row['url'] == "" || !$row['url']) {
                $link    = GetDownloadLink($row['dokument_id'], $row['filename'], 0,'force_download');
            }

            // get file extension
            $path_parts = pathinfo($row['filename']);
            $extension  = strtoupper($path_parts['extension']);

            //get extension icon
            // TODO (mlunzena) replace this with a lookup table
            switch($extension)
            {
            case "PDF":
                $icon_link= "/public/images/icons/files32/pdf.png";
                break;
            case "XLS":
                $icon_link= "/public/images/icons/files32/xls.png";
                break;
            case "PPT":
                $icon_link= "/public/images/icons/files32/ppt.png";
                break;
            case "ZIP":
                $icon_link= "/public/images/icons/files32/zip.png";
                break;
            case "RTF":
                $icon_link= "/public/images/icons/files32/rtf.png";
                break;
            case "TXT":
                $icon_link= "/public/images/icons/files32/txt.png";
                break;
            case "TGZ":
                $icon_link= "/public/images/icons/files32/tgz.png";
                break;
            default:
                $icon_link= "/public/images/icons/files32/_blank.png";
            }
            //check access
            $file_object = \StudipDocument::find($row['dokument_id']);

            // Falls $file_oject vorhanden ab in den Array
            if (isset($file_object) && $file_object->checkAccess($user_id)) {
                $files[] = array(
                    'id'            => $row['dokument_id'],
                    'name'          => $row['name'],
                    'Seminar_id'    => $row['seminar_id'],
                    'author'        => $row['author_name'],
                    'author_id'     => $row['user_id'],
                    'description'   => $row['description'],
                    'mkdate'        => $row['mkdate'],
                    'chdate'        => $row['chdate'],
                    'filesize'      => $row['filesize'],
                    'link'          => $link,
                    'filename'      => $row['filename'],
                    'icon_link'      => $icon_link,
                    'extension'     => $extension
               );
            }
        }

        return $files;
    }

    static function findAllFiles($user_id)
    {
        //alle kurse besorgen
        $courses = self::findAllByUser($user_id);
        //alle files von alles kursen holen
        $files = array();
        foreach ($courses AS $course) {
            $actFiles = Course::find_files($course["Seminar_id"], $user_id);
            foreach ($actFiles AS $actFile) {
                array_push($files, $actFile);
            }

        }

        // TODO (mlunzena) something missing here
        var_dump($files);
    }

    /**
     * This script shoud upload a file to a
     * special folder in the users dropbox.
     * The session should bestarted and the
     * user should be logged in already.
     *
     * @param  filename should be the path to the local file
     * @param  folder the folder in the dropbox
     *
     * @return  fail:   filename    if something went wrong
     *          success:filename    if everything went right
     *          exists: filename    if the file already exists
     */
    static function dropboxUpload($fileid)
    {
        if (isset($fileid)) {

            //generate filename, filepath, intended_path
            //get filename from database
            $db = \DBManager::get();
            $query ="SELECT dokument_id, range_id, filename, seminar_id
                     FROM dokumente
                     WHERE dokumente.dokument_id = '$fileid'";
            $file_result = $db->query($query)->fetchAll();

            //get folder from database
            $seminar_id = $file_result[0]["seminar_id"];
            $range_id   = $file_result[0]["range_id"];

            $query = "SELECT folder.folder_id, folder.name as folder_name, seminare.Seminar_id, seminare.name as seminar_name
                      FROM  folder
                      JOIN  seminare ON seminare.Seminar_id = '$seminar_id'
                      WHERE folder.folder_id = '$range_id' ";
            $folder_result = $db->query($query)->fetchAll();

            // repart the important strings
            $filename = Helper::cleanFilename($file_result[0]["filename"]);
            $file     = $GLOBALS['UPLOAD_PATH']."/".substr($fileid,0,2)."/".$fileid;
            $folder   = Helper::cleanFilename($folder_result[0]["seminar_name"] ."/".  $folder_result[0]["folder_name"]);
            //check if everthing is allright
            if (!isset($filename) || !isset($file) || !isset($folder)) {
                if (!isset($filename)) {
                    $filename = fileid;
                }
                return "fail:".$filename;
            }

            /* start interaction width dropbox
               session shoud be started, user should logged in
               Please supply your own consumer key and consumer secret */

            // TODO (mlunzena) remove credentials from source code
            $consumerKey    = '5wty9mf06gcuco0';
            $consumerSecret = 'hveok3hllw48hji';

            try {
                $oauth   = new \Dropbox_OAuth_PEAR($consumerKey, $consumerSecret);
                $dropbox = new \Dropbox_API($oauth,\Dropbox_API::ROOT_SANDBOX);

                $oauth->setToken($_SESSION['oauth_tokens']);

                //Check if the directories are created and
                //single subfolders in $folders
                $folders = explode("/", $folder);
                $checked_path = "/";
                foreach ($folders AS $subfolder) {
                    $found_folder = false;
                    $info = $dropbox->getMetaData($checked_path);
                    foreach($info["contents"] AS $meta_info) {
                        if ($meta_info["is_dir"] == 1 && Helper::endsWith($meta_info["path"],$subfolder)) {
                            $found_folder = true;
                            break;
                        }
                    }
                    if (!$found_folder) {
                        $dropbox->createFolder($checked_path . "/" . $subfolder);
                    }
                    if ($checked_path == "/") {
                        $checked_path .= $subfolder;
                    } else {
                        $checked_path .= "/" .$subfolder;
                    }
                }

                //depending folder exists

                //check if file already exisits
                $found = false; // $found shows if file already exisits
                $info  = $dropbox->getMetaData($folder);
                foreach ($info["contents"] AS $array_files) {
                    if (strpos($array_files["path"], $filename) != false) {
                        $ausgabe= "exists:" . $filename;
                        $found = true;
                    }
                }

                //Upload the file if nessasery
                if ($found == false) {
                    if($dropbox->putFile($folder . "/". $filename, $file)) {
                        $ausgabe= "success:" . $filename;
                    } else {
                        $ausgabe= "fail:" . $filename;
                    }
                }
            } catch(HTTP_OAuth_Exception $e) {
                $ausgabe= "fail:" . $filename;
            } catch(Exception $e) {
                // something went wrong, not specified
                // to specify the error there are other exeptions to catch
                $ausgabe = "fail:" . $filename;
            }
        } else {
            $ausgabe= "fail:" . $filename;
        }

        return $ausgabe;
    }



    function createDropboxFolders($semId)
    {
        // TODO (mlunzena) remove credentials
        $consumerKey    = '5wty9mf06gcuco0';
        $consumerSecret = 'hveok3hllw48hji';


        $folder_paths = self::get_folder_pathes($semId);
        $ausgabe = "Success";
        try {
            $oauth   = new \Dropbox_OAuth_PEAR($consumerKey, $consumerSecret);
            $dropbox = new \Dropbox_API($oauth,\Dropbox_API::ROOT_SANDBOX);

            $oauth->setToken($_SESSION['oauth_tokens']);
        } catch (Exception $e) {
            return "Error";
        }

        foreach ($folder_paths as $folder_path) {
            try {
                if (!self::create_dropbox_folder($folder_path, $dropbox)) {
                    $ausgabe ="Error";
                }
            } catch(Exception $e) {
                // TODO (mlunzena) how to handle this?
                throw $e;
            }
        }
        return $ausgabe;
    }


    /*
     * get all readable(!) Folderpathes for a seminar
     * pathexample: Veranstaltungsname/Ordner/unterodern
     * param:   semId  seminar Id
     */
    function get_folder_pathes($semId)
    {
        if (isset($semId)) {
            $seminar        = \Seminar::GetInstance($semId);
            $seminar_name   = Helper::cleanFilename($seminar->getName());
            $folder_tree    = \TreeAbstract::GetInstance('StudipDocumentTree', array('range_id' => $semId));
            //$folder_ids = array_keys($folder_tree->tree_data);
            //unset($folder_ids[0]); // root element l�schen
            $folder_ids = $folder_tree->getReadableFolders($GLOBALS['user']->id);


            $folder_paths = array();
            foreach ($folder_ids as $folder_id) {
                if (!$folder_tree->hasKids($folder_id)) {
                    $folder_paths[]= $seminar_name."/"
                    .Helper::cleanFilename(
                        eregi_replace("/virtual/","",eregi_replace(" / ", "/", $folder_tree->getItemPath($folder_id)))
                   );
                }
            }
            return $folder_paths;
        }
        return false;
    }

    /* creates a group of folders in the dropbox
     * pathexample: Veranstaltungsname/Ordner/unterodern/...
     * @param folderDumpPath path
     */
    function create_dropbox_folder($folderDumpPath, $dropbox)
    {

        //connection to dropbox should be valid

        if (isset($_SESSION['oauth_tokens'])) {
            try {
                //Check if the directories are created and
                //single subfolders in $folders
                $folders = explode("/", $folderDumpPath);
                $checked_path = "/";
                foreach ($folders AS $subfolder) {
                    $found_folder = false;
                    $info = $dropbox->getMetaData($checked_path);

                    foreach ($info["contents"] AS $meta_info) {
                        if ($meta_info["is_dir"] == 1 && Helper::endsWith($meta_info["path"], $subfolder)) {
                            $found_folder = true;
                            break;
                        }
                    }
                    if (!$found_folder) {
                        $dropbox->createFolder($checked_path . "/" . $subfolder);
                    }

                    if ($checked_path == "/") {
                        $checked_path .= $subfolder;
                    } else {
                        $checked_path .= "/" .$subfolder;
                    }
                }
            } catch(Exception $e) {
                // something went wrong, not specified
                // to specify the error there are other exeptions to catch
                return false;
            }
        } else {
            return false;
        }

        return true;
    }

    /* returns the path to a folder without the seminar name in front
     * if not readable return false
     * @param folderid
     * @return path to a folder without the seminar name in front
     */
    function get_droppath_for_folder($folderId)
    {
        $folder_tree = \TreeAbstract::GetInstance('StudipDocumentTree', array('range_id' => $semId));

        if ($folder_tree->isReadable($folderId,$GLOBALS['user']->id)) {
            return eregi_replace("/virtual/","",eregi_replace(" / ", "/", $folder_tree->getItemPath($item_id)));
        }
        return false;
    }

    function connectToDropbox($user_id, $link)
    {
        return DropboxCommunication::dropConnect($this->currentUser()->id, $link);
    }
}
