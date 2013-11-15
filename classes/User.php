<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of User
 *
 * @author Greg
 */
class User {

    // <editor-fold defaultstate="collapsed" desc="Static Variables">
    // </editor-fold>

    // <editor-fold defaultstate="collapsed" desc="Instance Variables">
   private $id;
   private $username;
   private $password;
   private $email;
   private $first_name;
   private $surname;
   private $description;
   private $homepage;
   private $default_scope;
   private $forgotten_password_uid;
   private $web_hook_url_nugget_add;
   private $web_hook_url_nugget_update;
   private $web_hook_url_nugget_delete;
   private $dt_last_mod;
   private $dt_created;
   private $user_type = null; // guest or member
   private $db;
    // </editor-fold>

    // <editor-fold defaultstate="collapsed" desc="Getters and Setters">
    public function getDb() {
        return $this->db;
    }

    public function setDb($db) {
        $this->db = $db;
    }
    public function getId() {
       return $this->id;
    }

    public function setId($id) {
       $this->id = $id;
    }

    public function getUsername() {
       return $this->username;
    }

    public function setUsername($username) {
       $this->username = $username;
    }

    public function getPassword() {
       return $this->password;
    }

    public function setPassword($password) {
       $this->password = $password;
    }

    public function getEmail() {
       return $this->email;
    }

    public function setEmail($email) {
       $this->email = $email;
    }

    public function getFirst_name() {
       return $this->first_name;
    }

    public function setFirst_name($first_name) {
       $this->first_name = $first_name;
    }

    public function getSurname() {
       return $this->surname;
    }

    public function setSurname($surname) {
       $this->surname = $surname;
    }

    public function getDescription() {
       return $this->description;
    }

    public function setDescription($description) {
       $this->description = $description;
    }

    public function getHomepage() {
       return $this->homepage;
    }

    public function setHomepage($homepage) {
       $this->homepage = $homepage;
    }

    public function getDefault_scope() {
       return $this->default_scope;
    }

    public function setDefault_scope($default_scope) {
       $this->default_scope = $default_scope;
    }

    public function getForgotten_password_uid() {
       return $this->forgotten_password_uid;
    }

    public function setForgotten_password_uid($forgotten_passwprd_uid) {
       $this->forgotten_password_uid = $forgotten_passwprd_uid;
    }

    public function getWeb_hook_url_nugget_add() {
       return $this->web_hook_url_nugget_add;
    }

    public function setWeb_hook_url_nugget_add($web_hook_url_nugget_add) {
       $this->web_hook_url_nugget_add = $web_hook_url_nugget_add;
    }

    public function getWeb_hook_url_nugget_update() {
       return $this->web_hook_url_nugget_update;
    }

    public function setWeb_hook_url_nugget_update($web_hook_url_nugget_update) {
       $this->web_hook_url_nugget_update = $web_hook_url_nugget_update;
    }

    public function getWeb_hook_url_nugget_delete() {
       return $this->web_hook_url_nugget_delete;
    }

    public function setWeb_hook_url_nugget_delete($web_hook_url_nugget_delete) {
       $this->web_hook_url_nugget_delete = $web_hook_url_nugget_delete;
    }

    public function getDt_last_mod() {
       return $this->dt_last_mod;
    }

    public function setDt_last_mod($dt_last_mod) {
       $this->dt_last_mod = $dt_last_mod;
    }

    public function getDt_created() {
       return $this->dt_created;
    }

    public function setDt_created($dt_created) {
       $this->dt_created = $dt_created;
    }
    public function getUser_type() {
       return $this->user_type;
    }

    public function setUser_type($user_type) {
       $this->user_type = $user_type;
    }

    // </editor-fold>

