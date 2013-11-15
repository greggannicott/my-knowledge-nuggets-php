<?php
require_once($root_dir."/classes/Nugget_Tag_List.php");
/**
 * Store is used to query and maintain the nugget store.
 *
 * @author greggannicott
 */
class Nugget_Store {

    // <editor-fold defaultstate="collapsed" desc="Static Variables">

    // </editor-fold>

    // <editor-fold defaultstate="collapsed" desc="Instance Variables">
    private $db;
    // </editor-fold>

    // <editor-fold defaultstate="collapsed" desc="Getters and Setters">
    // </editor-fold>

    // <editor-fold defaultstate="collapsed" desc="Constructor and Destructor">
    public function __construct($db) {
        if ($db == '') {throw new Exception("Unable to create Store object. No Database Connection details provided.",PEAR_LOG_ERR);}
        $this->db = $db;
    }
    // </editor-fold>

    // <editor-fold defaultstate="collapsed" desc="Public Static Methods">
    public static function parse_tags($tags) {
       // Trim whitespace from start and end of string
       $tag_string = trim($tags);
       // Remove double spacing
       $tag_string = preg_replace('/\s+/i', ' ', $tag_string);
       // Remove duplicates
       $tags = self::remove_duplicate_tags($tags);
       // Trim again
       $tags = trim($tags);
       // Return the result
       return ($tags);
    }

    /**
     * Returns a list of possible tags based on the user's input
     * @param string $input User input so far
     * @param string $user_id User performing the query
     * @return array List of possible tags
     */
    public static function suggest_tags($input,$user_id,$db) {
       $suggestions = array();

       // Build the query
       $sql = "SELECT tag_value, count(*) as num_of_tags FROM nuggets_tags WHERE user_id = ".$user_id." AND tag_value like '".$input."%' GROUP BY tag_value ORDER BY num_of_tags desc";

       // Execute the query
       if (!$results = $db->query($sql)) {throw new Exception("Unable to suggest tags: ".$db->error, PEAR_LOG_ERR);}

       // Associate the results with the array we're going to pass back
       while ($tag = $results->fetch_array()) {
//          $data['value'] = $tag['tag_value'];
//          $data['name'] = $tag['tag_value'];
//          $data['count'] = $tag['num_of_tags'];
          array_push($suggestions,$tag['tag_value']);
       }
       return $suggestions;
    }

    // </editor-fold>

    // <editor-fold defaultstate="collapsed" desc="Private Static Methods">
    private static function remove_duplicate_tags($tag_string) {
        // Convert it into an array
        $tags = explode(" ",$tag_string);
        // Make it unique
        $tags = array_unique($tags);
        // Convert it back to a string
        $tag_string = implode(" ", $tags);
        // Pass back the results
        return $tag_string;
    }
    // </editor-fold>

    // <editor-fold defaultstate="collapsed" desc="Public Instance Methods">

