<?php

/**
 * Description of Tag_List
 *
 * @author GannicottG
 */
class Nugget_Tag_List {

    // <editor-fold defaultstate="collapsed" desc="Static Variables">

    // </editor-fold>

    // <editor-fold defaultstate="collapsed" desc="Instance Variables">
    private $db;
    private $nugget_id;
    private $user_id;
    private $tags = array();
    // </editor-fold>

    // <editor-fold defaultstate="collapsed" desc="Getters and Setters">
    public function getDb() {
        return $this->db;
    }

    public function setDb($db) {
        $this->db = $db;
    }

    public function getNugget_id() {
        return $this->nugget_id;
    }

    public function setNugget_id($nugget_id) {
        $this->nugget_id = $nugget_id;
    }

    public function getUser_id() {
        return $this->user_id;
    }

    public function setUser_id($user_id) {
        $this->user_id = $user_id;
    }

    public function getTags() {
        return $this->tags;
    }

    public function setTags($tags) {
        $this->tags = $tags;
    }
    // </editor-fold>

    // <editor-fold defaultstate="collapsed" desc="Constructor and Destructor">
    /**
     * 
     * @param mixed $db Database connection details.
     */
    function __construct($db) {
        $this->setDb($db);
    }
    // </editor-fold>

    // <editor-fold defaultstate="collapsed" desc="Public Static Methods">
    /**
     * Removes duplicate tag names.
     * @param Array $tags
     * @return Array Tags
     */
    public static function remove_duplicate_tags($tags) {
        $tags = array_unique($tags);
        return $tags;
    }
    // </editor-fold>

    // <editor-fold defaultstate="collapsed" desc="Private Static Methods">

    // </editor-fold>

