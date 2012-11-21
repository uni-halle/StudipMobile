<?php

require_once 'Dropbox/autoload.php';


//fopen("http://localhost/~nils/studip/public/sendfile.php?force_download=0&type=0&file_id=aa164a04d7fa4f69535ec3d7d99f57a5&file_name=Auto_Plan.xls", "r");

$this->set_layout("layouts/single_page_back");
$page_title      = "Dateien droppen";
$page_id         = "courses-dropfiles";



////////////////////////////////
///// LOGIN TO DROPBOX   //////
////////////////////////////////
// custom settings
$consumerKey     = '5wty9mf06gcuco0';
$consumerSecret  = 'hveok3hllw48hji';
$call_back_link  = "http://localhost/~nbussman/studip2/public/plugins.php/studipmobile/courses/dropfiles/" . $seminar_id;

// is set if the user is logged in
$connection_good = false;

$tokenWasWrong   = false;
$tokenNew        = true;
//check if the php pear dropbox extension is installed
if (class_exists("Dropbox_OAuth_PEAR") && class_exists("Dropbox_API") )
{

	//require 'Dropbox/autoload.php';

	$oauth   = new Dropbox_OAuth_PEAR ($consumerKey, $consumerSecret);
	$dropbox = new Dropbox_API        ($oauth, Dropbox_API::ROOT_SANDBOX);

	// For convenience, definitely not required
	//header('Content-Type: text/plain');

	// We need to start a session
	session_start();

    //check if tokens are already stored
    if ( isset( $db_tokens[0]['token'], $db_tokens[0]['token_secret'] ) )
    {
	     // user already got dropbox linked
	     $_SESSION['state']         = 3;
	     $_SESSION['oauth_tokens']  = array( "token" => $db_tokens[0]['token'], 
	                         				 "token_secret" =>  $db_tokens[0]['token_secret'] );
	     $tokenNew = false;
    }

	// There are multiple steps in this workflow, we keep a 'state number' here
	if (isset($_SESSION['state'])) {
	    $state = $_SESSION['state'];
	} else {
	    $state = 1;
	}

	// switch cases for autification
        switch($state) 
                        {
                        
                            /* In this phase we grab the initial request tokens
                               and redirect the user to the 'authorize' page hosted
                               on dropbox */
                            case 1 :
                                $tokens 					= $oauth->getRequestToken();
                                $link 						= $oauth->getAuthorizeUrl( $call_back_link ) . "\n";

                                // Note that if you want the user to automatically redirect back, you can
                                // add the 'callback' argument to getAuthorizeUrl.
                                $link = $oauth->getAuthorizeUrl( $call_back_link ) . "\n";
                                $_SESSION['state'] 			= 2;
                                $_SESSION['oauth_tokens'] 	= $tokens;
                                ?>
                                <ul data-role="listview" data-inset="true" data-theme="e">
                                        <li>
                                                <center><img src="<?=$plugin_path ?>/public/images/dropbox.png" ></center>
                                                <h1>Dropbox mit Studip verbinden</h1>
                                                Um Dateien mit ihrer Dropbox auszutauschen müssen Sie Ihre Dropbox mit Studip verbinden.<br /> Hierzu müssen Sie sich einloggen.<br /> Dateien werden sie unter<strong>Apps/studipmobile</strong> finden.<br /><small>StudIp erhält nicht auf Ihre gesamte Dropbox Zugriff.</small>
                                        </li>
                                </ul>
                                <a href="<?=$link ?>" data-role="button" data-theme="b">StudIp verbinden</a>
                                <?
                                //die();
                                break;
                        
                            /* In this phase, the user just came back from authorizing
                               and we're going to fetch the real access tokens */
                            case 2 :
                                $oauth->setToken($_SESSION['oauth_tokens']);
                                try{
                                    $tokens            		  = $oauth->getAccessToken();
                                    $_SESSION['state'] 		  = 3;
                                    $state             		  = 3;
                                    $_SESSION['oauth_tokens'] = $tokens;
                                }catch(Exception $e){
                                    $_SESSION['state']        = 1;
                                    $state                    = 1;
                                    $_SESSION['oauth_tokens'] = NULL;
                                    $tokens                   = NULL;
                                    $link = $oauth->getAuthorizeUrl( $call_back_link ) . "\n";

                                    ?>
                                    <ul data-role="listview" data-inset="true" data-theme="e">
                                            <li>
                                                    <center><img src="<?=$plugin_path ?>/public/images/dropbox.png" ></center>
                                                    <h1>Dropbox mit Studip verbinden</h1>
                                                    Um Dateien mit ihrer Dropbox auszutauschen müssen Sie Ihre Dropbox mit Studip verbinden.<br /> Hierzu müssen Sie sich einloggen.<br /> Dateien werden sie unter<strong>Apps/studipmobile</strong> finden.<br /><small>StudIp erhält nicht auf Ihre gesamte Dropbox Zugriff.</small>
                                            </li>
                                    </ul>
                                    <a href="<?=$link ?>" data-role="button" data-theme="b">StudIp verbinden</a>
                                    <?
                                    //die();
                                    break;
                                }
                                // There is no break here, intentional
                        
                            /* This part gets called if the authentication process
                               already succeeded. We can use our stored tokens and the api 
                               should work. Store these tokens somewhere, like a database */
                            case 3 :
                                try
                                {
                                        $oauth->setToken($_SESSION['oauth_tokens']);
                                        $connection_good = true;
                                        //checks if Connection is Good
                                        $dropbox->getAccountInfo();
                                }
                                catch(Dropbox_Exception $e)
                                {
                                        ?>	<ul data-role="listview" data-inset="true" data-theme="e">
	                                        	<li>Gespeicherter key falsch.</li>
	                                        </ul>
	                                    <?
                                        $_SESSION['state'] = 1;
                                        $state             = 1;
                                        $connection_good   = false;
                                        $link = $oauth->getAuthorizeUrl( $call_back_link ) . "\n";
                                        $tokenWasWrong 	   = true;
                                        ?>
                                        <ul data-role="listview" data-inset="true" data-theme="e">
                                            <li>
                                                    <center><img src="<?=$plugin_path ?>/public/images/dropbox.png" ></center>
                                                    <h1>Dropbox mit Studip verbinden</h1>
                                                    Um Dateien mit ihrer Dropbox auszutauschen müssen Sie Ihre Dropbox mit Studip verbinden.<br /> Hierzu müssen Sie sich einloggen.<br /> Dateien werden sie unter<strong>Apps/studipmobile</strong> finden.<br /><small>StudIp erhält nicht auf Ihre gesamte Dropbox Zugriff.</small>
                                            </li>
	                                    </ul>
	                                    
	                                    <a href="<?=$link ?>" data-role="button" data-theme="b">StudIp verbinden</a>
	                                    <?
                                }
                                break;
                        }
                       
                        if ( $connection_good == true )
                        {
                            //print_r($_SESSION['oauth_tokens']);
                            /*
                             * save tokens in database
                             */
                            $token_token  = $_SESSION['oauth_tokens']['token'];
                            $token_secret = $_SESSION['oauth_tokens']['token_secret'];
                            $db    = \DBManager::get();
                            
                            // ändernungen an der Datenbank
                            if ($tokenWasWrong)
                            {
	                            //when saved token is wrong -> Update the token in database
	                            $query = "UPDATE `dropbox_tokens`
	                            		  SET token='$token_token', token_secret='$token_secret'
	                            		  WHERE user_id='$user_id'";
	                            $result = $db->query($query);
                            }
                            elseif ($tokenNew)
                            {
	                            //else save the new token in database
	                            $query = "INSERT INTO `dropbox_tokens` 
                            		  			 (user_id,           token,             token_secret)
                            		  	  VALUES ('$user_id', '$token_token', '$token_secret')";
                            	$result = $db->query($query);
                            }
                            
                            $accInfo= $dropbox->getAccountInfo();
                            ?>
                            <ul data-role="listview" data-inset="true" data-theme="e">
                                <li>
                                    <h1>Verbundener Dropbox Account</h1>
                                    <fieldset class="ui-grid-a">
                                     <div class="ui-block-a" style="font-size:10pt;font-weight:normal;">Name:<br>Mail:</div> 
                                     <div class="ui-block-b" style="font-size:10pt;font-weight:normal;"><?=$accInfo["display_name"] ?> <br><?=$accInfo["email"] ?> </div>       
                                    </fieldset>
                                </li>
                            </ul>
                            
                            
                            <?
                            	////////////////////////////////
                            	///// LOGIN PROZESS FERTIG  ////
                            	////////////////////////////////

                            ?>
                            <script>
                                 //create_folders('<?= $controller->url_for("courses/createDropboxFolder", htmlReady( $seminar_id )) ?>');
                                 //creating folders, after that uploading files
                                 $.ajax(
									{
									  	type:  "GET",
									  	url:   '<?= $controller->url_for("courses/createDropboxFolder", htmlReady( $seminar_id )) ?>',
									  	data:  { },
										success: function( data )
										{
										    DROPBOX_COUNTER = 0;
											var newLI           = document.createElement("li");
											newLI.className         = "ui-li ui-li-static ui-body-b ui-corner-top ui-corner-bottom";
											newLI.innerHTML =  "Ordnerstruktur angelegt";
											document.getElementById("uploadList").appendChild(newLI);
										},
										error: function()
										{
											var newLI           = document.createElement("li");
											newLI.innerHTML =  "Anlegen Ordnerstruktur fehlgeschlagen";
											newLI.className         = "ui-li ui-li-static ui-body-b ui-corner-top ui-corner-bottom";
											document.getElementById("uploadList").appendChild(newLI);
										}
										
									}).done(function() { 
											<?
												list($upload_link) = explode( "?cid=",$controller->url_for("courses/upload") );
												foreach($files AS $file)
												{
													?>
														uploadFileDropbox("<?=htmlReady($upload_link) ?>","<?= $file['id'] ?>");
													<?
												}
											?>
									});
                                 
                            </script>
                            <ul id="uploadList" data-role="listview" data-inset="true" data-theme="b" data-divider-theme="a">
                                <li data-role="list-divider">Abgleich beginnt</li>
                                </ul>
                            <?
                        }
}
else
{
	?>
	<ul data-role="listview" data-inset="true" data-theme="e">
		<li>
			<center><img src="<?=$plugin_path ?>/public/images/dropbox.png" ></center>
			<h1>Dropbox Fehler</h1>
			<p>Leider ist von dem Systemadministrator nicht die PHP Pear Erweiterung Dropbox installiert worden.<br /> Daher steht Ihnen dieses Feature nicht zu Verfügung.</p>
		</li>
	</ul>
	<?
}



?>
