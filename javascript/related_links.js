/* 
 * Contains the functions required for code that handles related urls
 */

function get_page_title() {
    // Using the button just clicked, create a variable that defines the appropriate URL box
    var url_box = $(this).prev();
    // Grab the ID of the related URL box we're working with
    var current_id = return_url_id(url_box.attr("name"));
    // Check to see whether there is a URL or not. If not, blank the title and exit
    if (url_box.val() == '') {
        $("input[name=related_link_title[" + (current_id) + "]]").val('');
        return true;
    }
    // Block the user from doing anything else...
    $.blockUI({ message: '<p class="uiblock_message_text">One moment... just grabbing the site\'s title for you.</p>', css: { padding: '10px' } });
    $.ajax({
        type: "GET",
        url: "ajax/get_page_title.php",
        data: "url="+url_box.val(),
        dataType: "json",
        success: function(json) {
            if (json.type == 'success') {
                // Determine if this is a new addition or update
                if (requires_new_related_url_field(current_id) == true) {
                    // Determine the ID for the new title box
                    // Create a new text box to hold the page title
                    // Update it with the page title
                    $("input[name=related_link_title[" + (current_id) + "]]").val(json.title);
                    // Now add a new box to insert the next URL:
                    url_box.parent().parent().parent().after("<li><ol><li><label for=\"related_link_url[" + (current_id + 1) + "]\">URL</label><input type=\"text\" name=\"related_link_url[" + (current_id + 1) + "]\" class=\"related_link_url_entry\"> <button type=\"button\" name=\"get_page_title_button[" + (current_id + 1) + "]\">Get Title!</button></li><li><label for=\"related_link_title[" + (current_id + 1) + "]\">Title</label><input type=\"text\" name=\"related_link_title[" + (current_id + 1) + "]\" class=\"related_link_title\"></li></ol></li>");
                    //url_box.parent().next().after("<p>URL: <input type=\"text\" name=\"related_link_url[" + ( current_id + 1) + "]\" size=\"120\" class=\"related_link_url_entry\"> <input type=\"button\" value=\"Grab Site's Title\" name=\"get_page_title_button[" + ( current_id + 1) + "]\" class=\"get_page_title_buttons\"></p>");
                    // Add a box to handle the next title
                    //url_box.parent().next().next().after("<p>Title: <input type=\"text\" name=\"related_link_title[" + ( current_id + 1) + "]\" size=\"130\"></p>");
                    // Bind the new button to the get_page_title() function:
                    $(":button[name=get_page_title_button[" + (current_id + 1) + "]]").bind('click',get_page_title);
                    // Give it focus
                    $("input[name=related_link_url[" + (current_id + 1) + "]]").focus();
                    $.unblockUI();
                } else {
                    // Update it with the page title
                    $("input[name=related_link_title[" + current_id + "]]").val(json.title);
                    // Give the next link focus and 'select all' the text.
                    $("input[name=related_link_url[" + (current_id + 1) + "]]").focus().select();
                    // Unblock the UI
                    $.unblockUI();
                }
            } else {
                // give the URL box focus/select all:
                url_box.focus().select();
                // Unblock the UI
                $.unblockUI();
                alert("Error: " + json.message);
            }
        }
    })
}

// Determines whether a new related url field is required
// current_id = id of field being updated
function requires_new_related_url_field(current_id) {
    // Find out the value of the lastest related url box to be added
    // Strip away the text in the process
    var id_vals = new Array();
    $('.related_link_url_entry').each(
        function(intIndex) {
            var myregexp = /related_link_url\[([0-9]+)\]/i;
            var match = myregexp.exec($(this).attr("name"));
            if (match != null) {
                id_vals.push(match[1]);
            }
        }
    );
    // sort the array (to help us find the highest value)
    id_vals.sort(function(a,b){return b - a});  // The custom function inside is to sort numerically desc
    // Grab highest value
    var max_id = id_vals[0];
    // Now see if the max equals the current. If they do, we're adding a new entry
    if (max_id == current_id) {
        return true;
    } else {
        return false;
    }
}

function return_url_id(id) {
    var myregexp = /related_link_url\[([0-9]+)\]/i;
    var match = myregexp.exec(id);
    if (match != null) {
        current_id = match[1];
    }
    // Convert it to an int
    current_id = parseInt(current_id);
    return(current_id);
}