    // <editor-fold defaultstate="collapsed" desc="Public Instance Methods">
    /**
     * Populates the object with contents of a tag list based on a knowledge item id
     * @param int $id Nugget ID
     * @return bool
     */
    public function init($id) {
        if ($id == '') {throw new Exception("Unable to initiate object. No ID provided.",PEAR_LOG_ERR);}
        $this->nugget_id = $id;
        $sql = "SELECT tag_value FROM nuggets_tags WHERE nugget_id = ".$id." ORDER BY tag_value asc";
        if (!$results = $this->db->query($sql)) {throw new Exception("Unable to initiate object: ".$this->db->error,PEAR_LOG_ERR);}
        if ($results->num_rows > 0) {
            while ($tag = $results->fetch_array()) {
                array_push($this->tags,$tag['tag_value']);
            }
        }
        return (true);
    }
    /**
     * Commits the value of the object to the database
     * @return bool
     */
    public function commit() {
        if (isset($this->nugget_id))  {

            // Instantiate an instance of Nugget so we can find out the user id
            $nugget = new Nugget($this->nugget_id, $this->db);
            $this->user_id = $nugget->getUser_id();

            // Create an array to track whether a tag has been dealt with or not
            $tags_progress = array();

            foreach ($this->tags as $tag) {
                $progress = array();
                $progress['tag_value'] = $tag;
                $progress['progress'] = 0;  // 0 = not dealt with, 1 = dealt with
                $tags_progress[$tag] = $progress;
            }
            // Update entries that exist already
            foreach ($this->tags as $tag) {
                // Check to see if this tag needs to be checked
                if ($progress[$tag]['progress'] == 0) {
                    // Simply check if the tag exists. If it does, leave it alone, and mark it as actioned
                    $sql = "SELECT id FROM nuggets_tags WHERE nugget_id = ".$this->nugget_id." AND tag_value = '".$tag."'";
                    if (!$results = $this->db->query($sql)) {throw new Exception("Unable to determine if tag already exists: " + $this->db->error,PEAR_LOG_ERR);}
                    // If we had 1 row returned, we can now ignore this tag going forwards
                    if ($results->num_rows == 1) {$progress[$tag]['progress'] = 1;}
                }
            }
            // Add new entries
            foreach ($this->tags as $tag) {
                // Check to see if this tag needs to be checked
                if ($progress[$tag]['progress'] == 0) {
                    // At this point, if the tag hasn't been dealt with we can assume we need to add it
                    $ts = time();
                    $sql = "INSERT INTO nuggets_tags (nugget_id,user_id,tag_value,dt_created) VALUES (".$this->nugget_id.", ".$this->user_id.", '".$tag."', ".$ts.")";
                    if (!$results = $this->db->query($sql)) {throw new Exception("Unable to insert new tags: " + $this->db->error,PEAR_LOG_ERR);}
                    // We can now consider this tag dealt with
                    $progress[$tag]['progress'] = 1;
                }                
            }
            // Remove any entries we no longer need
            // Create a string of the tags to be used in an 'in' statement
            $tag_string = "";
            $tag_count = 0;
            foreach ($this->tags as $tag) {
                if ($tag_count == 0) {
                    $tag_string = "'".$tag."'";
                } else {
                    $tag_string .= ", '".$tag."'";
                }
                $tag_count++;
            }

            if ($tag_count > 0) {
               // If the tag string is empty, delete all tags related to the nugget
               if ($tag_string == "''") {
                  $sql = "DELETE FROM nuggets_tags WHERE nugget_id=".$this->getNugget_id();
               // Otherwise, delete only those that require deleting.
               } else {
                  $sql = "DELETE FROM nuggets_tags WHERE nugget_id=".$this->getNugget_id()." and tag_value not in (".$tag_string.")";
               }
               if (!$deletes = $this->db->query($sql)) {throw new Exception("Failed to delete tags: ".$this->db->error,PEAR_LOG_ERR);}
            }
        } else {
            throw new Exception("Unable to commit changes to database. Insuffecient parameters set.", PEAR_LOG_ERR);
        }
        return (true);
    }
    /**
     * Takes either a string or array and assigns it to $this->tags
     * Also removes duplicates and trimming of trailing spaces and commas if string is passed in.
     * @param string $in_tags
     * @return bool
     */
    public function parse_and_add_tags($in_tags) {
       // Create an empty array to hold the tags
       $tags = array();
       // If it's a string being passed in, convert it to an array
       // Clean up the data whilst doing so
       if (is_string($in_tags)) {
          // Trim whitespace
          $in_tags = trim($in_tags);
          // Remove double spacing
          $in_tags = preg_replace('/\s+/i', ' ', $in_tags);
          // Convert the text provided by user into an array
          // We only perform this if we have tags to deal with
          if ($in_tags != '') {
            $tags = explode(" ",$in_tags);
          }
          // Remove duplicates
          $tags = self::remove_duplicate_tags($tags);
       // If it's an array, just assign the value - no further work required
       } elseif (is_array($in_tags)) {
          $tags = $in_tags;
       // If it's neither, throw an exception
       } else {
          throw new Exception("Unable to parse and add tags. Invalid datatype. Must be string or array.");
       }
       // Add the tags
       $this->add_tags($tags);
       return (true);
    }
    /**
     * Adds a list of tags to $tags[]
     * @param array $tag_list
     * @return bool 
     */
    public function add_tags($tag_list) {
        if (!isset($tag_list)) {throw new Exception("Unable to add tags. No array provided.", PEAR_LOG_ERR);}
        if (count($tag_list) > 0) {
           // Check if any of the tags are already in the tag list
           // If they are not, add them
           foreach ($tag_list as $tag) {
               if (!in_array($tag,$this->tags)) {
                   array_push($this->tags,$tag);
               }
           }
        }
        return (true);
    }
    /**
     * Removes a single tag from $tags[], based on tag value provided (not id)
     * @param string $tag_value
     * @return bool
     */
    public function remove_tag($tag_value) {
        if ($tag_value == '') {throw new Exception("Unable to remove tag. No tag value provided.", PEAR_LOG_ERR);}
        try {
            $this->tags = $this->remove_array_elements_by_value($this->tags, $tag_value);
        } catch (Exception $e) {
            throw new Exception("Unable to remove tag. Error when performing 'unset': ".$e->getMessage(), PEAR_LOG_ERR);
        }
        return (true);
    }
    /**
     * Removes tags from $tags[] based on the array passed in
     * @param array $tag_list
     * @return bool 
     */
    public function remove_tags($tag_list) {
        if (!isset($tag_list)) {throw new Exception("Unable to remove tags. No array provided.", PEAR_LOG_ERR);}
        if (count($tag_list) == 0) {throw new Exception("Unable to remove tags. No elements in array provided.", PEAR_LOG_ERR);}
        // Check that the each of the tags we're removing exist.
        // If they do, remove them
        foreach ($tag_list as $tag) {
            if (in_array($tag,$this->tags)) {
                $this->tags = $this->remove_array_elements_by_value($this->tags, $tag);
            }
        }
        return (true);
    }
    /**
     * Returns an array of the tags associated with this object
     * @return array List of tags associated with Nugget
     */
    public function return_tags() {
        return ($this->tags);
    }
    // </editor-fold>

    // <editor-fold defaultstate="collapsed" desc="Private Instance Methods">
    /**
     *
     * @param array $arr Array to remove elements from
     * @param mixed $val Predicate for removal
     * @return array Array with elements removed 
     */
    private function remove_array_elements_by_value($arr, $val){
        foreach ($arr as $key => $value){
            if ($arr[$key] == $val){
                unset($arr[$key]);
            }
        }
        return $arr = array_values($arr);
    }
    // </editor-fold>

}
?>
