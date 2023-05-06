<?
/*========================================================================
*   Open eClass 2.3
*   E-learning and Course Management System
* ========================================================================
*  Copyright(c) 2003-2010  Greek Universities Network - GUnet
*  A full copyright notice can be read in "/info/copyright.txt".
*
*  Developers Group:    Costas Tsibanis <k.tsibanis@noc.uoa.gr>
*                       Yannis Exidaridis <jexi@noc.uoa.gr>
*                       Alexandros Diamantidis <adia@noc.uoa.gr>
*                       Tilemachos Raptis <traptis@noc.uoa.gr>
*
*  For a full list of contributors, see "credits.txt".
*
*  Open eClass is an open platform distributed in the hope that it will
*  be useful (without any warranty), under the terms of the GNU (General
*  Public License) as published by the Free Software Foundation.
*  The full license can be read in "/info/license/license_gpl.txt".
*
*  Contact address:     GUnet Asynchronous eLearning Group,
*                       Network Operations Center, University of Athens,
*                       Panepistimiopolis Ilissia, 15784, Athens, Greece
*                       eMail: info@openeclass.org
* =========================================================================*/

$require_admin = true;
include '../../include/baseTheme.php';
include '../../include/sendMail.inc.php';
$navigation[] = array("url" => "../admin/index.php", "name" => $langAdmin);

// Initialise $tool_content
$tool_content = "";

$all_set = register_posted_variables(array(
        'auth' => true,
        'uname' => true,
        'nom_form' => true,
        'prenom_form' => true,
        'email_form' => true,
        'language' => true,
        'department' => true,
        'comment' => false,
        'password' => true,
        'pstatut' => true,
        'rid' => false,
        'submit' => true));

$submit = isset($_POST['submit'])?$_POST['submit']:'';

