<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Nugget
 *
 * @author greggannicott
 */
class Nugget {
    private $id;
    private $user_id;
    private $author;
    private $title;
    private $body;
    private $tags = array();
    private $tags_string;
    private $related_links = array();
    private $public;
    private $draft;
    private $hits_by_owner;
    private $hits_by_others;
    private $headings = array();
    private $dt_last_mod;
    private $dt_created;
    private $db;

    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function getUser_id() {
       return $this->user_id;
    }

    public function setUser_id($user_id) {
       $this->user_id = $user_id;
    }

    public function getAuthor() {
       return $this->author;
    }
    
    public function setAuthor($author) {
       $this->author = $author;
    }

    public function getTitle() {
        return $this->title;
    }

    public function setTitle($title) {
        $this->title = $title;
    }

    public function getBody() {
        return $this->body;
    }

    public function setBody($body) {
        $this->body = $body;
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
    public function getDb() {
        return $this->db;
    }

    public function setDb($db) {
        $this->db = $db;
    }
    public function getTags() {
        return $this->tags;
    }
    /**
     * Sets $this->tags (an array)
     * If string is passed in, it parses and convert that string into an array
     * of tags. This process also includes the stripping of whitespace and commas.
     * String must be comma delimited.
     * @param mixed $tags Either string or array containing tags
     */
    public function setTags($tags) {
       if (is_array($tags)) {
          $this->tags = $tags;
       } else {
          $tags_list = new Nugget_Tag_List($this->db);
          $tags_list->setNugget_id($this->getId());
          $tags_list->parse_and_add_tags($tags);
          $this->tags = $tags_list->getTags();
       }
    }
    public function getRelated_links() {
        return $this->related_links;
    }

    public function setRelated_links($related_links) {
        $this->related_links = $related_links;
    }
    /**
     * Whether the Nugget is public or not
     * @return integer 1 = public, 0 = private
     */
    public function getPublic() {
       return $this->public;
    }
    public function setPublic($public) {
       $this->public = $public;
    }
    public function getDraft() {
       return $this->draft;
    }
    public function setDraft($draft) {
       $this->draft = $draft;
    }
    public function getHits_by_owner() {
       return $this->hits_by_owner;
    }
    public function setHits_by_owner($hits_by_owner) {
       $this->hits_by_owner = $hits_by_owner;
    }
    public function getHits_by_others() {
       return $this->hits_by_others;
    }
    public function setHits_by_others($hits_by_others) {
       $this->hits_by_others = $hits_by_others;
    }
      public function getTags_string() {
         return $this->tags_string;
      }

      public function setTags_string($tags_string) {
         $this->tags_string = $tags_string;
      }

      public function getHeadings() {
         return $this->headings;
      }

      public function setHeadings($headings) {
         $this->headings = $headings;
      }

          /**
     * Constructor: Initiates a Nugget object
     * @param integer $id
     * @param <type> $db
     */
    public function __construct($id,$db) {
        # validate that we have an id
        if ($id == '') {throw new Exception("Unable to initiate the Knowledge Item: No ID provided",PEAR_LOG_ERR);}
        if ($db == '') {throw new Exception("Unable to initiate the Knowledge Item: No DB connection provided",PEAR_LOG_ERR);}
        $this->db = $db;
        // Update from details in the nuggets table
        $sql = "SELECT ";
        $sql .= "nuggets.* ";
        $sql .= ", users.username ";
        $sql .= "FROM ";
        $sql .= "nuggets ";
        $sql .= ", users ";
        $sql .= "WHERE ";
        $sql .= "nuggets.id = ".$id." ";
        $sql .= "AND nuggets.user_id = users.id ";
        if (!$result = $this->db->query($sql)) {throw new Exception("Unable to initiate the item: ".$this->db->error,PEAR_LOG_ERR);}
        if ($result->num_rows != 0) {
            while ($entry = $result->fetch_array()) {
                $this->id = $entry['id'];
                $this->user_id = $entry['user_id'];
                $this->title = $entry['title'];
                $this->body = $entry['body'];
                $this->public = $entry['public'];
                $this->draft = $entry['draft'];
                $this->hits_by_owner = $entry['hits_by_owner'];
                $this->hits_by_others = $entry['hits_by_others'];
                $this->dt_last_mod = $entry['dt_last_mod'];
                $this->dt_created = $entry['dt_created'];
                $this->author = $entry['username'];
            }

            // Populate $this->headings
            $this->headings = $this->extract_headings();

            // Populate $this->tags
            $tags = new Nugget_Tag_List($this->db);
            $tags->init($this->id);
            $this->tags = $tags->getTags();
            
            // Associate related links to the nugget
            if (!$result = $this->db->query("SELECT * FROM nuggets_related_links WHERE nugget_id=".$id)) {throw new Exception("Unable to initiate the item and associate related links: ".$this->db->error,PEAR_LOG_ERR);}
            if ($result->num_rows > 0) {
               while($entry = $result->fetch_array()) {
                   $link = array();
                   $link['url'] = $entry['url'];
                   $link['title'] = $entry['title'];
                   array_push($this->related_links,$link);
               }
            }
        } else {
            throw new Exception("Unable to initiate the nugget: ID not found.",PEAR_LOG_ERR);
        }
    }

    /**
     * Injects <a>s where <h>eadings are used
     * This is to allow table of contents for a Nugget
     * @param Text $input Text to inject anchors into
     * @return Text Text with anchors included
     */
    public static function inject_anchors_into_headings($input) {
       // Add the tags (this also trims whitespace from the anchor tag)
       return preg_replace_callback("%(<H[1-6][^>]*>)(.*?)(</H[1-6]>)%si", "self::inject_anchors_into_headings_callback", $input);
    }

    /**
     * Performs replace as part of inject_anchors_into_headings
     * @param Array $matches List of matches from preg_replace
     * @return String Replacement text
     */
    private static function inject_anchors_into_headings_callback($matches) {
       return $matches[1].'<a name="'.trim($matches[2]).'">'.$matches[2].'</a>'.$matches[3];
    }


    public function delete() {
       if (!isset($this->id)) {throw new Exception("Unable to delete nugget. No ID provided.");}
       // delete the entry from nuggets
       if (!$result = $this->db->query("DELETE FROM nuggets WHERE id=".$this->id)) {throw new Exception("Unable to delete nugget: ".$this->db->error,PEAR_LOG_ERR);}
       // delete the tags
       if (!$result = $this->db->query("DELETE FROM nuggets_tags WHERE nugget_id=".$this->id)) {throw new Exception("Unable to delete nugget's tags: ".$this->db->error,PEAR_LOG_ERR);}
       // delete the related links
       if (!$result = $this->db->query("DELETE FROM nuggets_related_links WHERE nugget_id=".$this->id)) {throw new Exception("Unable to delete nugget's related links: ".$this->db->error,PEAR_LOG_ERR);}
       // Call the web hook (if one exists)
       $this->web_hook_delete();
    }
    public function update() {
        # Build the query that handles the nuggets table
        $sql = "UPDATE nuggets SET ";
        $sql .= "title='".$this->title."'";
        $sql .= ", body='".$this->body."'";
        $sql .= ", public='".$this->public."'";
        $sql .= ", draft='".$this->draft."'";
        $sql .= ", dt_last_mod=".time()." ";
        $sql .= "WHERE id=".$this->id;
        # Execute the query
        if (!$result = $this->db->query($sql)) {throw new Exception("Unable to update the item: ".$this->db->error,PEAR_LOG_ERR);}
        // Now deal with the tags
        $tags = new Nugget_Tag_List($this->db);
        $tags->setNugget_id($this->id); // We're using this rather than 'init' as with commit you then have to wipe tags associated with it
        $tags->setTags($this->getTags());
        $tags->commit();
        // Now deal with related links
        $links = new Nugget_Related_Links($this->db);
        $links->setNugget_id($this->id);
        // Remove the duplicates
        $this->related_links = Nugget_Related_Links::remove_duplicate_links($this->related_links);
        $links->setLinks($this->related_links);
        $links->commit();
        // Call the web hook
        $this->web_hook_update();
    }
    /**
     * Returns a string of tags, seperated by a space
     * @return <type>
     */
    public function get_tags_string() {
        foreach ($this->tags as $tag) {
            $tags .= $tag.' ';
        }
        // Trim spaces and commas
        trim($tags);
        trim($tags,",");
        return $tags;
    }

    /**
     * Adds tags to the array $this->tags
     * @param mixed $tags
     */
    public function add_tags($tags) {
       // If it's not an array, we need to convert it to an array
       if (!is_array($tags)) {
          $tags_list = new Nugget_Tag_List($this->db);
          $tags_list->setNugget_id($this->getId());
          $tags_list->parse_and_add_tags($tags);
          $tags = $tags_list->getTags();
       }
       // Now add the tags
       foreach($tags as $tag) {
           array_push($this->tags,$tag);
       }
    }

    /**
     * Returns a friendly URL for this Nugget
     * @return string
     */
    public function return_permalink() {
       // Remove odd characters from the title
       $title = preg_replace('/[^_ 0-9a-z]+/i', '', $this->title);
       // Replace spaces with hythens
       $title = preg_replace('/[ ]+/i', '_', $title);
       // Now build the URL
       $url = "http://".$_SERVER['HTTP_HOST']."/users/".$this->getAuthor()."/nuggets/".$this->getId()."-".$title."/";
       return $url;
    }

    /**
     * Performs the 'add' web hook
     * @return <type>
     */
    public function web_hook_add() {
       $result = null;

       // Find out the user's webhook url
       $user = new User($this->user_id, $this->db);
       $url = "http://".$user->getWeb_hook_url_nugget_add();
       if ($url != '') {

         // Post against it
         $fields = array(
            'hook_action'=>'nugget.add'
            , 'id'=>urlencode($this->id)
            , 'title'=>urlencode($this->title)
            , 'body'=>urlencode($this->body)
            , 'tags'=>urlencode($this->get_tags_string())
            , 'public'=>urlencode($this->public)
            , 'draft'=>urlencode($this->draft)
            , 'hits_by_owner'=>urlencode($this->hits_by_owner)
            , 'hits_by_others'=>urlencode($this->hits_by_others)
            , 'url'=>urlencode($this->return_permalink())
         );

         $result = web_hook_post($url, $fields);

       }
       return ($result);
    }

    /**
     * Performs the 'update' web hook
     * @return <type>
     */
    public function web_hook_update() {
       $result = null;

       // Find out the user's webhook url
       $user = new User($this->user_id, $this->db);
       $url = "http://".$user->getWeb_hook_url_nugget_update();
       if ($url != '') {

         // Post against it
         $fields = array(
            'hook_action'=>'nugget.update'
            , 'id'=>urlencode($this->id)
            , 'title'=>urlencode($this->title)
            , 'body'=>urlencode($this->body)
            , 'tags'=>urlencode($this->get_tags_string())
            , 'public'=>urlencode($this->public)
            , 'draft'=>urlencode($this->draft)
            , 'hits_by_owner'=>urlencode($this->hits_by_owner)
            , 'hits_by_others'=>urlencode($this->hits_by_others)
            , 'url'=>urlencode($this->return_permalink())
         );

         $result = web_hook_post($url, $fields);

       }
       return ($result);
    }

    /**
     * Performs the 'delete' web hook
     * @return <type>
     */
    public function web_hook_delete() {
       $result = null;

       // Find out the user's webhook url
       $user = new User($this->user_id, $this->db);
       $url = "http://".$user->getWeb_hook_url_nugget_delete();
       if ($url != '') {

         // Post against it
         $fields = array(
            'hook_action'=>'nugget.delete'
            , 'id'=>urlencode($this->id)
            , 'title'=>urlencode($this->title)
            , 'body'=>urlencode($this->body)
            , 'tags'=>urlencode($this->get_tags_string())
            , 'public'=>urlencode($this->public)
            , 'draft'=>urlencode($this->draft)
            , 'hits_by_owner'=>urlencode($this->hits_by_owner)
            , 'hits_by_others'=>urlencode($this->hits_by_others)
            , 'url'=>urlencode($this->return_permalink())
         );

         $result = web_hook_post($url, $fields);

       }
       return ($result);
    }

    /**
     * Increments the hit count for a Nugget
     * @param String $type States the type of hit to increase (eg. owner or others)
     */
    public function increment_hits($type) {
       if (!isset($type)) {throw new Exception("Unable to register hit. No type provided.",PEAR_LOG_ERR);}
       // Lower the case of $type
       $type = strtolower($type);
       // Generate the SQL
       $sql = "UPDATE nuggets ";
       if ($type == 'owner') {
          $sql .= "SET hits_by_owner = hits_by_owner + 1 ";
       } elseif ($type == 'others') {
          $sql .= "SET hits_by_others = hits_by_others + 1 ";
       } else {
          throw new Exception("Unable to register hit. Incorrect type provided.");
       }
       $sql .= "WHERE id = ".$this->getId()." ";
       // Execute the SQL
       if (!$query = $this->db->query($sql)) {throw new Exception("Unable to register hit: ".$this->db->error,PEAR_LOG_ERR);}
    }

    /**
     * Extracts the headings contained within the Nugget's $this->body
     * @return array List of headings and their heading sizes
     */
    public function extract_headings() {

       $extracted_headings = array();

       // Extract all headings contained within the Nugget's body (including the HTML of the heading)
       preg_match_all('%<H[1-6][^>]*>(.*?)</H[1-6]>%si', $this->body, $headings, PREG_PATTERN_ORDER);

       // Loop through those headings
       for ($i = 0; $i < count($headings[0]); $i++) {

          $heading_size = null;
          $heading_text = null;

          // Determine the heading number
          if (preg_match('/<H([1-6])/si', $headings[0][$i], $regs)) {
             $heading_size = $regs[1];
          }

          // Extract the heading
          if (preg_match('%<H[1-6][^>]*>(.*?)</H[1-6]>%si', $headings[0][$i], $regs)) {
             $heading_text = $regs[1];
          }

          // Assign relevant details to an array
          $extracted_headings[$i]['size'] = $heading_size;
          $extracted_headings[$i]['text'] = $heading_text;
       }
       
       // Return the resulting array
       return $extracted_headings;
    }

}
?>
