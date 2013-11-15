<?php

/**
 * Contains methods used for user logins.
 */
class UserLogin {

	/**
	 * Determines whether user is logged in.
	 * If user is not logged in, they're redirected to login page.
	 * @access public
	 * @static
	 * @return
	 */
	public static function perform_login_check() {
		if (self::is_logged_in()) {
			# User is logged in - leave them be
			return (true);
		} else {
			# User isn't logged in - send them to the login form
			$host  = $_SERVER['HTTP_HOST'];
			$uri  = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
			$extra = 'login.php';
			header("Location: http://$host$uri/$extra");

			exit;
		}
	}
	/**
	 * Checks to see if user is logged in.
	 * This is determined by checking a cookie.
	 * @access public
	 * @static
	 * @return boolean
	 */
	private static function is_logged_in () {
		# Check for the existence of the cookie
		if (isset($_COOKIE["user_id"])) {
			return (true);
		} else {
			return (false);
		}
	}

	/**
	 * Returns users.id value based on username/password provided.
	 * @access public
	 * @static
	 * @return $company['id']
	 * @param string $inUsername
	 * @param string $inPassword
	 * @param object $inDb
	 */
	public static function get_user_id ($inUsername, $inPassword, $inDb) {
		// Check we have the values we need
		if ($inUsername == '') {throw new Exception("Unable to check credentials. No username provided.");}
		if ($inPassword == '') {throw new Exception("Unable to check credentials. No password provided.");}
		if ($inDb == '') {throw new Exception("Unable to check credentials. No database details provided.");}
		// Now query
		$sql = "SELECT ";
		$sql .= " id ";
		$sql .= "FROM ";
		$sql .= " users ";
		$sql .= "WHERE ";
		$sql .= " username='".$inUsername."' ";
		$sql .= " and password='".md5($inPassword)."' ";
		// Execute the query
		if (!$results = $inDb->query($sql)) {throw new Exception("Query to check credentials failed: ".$inDb->error());}
		// Check the results
		if ($results->num_rows == 1) {
			while ($user = $results->fetch_array()) {
				return ($user['id']);
			}
		} elseif ($results->num_rows > 1) {
			throw new Exception("Duplicate rows returned when checking credentials. Data needs correcting. Please notify us of this error.");
		} else {
			throw new Exception("Sorry, the details you provided us do not match those that we have in the system. Please try again.");
		}
	}

	/**
	 * Based on the ID passed in, logs the user in.
	 * Logging the user in is achieved by placing a cookie.
	 * @access public
	 * @static
	 * @return boolean
	 * @param integer $inId
	 */
	public static function login($inId) {
		if (setcookie("user_id", $inId, time()+60*60*24*365)) {
			return (true);
		} else {
			throw new Exception("Sorry, we are unable to log you in. Please check that your browser is setup to accept cookies.");
		}
	}

	// Logs the user out
	/**
	 * Logs the user out of the client site.
	 * Logging a user out is achieved by deleting the cookie.
	 * @access public
	 * @static
	 * @return boolean
	 */
	public static function logout() {
		# Delete the cookie
		if (setcookie("user_id", "", time()-60*60*24*365)) {
			return (true);
		} else {
			throw new Exception("Sorry, we are unable to log you out.");
		}
	}
}
?>