    // <editor-fold defaultstate="collapsed" desc="Constructor and Destructor">
    function __construct($id, $db) {
       if (!isset($db)) {throw new Exception("Unable to initiate user. No DB connection provided.");}
       // If an ID was provided, we assume it's a member and we'll create this user off the back of
       // their database entry.
       if (isset($id)) {
          $this->db = $db;
          $this->user_type = 'member';

          $sql = "SELECT * FROM users WHERE id=".$id;
          if (!$result = $this->db->query($sql)) {throw new Exception("Unable to initate user: ".$this->db->error);}
          while ($user = $result->fetch_array()) {
             $this->id = $user['id'];
             $this->username = $user['username'];
             $this->password = $user['password'];
             $this->email = $user['email'];
             if (isset($user['first_name'])) {$this->first_name = $user['first_name'];}
             if (isset($user['surname'])) {$this->surname = $user['surname'];}
             if (isset($user['description'])) {$this->description = $user['description'];}
             if (isset($user['homepage'])) {$this->homepage = $user['homepage'];}
             if (isset($user['default_scope'])) {$this->default_scope = $user['default_scope'];}
             if (isset($user['forgotten_password_uid'])) {$this->forgotten_password_uid = $user['forgotten_password_uid'];}
             if (isset($user['web_hook_url_nugget_add'])) {$this->web_hook_url_nugget_add = $user['web_hook_url_nugget_add'];}
             if (isset($user['web_hook_url_nugget_update'])) {$this->web_hook_url_nugget_update = $user['web_hook_url_nugget_update'];}
             if (isset($user['web_hook_url_nugget_delete'])) {$this->web_hook_url_nugget_delete = $user['web_hook_url_nugget_delete'];}
             $this->dt_last_mod = $user['dt_last_mod'];
             $this->dt_created = $user['dt_created'];
          }
       } else {
          $this->user_type = 'guest';
       }
    }
    // </editor-fold>

    // <editor-fold defaultstate="collapsed" desc="Public Static Methods">
    /**
     * Creates a new user.
     * @param string $username
     * @param string $password
     * @param string $email
     * @return int ID
     */
    public static function create($username, $password, $email, $first_name, $surname, $db) {
       // Validate that we have required data
       if ($username == '') {throw new Exception("Unable to create new user. A username has not been provided.", PEAR_LOG_ERR);}
       if ($password == '') {throw new Exception("Unable to create new user. A password has not been provided.", PEAR_LOG_ERR);}
       if (!self::is_valid_password($password)) {throw new Exception("Unable to create new user. Password is not valid.", PEAR_LOG_ERR);}
       if ($email == '') {throw new Exception("Unable to create new user. An email address has not been provided.", PEAR_LOG_ERR);}
       if ($first_name == '') {throw new Exception("Unable to create new user. A first name has not been provided.", PEAR_LOG_ERR);}
       if ($surname == '') {throw new Exception("Unable to create new user. A surname has not been provided.", PEAR_LOG_ERR);}
       if (!isset($db)) {throw new Exception("Unable to create new user. A database connection has not been provided.", PEAR_LOG_ERR);}
       // Check whether this user already exists
       if (self::is_duplicate($username, $db)) {throw new Exception("Unable to create new user. The username '".$username."' is already in use.",PEAR_LOG_ERR);}
       // Check whether the username is legit (ie. does it contain illegal chars)
       if (!self::is_valid_username($username)) {throw new Exception("Unable to create new user. The username '".$username."' contains illegal characters.",PEAR_LOG_ERR);}
       // Create the query string
       $ts = time();
       $sql = "INSERT INTO users ";
       $sql .= "(username, password, email, first_name, surname, default_scope, dt_last_mod, dt_created) ";
       $sql .= "VALUES ";
       $sql .= "('".$username."', '".md5($password)."', '".$email."', '".$first_name."', '".$surname."', 'public', ".$ts.", ".$ts.")";
       // Execute it
       if (!$result = $db->query($sql)) {throw new Exception("Unable to create new user: ".$db->error,PEAR_LOG_ERR);}
       // Find out the ID
       $id = $db->insert_id;
       return $id;
    }