    /**
     * Returns Nuggets based on the params specified in $params
     * @param array $params Parameters for Nugget Search
     * @return array List of nuggets that match criteria
     */
    public function query($params) {

       /* declare and some vars we might need */

       // Create an array to hold resulting entries
       $results['entries'] = array();

       // Create a string to hold the search terms 'IN' sql
       $search_tags_sql = null;

       /* Read in the $params array:
        * -----------------------
        * This is used to configure the search being performed.
        */

       // tag_multiplier: used to give additional weight to tag matches:
       if ($params['search']['tag_multiplier'] != '') {$tag_multiplier = $params['search']['tag_multiplier'];}

       // scope: (ie. user's nuggets or everyone elses)
       $scope = $params['scope']['type'];

       // privacy level: (ie. private/public):
       $privacy_level = $params['scope']['privacy_level'];

       // The user to search for (ie. only return nuggets for this user):
       // This could be current user or another user
       $user = $params['scope']['user_id'];

       // draft: Whether to include or exclude draft posts
       $draft = $params['draft'];   // exclude / include / only

       // search term:
       $search_term = $params['search']['term'];
       
       /* End of params read-in */

       // Check whether a search has been performed at all
       if ($params['search']['term'] != '' AND isset($params['search'])) {
          $search_performed = true;
       }

       // If a search has been performed, convert the search string into a set of tags
       if ($search_performed) {
           // Convert the search terms to lower case
           $search_terms_lower = strtolower($params['search']['term']);
           // Breakdown the search
           $search_tags = explode(" ",$search_terms_lower);
           // Now loop through the search terms and build a "('tag1','tag2')" for the 'IN' statement
           $count = 0;
           foreach($search_tags as $tag) {
               if ($count == 0) {
                   $search_tags_sql = "'".$tag."'";
               } else {
                   $search_tags_sql .= ", '".$tag."'";
               }
               $count++;
           }
           $count = null;
       }

       /* Build the query
        * --------------------
        * This is built using the parameters read in from $params
        */
       
       $sql = "SELECT SQL_CALC_FOUND_ROWS results.* ";
       
       // If a search has been performed we need to know the score for each result
       if ($search_performed) {
            $sql .= ", results.tag_score as total_score ";
       }
       $sql .= "FROM ";
       $sql .= "( ";
       $sql .= "SELECT ";
       $sql .= "   n.id as id ";
       $sql .= "   , n.title as title";
       $sql .= "   , n.dt_last_mod as dt_last_mod";
       $sql .= "   , n.dt_created as dt_created ";

       // Determine what type of hits to return (if it's an all user search
       // we want hits_by_others, if it's a person one hits_by_owner
       if ($scope == 'user') {
           $sql .= "   , n.hits_by_owner as hits ";
       } else {
           $sql .= "   , n.hits_by_others as hits ";
       }

       if ($search_performed) {
          // Determine the number of tags matched for each result.
          $sql .= "   , (SELECT count(*) FROM nuggets_tags as t1 WHERE t1.tag_value in (".$search_tags_sql.") AND t1.nugget_id = n.id) as tag_score ";
          $sql .= "   , t.tag_value ";
       }

       $sql .= "FROM ";

       // If a search was performed, we need to join against tags
       if ($search_performed) {
           $sql .= "   nuggets AS n LEFT JOIN nuggets_tags AS t ";
           $sql .= "      ON n.id = t.nugget_id AND t.tag_value in (".$search_tags_sql.") ";

       // Otherwise, just grab the nuggets table. Tags won't be used to order non-searched-for results
       } else {
           $sql .= "   nuggets as n ";
       }
       $sql .= "WHERE ";
       $sql .= "   1=1 ";  // Included to make sure at least 1 where clause exists

       // The following is run if the user just wants nuggets regarding a particular user
       if ($scope == 'user') {
          $sql .= "AND n.user_id = ".$user." ";
          // If the user just wants to see private nuggets, only return private ones
          // Note: see issue 351 for a flaw in this code
          if ($privacy_level == 'private') {
              $sql .= "AND (n.public = 0) ";
          // If the user just wants to see public nuggets, only return public nuggets
          } elseif ($privacy_level == 'public') {
             $sql .= "AND (n.public = 1) ";
          }

          // If $user = $_COOKIE['user_id'], check to see whether drafts should be included
          if ($user == $_COOKIE['user_id']) {
             // Exclude drafts
             if ($draft == 'exclude') {
                $sql .= "AND (n.draft != 1) ";
             // Show only drafts
             } elseif ($draft == 'only') {
                $sql .= "AND (n.draft = 1) ";
             // Show all (ie. drafts and published nuggets)
             } else {
                $sql .= "AND (n.draft = 0 OR n.draft = 1) ";
             }
          }

       } elseif ($scope == 'notuser') {
          // Check if we have a user id.
          // If we do, then exclude all nugges from that user
          if ($user) {
             // If its a search for all nuggets not belonging to the user, exclude those and private ones
             $sql .= "AND (n.public = 1 AND n.user_id != ".$user.") ";
          // If we do not, it means no user has been specified so we want all
          // public nuggets
          } else {
             $sql .= "AND (n.public = 1) ";
          }

          // Exclude draft nuggets. If they don't belong to the user, they should
          // not be visible.
          $sql .= "AND (n.draft = 0) ";
       }

       // If a search was performed query both nuggets and nuggets_tags
       if ($search_performed) {
          $sql .= "   AND (t.tag_value IN (".$search_tags_sql.")) ";
       }
       $sql .= "GROUP BY ";
       $sql .= "   n.id ";
       $sql .= ") AS results ";

       // Loop through the possible order by statements
       for ($i = 0; $i < count($params['sorting']['order_by']); $i++) {
           if ($i == 0) {
               $sql .= "ORDER BY ".$params['sorting']['order_by'][$i]." ".$params['sorting']['order'][$i]." ";
           } else {
               $sql .= ", ".$params['sorting']['order_by'][$i]." ".$params['sorting']['order'][$i]." ";
           }
       }

       $sql .= "LIMIT ".$params['pagination']['starting_point'].", ".$params['pagination']['number_of_results']." ";
//       print '<pre style="color: white">'.$sql.'</pre>';

       // Execute it
       if (!$result = $this->db->query($sql)) {throw new Exception("Unable to return nuggets: ".$this->db->error,PEAR_LOG_ERR);}
       while ($entry = $result->fetch_array()) {
          array_push($results['entries'], $entry);
       }

       // Find out how many results were found ignoring the 'limit'
       if (!$result = $this->db->query("SELECT FOUND_ROWS() AS `rows_found`;")) {throw new Exception("Unable to determine how many rows were found.", PEAR_LOG_ERR);}
       $rows = $result->fetch_array();
       $results['statistics']['rows_found'] = $rows['rows_found'];

       // Finally, return the results
       return($results);
    }


