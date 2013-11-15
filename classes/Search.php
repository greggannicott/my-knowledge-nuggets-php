<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * A collection of functions to help with search
 *
 * @author GannicottG
 */
class Search {

    // <editor-fold defaultstate="collapsed" desc="Static Variables">

    // </editor-fold>

    // <editor-fold defaultstate="collapsed" desc="Instance Variables">

    // </editor-fold>

    // <editor-fold defaultstate="collapsed" desc="Getters and Setters">

    // </editor-fold>

    // <editor-fold defaultstate="collapsed" desc="Constructor and Destructor">

    // </editor-fold>

    // <editor-fold defaultstate="collapsed" desc="Public Static Methods">
    /**
     * Takes a search query and returns an array of tags
     *
     * @param string $query The search query to be parsed
     * @return array An array of tags
     */
    public static function parse_query($query) {
       $tags = explode(" ",$query);
       return $tags;
    }
    // </editor-fold>

    // <editor-fold defaultstate="collapsed" desc="Private Static Methods">
    // </editor-fold>

    // <editor-fold defaultstate="collapsed" desc="Public Instance Methods">

    // </editor-fold>

    // <editor-fold defaultstate="collapsed" desc="Private Instance Methods">

    // </editor-fold>

}
?>
