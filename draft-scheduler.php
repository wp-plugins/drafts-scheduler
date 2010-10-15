<?php
/*
Plugin Name: Drafts Scheduler
Plugin URI: http://www.installedforyou.com/draftscheduler
Description: Allows WordPress admins to bulk schedule posts currently saved as drafts
Author: Jeff Rose
Version: 1.0
Author URI: http://www.installedforyou.com

*/

/*  Copyright 2010  Jeff Rose  (email : info@installedforyou.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if (!class_exists('DraftScheduler')) {

    define ('IFDS_VERSION', '1.0');
    define('DraftScheduler_root', WP_PLUGIN_URL . '/drafts-scheduler/');

    class DraftScheduler {

        /*
         * function DraftScheduler
         * Constructor for the class
         * Primarily connects the WP hooks
         */
        function DraftScheduler() {
            //add_action('init', array(&$this, 'draftScheduler_Init'));

            add_action('admin_menu', array(&$this,'draftScheduler_Admin'));
        }

        /*
         * function draftScheduler_Init
         * @todo: I don't think I need anything here, yet.
         */
        function draftScheduler_Init() {
//            wp_register_script(
//                'draftScripts',
//                DraftScheduler_root . 'js/jquery-ui-1.7.2.custom.min.js',
//                array('jquery'),
//                ''
//            );
        }

        /*
         * function draftScheduler_Admin
         * Sets up the Admin page for the user to schedule draft posts
         */
        function draftScheduler_Admin() {
            add_management_page('Draft Scheduler', 'Draft Scheduler', 'administrator', 'draft_scheduler', array(&$this, 'draftScheduler_manage_page'));
            //wp_enqueue_script('draftScripts');
        }

        /*
         * function draftScheduler_Scheduler
         * The supposed workhorse - this applies the chosen parameters
         * to the posts
         */
        function draftScheduler_Scheduler(){
            GLOBAL $wpdb;

            $draftList = $wpdb->get_results("SELECT * from $wpdb->posts WHERE status='draft'");
        }

        /*
         * function draft_manage_page
         * Check to see if page was posted, display messages, then display page
         */
        function draftScheduler_manage_page() {
		$ifds_msg = '';
		$ifds_msg = $this->ifdsManagePg();
		if ( trim($ifds_msg) != '' ) {
			echo '<div id="message" class="updated fade"><p><strong>'.$ifds_msg.'</strong></p></div>';
		}
		echo '<div class="wrap">';
		$this->draftScheduler_admin_page();
		echo '</div>';
        }

        /*
         * function ifdsManagePg
         * Process the form post and do the work.
         */
        function ifdsManagePg() {
            if ( !empty($_POST) && check_admin_referer('do-schedule-drafts') ) {
                $msg = '';
                if (!$_POST["action"] == "schedule_drafts") {
                    /*
                     * No action set, so just return
                     */
                    return $msg;
                } else {
                    if (!j_is_date($_POST['startDate'])) {
                        $msg = 'Please enter a valid date to start posting';
                        return $msg;
                    }

                    $startDate = $_POST['startDate'];
                    $startTime = $_POST['startTime'];
                    $endTime = $_POST['endTime'];

                    $dailyMax = $_POST['postsperday'];

                    $intHours = $_POST['intHours'];
                    $intMins = $_POST['intMins'];

                    if ($_POST['randomness'] == "sequential" || $_POST['randomness'] == "random") {
                        if ( !is_numeric( $intHours ) || ( $intHours =='' ) || ( 24 < $intHours ) || (0 > $intHours ) ) {
                            $msg = 'Please enter a valid Post Interval';
                            return $msg;
                        }
                        if ( !is_numeric( $intMins ) || ( $intMins =='' ) || ( 60 < $intMins ) || 0 > $intMins ) {
                            $msg = 'Please enter a valid Post Interval';
                            return $msg;
                        }
                    } else {

                        if ( !j_is_date( $startTime ) || ( $startTime == '' ) ) {
                            $msg = 'Please enter a valid start time';
                            return $msg;
                        }
                        if ( !j_is_date( $endTime ) || ( $endTime == '' ) ) {
                            $msg = 'Please enter a valid end time';
                            return $msg;
                        }

                        if (strtotime($startTime) > strtotime($endTime)) {
                            $msg = 'Ensure the start time is before the end time.';
                            return $msg;
                        }
                    }

                    if ($_POST['randomness'] == "sequential"){

                        $msg = $this->draftScheduler_sequential_post_by_interval($startDate, $intHours, $intMins);

                    } else if ($_POST['randomness'] == "random")  {

                        $msg = $this->draftScheduler_random_post_by_interval($startDate, $intHours, $intMins);

                    } else {

                        $msg = $this->draftScheduler_randomize_drafts($startDate, $dailyMax, $startTime, $endTime);
                    }

                    return $msg;
                } // end check for post action
            } // end check admin referrer
        }

        /*
         * function print_admin_page
         * Display the amdin options page
         */
        function draftScheduler_admin_page() {
            ?>
            <script type="text/javascript" src="<?php echo DraftScheduler_root ?>js/jquery-ui-1.7.2.custom.min.js"></script>
            <link rel="stylesheet" type="text/css" href="<?php echo DraftScheduler_root ?>css/sunny/jquery-ui-1.7.2.custom.css" />
            <script type="text/javascript">
                jQuery(document).ready(function(jQuery) {
                        jQuery("#datepicker").datepicker({altField: '#startDate', altFormat: 'MM d, yy', defaultDate: '<?php echo $startDate; ?>'});
                });
            </script>

                <h2>Draft Scheduler</h2>
                <form method="post">
                    <?php wp_nonce_field('do-schedule-drafts'); ?>
                    <table class="form-table">
                        <tr valign="top">
                            <th scope="row">Schedule Start Date</th>
                            <td>
                                <div id="datepicker"></div>
                                <input type="text" name="startDate" id="startDate" size="31" value="<?php echo $_POST['startDate']; ?>" />
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">Posting order</th>
                            <td>
                                <input type="radio" name="randomness" value="random" <?php if ($_POST['randomness'] == "random" || $_POST['randomness'] == "") echo " checked " ?>> Randomly grab any draft in any order<br />
                                <input type="radio" name="randomness" value="sequential" <?php if ($_POST['randomness'] == "sequential") echo " checked " ?>> Sequentially schedule drafts from oldest to newest in order
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">Post interval</th>
                            <td>
                                <input type="text" name="intHours" id="intHours" size="4" maxlength="2" value="<?php echo $_POST['intHours']; ?>" /><strong>hrs</strong>
                                <input type="text" name="intMins" id="intMins" size="4" maxlength="2" value="<?php echo $_POST['intMins']; ?>" /><strong>mins</strong>
                            </td>
                        </tr>

                        <tr valign="top">
                            <th scope="row">&nbsp;&nbsp;&nbsp; - OR -</th>
                            <td><hr style="width:400px; float: left;"/></td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">Post Randomly</th>
                            <td>
                                <input type="radio" name="randomness" value="fullrandom" <?php if ($_POST['randomness'] == "fullrandom") echo " checked " ?>> Surprise me <em>(posts entirely randomly after the date above, but within the times below)</em><br />
                                Post up to <input type="text" name="postsperday" id="postsperda" value="<?php echo $_POST['postsperday']; ?>" size="3" maxlength="3" /> posts per day<br />
                                Randomly after: 
                                <select name="startTime" id="startTime">
                                    <option value="">Choose a time</option>
                                <?php echo self::createDateTimeDropDowns($_POST['startTime']); ?>
                                </select><br />
                                And before:
                                <select name="endTime" id="endTime">
                                    <option value="">Choose a time</option>
                                <?php echo self::createDateTimeDropDowns($_POST['endTime']); ?>
                                </select><br />
                            </td>
                        </tr>

                    </table>
                    <input type="hidden" name="action" value="schedule_drafts">
                    <p class="submit">
                    <input type="submit" class="button-primary" value="<?php _e('Schedule Drafts') ?>" />
                    </p>
                </form>
<?php
        } // End Funcion print_admin_page

        /*
         * function draftScheduler_randomize_drafts
         *
         */
        function draftScheduler_randomize_drafts($startDate, $dailyMax, $startTime, $endTime) {

            global $wpdb;

            $postsToday = rand( 0, $dailyMax * 100 ) / 100;

            $startPosts = date_i18n( 'Y-n-j g:i:s', strtotime($startDate . ' ' . $startTime));

            $randInterval = (strtotime($endTime) - strtotime($startTime));

            $findDrafts = "SELECT ID FROM " .$wpdb->prefix . "posts WHERE post_status = 'draft' ORDER BY rand()";

            $draftList = $wpdb->get_results($findDrafts);

            if ( empty($draftList) ) {
                return "No drafts were found";
            }

            $finished = false;

            while ( ! $finished ):

                if ( 0 >= $postsToday ){
                    $postsToday = rand( 0, $dailyMax * 100 ) / 100;
                    $startPosts = date_i18n( 'Y-n-j g:i:s', strtotime( "+1 day", strtotime($startPosts) ) );
                }

                $thisDraft = array_pop( $draftList );
                $postRandom = rand( 0, $randInterval );
                $postTime = date_i18n('Y-n-j G:i:s', strtotime( "+" . $postRandom . " seconds", strtotime($startPosts)));

                $wpdb->update( $wpdb->prefix . 'posts', array( 'post_date' =>  $postTime ,
                        'post_date_gmt' =>  get_gmt_from_date( $postTime),
                        'post_status' => 'future' ), array( 'ID' => $thisDraft->ID )
                );
$wpdb->print_error();
                check_and_publish_future_post($thisDraft->ID);

                if ( 0 == count($draftList) ){
                    $finished = true;
                }
                $postsToday --;
            endwhile;

            return "Drafts updated!";
            //return $query;
        }

        /*
         * function draftScheduler_random_post_by_interval
         * Schedule drafts in random order, based on an interval
         */
        function draftScheduler_random_post_by_interval( $startDate, $intvHours, $intvMins) {
            global $wpdb;

            /*
             * $intvSecs, convert hours and minutes to seconds for the
             * sql query
             */

            $intvSecs = ($intvHours * 60 * 60) + ($intvMins * 60);

            $findDrafts = "SELECT * FROM " .$wpdb->posts . " WHERE post_status = 'draft' ORDER BY rand()";

            $draftList = $wpdb->get_results($findDrafts);

            if ( empty($draftList) ) {
                return "No drafts were found";
            }

            $startDate = $startDate;

            $ds_postDate = strtotime($startDate);
            
            foreach ($draftList as $thisDraft){
                $ds_gmt = get_gmt_from_date(date('Y-m-d H:i:s', $ds_postDate));
                $wpdb->update( $wpdb->posts, array( 'post_date' => date_i18n('Y-n-j G:i:s', $ds_postDate),
                        'post_date_gmt' => $ds_gmt,
                        'post_status' => 'future'
                        ),
                    array( 'ID' => $thisDraft->ID )
                );
                
                check_and_publish_future_post($thisDraft->ID);

                $ds_postDate += $intvSecs;
            }
            //$query = "update";
            return "Drafts updated!";
        } // End Function draftScheduler_random_post_by_interval

        /*
         * function draftScheduler_sequential_post_by_interval
         * Schedule drafts in sequential order, based on an interval
         */
        function draftScheduler_sequential_post_by_interval( $startDate, $intvHours, $intvMins) {
            global $wpdb;

            /*
             * $intvSecs, convert hours and minutes to seconds for the
             * sql query
             */
            $intvSecs = ($intvHours * 60 * 60) + ($intvMins * 60);

            $findDrafts = "SELECT ID FROM " .$wpdb->posts . " WHERE post_status = 'draft' ORDER BY id ASC";

            $draftList = $wpdb->get_results($findDrafts);

            if ( empty( $draftList ) ) {
                return "No drafts were found";
            }

            $ds_postDate = strtotime($startDate);

            foreach ($draftList as $thisDraft){
                $ds_gmt = get_gmt_from_date(date('Y-m-d H:i:s', $ds_postDate));
                $wpdb->update( $wpdb->posts, array( 'post_date' => date_i18n('Y-n-j G:i:s', $ds_postDate),
                        'post_date_gmt' => $ds_gmt,
                        'post_status' => 'future'
                        ),
                    array( 'ID' => $thisDraft->ID )
                );

                check_and_publish_future_post($thisDraft->ID);

                $ds_postDate += $intvSecs;
            }
            return "Drafts updated!";

        } // End Function draftScheduler_sequential_post_by_interval

        /*
         * function createDateTimeDropDowns
         * generate a dropdown select list for 30 minute intervals
         */
        function createDateTimeDropDowns ( $selected ) {
            //$outputHTML = '<option value=""></option>';
            for ($i = 0; $i <= 23; $i ++) {
                if($i < 10 ) {
                    $hourTime = "0" . $i;
                } else {
                    $hourTime = $i;
                }
                $outputHTML .= '<option value="'.$hourTime.':00"';
                    if ($hourTime . ":00" == $selected) {
                        $outputHTML .= " selected='selected' ";
                    }
                $outputHTML .= '>'.$hourTime.':00</option>';
                $outputHTML .= '<option value="'.$hourTime.':30"';
                    if ($hourTime . ":30" == $selected) {
                        $outputHTML .= " selected='selected' ";
                    }
                $outputHTML .= '>'.$hourTime.':30</option>';
            }
            return $outputHTML;
        } // End Function createDateTimeDropDowns

    } // End Class DraftScheduler
} // End If

$draftScheduler = new DraftScheduler();

    function j_is_date( $str )
    {
      $stamp = strtotime( $str );

      if (!is_numeric($stamp))
      {
         return FALSE;
      }
      $month = date( 'm', $stamp );
      $day   = date( 'd', $stamp );
      $year  = date( 'Y', $stamp );

      if (checkdate($month, $day, $year))
      {
         return TRUE;
      }

      return FALSE;
    }

?>