    /**
     * Checks to see whether the username provided is unique or not.
     * @param string $username Username to check.
     * @param string $db Database connection.
     * @return bool
     */
    public static function is_duplicate($username,$db) {
       if (!isset($username)) {throw new Exception("Unable to check for duplicate username. No username provided.",PEAR_LOG_ERR);}
       if (!$result = $db->query("SELECT id FROM users WHERE username='".$username."'")) {throw new Exception("Unable to check for duplicate username: ".$db->error);}
       if ($result->num_rows > 0) {
          $outcome = true;
       } else {
          $outcome = false;
       }
       return ($outcome);
    }

    /**
     * Returns the display name of a user
     * Full Name takes priority over username.
     * @param int $user_id_to_check User ID to return name of
     * @param int $users_own_id ID of user viewing site
     * @param mixed $db Database connection
     * @return string The user's display name
     */
    public static function return_display_name($user_id_to_check, $users_own_id, $db) {
       // In future this should check if a full name has been provided
       // If it hasn't return a username. For now, simply return the username
       $display_name = null;
       // Check to see if the display name is the user himself. If so, return 'me'
       if ($user_id_to_check == $users_own_id) {
          $display_name = 'Me';
       // Otherwise get the result from the database
       } else {
          $sql = "SELECT first_name, surname FROM users WHERE id=".$user_id_to_check;
          if (!$result = $db->query($sql)) {throw new Exception("Unable to grab the the user's name: ".$db->error,PEAR_LOG_ERR);}
          while ($user = $result->fetch_array()) {
             $display_name = $user['first_name'].' '.$user['surname'];
          }
       }
       return $display_name;
    }

   /**
    * Checks to see if username provide contains illegal chars
    * @param string $username
    * @return boolean
    */
   public static function is_valid_username($username) {
      $outcome = null;
      if (ereg('[^_a-zA-Z0-9]+', $username)) {
         # If we get a match, it means there are illegal chars
         $outcome = false;
      } else {
         $outcome = true;
      }
      return $outcome;
   }

   /**
    * Checks that a given password is valid
    * @param string $password
    * @return boolean
    */
   public static function is_valid_password($password) {
      $outcome = null;
      // Check that password is a valid length
      if (strlen($password) >= 5 AND strlen($password) <= 15) {
         $outcome = true;
      } else {
         $outcome = false;
      }
      return $outcome;
   }

    /**
     * Returns user id based on a give username.
     * @param string $username
     * @param mixed $db
     * @return int user id
     */
    public static function return_user_id($username,$db) {
       $sql = "SELECT id FROM users WHERE username='".$username."'";
       if (!$result = $db->query($sql)) {throw new Exception("Unable to grab the the user's ID: ".$db->error,PEAR_LOG_ERR);}
       while ($user = $result->fetch_array()) {
          $id = $user['id'];
       }       
       return $id;
    }

    // </editor-fold>

    // <editor-fold defaultstate="collapsed" desc="Private Static Methods">

    // </editor-fold>