if($submit) {
	// register user
	$depid = intval(isset($_POST['department'])?$_POST['department']: 0);
	$proflanguage = isset($_POST['language'])?$_POST['language']:'';
	if (!isset($native_language_names[$proflanguage])) {
		$proflanguage = langname_to_code($language);
	}

	$mysqli = new mysqli($GLOBALS['mysqlServer'], $GLOBALS['mysqlUser'], $GLOBALS['mysqlPassword'], $mysqlMainDb);

	$stmt = $mysqli->prepare("SELECT username FROM `$mysqlMainDb`.user WHERE username=?");
	$stmt->bind_param("s", htmlspecialchars(strip_tags($uname)));
	$stmt->execute();

	// check if user name exists
    $username_check = $stmt->get_result();
    $rows = $username_check->fetch_assoc();
    $user_exist = ($rows > 0);

    $stmt->close();
    $mysqli->close();

	// check if there are empty fields
	if (!$all_set) {
		$tool_content .= "<p class='caution_small'>$langEmptyFields</p>
			<br><br><p align='right'><a href=" . htmlspecialchars($_SERVER['PHP_SELF']) . "'>$langAgain</a></p>";
	} elseif ($user_exist) {
		$tool_content .= "<p class='caution_small'>$langUserFree</p>
			<br><br><p align='right'><a href=" . htmlspecialchars($_SERVER['PHP_SELF']) . "'>$langAgain</a></p>";
	} elseif(!email_seems_valid($email_form)) {
		$tool_content .= "<p class='caution_small'>$langEmailWrong.</p>
			<br><br><p align='right'><a href=" . htmlspecialchars($_SERVER['PHP_SELF']) . "'>$langAgain</a></p>";
	} else {
  
		$registered_a = time();
		$expires_at = time() + $durationAccount;
		$password_encrypted = md5($password);
		
		$mysqli = new mysqli($GLOBALS['mysqlServer'], $GLOBALS['mysqlUser'], $GLOBALS['mysqlPassword'], $mysqlMainDb);

		// Check connection
		if ($mysqli->connect_error) {
			die("Connection failed: " . $mysqli->connect_error);
		}

		// Sanitize the variables by removing any harmful characters
		// TODO: this is better than the other approaches
		$nom_form = filter_var($nom_form, FILTER_SANITIZE_STRING);
		$prenom_form = filter_var($prenom_form, FILTER_SANITIZE_STRING);
		$uname = filter_var($uname, FILTER_SANITIZE_STRING);
		$email_form = filter_var($email_form, FILTER_SANITIZE_EMAIL);
		$comment = filter_var($comment, FILTER_SANITIZE_STRING);
		$proflanguage = filter_var($proflanguage, FILTER_SANITIZE_STRING);
		$depid = filter_var($depid, FILTER_SANITIZE_NUMBER_INT);

		// Prepare the SQL statement
		$stmt = $mysqli->prepare("INSERT INTO user (nom, prenom, username, password,
								email, statut, department, am,
								registered_at, expires_at, lang)
								VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

		// Bind the variables to the placeholders
		$stmt->bind_param("sssssiisiis", $nom_form, $prenom_form, $uname, $password_encrypted, $email_form, $pstatut, $depid, $comment, $registered_at, $expires_at, $proflanguage);

		// Execute the SQL statement
		$stmt->execute();

		echo "New record created successfully";

		// Close the statement and connection
		$stmt->close();
		$mysqli->close();

	  	$rid = mysql_real_escape_string(intval($_POST['rid']));

  	  	db_query("UPDATE prof_request set status = 2, date_closed = NOW() WHERE rid = '$rid'");

                if ($pstatut == 1) {
                        $message = $profsuccess;
                        $reqtype = '';
                        $type_message = $langAsProf;
                } else {
                        $message = $usersuccess;
                        $reqtype = '?type=user';
                        $type_message = '';
                        // $langAsUser;
                }
	       	$tool_content .= "<p class='success_small'>$message</p><br><br><p align='right'><a href='../admin/listreq.php$reqtype'>$langBackRequests</a></p>";
		
		// send email
		
        $emailsubject = "$langYourReg $siteName $type_message";
        $emailbody = "
$langDestination $prenom_form $nom_form

$langYouAreReg $siteName $type_message, $langSettings $uname
$langPass : $password
$langAddress $siteName $langIs: $urlServer
$langProblem

$administratorName $administratorSurname
$langManager $siteName
$langTel $telephone
$langEmail : $emailhelpdesk
";
		
		send_mail('', '', '', $email_form, $emailsubject, $emailbody, $charset);

        }

} else {
		$lang = false;

		// Create connection
		$mysqli = new mysqli($GLOBALS['mysqlServer'], $GLOBALS['mysqlUser'], $GLOBALS['mysqlPassword'], $mysqlMainDb);

		// Check connection
		if ($mysqli->connect_error) {
			die("Connection failed: " . $conn->connect_error);
		}

		// Prepare the SQL statement
		$stmt = $mysqli->prepare("SELECT profname, profsurname, profuname, profemail, proftmima, comment, lang, statut FROM prof_request WHERE rid=?");

		// Bind the variable to the placeholder
		$stmt->bind_param("i", intval($id));

		// Execute the SQL statement
		$stmt->execute();

		// Fetch the result set and store the values in variables
		$stmt->bind_result($pn, $ps, $pu, $pe, $pt, $pcom, $lang, $pstatut);
		$stmt->fetch();

		// Sanitize the variables by removing any harmful characters
		$pn = filter_var($pn, FILTER_SANITIZE_STRING);
		$ps = filter_var($ps, FILTER_SANITIZE_STRING);
		$pu = filter_var($pu, FILTER_SANITIZE_STRING);
		$pe = filter_var($pe, FILTER_SANITIZE_EMAIL);
		$pt = filter_var($pt, FILTER_SANITIZE_STRING);
		$pcom = filter_var($pcom, FILTER_SANITIZE_STRING);
		$lang = filter_var($lang, FILTER_SANITIZE_STRING);
		$pstatut = filter_var($pstatut, FILTER_SANITIZE_NUMBER_INT);

		// Close the statement and connection
		$stmt->close();
		$mysqli->close();

	} elseif (@$_GET['type'] == 'user') {
                $pstatut = 5;
        } else {
                $pstatut = 1;
        }

        if ($pstatut == 5) {
                $nameTools = $langUserDetails;
                $title = $langInsertUserInfo;
        } else {
                $nameTools = $langProfReg;
                $title = $langNewProf;
        }

	$tool_content .= "<form action='" . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES) . "' method='post'>
	<table width='99%' align='left' class='FormData'>
	<tbody><tr>
	<th width='220'>&nbsp;</th>
	<td><b>$title</b></td>
	</tr>
	<tr>
	<th class='left'><b>".$langSurname."</b></th>
	<td><input class='FormData_InputText' type='text' name='nom_form' value='".@$ps."' >&nbsp;(*)</td>
	</tr>
	<tr>
	<th class='left'><b>".$langName."</b></th>
	<td><input class='FormData_InputText' type='text' name='prenom_form' value='".@$pn."'>&nbsp;(*)</td>
	</tr>
	<tr>
	<th class='left'><b>$langUsername</b></th>
	<td><input class='FormData_InputText' type='text' name='uname' value='".@$pu."'>&nbsp;(*)</td>
	</tr>
	<tr>
	<th class='left'><b>$langPass</b></th>
	<td><input class='FormData_InputText' type='text' name='password' value='".create_pass()."'></td>
	</tr>
	<tr>
	<th class='left'><b>$langEmail</b></th>
	<td><input class='FormData_InputText' type='text' name='email_form' value='".@$pe."'>&nbsp;(*)</b></td>
	</tr>
	<tr>
	<th class='left'>$langFaculty</th>
	<td>";
	$dep = array();
	$deps = db_query("SELECT id, name FROM faculte order by id");
	while ($n = mysql_fetch_array($deps)) {
		$dep[$n['id']] = $n['name'];
	}
	if (isset($pt)) {
		$tool_content .= selection ($dep, 'department', $pt);
	} else {
		$tool_content .= selection ($dep, 'department');
	}
	$tool_content .= "</td>
	</tr>
	<tr>
	<th class='left'><b>$langComments</b></th>
	<td><input class='FormData_InputText' type='text' name='comment' value='".@q($pcom)."'>&nbsp;</b></td>
	</tr>
	<tr>
	<th class='left'>$langLanguage</th>
	<td>";
	$tool_content .= lang_select_options('language', '', $lang);
	$tool_content .= "</td></tr>
	<tr>
	<th>&nbsp;</th>
	<td><input type='submit' name='submit' value='$langSubmit' >
		<small>$langRequiredFields</small></td>
	</tr>
	</tbody>
	</table>
	<input type='hidden' name='rid' value='".@$id."'>
	<input type='hidden' name='pstatut' value='$pstatut'>
        <input type='hidden' name='auth' value='1' >
	</form>";
	$tool_content .= "
	<br />
	<p align='right'><a href='../admin/index.php'>$langBack</p>";
}

draw($tool_content, 3, 'auth');
