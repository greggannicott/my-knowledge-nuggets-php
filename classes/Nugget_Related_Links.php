<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Used to manage all 'Related Links' regarding a particular Nugget.
 *
 * @author GannicottG
 */
class Nugget_Related_Links {

    // <editor-fold defaultstate="collapsed" desc="Static Variables">

    // </editor-fold>

    // <editor-fold defaultstate="collapsed" desc="Instance Variables">
    private $links = array();
    private $nugget_id;
    private $user_id;
    private $db;
    // </editor-fold>

    // <editor-fold defaultstate="collapsed" desc="Getters and Setters">
    public function getLinks() {
        return $this->links;
    }

    public function setLinks($links) {
        $this->links = $links;
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

    public function getDb() {
        return $this->db;
    }

    public function setDb($db) {
        $this->db = $db;
    }
    // </editor-fold>

    // <editor-fold defaultstate="collapsed" desc="Constructor and Destructor">
    function __construct($db) {
        $this->setDb($db);
    }
    // </editor-fold>

    // <editor-fold defaultstate="collapsed" desc="Public Static Methods">
    /**
     * Combines the URLs and Titles together based on the form data submitted.
     * @param array $urls
     * @param array $titles
     * @return array A combined list of related links
     */
    public static function combine_form_data($urls, $titles) {
        $related_links = array();
        // Loop through the list of URLs
        for ($i = 1; $i <= count($urls); $i++) {
            $related_links[$i][url] = self::prefix_http_protocol($urls[$i]);
            $related_links[$i][title] = $titles[$i];
        }
        $related_links = self::filter_empty_links($related_links);
        return $related_links;
    }

    /**
     * Removes any empty links from the related links array
     * @param <type> $links
     * @return <type>
     */
    public static function filter_empty_links($links) {
        // Loop through the array
        $filtered_array = array();
        foreach ($links as $link) {
            if ($link['title'] != '' and $link['url'] != '') {
                array_push($filtered_array,$link);
            }
        }
        return $filtered_array;
    }

    /**
     * Prefixes the http:// to a URL if it (or https://) isn't present
     * @param string $url
     * @return string $url with http:// prefixed
     */
    public static function prefix_http_protocol($url) {
        if (preg_match('%^(?:https??://)+(.*)%im', $url)) {
            $url = $url;
        } else {
            $url = 'http://'.$url;
        }
        return $url;
    }

    /**
     * Removes duplicate entries from the $links array provided.
     * @param Array $links
     * @return Array Links stripped of duplicates
     */
    public static function remove_duplicate_links($links) {
        // We onlt want to remove duplicate links if there are any links to handle
        if (count($links) > 0) {
            // Copy the links into a different array - this array will have the URL as the key
            foreach ($links as $link) {
                $tmp_links[$link['url']]['url'] = $link['url'];
                $tmp_links[$link['url']]['title'] = $link['title'];
            }
            // Now strip out the uniques. In theory it should act on the key (url)
            $tmp_links = array_unique($tmp_links);
            // Now with the duplicates removed, recreate our $links array
            $count = 0;
            foreach ($tmp_links as $link) {
                $links[$count]['url'] = $link['url'];
                $links[$count]['title'] = $link['title'];
                $count++;
            }
        }
        return $links;
    }

    // </editor-fold>

    // <editor-fold defaultstate="collapsed" desc="Private Static Methods">

    // </editor-fold>

    // <editor-fold defaultstate="collapsed" desc="Public Instance Methods">
    /**
     * Commits the values of the object to the database
     * @return bool
     */
    public function commit() {
        if (isset($this->nugget_id))  {
            // Instantiate an instance of Nugget so we can find out the user id
            $nugget = new Nugget($this->nugget_id, $this->db);
            $this->user_id = $nugget->getUser_id();
            // Create an array to track whether a link has been dealt with or not
            $links_progress = array();
            foreach ($this->links as $link) {
                $progress = array();
                $progress['progress'] = 0;  // 0 = not dealt with, 1 = dealt with
                $links_progress[$link['url']] = $progress;
            }
            // Update entries that exist already
            foreach ($this->links as $link) {
                // Check to see if this tag needs to be checked
                if ($progress[$link['url']]['progress'] == 0) {
                    // Update the title for this URL (even if it hasn't changed)
                    $ts = time();
                    $sql = "UPDATE nuggets_related_links SET title='".$link['title']."', dt_last_mod=".$ts." WHERE nugget_id = ".$this->nugget_id." AND url = '".$link['url']."'";
                    if (!$results = $this->db->query($sql)) {throw new Exception("Unable to update the related link: " + $this->db->error,PEAR_LOG_ERR);}
                    // Perform a select to see which URLs exist for the query we just ran.
                    // Even if they were not affected, we don't wish to deal with them again.
                    $sql = "SELECT id FROM nuggets_related_links WHERE nugget_id = ".$this->nugget_id." AND url = '".$link['url']."'";
                    if (!$results = $this->db->query($sql)) {throw new Exception("Unable to select related links: " + $this->db->error,PEAR_LOG_ERR);}
                    // If we had 1 row returned, we can now ignore this link going forwards
                    if ($results->num_rows > 0) {$progress[$link['url']]['progress'] = 1;}
                }
            }
            // Add new entries
            foreach ($this->links as $link) {
                // Check to see if this tag needs to be checked
                if ($progress[$link['url']]['progress'] == 0) {
                    // At this point, if the link hasn't been dealt with we can assume we need to add it
                    $ts = time();
                    $sql = "INSERT INTO nuggets_related_links (nugget_id,user_id,url,title,dt_created) VALUES (".$this->nugget_id.", ".$this->user_id.", '".$link['url']."', '".$link['title']."', ".$ts.")";
                    if (!$results = $this->db->query($sql)) {throw new Exception("Unable to insert new related links: " + $this->db->error,PEAR_LOG_ERR);}
                    // We can now consider this link dealt with
                    $progress[$link['url']]['progress'] = 1;
                }
            }
            // Remove any entries we no longer need
            // The way we handle this depends on whether there are any links to be dealt with
            if (count($this->links) > 0) {
                // If the user has submitted links, we have to create a string of
                // the tags to be used in an 'in' statement
                $link_url_string = "";
                $link_url_count = 0;
                foreach ($this->links as $link) {
                    if ($link_url_count == 0) {
                        $link_url_string = "'".$link['url']."'";
                    } else {
                        $link_url_string .= ", '".$link['url']."'";
                    }
                    $link_url_count++;
                }
                $sql = "DELETE FROM nuggets_related_links WHERE nugget_id=".$this->getNugget_id()." and url not in (".$link_url_string.")";
                if (!$deletes = $this->db->query($sql)) {throw new Exception("Failed to delete related links: ".$this->db->error,PEAR_LOG_ERR);}
            } else {
                // If the user has submitted no links, just delete all those relating to the nugget
                $sql = "DELETE FROM nuggets_related_links WHERE nugget_id=".$this->getNugget_id();
                if (!$deletes = $this->db->query($sql)) {throw new Exception("Failed to delete related links: ".$this->db->error,PEAR_LOG_ERR);}
            }
        } else {
            throw new Exception("Unable to commit changes to database. Insuffecient parameters set.", PEAR_LOG_ERR);
        }
        return (true);
    }
    // </editor-fold>

    // <editor-fold defaultstate="collapsed" desc="Private Instance Methods">

    // </editor-fold>

}
?>