    // <editor-fold defaultstate="collapsed" desc="Public Instance Methods">
    /**
     * Updates the database records relating to current user with data contained in instance variables
     * @return boolean 
     */
    public function update() {
        # Validate to make sure we have the required data.
        if (!isset($this->email)) {throw new Exception("Unable to update user details. An email address is required.",PEAR_LOG_ERR);}
        if (!isset($this->first_name)) {throw new Exception("Unable to update user details. A first name is required.",PEAR_LOG_ERR);}
        if (!isset($this->surname)) {throw new Exception("Unable to update user details. An surname is required.",PEAR_LOG_ERR);}
        # Build the query that handles the users table
        $sql = "UPDATE users SET ";
        $sql .= "email='".$this->email."'";
        $sql .= ", first_name='".$this->first_name."'";
        $sql .= ", surname='".$this->surname."'";
        if (isset($this->description)) {$sql .= ", description='".$this->description."'";}
        if (isset($this->homepage)) {$sql .= ", homepage='".remove_http($this->homepage)."'";}
        if (isset($this->default_scope)) {$sql .= ", default_scope='".$this->default_scope."'";}
        if (isset($this->web_hook_url_nugget_add)) {$sql .= ", web_hook_url_nugget_add='".remove_http($this->web_hook_url_nugget_add)."'";}
        if (isset($this->web_hook_url_nugget_update)) {$sql .= ", web_hook_url_nugget_update='".remove_http($this->web_hook_url_nugget_update)."'";}
        if (isset($this->web_hook_url_nugget_delete)) {$sql .= ", web_hook_url_nugget_delete='".remove_http($this->web_hook_url_nugget_delete)."'";}
        $sql .= ", dt_last_mod=".time()." ";
        $sql .= "WHERE id=".$this->id;
        # Execute the query
        if (!$result = $this->db->query($sql)) {throw new Exception("Unable to update user: ".$this->db->error,PEAR_LOG_ERR);}
       return (true);
    }

    /**
     * Deletes any data relating to a user
     * @return boolean Outcome
     */
    public function delete() {
       // Delete the user
       if (!$result = $this->db->query("DELETE FROM users WHERE id=".$this->id)) {throw new Exception("Unable to delete user: ".$this->db->error,PEAR_LOG_ERR);}
       // Delete their nuggets
       if (!$nuggets = $this->db->query("SELECT id FROM nuggets WHERE user_id = ".$this->id)) {throw Exception("Unable to delete user. Failed to generate list of user's nuggets: ".$this->db->error,PEAR_LOG_ERR);}
       while ($nugget_id = $nuggets->fetch_array()) {
          $nugget = new Nugget($nugget_id['id']);
          $nugget->delete();  // This will take care of all data relating to said nugget
          unset($nugget);
       }
       return (true);
    }

    /**
     * Updates the current user's password
     * @param string $existing The user's existing password
     * @param string $new The password the user would like to use
     * @param string $new_confirmation Confirmation of the password the user would like to use
     * @return bool
     */
    public function update_password($existing,$new,$new_confirmation) {
       // Validate that what the user submitted was correct
       # Check that the new password meets criteria
       if (!self::is_valid_password($new)) {throw new Exception("Unable to update password. Password provided is not valid.",PEAR_LOG_ERR);}
       # Check that existing password matches
       $sql = "SELECT * FROM users WHERE id=".$this->id." AND '".md5($existing)."' = password";
       if (!$user_query = $this->db->query($sql)) {throw new Exception("Unable to confirm existing password: ".$this->db->error,PEAR_LOG_ERR);}
       if ($user_query->num_rows == 0) {throw new Exception("Unable to update password. Current password provided was incorrect.",PEAR_LOG_ERR);}
       # Check that the two new passwords match
       if ($new != $new_confirmation) {throw new Exception("Unable to update password. New passwords provided do not match.",PEAR_LOG_ERR);}
       // If we've got this far, update the password
       $sql = "UPDATE users SET password='".md5($new)."' WHERE id=".$this->id;
       if (!$user_query = $this->db->query($sql)) {throw new Exception("Unable to update password: ".$this->db->error,PEAR_LOG_ERR);}
       return true;
    }

    /**
     * Returns the number of nuggets belonging to a user
     * @return integer Number of Nuggets
     */
    public function return_number_of_nuggets() {
       $sql = "SELECT id FROM nuggets WHERE user_id=".$this->id."";
       if (!$result = $this->db->query($sql)) {throw new Exception("Unable to determine the number of nuggets: ".$this->db->error,PEAR_LOG_ERR);}
       return $result->num_rows;
    }