    /**
     * Adds a new entry to the Knowledge Base
     * @param string $title
     * @param string $body
     * @param bool $public
     * @param string $tags
     * @param array $related_links
     * @param int $user_id
     * @return int ID of new Nugget created
     */
    public function add($title,$body,$public,$tag_string,$related_links,$user_id,$draft = 0) {
        if ($title == '') {throw new Exception("Unable to add new item: no title provided.",PEAR_LOG_ERR);}
        if ($body == '') {throw new Exception("Unable to add new item: no body provided.",PEAR_LOG_ERR);}
        if ($user_id == '') {throw new Exception("Unable to add new item: no user id provided.",PEAR_LOG_ERR);}
        // Parse the tags
        //$tags = self::parse_tags($tags);
        // Add the item to the main nuggets table
        $ts = time();
        $sql = "INSERT INTO nuggets SET ";
        $sql .= "user_id = '".$user_id."' ";
        $sql .= ", title = '".$title."' ";
        $sql .= ", body = '".$body."' ";
        $sql .= ", public = '".$public."' ";
        $sql .= ", draft = '".$draft."' ";
        $sql .= ", hits_by_owner = 0 ";
        $sql .= ", hits_by_others = 0 ";
        $sql .= ", dt_last_mod = ".$ts." ";
        $sql .= ", dt_created = ".$ts." ";
        # Execute the query
        if (!$result = $this->db->query($sql)) {throw new Exception("Unable to create new entry: ".$this->db->error,PEAR_LOG_ERR);}
        # Capture the ID
        $id = $this->db->insert_id;
        // Now add the tags
        $tags = new Nugget_Tag_List($this->db);
        $tags->setNugget_id($id); // We're using this rather than 'init' as with commit you then have to wipe tags associated with it
        $tags->parse_and_add_tags($tag_string);
        $tags->commit();
        // Now add the related links
        $rl = new Nugget_Related_Links($this->db);
        $rl->setNugget_id($id);
        $related_links = Nugget_Related_Links::remove_duplicate_links($related_links);
        $rl->setLinks($related_links);
        $rl->commit();
        // Action the Webhook
        $nugget = new Nugget($id,$this->db);
        $nugget->web_hook_add();
        return ($id);
    }
    // </editor-fold>

    // <editor-fold defaultstate="collapsed" desc="Private Instance Methods">
    // </editor-fold>
}
?>