    /**
     * Returns the date of the user's lasest Nugget
     * @return integer Date of user's last nugget
     */
    public function return_last_nugget_dt() {
       $sql = "SELECT dt_created FROM nuggets WHERE user_id=".$this->id." ORDER BY dt_created desc LIMIT 0,1";
       if (!$result = $this->db->query($sql)) {throw new Exception("Unable to determine the number of nuggets: ".$this->db->error,PEAR_LOG_ERR);}
       $count = 0;
       while ($nugget = $result->fetch_array()) {
          $last_nugget_dt = $nugget['dt_created'];
       }
       return $last_nugget_dt;
    }

    /**
     * Generates a unique ID specific to this user for use in the 'Forgot My Password' procedure.
     * If a user forgets their password, an email is sent out containing a unique link for the user to click.
     * This link is build using the unique ID generated here.
     * @return string Unique ID specific to this user
     */
    public function generate_forgotten_password_uid() {
       // Generate the ID
       $uid = uniqid();
       // Associate it with the user (ie. write it to the db)
       $sql = "UPDATE users SET forgotten_password_uid = '".$uid."' WHERE id = ".$this->id."";
       if (!$query = $this->db->query($sql)) {throw new Exception("Unable to generate UID for forgotten password: ".$this->db->error,PEAR_LOG_ERR);}
       // Associate it with this object
       $this->forgotten_password_uid = $uid;

       return $uid;
    }

    /**
     * Adds an entry to 'users_history_nuggets_viewed'
     * @param int $nugget_id
     * @return int The newly generated ID representing the database entry
     */
    public function add_viewed_nugget_history($nugget_id) {
       // Check we have all required parameters
       if (!isset($nugget_id)) {throw new Exception("Unable to add nugget into user's history. A nugget ID is required.",PEAR_LOG_ERR);}
       // Generate the SQL to handle the insert
       $ts = time();
       $sql = "INSERT INTO users_history_nuggets_viewed (user_id, nugget_id, dt_created) VALUES (".$this->id.",".$nugget_id.",".$ts.")";
       // Perform the insert
       if (!$query = $this->db->query($sql)) {throw new Exception("Unable to add nugget into user's history: ".$this->db->error,PEAR_LOG_ERR);}
       // Return the newly generated ID
       return ($this->db->insert_id);
    }

    /**
     * Returns a list of nuggets recently visited by the user
     * @return array List of nuggets recently viewed by user
     */
    public function return_viewed_nugget_history($params) {
       $results['entries'] = array();

       // Grab a list of Nuggets that this user has viewed
       // Generate SQL
       $sql = "SELECT SQL_CALC_FOUND_ROWS nuggets.id, max(history.dt_created) as dt_visited ";
       $sql .= "FROM ";
       $sql .= "users_history_nuggets_viewed history ";
       $sql .= ", nuggets nuggets ";
       $sql .= "WHERE ";
       $sql .= "history.nugget_id = nuggets.id ";
       $sql .= "AND history.user_id = ".$this->id." ";
       $sql .= "GROUP BY nuggets.id ";
       $sql .= "ORDER BY ".$params['sorting']['order_by']." ".$params['sorting']['order']." ";
       $sql .= "LIMIT ".$params['pagination']['starting_point'].", ".$params['pagination']['number_of_results']." ";
       // Execute SQL
       if (!$result = $this->db->query($sql)) {throw new Exception("Unable to obtain list of visited nuggets: ".$this->db->error, PEAR_LOG_ERR);}
       while ($entry = $result->fetch_array()) {
          array_push($results['entries'], $entry);
       }
       // Find out how many results were found ignoring the 'limit'
       if (!$result = $this->db->query("SELECT FOUND_ROWS() AS `rows_found`;")) {throw new Exception("Unable to determine how many rows were found.", PEAR_LOG_ERR);}
       $rows = $result->fetch_array();
       $results['statistics']['rows_found'] = $rows['rows_found'];
       // Return the array
       return($results);
    
    }

    // </editor-fold>

    // <editor-fold defaultstate="collapsed" desc="Private Instance Methods">

    // </editor-fold>

}
?>
