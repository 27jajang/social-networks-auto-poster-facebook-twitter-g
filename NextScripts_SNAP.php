<?php
/*
Plugin Name: Next Scripts Social Networks Auto-Poster
Plugin URI: http://www.nextscripts.com/social-networks-auto-poster-for-wordpress
Description: This plugin automatically publishes posts from your blog to your Facebook, Twitter, Tumblr, Pinterest, Blogger and Google+ profiles and/or pages.
Author: Next Scripts
Version: 1.8.5
Author URI: http://www.nextscripts.com
Copyright 2012  Next Scripts, Inc
*/
$php_version = (int)phpversion(); 
if (file_exists(realpath(ABSPATH."wp-content/plugins/postToGooglePlus.php"))) require realpath(ABSPATH."wp-content/plugins/postToGooglePlus.php");
  elseif (file_exists(realpath(dirname( __FILE__ ))."/apis/postToGooglePlus.php")) require realpath(dirname( __FILE__ ))."/apis/postToGooglePlus.php";
  if (file_exists(realpath(ABSPATH."wp-content/plugins/postToPinterest.php"))) require realpath(ABSPATH."wp-content/plugins/postToPinterest.php");
  elseif (file_exists(realpath(dirname( __FILE__ ))."/apis/postToPinterest.php")) require realpath(dirname( __FILE__ ))."/apis/postToPinterest.php";

//  $included_files = get_included_files(); echo "<br/><br/><br/><br/><br/>"; prr($included_files);
    
define( 'NextScripts_SNAP_Version' , '1.8.5' );
if (!function_exists('prr')){ function prr($str) { echo "<pre>"; print_r($str); echo "</pre>\r\n"; }}        

//## Define class
if (!class_exists("NS_SNAutoPoster")) {
    class NS_SNAutoPoster {//## General Functions         
        //## Name for the DB Record for NS SNAP Options
        var $dbOptionsName = "NS_SNAutoPoster";        
        //## Constructor
        function NS_SNAutoPoster() { global $wp_version; $this->wp_version = $wp_version;}
        //## Initialization function
        function init() { $this->getAPOptions();}
        //## Administrative Functions
        //## Options loader function
        function getAPOptions($user_login = "") {
            //## Some Default Values
            $options = array('fbAttch'=>1,'gpAttch'=>1, 'nsOpenGraph'=>1, 'gpMsgFormat'=>'New post has been published on %SITENAME%', 'fbMsgFormat'=>'New post has been published on %SITENAME%', 'twMsgFormat'=>'%TITLE% - %URL%');
            //## User's Options?
            if (empty($user_login))  $optionsAppend = ""; else  $optionsAppend = "_" . $user_login;
            //## Get values from the WP options table in the database, re-assign if found
            $dbOptions = get_option($this->dbOptionsName.$optionsAppend);
            if (!empty($dbOptions))  foreach ($dbOptions as $key => $option) $options[$key] = $option;            
            //## Update the options for the panel
            update_option($this->dbOptionsName . $optionsAppend, $options);
            return $options;
        }
        function showSNAutoPosterUsersOptionsPage($user_login = "") { global $current_user; get_currentuserinfo(); $this->showSNAutoPosterOptionsPage($current_user->user_login); }
        //## Print the admin page for the plugin
        function showSNAutoPosterOptionsPage($user_login = "") { $emptyUser = empty($user_login); $nxsOne = '';        
            //## Get the user options
            $options = $this->getAPOptions($user_login);    
            if (isset($_POST['update_NS_SNAutoPoster_settings'])) { 
                if (isset($_POST['apDoGP']))   $options['doGP'] = $_POST['apDoGP']; else $options['doGP'] = 0; 
                if (isset($_POST['apDoFB']))   $options['doFB'] = $_POST['apDoFB']; else $options['doFB'] = 0;
                if (isset($_POST['apDoTW']))   $options['doTW'] = $_POST['apDoTW']; else $options['doTW'] = 0;
                if (isset($_POST['apDoTR']))   $options['doTR'] = $_POST['apDoTR']; else $options['doTR'] = 0;
                if (isset($_POST['apDoPN']))   $options['doPN'] = $_POST['apDoPN']; else $options['doPN'] = 0;
                if (isset($_POST['apDoBG']))   $options['doBG'] = $_POST['apDoBG']; else $options['doBG'] = 0;
                
                
                if (isset($_POST['apGPUName']))   $options['gpUName'] = $_POST['apGPUName'];
                if (isset($_POST['apGPPass']))    $options['gpPass'] = $_POST['apGPPass'];                                
                if (isset($_POST['apGPPage']))    $options['gpPageID'] = $_POST['apGPPage'];                
                if (isset($_POST['apGPAttch']))   $options['gpAttch'] = $_POST['apGPAttch'];  else $options['gpAttch'] = 0;                               
                if (isset($_POST['apGPMsgFrmt'])) $options['gpMsgFormat'] = $_POST['apGPMsgFrmt'];     
                
                if (isset($_POST['apPNUName']))   $options['pnUName'] = $_POST['apPNUName'];
                if (isset($_POST['apPNPass']))    $options['pnPass'] = $_POST['apPNPass'];                                
                if (isset($_POST['apPNBoard']))   $options['pnBoard'] = $_POST['apPNBoard'];                
                if (isset($_POST['apPNDefImg']))  $options['pnDefImg'] = $_POST['apPNDefImg'];
                if (isset($_POST['apPNMsgFrmt'])) $options['pnMsgFormat'] = $_POST['apPNMsgFrmt'];     
                
                if (isset($_POST['apBGUName']))   $options['bgUName'] = $_POST['apBGUName'];
                if (isset($_POST['apBGPass']))    $options['bgPass'] = $_POST['apBGPass'];                                
                if (isset($_POST['apBGBlogID']))   $options['bgBlogID'] = $_POST['apBGBlogID'];                
                if (isset($_POST['apBGMsgFrmt'])) $options['bgMsgFormat'] = $_POST['apBGMsgFrmt'];   
                if (isset($_POST['apBGMsgTFrmt']))    $options['bgMsgTFormat'] = $_POST['apBGMsgTFrmt'];         
                if (isset($_POST['bgInclTags']))    $options['bgInclTags'] = $_POST['bgInclTags'];  else $options['bgInclTags'] = 0;        
                
                
                if (isset($_POST['apFBURL']))  {   $options['fbURL'] = $_POST['apFBURL'];
                  $fbPgID = $options['fbURL']; if (substr($fbPgID, -1)=='/') $fbPgID = substr($fbPgID, 0, -1);  $fbPgID = substr(strrchr($fbPgID, "/"), 1);
                  $options['fbPgID'] = $fbPgID; //echo $fbPgID;
                }
                
                if (isset($_POST['apFBAppID']))   $options['fbAppID'] = $_POST['apFBAppID'];                                
                if (isset($_POST['apFBAppSec']))  $options['fbAppSec'] = $_POST['apFBAppSec'];        
                if (isset($_POST['apFBAttch']))   $options['fbAttch'] = $_POST['apFBAttch'];    else $options['apFBAttch'] = 0;                                    
                if (isset($_POST['apFBMsgFrmt'])) $options['fbMsgFormat'] = $_POST['apFBMsgFrmt'];                                
                
                if (isset($_POST['apTWURL']))        $options['twURL'] = $_POST['apTWURL'];
                if (isset($_POST['apTWConsKey']))    $options['twConsKey'] = $_POST['apTWConsKey'];
                if (isset($_POST['apTWConsSec']))    $options['twConsSec'] = $_POST['apTWConsSec'];                                
                if (isset($_POST['apTWAccToken']))   $options['twAccToken'] = $_POST['apTWAccToken'];                
                if (isset($_POST['apTWAccTokenSec']))$options['twAccTokenSec'] = $_POST['apTWAccTokenSec'];                                
                if (isset($_POST['apTWMsgFrmt']))    $options['twMsgFormat'] = $_POST['apTWMsgFrmt'];                                
                
                
                if (isset($_POST['apTRURL']))  {   $options['trURL'] = $_POST['apTRURL'];
                  $trPgID = $options['trURL']; if (substr($trPgID, -1)=='/') $trPgID = substr($trPgID, 0, -1);  $trPgID = substr(strrchr($trPgID, "/"), 1);
                  $options['trPgID'] = $trPgID; //echo $fbPgID;
                }
                if (isset($_POST['apTRConsKey']))    $options['trConsKey'] = $_POST['apTRConsKey'];
                if (isset($_POST['apTRConsSec']))    $options['trConsSec'] = $_POST['apTRConsSec'];                                
                if (isset($_POST['apTRMsgFrmt']))    $options['trMsgFormat'] = $_POST['apTRMsgFrmt'];                                
                if (isset($_POST['apTRMsgTFrmt']))    $options['trMsgTFormat'] = $_POST['apTRMsgTFrmt'];   
                if (isset($_POST['trInclTags']))    $options['trInclTags'] = $_POST['trInclTags']; else $options['trInclTags'] = 0;
                
                if (isset($_POST['apCats']))      $options['apCats'] = $_POST['apCats'];
                
                if (isset($_POST['ogImgDef']))      $options['ogImgDef'] = $_POST['ogImgDef'];
                if (isset($_POST['nsOpenGraph']))   $options['nsOpenGraph'] = $_POST['nsOpenGraph'];  else $options['nsOpenGraph'] = 0;                               
                
                
                
                if ($emptyUser) { //## then we're dealing with the main Admin options
                    $options[$this->NextScripts_GPAutoPosterAllUsers] = $_POST['NS_SNAutoPosterallusers'];
                    $options[$this->NextScripts_GPAutoPosterNoPublish] = $_POST['NS_SNAutoPosternopublish'];                    
                    $optionsAppend = "";
                } else $optionsAppend = "_" . $user_login;       //  prr($options);       
                update_option($this->dbOptionsName . $optionsAppend, $options);
                //## Update settings notification
                ?>
                <div class="updated"><p><strong><?php _e("Settings Updated.", "NS_SNAutoPoster");?></strong></p></div>
            <?php
            }
            //## Display HTML form for the options below
           
            ?>            
            <script type="text/javascript"> if (typeof jQuery == 'undefined') {var script = document.createElement('script'); script.type = "text/javascript"; 
              script.src = "http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"; document.getElementsByTagName('head')[0].appendChild(script);
            }</script>
            <script type="text/javascript">
            function doShowHideAltFormat(){ if (jQuery('#NS_SNAutoPosterAttachPost').is(':checked')) { 
                    jQuery('#altFormat').css('margin-left', '20px'); jQuery('#altFormatText').html('Post Announce Text:'); } else {jQuery('#altFormat').css('margin-left', '0px'); jQuery('#altFormatText').html('Post Text Format:');}
            }
            function doShowHideBlocks(blID){ if (jQuery('#apDo'+blID).is(':checked')) jQuery('#do'+blID+'Div').show(); else jQuery('#do'+blID+'Div').hide();
                    
            }
            
            function getBoards(u,p){ jQuery("#pnLoadingImg").show();
                
                jQuery.post(ajaxurl,{u:u,p:p, action: 'getBoards', id: 0, _wpnonce: jQuery('input#getBoards_wpnonce').val(), ajax: 'true'}, function(j){ var options = '';                    
                    jQuery("select#apPNBoard").html(j); jQuery("#pnLoadingImg").hide();
                }, "html")

            }
           
            function callAjSNAP(data, label) {
            var style = "position: fixed; display: none; z-index: 1000; top: 50%; left: 50%; background-color: #E8E8E8; border: 1px solid #555; padding: 15px; width: 350px; min-height: 80px; margin-left: -175px; margin-top: -40px; text-align: center; vertical-align: middle;";
            jQuery('body').append("<div id='test_results' style='" + style + "'></div>");
            jQuery('#test_results').html("<p>Sending update to "+label+"</p>" + "<p><img src='http://gtln.us/img/misc/ajax-loader-med.gif' /></p>");
            jQuery('#test_results').show();            
            jQuery.post(ajaxurl, data, function(response) { if (response=='') response = 'Message Posted';
                jQuery('#test_results').html('<p> ' + response + '</p>' +'<input type="button" class="button" name="results_ok_button" id="results_ok_button" value="OK" />');
                jQuery('#results_ok_button').click(remove_results);
            });
            
        }        
        function remove_results() { jQuery("#results_ok_button").unbind("click");jQuery("#test_results").remove();
            if (typeof document.body.style.maxHeight == "undefined") { jQuery("body","html").css({height: "auto", width: "auto"}); jQuery("html").css("overflow","");}
            document.onkeydown = "";document.onkeyup = "";  return false;
        }
            function testPost(nt){
              if (nt=='GP') { var data = { action: 'rePostToGP', id: 0, _wpnonce: jQuery('input#rePostToGP_wpnonce').val()}; callAjSNAP(data, 'Google+'); }
              if (nt=='FB') { var data = { action: 'rePostToFB', id: 0, _wpnonce: jQuery('input#rePostToFB_wpnonce').val()}; callAjSNAP(data, 'Facebook'); }
              if (nt=='TW') { var data = { action: 'rePostToTW', id: 0, _wpnonce: jQuery('input#rePostToTW_wpnonce').val()}; callAjSNAP(data, 'Twitter'); }
              if (nt=='TR') { var data = { action: 'rePostToTR', id: 0, _wpnonce: jQuery('input#rePostToTR_wpnonce').val()}; callAjSNAP(data, 'Tumblr'); }
              if (nt=='PN') { var data = { action: 'rePostToPN', id: 0, _wpnonce: jQuery('input#rePostToPN_wpnonce').val()}; callAjSNAP(data, 'Pinterest'); }
              if (nt=='BG') { var data = { action: 'rePostToBG', id: 0, _wpnonce: jQuery('input#rePostToBG_wpnonce').val()}; callAjSNAP(data, 'Blogger'); }
            }
            
            </script>
            
<style type="text/css">
.NXSButton {
    background:-webkit-gradient( linear, left top, left bottom, color-stop(0.05, #89c403), color-stop(1, #77a809) );
    background:-moz-linear-gradient( center top, #89c403 5%, #77a809 100% );
    filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#89c403', endColorstr='#77a809');
    background-color:#89c403;
    -moz-border-radius:4px;
    -webkit-border-radius:4px;
    border-radius:4px;
    border:1px solid #74b807;
    display:inline-block;
    color:#ffffff;
    font-family:Trebuchet MS;
    font-size:12px;
    font-weight:bold;
    padding:2px 5px;
    text-decoration:none;
    text-shadow:1px 1px 0px #528009;
}.NXSButton:hover {color:#ffffff;
    background:-webkit-gradient( linear, left top, left bottom, color-stop(0.05, #77a809), color-stop(1, #89c403) );
    background:-moz-linear-gradient( center top, #77a809 5%, #89c403 100% );
    filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#77a809', endColorstr='#89c403');
    background-color:#77a809;
}.NXSButton:active {color:#ffffff;
    position:relative;
    top:1px;
}.NXSButton:focus {color:#ffffff;
    position:relative;
    top:1px;
}

</style>
            <div style="float:right; padding-top: 10px; padding-right: 10px;">
              <div style="float:right;"><a target="_blank" href="http://www.nextscripts.com"><img src="http://direct.gtln.us/img/nxs/NextScriptsLogoT.png"></a></div>
              <div style="float:right; text-align: right; padding-right: 10px;"><a style="font-weight: normal; font-size: 16px; line-height: 24px;" target="_blank" href="http://www.nextscripts.com/support">Contact support</a>&nbsp;|&nbsp;
              <a style="font-weight: normal; font-size: 16px; line-height: 24px;" target="_blank" href="http://gd.is/s9xd">Donate</a>
              
              <br/><a target="_blank" href="http://www.owssoftware.com/startcouponwebsite">Make Money with Your Own<br/> Free Deals/Coupons Website</a>
              </div>
            </div>
            
           <div class="wrap"><h2>Next Scripts: Social Networks AutoPoster Options</h2>Version: <?php echo NextScripts_SNAP_Version; ?> [Single Account] - <a target="_blank" href="http://www.nextscripts.com/social-networks-auto-poster-for-wp-multiple-accounts">Get Multiple Accounts Edition</a><br/><br/>
           Please see the <a target="_blank" href="http://www.nextscripts.com/installation-of-social-networks-auto-poster-for-wordpress">detailed installation instructions</a> (will open in a new tab)
           <?php
          
           if (!function_exists('curl_init')) {  echo ('<br/><b style=\'font-size:16px; color:red;\'>Error: No CURL Found</b> <br/><i>Social Networks AutoPoster needs the CURL PHP extension. Please install it or contact your hosting company to install it.</i><br/>'); }
           
           ?>
            
            <form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">                
            <!-- ######################## G+ ###################################-->   
            <h3 style="font-size: 17px;">Google+ Settings</h3>   
            
            <?php if(!function_exists('doPostToGooglePlus')) {?> Google+ don't have a built-in API for automated posts yet. The current <a href="http://developers.google.com/+/api/">Google+ API</a> is "Read Only" and can't be used for posting.  <br/>You need to get a special <a target="_blank" href="http://www.nextscripts.com/google-plus-automated-posting">library module</a> to be able to publish your content to Google+. <br/><br/>When you get the library, please place the <b>postToGooglePlus.php</b> file to the <b>/wp-content/plugins/</b> or <b>/wp-content/plugins/social-networks-auto-poster-facebook-twitter-g/apis/</b> folder to activate Google+ publishing functionality.  <br/>
            <i><b>*****</b> If you have <b>upgraded</b> the script from WordPress.org and lost Google+ functionality, please upload <b>postToGooglePlus.php</b> file to the <b>/wp-content/plugins/</b> That will keep it from getting removed again with the next update.</i>
            
            <?php } else {?>
            
            <p style="margin: 0px;margin-left: 5px;"><input value="1" id="apDoGP" name="apDoGP" onchange="doShowHideBlocks('GP');" type="checkbox" <?php if ((int)$options['doGP'] == 1) echo "checked"; $nxsOne = "?g=1" ?> /> 
              <strong>Auto-publish your Posts to your Google+ Page or Profile</strong>                                 
            </p>
            <div id="doGPDiv" style="margin-left: 10px;<?php if ((int)$options['doGP'] != 1) echo "display:none"; ?> ">
                  
            <div style="width:100%;"><strong>Google+ Username:</strong> </div><input name="apGPUName" id="apGPUName" style="width: 30%;" value="<?php _e(apply_filters('format_to_edit',$options['gpUName']), 'NS_SNAutoPoster') ?>" />                
            <div style="width:100%;"><strong>Google+ Password:</strong> </div><input name="apGPPass" id="apGPPass" type="password" style="width: 30%;" value="<?php _e(apply_filters('format_to_edit',$options['gpPass']), 'NS_SNAutoPoster') ?>" />  <br/>                
            <p><div style="width:100%;"><strong>Google+ Page ID (Optional):</strong> 
            <p style="font-size: 11px; margin: 0px;">If URL for your page is https://plus.google.com/u/0/b/117008619877691455570/ your Page ID is: 117008619877691455570. Leave Empty to publish to your profile.</p>
            </div><input name="apGPPage" id="apGPPage" style="width: 30%;" value="<?php _e(apply_filters('format_to_edit',$options['gpPageID']), 'NS_SNAutoPoster') ?>" /> 
            <br/><br/>
            <p style="margin: 0px;"><input value="1"  id="apGPAttch" onchange="doShowHideAltFormat();" type="checkbox" name="apGPAttch"  <?php if ((int)$options['gpAttch'] == 1) echo "checked"; ?> /> 
              <strong>Publish Posts to Google+ as an Attachement</strong>                                 
            </p>
            
            <div id="altFormat" style="<?php if ((int)$options['gpAttch'] == 1) echo "margin-left: 20px;"; ?> ">
              <div style="width:100%;"><strong id="altFormatText"><?php if ((int)$options['gpAttch'] == 1) echo "Post Announce Text:"; else echo "Post Text Format:"; ?></strong> 
              <p style="font-size: 11px; margin: 0px;">%SITENAME% - Inserts the Your Blog/Site Name. &nbsp; %TITLE% - Inserts the Title of your post. &nbsp; %URL% - Inserts the URL of your post. &nbsp; %IMG% - Inserts the featured image. &nbsp; %TEXT% - Inserts the excerpt of your post. &nbsp;  %FULLTEXT% - Inserts the body(text) of your post, %AUTHORNAME% - Inserts the author's name.</p>
              </div><input name="apGPMsgFrmt" id="apGPMsgFrmt" style="width: 50%;" value="<?php _e(apply_filters('format_to_edit',$options['gpMsgFormat']), 'NS_SNAutoPoster') ?>" />
            </div><br/>    
            
            <?php if ($options['gpPass']!='') { ?>
            <?php wp_nonce_field( 'rePostToGP', 'rePostToGP_wpnonce' ); ?>
            <b>Test your settings:</b>&nbsp;&nbsp;&nbsp; <a href="#" class="NXSButton" onclick="testPost('GP'); return false;">Submit Test Post to Google+</a>         
            <?php } ?>
            
            <div class="submit"><input type="submit" class="button-primary" name="update_NS_SNAutoPoster_settings" value="<?php _e('Update Settings', 'NS_SNAutoPoster') ?>" /></div>
            </div>
            <?php } ?>
            <!-- ############# PINTEREST ################ -->   
            <h3 style="font-size: 17px;">Pinterest Settings</h3>   
            
            <?php if(!function_exists('doPostToPinterest')) {?> Pinterest don't have a built-in API for automated posts yet. <br/>You need to get a special <a target="_blank" href="http://www.nextscripts.com/pinterest-automated-posting">library module</a> to be able to publish your content to Pinterest. <br/><br/>When you get the library, please place the <b>postToPinterest.php</b> file to the <b>/wp-content/plugins/</b> <br/>           
            
            <?php } else {?>
            
            <p style="margin: 0px;margin-left: 5px;"><input value="1" id="apDoPN" name="apDoPN" onchange="doShowHideBlocks('PN');" type="checkbox" <?php if ((int)$options['doPN'] == 1) echo "checked"; $nxsOne = "?g=1" ?> /> 
              <strong>Auto-publish your Posts to your Pinterest Board</strong>                                 
            </p>
            <div id="doPNDiv" style="margin-left: 10px;<?php if ((int)$options['doPN'] != 1) echo "display:none"; ?> ">
                  
            <div style="width:100%;"><strong>Pinterest Username:</strong> </div><input name="apPNUName" id="apPNUName" style="width: 30%;" value="<?php _e(apply_filters('format_to_edit',$options['pnUName']), 'NS_SNAutoPoster') ?>" />                
            <div style="width:100%;"><strong>Pinterest Password:</strong> </div><input name="apPNPass" id="apPNPass" type="password" style="width: 30%;" value="<?php _e(apply_filters('format_to_edit',$options['pnPass']), 'NS_SNAutoPoster') ?>" />  <br/>                
            <div style="width:100%;"><strong>Defailt Image to Pin:</strong> 
            <p style="font-size: 11px; margin: 0px;">If your post missing Featured Image this will be used instead.</p>
            </div><input name="apPNDefImg" id="apPNDefImg" style="width: 30%;" value="<?php _e(apply_filters('format_to_edit',$options['pnDefImg']), 'NS_SNAutoPoster') ?>" /> 
            <br/><br/>            
            
            <div style="width:100%;"><strong>Board:</strong> 
            Please <a href="#" onclick="getBoards(jQuery('#apPNUName').val(),jQuery('#apPNPass').val()); return false;">click here to retreive your boards</a>
            </div>
            <?php wp_nonce_field( 'getBoards', 'getBoards_wpnonce' ); ?><img id="pnLoadingImg" style="display: none;" src='http://gtln.us/img/misc/ajax-loader-sm.gif' />
            <select name="apPNBoard" id="apPNBoard">
            <?php if ($options['pnBoardsList']!=''){ $gPNBoards = $options['pnBoardsList']; if ($options['pnBoard']!='') $gPNBoards = str_replace($options['pnBoard'].'"', $options['pnBoard'].'" selected="selected"', $gPNBoards);  echo $gPNBoards;} else { ?>
              <option value="0">None(Click above to retreive your boards)</option>
            <?php } ?>
            </select>
            
            <br/><br/>            
            
            <div id="altFormat" style="">
              <div style="width:100%;"><strong id="altFormatText">Post Text Format</strong> 
              <p style="font-size: 11px; margin: 0px;">%SITENAME% - Inserts the Your Blog/Site Name. &nbsp; %TITLE% - Inserts the Title of your post. &nbsp; %URL% - Inserts the URL of your post. &nbsp; %IMG% - Inserts the featured image. &nbsp; %TEXT% - Inserts the excerpt of your post. &nbsp;  %FULLTEXT% - Inserts the body(text) of your post, %AUTHORNAME% - Inserts the author's name.</p>
              </div><input name="apPNMsgFrmt" id="apPNMsgFrmt" style="width: 50%;" value="<?php if ($options['pnMsgFormat']!='') _e(apply_filters('format_to_edit',$options['pnMsgFormat']), 'NS_SNAutoPoster');  else echo "%TITLE% - %URL%"; ?>" />
            </div><br/>    
            
            <?php if ($options['pnPass']!='') { ?>
            <?php wp_nonce_field( 'rePostToPN', 'rePostToPN_wpnonce' ); ?>
            <b>Test your settings:</b>&nbsp;&nbsp;&nbsp; <a href="#" class="NXSButton" onclick="testPost('PN'); return false;">Submit Test Post to Pinterest</a>         
            <?php } ?>
            
            <div class="submit"><input type="submit" class="button-primary" name="update_NS_SNAutoPoster_settings" value="<?php _e('Update Settings', 'NS_SNAutoPoster') ?>" /></div>
            </div>
            <?php } ?>
            
            <!-- ##################### FB #####################-->   <hr/>
            <h3 style="font-size: 17px;">FaceBook Settings</h3>   
            <p style="margin: 0px;margin-left: 5px;"><input value="1" id="apDoFB" name="apDoFB" onchange="doShowHideBlocks('FB');" type="checkbox" <?php if ((int)$options['doFB'] == 1) echo "checked"; ?> /> 
              <strong>Auto-publish your Posts to your Facebook Page or Profile</strong>                                 
            </p>
            <div id="doFBDiv" style="margin-left: 10px;<?php if ((int)$options['doFB'] != 1) echo "display:none"; ?> ">
                           
            <div style="width:100%;"><strong>Your Facebook URL:</strong> </div>
            <p style="font-size: 11px; margin: 0px;">Could be your Facebook Profile, Facebook Page, Facebook Group</p>
            <input name="apFBURL" id="apFBURL" style="width: 50%;" value="<?php _e(apply_filters('format_to_edit',$options['fbURL']), 'NS_SNAutoPoster') ?>" />                
            
            <div style="width:100%;"><strong>Your Facebook App ID:</strong> </div><input name="apFBAppID" id="apFBAppID" style="width: 30%;" value="<?php _e(apply_filters('format_to_edit',$options['fbAppID']), 'NS_SNAutoPoster') ?>" />  
            <div style="width:100%;"><strong>Your Facebook App Secret:</strong> </div><input name="apFBAppSec" id="apFBAppSec" style="width: 30%;" value="<?php _e(apply_filters('format_to_edit',$options['fbAppSec']), 'NS_SNAutoPoster') ?>" />
            
            
            <br/><br/>
            <p style="margin: 0px;"><input value="1"  id="apFBAttch" onchange="doShowHideAltFormat();" type="checkbox" name="apFBAttch"  <?php if ((int)$options['fbAttch'] == 1) echo "checked"; ?> /> 
              <strong>Publish Posts to Facebook as an Attachement</strong>                                 
            </p>
            
            <div id="altFormat" style="<?php if ((int)$options['fbAttch'] == 1) echo "margin-left: 20px;"; ?> ">
              <div style="width:100%;"><strong id="altFormatText"><?php if ((int)$options['fbAttch'] == 1) echo "Post Announce Text:"; else echo "Post Text Format:"; ?></strong> 
              <p style="font-size: 11px; margin: 0px;">%SITENAME% - Inserts the Your Blog/Site Name. &nbsp; %TITLE% - Inserts the Title of your post. &nbsp; %URL% - Inserts the URL of your post. &nbsp;  %IMG% - Inserts the featured image. &nbsp;  %IMG% - Inserts the featured image. &nbsp;  %TEXT% - Inserts the excerpt of your post. &nbsp;  %FULLTEXT% - Inserts the body(text) of your post, %AUTHORNAME% - Inserts the author's name.</p>
              </div><input name="apFBMsgFrmt" id="apFBMsgFrmt" style="width: 50%;" value="<?php _e(apply_filters('format_to_edit',$options['fbMsgFormat']), 'NS_SNAutoPoster') ?>" />
            </div><br/>   
            <?php if ($options['fbPgID']!='') {?><div style="width:100%;"><strong>Your Facebook Page ID:</strong> <?php _e(apply_filters('format_to_edit',$options['fbPgID']), 'NS_SNAutoPoster') ?> </div><?php } ?>
            <?php 
            if($options['fbAppSec']=='') { ?>
            <b>Authorize Your FaceBook Account</b>. Please save your settings and come back here to Authorize your account.
            <?php } else { if($options['fbAppAuthUser']>0) { ?>
            Your FaceBook Account has been authorized. User ID: <?php _e(apply_filters('format_to_edit',$options['fbAppAuthUser']), 'NS_SNAutoPoster') ?>. 
            You can Re- <?php } ?>            
            <a target="_blank" href="https://www.facebook.com/dialog/oauth?client_id=<?php echo $options['fbAppID'];?>&client_secret=<?php echo $options['fbAppSec'];?>&redirect_uri=<? echo site_url();?>/wp-admin/options-general.php?page=NextScripts_SNAP.php&scope=publish_stream,offline_access,read_stream,manage_pages">Authorize Your FaceBook Account</a> 
            
            <?php if($options['fbAppAuthUser']<1) { ?>
            <br/><br/><i> If you get Facebook message : <b>"Error. An error occurred. Please try again later."</b> please make sure that domain name in your Facebook App matches your website domain exactly. Please note that <b>nextscripts.com</b> and <b style="color:#800000;">www.</b><b>nextscripts.com</b> are different domains.</i> <?php }?>
            <?php }
            
            if ( isset($_GET['code']) && $_GET['code']!='' && $_GET['action']!='gPlusAuth'){ $at = $_GET['code'];  echo "Code:".$at;
                $response  = wp_remote_get('https://graph.facebook.com/oauth/access_token?client_id='.$options['fbAppID'].'&redirect_uri='.urlencode(site_url().'/wp-admin/options-general.php?page=NextScripts_SNAP.php').'&client_secret='.$options['fbAppSec'].'&code='.$at); 
                if ((is_object($response) && isset($response->errors))) { prr($response); die();}
                parse_str($response['body'], $params); $at = $params['access_token'];
                $response  = wp_remote_get('https://graph.facebook.com/oauth/access_token?client_secret='.$options['fbAppSec'].'&client_id='.$options['fbAppID'].'&grant_type=fb_exchange_token&fb_exchange_token='.$at); 
                if ((is_object($response) && isset($response->errors))) { prr($response); die();}
                if ((is_array($response) && isset($response['response']['code']) && $response['response']['code']!='200')) { prr($response['body']); die();}
                parse_str($response['body'], $params); $at = $params['access_token']; $options['fbAppAuthToken'] = $at; 
                require_once ('apis/facebook.php'); echo "Using API";
                $facebook = new NXS_Facebook(array( 'appId' => $options['fbAppID'], 'secret' => $options['fbAppSec'], 'cookie' => true)); 
                    $facebook -> setAccessToken($options['fbAppAuthToken']); $user = $facebook->getUser(); echo "USER:"; prr($user);
                    if ($user) {
                        try { $page_id = $options['fbPgID']; $page_info = $facebook->api("/$page_id?fields=access_token");
                            if( !empty($page_info['access_token']) ) { $options['fbAppPageAuthToken'] = $page_info['access_token']; }
                        } catch (NXS_FacebookApiException $e) { error_log($e); $user = null;}
                    }else echo "Please login to Facebook";                
                                                
                 if ($user>0) $options['fbAppAuthUser'] = $user; update_option($this->dbOptionsName . $optionsAppend, $options);                            
                 ?><script type="text/javascript">window.location = "<?php echo site_url(); ?>/wp-admin/options-general.php?page=NextScripts_SNAP.php"</script><?php            
                 die();
            }
            ?>
            <?php if($options['fbAppAuthUser']>0) { ?>
            <?php wp_nonce_field( 'rePostToFB', 'rePostToFB_wpnonce' ); ?>
            <br/><br/><b>Test your settings:</b>&nbsp;&nbsp;&nbsp; <a href="#" class="NXSButton" onclick="testPost('FB'); return false;">Submit Test Post to Facebook</a>         
            <?php }?>
            <div class="submit"><input type="submit" class="button-primary" name="update_NS_SNAutoPoster_settings" value="<?php _e('Update Settings', 'NS_SNAutoPoster') ?>" /></div>
            
            </div>         
             <!-- ##################### TW #####################-->  <br/><hr/>
            <h3 style="font-size: 17px;">Twitter Settings</h3> 
            <p style="margin: 0px;margin-left: 5px;"><input value="1" id="apDoTW" name="apDoTW" onchange="doShowHideBlocks('TW');" type="checkbox" <?php if ((int)$options['doTW'] == 1) echo "checked"; ?> /> 
              <strong>Auto-publish your Posts to your Twitter</strong>                                 
            </p>
            <div id="doTWDiv" style="margin-left: 10px;<?php if ((int)$options['doTW'] != 1) echo "display:none"; ?> "> 
            
            <div style="width:100%;"><strong>Your Twitter URL:</strong> </div><input name="apTWURL" id="apTWURL" style="width: 30%;" value="<?php _e(apply_filters('format_to_edit',$options['twURL']), 'NS_SNAutoPoster') ?>" />                
            <div style="width:100%;"><strong>Your Twitter Consumer Key:</strong> </div><input name="apTWConsKey" id="apTWConsKey" style="width: 30%;" value="<?php _e(apply_filters('format_to_edit',$options['twConsKey']), 'NS_SNAutoPoster') ?>" />  
            <div style="width:100%;"><strong>Your Twitter Consumer Secret:</strong> </div><input name="apTWConsSec" id="apTWConsSec" style="width: 30%;" value="<?php _e(apply_filters('format_to_edit',$options['twConsSec']), 'NS_SNAutoPoster') ?>" />
            <div style="width:100%;"><strong>Your Access Token:</strong> </div><input name="apTWAccToken" id="apTWAccToken" style="width: 30%;" value="<?php _e(apply_filters('format_to_edit',$options['twAccToken']), 'NS_SNAutoPoster') ?>" />
            <div style="width:100%;"><strong>Your Access Token Secret:</strong> </div><input name="apTWAccTokenSec" id="apTWAccTokenSec" style="width: 30%;" value="<?php _e(apply_filters('format_to_edit',$options['twAccTokenSec']), 'NS_SNAutoPoster') ?>" />
            
            <div style="width:100%;"><strong id="altFormatText"><?php if ((int)$options['gpAttch'] == 1) echo "Post Announce Text:"; else echo "Post Text Format:"; ?></strong> 
              <p style="font-size: 11px; margin: 0px;">%SITENAME% - Inserts the Your Blog/Site Name. &nbsp; %TITLE% - Inserts the Title of your post. &nbsp; %URL% - Inserts the URL of your post. &nbsp; %SURL% - Inserts the <b>Shortened URL</b> of your post. &nbsp;  %TEXT% - Inserts the excerpt of your post. &nbsp;  %FULLTEXT% - Inserts the body(text) of your post, %AUTHORNAME% - Inserts the author's name.</p>
              </div><img src="http://www.nextscripts.com/gif.php<?php echo $nxsOne; ?> ">
              
              <input name="apTWMsgFrmt" id="apTWMsgFrmt" style="width: 50%;" value="<?php if (!$isNew) _e(apply_filters('format_to_edit',$options['twMsgFormat']), 'NS_SNAutoPoster'); else echo "%TITLE% - %URL%"; ?>" />
              
              <?php if($options['twAccTokenSec']!='') { ?>
            <?php wp_nonce_field( 'rePostToTW', 'rePostToTW_wpnonce' ); ?>
            <br/><br/><b>Test your settings:</b>&nbsp;&nbsp;&nbsp; <a href="#" class="NXSButton" onclick="testPost('TW'); return false;">Submit Test Post to Twitter</a>  <br/><br/>
            <?php }?>
            <div class="submit"><input type="submit" class="button-primary" name="update_NS_SNAutoPoster_settings" value="<?php _e('Update Settings', 'NS_SNAutoPoster') ?>" /></div>  
            </div>
            <!-- ############# BLOGGER ################ -->   
            <h3 style="font-size: 17px;">Blogger Settings</h3>               
                       
            <p style="margin: 0px;margin-left: 5px;"><input value="1" id="apDoBG" name="apDoBG" onchange="doShowHideBlocks('BG');" type="checkbox" <?php if ((int)$options['doBG'] == 1) echo "checked"; $nxsOne = "?g=1" ?> /> 
              <strong>Auto-publish your Posts to your Pinterest Board</strong>                                 
            </p>
            <div id="doBGDiv" style="margin-left: 10px;<?php if ((int)$options['doBG'] != 1) echo "display:none"; ?> ">
                  
            <div style="width:100%;"><strong>Blogger Username/Email:</strong> </div><input name="apBGUName" id="apBGUName" style="width: 30%;" value="<?php _e(apply_filters('format_to_edit',$options['bgUName']), 'NS_SNAutoPoster') ?>" />                
            <div style="width:100%;"><strong>Blogger Password:</strong> </div><input name="apBGPass" id="apBGPass" type="password" style="width: 30%;" value="<?php _e(apply_filters('format_to_edit',$options['bgPass']), 'NS_SNAutoPoster') ?>" />  <br/>                
            <div style="width:100%;"><strong>Blogger Blog ID:</strong> 
            <p style="font-size: 11px; margin: 0px;">Log to your Blogger management panel and look at the URL: http://www.blogger.com/blogger.g?blogID=8959085979163812093#allposts. Your Blog ID will be: 8959085979163812093</p>
            </div><input name="apBGBlogID" id="apBGBlogID" style="width: 30%;" value="<?php _e(apply_filters('format_to_edit',$options['bgBlogID']), 'NS_SNAutoPoster') ?>" /> 
            <br/><br/>            
            
            <div style="width:100%;"><strong id="altFormatText">Post Title Format</strong> 
              <p style="font-size: 11px; margin: 0px;">%SITENAME% - Inserts the Your Blog/Site Name. &nbsp; %TITLE% - Inserts the Title of your post. &nbsp; %URL% - Inserts the URL of your post. &nbsp; %SURL% - Inserts the <b>Shortened URL</b> of your post. &nbsp;  %IMG% - Inserts the featured image. &nbsp;  %TEXT% - Inserts the excerpt of your post. &nbsp;  %FULLTEXT% - Inserts the body(text) of your post, %AUTHORNAME% - Inserts the author's name.</p>
              </div>
              
              <input name="apBGMsgTFrmt" id="apBGMsgTFrmt" style="width: 50%;" value="<?php if ($options['bgMsgTFormat']!='') _e(apply_filters('format_to_edit', stripcslashes(str_replace('"',"'",$options['bgMsgTFormat']))), 'NS_SNAutoPoster'); else echo "%TITLE%"; ?>" /><br/>
            
            <div id="altFormat" style="">
              <div style="width:100%;"><strong id="altFormatText">Post Text Format</strong> 
              <p style="font-size: 11px; margin: 0px;">%SITENAME% - Inserts the Your Blog/Site Name. &nbsp; %TITLE% - Inserts the Title of your post. &nbsp; %URL% - Inserts the URL of your post. &nbsp; %IMG% - Inserts the featured image. &nbsp; %TEXT% - Inserts the excerpt of your post. &nbsp;  %FULLTEXT% - Inserts the body(text) of your post, %AUTHORNAME% - Inserts the author's name.</p>
              </div><input name="apBGMsgFrmt" id="apBGMsgFrmt" style="width: 50%;" value="<?php if ($options['bgMsgFormat']!='') _e(apply_filters('format_to_edit',stripcslashes(str_replace('"',"'",$options['bgMsgFormat']))), 'NS_SNAutoPoster');  else echo "%FULLTEXT% <br/><a href='%URL%'>%TITLE%</a>"; ?>" />
            </div>
            
             <p style="margin-bottom: 20px;margin-top: 5px;"><input value="1"  id="bgInclTags" type="checkbox" name="bgInclTags"  <?php if ((int)$options['bgInclTags'] == 1) echo "checked"; ?> /> 
              <strong>Post with tags</strong>  Tags from the blogpost will be auto posted to Blogger/Blogspot                                                               
            </p> 
            
            <?php if ($options['bgPass']!='') { ?>
            <?php wp_nonce_field( 'rePostToBG', 'rePostToBG_wpnonce' ); ?>
            <b>Test your settings:</b>&nbsp;&nbsp;&nbsp; <a href="#" class="NXSButton" onclick="testPost('BG'); return false;">Submit Test Post to Blogger</a>         
            <?php } ?>
            
            <div class="submit"><input type="submit" class="button-primary" name="update_NS_SNAutoPoster_settings" value="<?php _e('Update Settings', 'NS_SNAutoPoster') ?>" /></div>
            </div>

            <!-- ##################### TR #####################-->
            <br/><hr/>
            <h3 style="font-size: 17px;">Tumblr Settings</h3> 
            <p style="margin: 0px;margin-left: 5px;"><input value="1" id="apDoTR" name="apDoTR" onchange="doShowHideBlocks('TR');" type="checkbox" <?php if ((int)$options['doTR'] == 1) echo "checked"; ?> /> 
              <strong>Auto-publish your Posts to your Tumblr</strong>                                 
            </p>
            <div id="doTRDiv" style="margin-left: 10px;<?php if ((int)$options['doTR'] != 1) echo "display:none"; ?> "> 
            
            <div style="width:100%;"><strong>Your Tumblr URL:</strong> </div><input name="apTRURL" id="apTRURL" style="width: 30%;" value="<?php _e(apply_filters('format_to_edit',$options['trURL']), 'NS_SNAutoPoster') ?>" />                
            <div style="width:100%;"><strong>Your Tumblr OAuth Consumer Key:</strong> </div><input name="apTRConsKey" id="apTRConsKey" style="width: 30%;" value="<?php _e(apply_filters('format_to_edit',$options['trConsKey']), 'NS_SNAutoPoster') ?>" />             <div style="width:100%;"><strong>Your Tumblr Secret Key:</strong> </div><input name="apTRConsSec" id="apTRConsSec" style="width: 30%;" value="<?php _e(apply_filters('format_to_edit',$options['trConsSec']), 'NS_SNAutoPoster') ?>" />
            
            <div style="width:100%;"><strong id="altFormatText">Post Title Format</strong> 
              <p style="font-size: 11px; margin: 0px;">%SITENAME% - Inserts the Your Blog/Site Name. &nbsp; %TITLE% - Inserts the Title of your post. &nbsp; %URL% - Inserts the URL of your post. &nbsp; %SURL% - Inserts the <b>Shortened URL</b> of your post. &nbsp;  %IMG% - Inserts the featured image. &nbsp;  %TEXT% - Inserts the excerpt of your post. &nbsp;  %FULLTEXT% - Inserts the body(text) of your post, %AUTHORNAME% - Inserts the author's name.</p>
              </div>
              
              <input name="apTRMsgTFrmt" id="apTRMsgTFrmt" style="width: 50%;" value="<?php if ($options['trMsgTFormat']!='') _e(apply_filters('format_to_edit', stripcslashes(str_replace('"',"'",$options['trMsgTFormat']))), 'NS_SNAutoPoster'); else echo "New Post has been published on %SITENAME%"; ?>" /><br/>
            
            <div style="width:100%;"><strong id="altFormatText">Post Text Format</strong> 
              <p style="font-size: 11px; margin: 0px;">%SITENAME% - Inserts the Your Blog/Site Name. &nbsp; %TITLE% - Inserts the Title of your post. &nbsp; %URL% - Inserts the URL of your post. &nbsp; %SURL% - Inserts the <b>Shortened URL</b> of your post. &nbsp;  %IMG% - Inserts the featured image. &nbsp;  %TEXT% - Inserts the excerpt of your post. &nbsp;  %FULLTEXT% - Inserts the body(text) of your post, %AUTHORNAME% - Inserts the author's name.</p>
              </div>
              
              <input name="apTRMsgFrmt" id="apTRMsgFrmt" style="width: 50%;" value="<?php if ($options['trMsgFormat']!='') _e(apply_filters('format_to_edit', stripcslashes(str_replace('"',"'",$options['trMsgFormat']))), 'NS_SNAutoPoster'); else echo "<p>New Post has been published on %URL%</p><blockquote><p><strong>%TITLE%</strong></p><p><img src='%IMG%'/></p><p>%FULLTEXT%</p></blockquote>"; ?>" /><br/>
              
              <p style="margin-bottom: 20px;margin-top: 5px;"><input value="1"  id="trInclTags" type="checkbox" name="trInclTags"  <?php if ((int)$options['trInclTags'] == 1) echo "checked"; ?> /> 
              <strong>Post with tags</strong> Tags from the blogpost will be auto posted to Tumblr                                
            </p>
              
              <?php 
            if($options['trConsSec']=='') { ?>
            <b>Authorize Your Tumblr Account</b>. Please save your settings and come back here to Authorize your account.
            <?php } else { if(isset($options['trAccessTocken']) && isset($options['trAccessTocken']['oauth_token_secret']) && $options['trAccessTocken']['oauth_token_secret']!=='') { ?>
            Your Tumblr Account has been authorized. Blog ID: <?php _e(apply_filters('format_to_edit',$options['trPgID']), 'NS_SNAutoPoster') ?>. 
            You can Re- <?php } ?>            
            <a target="_blank" href="<? echo site_url();?>/wp-admin/options-general.php?page=NextScripts_SNAP.php&auth=tr">Authorize Your Tumblr Account</a> 
            
            <?php }
            
            if ( isset($_GET['auth']) && $_GET['auth']=='tr'){ require_once('apis/trOAuth.php'); $consumer_key = $options['trConsKey']; $consumer_secret = $options['trConsSec'];
              $callback_url = site_url()."/wp-admin/options-general.php?page=NextScripts_SNAP.php&auth=tra";
              $tum_oauth = new TumblrOAuth($consumer_key, $consumer_secret);
              $request_token = $tum_oauth->getRequestToken($callback_url); //echo "####"; prr($request_token);
              $options['trOAuthToken'] = $request_token['oauth_token'];
              $options['trOAuthTokenSecret'] = $request_token['oauth_token_secret'];
              switch ($tum_oauth->http_code) { case 200: $url = $tum_oauth->getAuthorizeURL($options['trOAuthToken']); update_option($this->dbOptionsName, $options);// prr($url);
                echo '<script type="text/javascript">window.location = "'.$url.'"</script>'; break; 
                default: echo 'Could not connect to Tumblr. Refresh the page or try again later.'; die();
              }
              die();
            }
            if ( isset($_GET['auth']) && $_GET['auth']=='tra'){ require_once('apis/trOAuth.php'); $consumer_key = $options['trConsKey']; $consumer_secret = $options['trConsSec'];
              $tum_oauth = new TumblrOAuth($consumer_key, $consumer_secret, $options['trOAuthToken'], $options['trOAuthTokenSecret']);
              $options['trAccessTocken'] = $tum_oauth->getAccessToken($_REQUEST['oauth_verifier']); // prr($_GET);  prr($_REQUEST);   prr($options['trAccessTocken']);         
              $tum_oauth = new TumblrOAuth($consumer_key, $consumer_secret, $options['trAccessTocken']['oauth_token'], $options['trAccessTocken']['oauth_token_secret']); update_option($this->dbOptionsName, $options);
              $userinfo = $tum_oauth->get('http://api.tumblr.com/v2/user/info');// prr($userinfo); die();
              if (is_array($userinfo->response->user->blogs)) {
                foreach ($userinfo->response->user->blogs as $blog){
                  if (stripos($blog->url, $options['trPgID'])!==false) {  echo '<script type="text/javascript">window.location = "'.site_url().'/wp-admin/options-general.php?page=NextScripts_SNAP.php"</script>'; break;  die();}
                } prr($userinfo);
                die("<span style='color:red;'>ERROR: Authorized USER don't have access to the specified blog: <span style='color:darkred; font-weight: bold;'>".$options['trPgID']."</span></span>");
              }
            }
            if ( isset($_GET['auth']) && $_GET['auth']=='trax'){ require_once('apis/trOAuth.php'); $consumer_key = $options['trConsKey']; $consumer_secret = $options['trConsSec'];
              $tum_oauth = new TumblrOAuth($consumer_key, $consumer_secret, $options['trAccessTocken']['oauth_token'], $options['trAccessTocken']['oauth_token_secret']);
              $userinfo = $tum_oauth->get('http://api.tumblr.com/v2/user/info'); prr($userinfo); echo $options['trPgID'];
              $trURL = trim(str_ireplace('http://', '', $options['trURL'])); if (substr($trURL,-1)=='/') $trURL = substr($trURL,0,-1); 
              $postinfo = $tum_oauth->post("http://api.tumblr.com/v2/blog/".$trURL."/post", array('type'=>'text', 'body'=>'This is a test post')); prr($postinfo); 
            }
              
              
            ?>
              
              <?php if($options['trConsSec']!='') { ?>
            <?php wp_nonce_field( 'rePostToTR', 'rePostToTR_wpnonce' ); ?>
            <br/><br/><b>Test your settings:</b>&nbsp;&nbsp;&nbsp; <a href="#" class="NXSButton" onclick="testPost('TR'); return false;">Submit Test Post to Tumblr</a>  <br/><br/>
            <?php }?>
            <div class="submit"><input type="submit" class="button-primary" name="update_NS_SNAutoPoster_settings" value="<?php _e('Update Settings', 'NS_SNAutoPoster') ?>" /></div>  
            </div>
            
            <br/><hr/>
            <!-- ##################### OTHER #####################-->
            <h3 style="font-size: 17px;">Other Settings</h3> 
            
            <p><div style="width:100%;"><strong style="font-size: 14px;">Categories to Include/Exclude:</strong> 
            <p style="font-size: 11px; margin: 0px;">Publish posts only from specific categories. List IDs like: 3,4,5 or exclude some from specific categories from publishing. List IDs like: -3,-4,-5</p>
            
            </div><input name="apCats" style="width: 30%;" value="<?php if (isset($options['apCats'])) _e(apply_filters('format_to_edit',$options['apCats']), 'NS_SNAutoPoster') ?>" /></p>
            
            
            
            <h3 style="font-size: 14px; margin-bottom: 2px;">"Open Graph" Tags</h3> 
             <span style="font-size: 11px; margin-left: 1px;">"Open Graph" tags are used for generating title, description and preview image for your Facebook and Google+ posts. This is quite simple implementation of "Open Graph" Tags. This option will only add tags needed for "Auto Posting". If you need something more serious uncheck this and use other specialized plugins. </span>
            <p style="margin: 0px;margin-left: 5px;"><input value="1" id="nsOpenGraph" name="nsOpenGraph"  type="checkbox" <?php if ((int)$options['nsOpenGraph'] == 1) echo "checked"; ?> /> 
              <strong>Add Open Graph Tags</strong>                                 
                         
            </p>
            
            <p><div style="width:100%;">            
            
            </div>
            <strong style="font-size: 11px; margin: 10px;">Default Image URL for og:image tag:</strong> 
            <input name="ogImgDef" style="width: 30%;" value="<?php if (isset($options['ogImgDef'])) _e(apply_filters('format_to_edit',$options['ogImgDef']), 'NS_SNAutoPoster') ?>" /></p>
             
            
           
        <div class="submit"><input type="submit" class="button-primary" name="update_NS_SNAutoPoster_settings" value="<?php _e('Update Settings', 'NS_SNAutoPoster') ?>" /></div>
        </form>
        </div>
        <?php
        }
        //## END OF showSNAutoPosterOptionsPage()
        
        function NS_SNAP_SavePostMetaTags($id) { if (isset($_POST["SNAPEdit"])) $nspost_edit = $_POST["SNAPEdit"]; 
            if (isset($nspost_edit) && !empty($nspost_edit)) { 
                
                $SNAP_AttachTR = $_POST["SNAP_AttachTR"];  $SNAP_AttachPN = $_POST["SNAP_AttachPN"];  
                $SNAP_FormatGP = $_POST["SNAP_FormatGP"];  $SNAP_FormatFB = $_POST["SNAP_FormatFB"];  $SNAP_FormatTW = $_POST["SNAP_FormatTW"]; // prr($_POST);
                $SNAP_FormatBG = $_POST["SNAP_FormatBG"];  $SNAP_FormatTBG = $_POST["SNAP_FormatTBG"];                
                $SNAP_FormatTR = $_POST["SNAP_FormatTR"];  $SNAP_FormatTTR = $_POST["SNAP_FormatTTR"];                
                
                if (isset($SNAP_AttachTR) && !empty($SNAP_AttachTR)) update_post_meta($id, 'SNAP_AttachTR', $SNAP_AttachTR);                
                if (isset($SNAP_AttachPN) && !empty($SNAP_AttachPN)) update_post_meta($id, 'SNAP_AttachPN', $SNAP_AttachPN);
                
                if (isset($SNAP_FormatGP) && !empty($SNAP_FormatGP)) update_post_meta($id, 'SNAP_FormatGP', $SNAP_FormatGP);                
                if (isset($SNAP_FormatFB) && !empty($SNAP_FormatFB)) update_post_meta($id, 'SNAP_FormatFB', $SNAP_FormatFB);
                if (isset($SNAP_FormatTW) && !empty($SNAP_FormatTW)) update_post_meta($id, 'SNAP_FormatTW', $SNAP_FormatTW); 
                if (isset($SNAP_FormatTR) && !empty($SNAP_FormatTR)) update_post_meta($id, 'SNAP_FormatTR', $SNAP_FormatTR); 
                if (isset($SNAP_FormatTTR) && !empty($SNAP_FormatTTR)) update_post_meta($id, 'SNAP_FormatTTR', $SNAP_FormatTTR); 
                if (isset($SNAP_FormatBG) && !empty($SNAP_FormatBG)) update_post_meta($id, 'SNAP_FormatBG', $SNAP_FormatBG); 
                if (isset($SNAP_FormatTBG) && !empty($SNAP_FormatTBG)) update_post_meta($id, 'SNAP_FormatTBG', $SNAP_FormatTBG); 
                
            }
        }
        function NS_SNAP_AddPostMetaTags() { global $post; $post_id = $post; if (is_object($post_id))  $post_id = $post_id->ID; $options = get_option($this->dbOptionsName);    
            $doGP = $options['doGP'];   $doFB = $options['doFB'];   $doTW = $options['doTW'];     $doTR = $options['doTR'];    $doPN = $options['doPN'];    $doBG = $options['doBG'];    
            $isAvailGP =  $options['gpUName']!='' && $options['gpPass']!='';
            $isAvailPN =  $options['pnUName']!='' && $options['pnPass']!='';
            $isAvailBG =  $options['bgUName']!='' && $options['bgPass']!='';
            $isAvailFB =  $options['fbURL']!='' && $options['fbAppID']!='' && $options['fbAppSec']!='';
            $isAvailTW =  $options['twURL']!='' && $options['twConsKey']!='' && $options['twConsSec']!='' && $options['twAccToken']!='';            
            
            $isAvailTR =  isset($options['trAccessTocken']) && isset($options['trAccessTocken']['oauth_token_secret']) && $options['trAccessTocken']['oauth_token_secret']!=='';   
                     
            $t = get_post_meta($post_id, 'SNAP_AttachGP', true);  $isAttachGP = $t!=''?$t:$options['gpAttch'];
            $t = get_post_meta($post_id, 'SNAP_AttachFB', true);  $isAttachFB = $t!=''?$t:$options['fbAttch'];            
            $t = get_post_meta($post_id, 'SNAP_FormatGP', true);  $gpMsgFormat = $t!=''?$t:$options['gpMsgFormat'];
            $t = get_post_meta($post_id, 'SNAP_FormatPN', true);  $pnMsgFormat = $t!=''?$t:$options['pnMsgFormat'];
            $t = get_post_meta($post_id, 'SNAP_FormatBG', true);  $bgMsgFormat = $t!=''?$t:$options['bgMsgFormat'];
            $t = get_post_meta($post_id, 'SNAP_FormatTBG', true); $bgMsgTFormat = $t!=''?$t:$options['bgMsgTFormat'];
            $t = get_post_meta($post_id, 'SNAP_FormatFB', true);  $fbMsgFormat = $t!=''?$t:$options['fbMsgFormat'];
            $t = get_post_meta($post_id, 'SNAP_FormatTW', true);  $twMsgFormat = $t!=''?$t:$options['twMsgFormat'];
            $t = get_post_meta($post_id, 'SNAP_FormatTR', true);  $trMsgFormat = $t!=''?$t:$options['trMsgFormat']; $trMsgFormat = stripcslashes(str_replace('"',"'",$trMsgFormat));
            $t = get_post_meta($post_id, 'SNAP_FormatTTR', true); $trMsgTFormat = $t!=''?$t:$options['trMsgTFormat'];
            ?>
              <div id="postftfp" class="postbox"><h3><?php _e('NextScripts: Social Networks Auto Poster - Post Options', 'NS_SPAP') ?></h3>
              <div class="inside"><div id="postftfp">
              <script type="text/javascript"> if (typeof jQuery == 'undefined') {var script = document.createElement('script'); script.type = "text/javascript"; 
                    script.src = "http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"; document.getElementsByTagName('head')[0].appendChild(script);
              }</script>
            <script type="text/javascript">function doShowHideAltFormatX(){if (jQuery('#SNAP').is(':checked')) {jQuery('#altFormat1').hide(); jQuery('#altFormat2').hide();} else { jQuery('#altFormat1').show(); jQuery('#altFormat2').show();}}</script>
            
            <input value="SNAPEdit" type="hidden" name="SNAPEdit" />
            <table style="margin-bottom:40px" border="0">
                <!-- G+ -->
                <tr><th style="text-align:left;" colspan="2">Google+ AutoPoster Options</th> <td><?php //## Only show RePost button if the post is "published"
                    if ($post->post_status == "publish" && $isAvailGP) { ?><input style="float: right;" type="button" class="button" name="rePostToGP_repostButton" id="rePostToGP_button" value="<?php _e('Repost to Google+', 're-post') ?>" />
                    <?php wp_nonce_field( 'rePostToGP', 'rePostToGP_wpnonce' ); } ?>
                </td></tr>
                
                
                <?php if (!$isAvailGP) { ?><tr><th scope="row" style="text-align:right; width:150px; padding-top: 5px; padding-right:10px;"></th> <td><b>Setup your Google+ Account to AutoPost to Google+</b>
                <?php } elseif ($post->post_status != "publish") { ?> 
                
                <tr><th scope="row" style="text-align:right; width:150px; padding-top: 5px; padding-right:10px;"><input value="1" type="checkbox" name="SNAPincludeGP" <?php if ((int)$doGP == 1) echo "checked"; ?> /></th>
                <td><b><?php _e('Publish this Post to Google+', 'NS_SPAP'); ?></b></td>
               </tr>
                <tr><th scope="row" style="text-align:right; width:150px; vertical-align:top; padding-top: 5px; padding-right:10px;">
                <input value="1"  id="SNAP_AttachGP" onchange="doShowHideAltFormatX();" type="checkbox" name="SNAP_AttachGP"  <?php if ((int)$isAttachGP == 1) echo "checked"; ?> /> </th><td><strong>Publish Post to Google+ as Attachement</strong></td></tr>
                <tr id="altFormat1" style=""><th scope="row" style="text-align:right; width:80px; padding-right:10px;"><?php _e('Format:', 'NS_SPAP') ?></th>
                <td><input value="<?php echo $gpMsgFormat ?>" type="text" name="SNAP_FormatGP" size="60px"/></td></tr>
                
                <tr id="altFormat2" style=""><th scope="row" style="text-align:right; width:150px; vertical-align:top; padding-top: 5px; padding-right:10px;">Format Options:</th>
                <td style="vertical-align:top; font-size: 9px;" colspan="2">%SITENAME% - Inserts the Your Blog/Site Name. &nbsp; %TITLE% - Inserts the Title of your post. <br/> %URL% - Inserts the URL of your post. &nbsp; %IMG% - Inserts the featured image. &nbsp;  %TEXT% - Inserts the excerpt of your post. &nbsp;  %FULLTEXT% - Inserts the body(text) of your post, %AUTHORNAME% - Inserts the author's name.</td></tr>
                <?php } ?>
                <!-- **************** PN **************** -->
                <tr><th style="text-align:left;" colspan="2">Pinterest AutoPoster Options</th> <td><?php //## Only show RePost button if the post is "published"
                    if ($post->post_status == "publish" && $isAvailPN) { ?><input style="float: right;" type="button" class="button" name="rePostToPN_repostButton" id="rePostToPN_button" value="<?php _e('Repost to Pinterest', 're-post') ?>" />
                    <?php wp_nonce_field( 'rePostToPN', 'rePostToPN_wpnonce' ); } ?>
                </td></tr>
                
                
                <?php if (!$isAvailPN) { ?><tr><th scope="row" style="text-align:right; width:150px; padding-top: 5px; padding-right:10px;"></th> <td><b>Setup your Pinterest Account to AutoPost to Pinterest</b>
                <?php } elseif ($post->post_status != "publish") { ?> 
                
                <tr><th scope="row" style="text-align:right; width:150px; padding-top: 5px; padding-right:10px;"><input value="1" type="checkbox" name="SNAPincludePN" <?php if ((int)$doPN == 1) echo "checked"; ?> /></th>
                <td><b><?php _e('Publish this Post to Pinterest', 'NS_SPAP'); ?></b></td>
                </tr> 
                
                <tr><th scope="row" style="text-align:right; width:150px; padding-top: 5px; padding-right:10px;">Select Board</th>
                <td><select name="apPNBoard" id="apPNBoard">
            <?php if ($options['pnBoardsList']!=''){ $gPNBoards = $options['pnBoardsList']; if ($options['pnBoard']!='') $gPNBoards = str_replace($options['pnBoard'].'"', $options['pnBoard'].'" selected="selected"', $gPNBoards);  echo $gPNBoards;} else { ?>
              <option value="0">None(Click above to retreive your boards)</option>
            <?php } ?>
            </select></td>
                </tr> 
                              
                <tr id="altFormat1" style=""><th scope="row" style="text-align:right; width:80px; padding-right:10px;"><?php _e('Format:', 'NS_SPAP') ?></th>
                <td><input value="<?php echo $pnMsgFormat ?>" type="text" name="SNAP_FormatPN" size="60px"/></td></tr>
                
                <tr id="altFormat2" style=""><th scope="row" style="text-align:right; width:150px; vertical-align:top; padding-top: 5px; padding-right:10px;">Format Options:</th>
                <td style="vertical-align:top; font-size: 9px;" colspan="2">%SITENAME% - Inserts the Your Blog/Site Name. &nbsp; %TITLE% - Inserts the Title of your post. <br/> %URL% - Inserts the URL of your post. &nbsp; %IMG% - Inserts the featured image. &nbsp;  %TEXT% - Inserts the excerpt of your post. &nbsp;  %FULLTEXT% - Inserts the body(text) of your post, %AUTHORNAME% - Inserts the author's name.</td></tr>
                <?php } ?>
                <!-- **************** FB **************** -->
                <tr><th style="text-align:left;" colspan="2">FaceBook AutoPoster Options</th><td><?php //## Only show RePost button if the post is "published"
                    if ($post->post_status == "publish" && $isAvailFB) { ?><input style="float: right;" type="button" class="button" name="rePostToFB_repostButton" id="rePostToFB_button" value="<?php _e('Repost to FaceBook', 're-post') ?>" />
                    <?php wp_nonce_field( 'rePostToFB', 'rePostToFB_wpnonce' ); } ?>
                </td></tr>
                <?php if (!$isAvailFB) { ?><tr><th scope="row" style="text-align:right; width:150px; padding-top: 5px; padding-right:10px;"></th> <td><b>Setup and Authorize your FaceBook Account to AutoPost to FaceBook</b>
                <?php } elseif ($post->post_status != "publish") {?> 
                
                <tr><th scope="row" style="text-align:right; width:150px; padding-top: 5px; padding-right:10px;"><input value="1" type="checkbox" name="SNAPincludeFB" <?php if ((int)$doFB == 1) echo "checked"; ?> /></th>
                <td><b><?php _e('Publish this Post to FaceBook', 'NS_SPAP'); ?></b></td>
                </tr>
                <tr><th scope="row" style="text-align:right; width:150px; vertical-align:top; padding-top: 5px; padding-right:10px;">
                <input value="1"  id="SNAP_AttachFB" onchange="doShowHideAltFormatX();" type="checkbox" name="SNAP_AttachFB"  <?php if ((int)$isAttachFB == 1) echo "checked"; ?> /> </th><td><strong>Publish Post to FaceBook as Attachement</strong></td>                </tr>
                <tr id="altFormat1" style=""><th scope="row" style="text-align:right; width:80px; padding-right:10px;"><?php _e('Format:', 'NS_SPAP') ?></th>
                <td><input value="<?php echo $fbMsgFormat ?>" type="text" name="SNAP_FormatFB" size="60px"/></td></tr>
                
                <tr id="altFormat2" style=""><th scope="row" style="text-align:right; width:150px; vertical-align:top; padding-top: 5px; padding-right:10px;">Format Options:</th>
                <td style="vertical-align:top; font-size: 9px;" colspan="2">%SITENAME% - Inserts the Your Blog/Site Name. &nbsp; %TITLE% - Inserts the Title of your post. <br/> %URL% - Inserts the URL of your post. &nbsp; %IMG% - Inserts the featured image. &nbsp;  %TEXT% - Inserts the excerpt of your post. &nbsp;  %FULLTEXT% - Inserts the body(text) of your post, %AUTHORNAME% - Inserts the author's name.</td></tr>
                <?php } ?>
                <!-- TW -->
                <tr><th style="text-align:left;" colspan="2">Twitter AutoPoster Options</th><td><?php //## Only show RePost button if the post is "published"
                    if ($post->post_status == "publish" && $isAvailTW) { ?><input style="float: right;" type="button" class="button" name="rePostToTW_repostButton" id="rePostToTW_button" value="<?php _e('Repost to Twitter', 're-post') ?>" />
                    <?php wp_nonce_field( 'rePostToTW', 'rePostToTW_wpnonce' ); } ?>
                </td></tr>
                <?php if (!$isAvailTW) { ?><tr><th scope="row" style="text-align:right; width:150px; padding-top: 5px; padding-right:10px;"></th> <td><b>Setup your Twitter Account to AutoPost to Twitter</b>
                <?php }elseif ($post->post_status != "publish") { ?> 
                
                <tr><th scope="row" style="text-align:right; width:150px; padding-top: 5px; padding-right:10px;"><input value="1" type="checkbox" name="SNAPincludeTW" <?php if ((int)$doTW == 1) echo "checked"; ?> /></th>
                <td><b><?php _e('Publish this Post to Twitter', 'NS_SPAP'); ?></b></td>
                </tr>                
                <tr id="altFormat1" style=""><th scope="row" style="text-align:right; width:80px; padding-right:10px;"><?php _e('Format:', 'NS_SPAP') ?></th>
                <td><input value="<?php echo $twMsgFormat ?>" type="text" name="SNAP_FormatTW" size="60px"/></td></tr>
                
                <tr id="altFormat2" style=""><th scope="row" style="text-align:right; width:150px; vertical-align:top; padding-top: 5px; padding-right:10px;">Format Options:</th>
                <td style="vertical-align:top; font-size: 9px;" colspan="2">%SITENAME% - Inserts the Your Blog/Site Name. &nbsp; %TITLE% - Inserts the Title of your post. <br/> %URL% - Inserts the URL of your post. &nbsp; %SURL% - Inserts the <b>Shortened URL</b> of your post. &nbsp; %TEXT% - Inserts the excerpt of your post. &nbsp;  %FULLTEXT% - Inserts the body(text) of your post, %AUTHORNAME% - Inserts the author's name.</td></tr>
                <?php } ?>
                <!-- **************** BG **************** -->
                <tr><th style="text-align:left;" colspan="2">Blogger AutoPoster Options</th> <td><?php //## Only show RePost button if the post is "published"
                    if ($post->post_status == "publish" && $isAvailBG) { ?><input style="float: right;" type="button" class="button" name="rePostToBG_repostButton" id="rePostToBG_button" value="<?php _e('Repost to Blogger', 're-post') ?>" />
                    <?php wp_nonce_field( 'rePostToBG', 'rePostToBG_wpnonce' ); } ?>
                </td></tr>
                
                
                <?php if (!$isAvailBG) { ?><tr><th scope="row" style="text-align:right; width:150px; padding-top: 5px; padding-right:10px;"></th> <td><b>Setup your Blogger Account to AutoPost to Blogger</b>
                <?php } elseif ($post->post_status != "publish") { ?> 
                
                <tr><th scope="row" style="text-align:right; width:150px; padding-top: 5px; padding-right:10px;"><input value="1" type="checkbox" name="SNAPincludeBG" <?php if ((int)$doBG == 1) echo "checked"; ?> /></th>
                <td><b><?php _e('Publish this Post to Blogger', 'NS_SPAP'); ?></b></td>
                </tr> 
                
                 <tr id="altFormat1" style=""><th scope="row" style="text-align:right; width:80px; padding-right:10px;"><?php _e('Title Format:', 'NS_SPAP') ?></th>
                <td><input value="<?php echo $bgMsgTFormat ?>" type="text" name="SNAP_FormatTBG" size="60px"/></td></tr>
                
                <tr id="altFormat2" style=""><th scope="row" style="text-align:right; width:150px; vertical-align:top; padding-top: 5px; padding-right:10px;">Title Format Options:</th>
                <td style="vertical-align:top; font-size: 9px;" colspan="2">%SITENAME% - Inserts the Your Blog/Site Name. &nbsp; %TITLE% - Inserts the Title of your post. <br/> %URL% - Inserts the URL of your post. &nbsp; %SURL% - Inserts the <b>Shortened URL</b> of your post. &nbsp; %IMG% - Inserts the featured image. &nbsp;  %TEXT% - Inserts the excerpt of your post. &nbsp;  %FULLTEXT% - Inserts the body(text) of your post, %AUTHORNAME% - Inserts the author's name.</td></tr>
                
                
                <tr id="altFormat1" style=""><th scope="row" style="text-align:right; width:80px; padding-right:10px;"><?php _e('Format:', 'NS_SPAP') ?></th>
                <td><input value="<?php echo $bgMsgFormat ?>" type="text" name="SNAP_FormatBG" size="60px"/></td></tr>
                
                <tr id="altFormat2" style=""><th scope="row" style="text-align:right; width:150px; vertical-align:top; padding-top: 5px; padding-right:10px;">Format Options:</th>
                <td style="vertical-align:top; font-size: 9px;" colspan="2">%SITENAME% - Inserts the Your Blog/Site Name. &nbsp; %TITLE% - Inserts the Title of your post. <br/> %URL% - Inserts the URL of your post. &nbsp; %IMG% - Inserts the featured image. &nbsp;  %TEXT% - Inserts the excerpt of your post. &nbsp;  %FULLTEXT% - Inserts the body(text) of your post, %AUTHORNAME% - Inserts the author's name.</td></tr>
                <?php } ?>
                <!-- #### TR #### -->
                <tr><th style="text-align:left;" colspan="2">Tumblr AutoPoster Options</th><td><?php //## Only show RePost button if the post is "published"
                    if ($post->post_status == "publish" && $isAvailTR) { ?><input style="float: right;" type="button" class="button" name="rePostToTR_repostButton" id="rePostToTR_button" value="<?php _e('Repost to Tumblr', 're-post') ?>" />
                    <?php wp_nonce_field( 'rePostToTR', 'rePostToTR_wpnonce' ); } ?>
                </td></tr>
                <?php if (!$isAvailTR) { ?><tr><th scope="row" style="text-align:right; width:150px; padding-top: 5px; padding-right:10px;"></th> <td><b>Setup and authorize your Tumblr Account to AutoPost to Tumblr</b>
                <?php }elseif ($post->post_status != "publish") { ?> 
                
                <tr><th scope="row" style="text-align:right; width:150px; padding-top: 5px; padding-right:10px;"><input value="1" type="checkbox" name="SNAPincludeTR" <?php if ((int)$doTR == 1) echo "checked"; ?> /></th>
                <td><b><?php _e('Publish this Post to Tumblr', 'NS_SPAP'); ?></b></td>
                </tr>       
                         
                <tr id="altFormat1" style=""><th scope="row" style="text-align:right; width:80px; padding-right:10px;"><?php _e('Title Format:', 'NS_SPAP') ?></th>
                <td><input value="<?php echo $trMsgTFormat ?>" type="text" name="SNAP_FormatTTR" size="60px"/></td></tr>
                
                <tr id="altFormat2" style=""><th scope="row" style="text-align:right; width:150px; vertical-align:top; padding-top: 5px; padding-right:10px;">Title Format Options:</th>
                <td style="vertical-align:top; font-size: 9px;" colspan="2">%SITENAME% - Inserts the Your Blog/Site Name. &nbsp; %TITLE% - Inserts the Title of your post. <br/> %URL% - Inserts the URL of your post. &nbsp; %SURL% - Inserts the <b>Shortened URL</b> of your post. &nbsp; %IMG% - Inserts the featured image. &nbsp;  %TEXT% - Inserts the excerpt of your post. &nbsp;  %FULLTEXT% - Inserts the body(text) of your post, %AUTHORNAME% - Inserts the author's name.</td></tr>
                
                <tr id="altFormat1" style=""><th scope="row" style="text-align:right; width:80px; padding-right:10px;"><?php _e('Text Format:', 'NS_SPAP') ?></th>
                <td><input value="<?php echo $trMsgFormat ?>" type="text" name="SNAP_FormatTR" size="60px"/></td></tr>
                
                <tr id="altFormat2" style=""><th scope="row" style="text-align:right; width:150px; vertical-align:top; padding-top: 5px; padding-right:10px;">Text Format Options:</th>
                <td style="vertical-align:top; font-size: 9px;" colspan="2">%SITENAME% - Inserts the Your Blog/Site Name. &nbsp; %TITLE% - Inserts the Title of your post. <br/> %URL% - Inserts the URL of your post. &nbsp; %SURL% - Inserts the <b>Shortened URL</b> of your post. &nbsp; %IMG% - Inserts the featured image. &nbsp;  %TEXT% - Inserts the excerpt of your post. &nbsp;  %FULLTEXT% - Inserts the body(text) of your post, %AUTHORNAME% - Inserts the author's name.</td></tr>
                <?php } ?>
            </table>
            </div></div></div>        <?php    
        }
    }
}

//## Instantiate the class
if (class_exists("NS_SNAutoPoster")) {$plgn_NS_SNAutoPoster = new NS_SNAutoPoster();}
//## Initialize the admin panel if the plugin has been activated
if (!function_exists("NS_SNAutoPoster_ap")) {
  function NS_SNAutoPoster_ap() { global $plgn_NS_SNAutoPoster;  if (!isset($plgn_NS_SNAutoPoster)) return;        
    if (function_exists('add_options_page')) {
      add_options_page('Social Networks Auto Poster', 'Social Networks Auto Poster', 'manage_options', basename(__FILE__), array(&$plgn_NS_SNAutoPoster, 'showSNAutoPosterOptionsPage'));
     // add_submenu_page('users.php', 'Social Networks AutoPoster', 'Social Networks AutoPoster', 2, basename(__FILE__), array(&$plgn_NS_SNAutoPoster, 'showSNAutoPosterUsersOptionsPage'));
    }            
  }    
}
//## AJAX to Post to Google+
if (!function_exists("jsPostToSNAP")) {
  function jsPostToSNAP() { ?>
    <script type="text/javascript" >
    jQuery(document).ready(function($) {                
        $('input#rePostToGP_button').click(function() { var data = { action: 'rePostToGP', id: $('input#post_ID').val(), _wpnonce: $('input#rePostToGP_wpnonce').val()}; callAjSNAP(data, 'Google+'); });
        $('input#rePostToFB_button').click(function() { var data = { action: 'rePostToFB', id: $('input#post_ID').val(), _wpnonce: $('input#rePostToFB_wpnonce').val()}; callAjSNAP(data, 'FaceBook');});
        $('input#rePostToTW_button').click(function() { var data = { action: 'rePostToTW', id: $('input#post_ID').val(), _wpnonce: $('input#rePostToTW_wpnonce').val()}; callAjSNAP(data, 'Twitter'); });
        $('input#rePostToTR_button').click(function() { var data = { action: 'rePostToTR', id: $('input#post_ID').val(), _wpnonce: $('input#rePostToTR_wpnonce').val()}; callAjSNAP(data, 'Tumblr'); });
        $('input#rePostToPN_button').click(function() { var data = { action: 'rePostToPN', id: $('input#post_ID').val(), _wpnonce: $('input#rePostToPN_wpnonce').val()}; callAjSNAP(data, 'Pinterest'); });
        $('input#rePostToBG_button').click(function() { var data = { action: 'rePostToBG', id: $('input#post_ID').val(), _wpnonce: $('input#rePostToBG_wpnonce').val()}; callAjSNAP(data, 'Blogger'); });

       function callAjSNAP(data, label) {
            var style = "position: fixed; display: none; z-index: 1000; top: 50%; left: 50%; background-color: #E8E8E8; border: 1px solid #555; padding: 15px; width: 350px; min-height: 80px; margin-left: -175px; margin-top: -40px; text-align: center; vertical-align: middle;";
            $('body').append("<div id='test_results' style='" + style + "'></div>");
            $('#test_results').html("<p>Sending update to "+label+"</p>" + "<p><img src='http://gtln.us/img/misc/ajax-loader-med.gif' /></p>");
            $('#test_results').show();            
            jQuery.post(ajaxurl, data, function(response) { if (response=='') response = 'Message Posted';
                $('#test_results').html('<p> ' + response + '</p>' +'<input type="button" class="button" name="results_ok_button" id="results_ok_button" value="OK" />');
                $('#results_ok_button').click(remove_results);
            });            
        }        
        function remove_results() { jQuery("#results_ok_button").unbind("click");jQuery("#test_results").remove();
            if (typeof document.body.style.maxHeight == "undefined") { jQuery("body","html").css({height: "auto", width: "auto"}); jQuery("html").css("overflow","");}
            document.onkeydown = ""; document.onkeyup = "";  return false;
        }
    });
    </script>    
    <?php
  }
}

//## Repost to Google+
if (!function_exists("rePostToGP_ajax")) {
  function rePostToGP_ajax() { check_ajax_referer('rePostToGP');  $id = $_POST['id'];  $result = nsPublishTo($id, 'GP', true);   
    if ($result == 200) die("Successfully sent your post to Google+."); else die($result);
  }
}                                    
if (!function_exists("rePostToFB_ajax")) {
  function rePostToFB_ajax() { check_ajax_referer('rePostToFB');  $id = $_POST['id'];  $result = nsPublishTo($id, 'FB', true);   
    if ($result == 200) die("Successfully sent your post to FaceBook."); else die($result);
  }
}                                    
if (!function_exists("rePostToTW_ajax")) {
  function rePostToTW_ajax() { check_ajax_referer('rePostToTW');  $id = $_POST['id'];  $result = nsPublishTo($id, 'TW', true);   
    if ($result == 200) die("Successfully sent your post to Twitter."); else die($result);
  }
}         
if (!function_exists("rePostToTR_ajax")) {
  function rePostToTR_ajax() { check_ajax_referer('rePostToTR'); $postID = $_POST['id']; $options = get_option('NS_SNAutoPoster'); 
    $twpo =  get_post_meta($postID, 'snapTR', true); $twpo =  maybe_unserialize($twpo); if (is_array($twpo)) $options['trMsgFormat'] = $twpo['SNAPformat']; 
    $result = doPublishToTR($postID, $options);  if ($result == 200) die("Successfully sent your post to Tumblr."); else die($result);
  }
}                 
if (!function_exists("rePostToPN_ajax")) {
  function rePostToPN_ajax() { check_ajax_referer('rePostToPN'); $postID = $_POST['id']; $options = get_option('NS_SNAutoPoster'); 
    $twpo =  get_post_meta($postID, 'snapPN', true); $twpo =  maybe_unserialize($twpo); if (is_array($twpo)) $options['pnMsgFormat'] = $twpo['SNAPformat']; 
    $result = doPublishToPN($postID, $options);  if ($result == 200) die("Successfully sent your post to Pinterest."); else die($result);
  }
} 
if (!function_exists("rePostToBG_ajax")) {
  function rePostToBG_ajax() { check_ajax_referer('rePostToBG'); $postID = $_POST['id']; $options = get_option('NS_SNAutoPoster'); 
    $twpo =  get_post_meta($postID, 'snapBG', true); $twpo =  maybe_unserialize($twpo); if (is_array($twpo)) $options['bgMsgFormat'] = $twpo['SNAPformat']; 
    $result = doPublishToBG($postID, $options);  if ($result == 200) die("Successfully sent your post to Pinterest."); else die($result);
  }
} 

if (!function_exists("nsGetBoards_ajax")) { 
  function nsGetBoards_ajax() { check_ajax_referer('getBoards');  $options = get_option('NS_SNAutoPoster');// prr($options); die();
   $loginError = doConnectToPinterest($_POST['u'], $_POST['p']);  if ($loginError!==false) {echo $loginError; return "BAD USER/PASS";} 
   $gPNBoards = doGetBoardsFromPinterest(); $options['pnBoardsList'] = $gPNBoards;  update_option('NS_SNAutoPoster', $options); echo $gPNBoards; die();
  }
}               


function nsTrnc($string, $limit, $break=" ", $pad=" ...") { if(strlen($string) <= $limit) return $string; $string = substr($string, 0, $limit-strlen($pad)); 
  $brLoc = strripos($string, $break);  if ($brLoc===false) return $string.$pad; else return substr($string, 0, $brLoc).$pad; 
}                 

function get_post_meta_all($post_id){ global $wpdb; $data = array(); $wpdb->query("SELECT `meta_key`, `meta_value` FROM $wpdb->postmeta WHERE `post_id` = $post_id");
    foreach($wpdb->last_result as $k => $v){ $data[$v->meta_key] =   $v->meta_value; }; return $data;
}          

if (!function_exists('nsBloggerGetAuth')){ function nsBloggerGetAuth($email, $pass) {
    $ch = curl_init("https://www.google.com/accounts/ClientLogin?Email=$email&Passwd=$pass&service=blogger&accountType=GOOGLE");    
    $headers = array(); $headers[] = 'Accept: text/html, application/xhtml+xml, */*'; 
    $headers[] = 'Connection: Keep-Alive'; $headers[] = 'Accept-Language: en-us'; 
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1; WOW64; Trident/4.0)");
    curl_setopt($ch, CURLOPT_HEADER,0); curl_setopt($ch, CURLOPT_RETURNTRANSFER ,1);
    $result = curl_exec($ch); $resultArray = curl_getinfo($ch); 
    curl_close($ch); $arr = explode("=",$result); $token = $arr[3];  return $token;
}}
if (!function_exists('nsBloggerNewPost')){ function nsBloggerNewPost($auth, $blogID, $title, $text) {    
    $text = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', "", $text);    $text = preg_replace('/<!--(.*)-->/Uis', "", $text);  // prr($text); 
    if (class_exists('DOMDocument')) {$doc = new DOMDocument();  @$doc->loadHTML($text);  $text = $doc->saveHTML(); $text = CutFromTo($text, '<body>', '</body>');
      $text = preg_replace('/<br(.*?)\/?>/','<br$1/>',$text);   $text = preg_replace('/<img(.*?)\/?>/','<img$1/>',$text);
      require_once ('apis/htmlNumTable.php');  $text = strtr($text, $HTML401NamedToNumeric);
    }    prr($text); 
    $postText = '<entry xmlns="http://www.w3.org/2005/Atom"><title type="text">'.htmlentities($title).'</title><content type="xhtml">'.$text.'</content></entry>';
    $len = strlen($entry); $ch = curl_init("https://www.blogger.com/feeds/$blogID/posts/default"); 
    $headers = array("Content-type: application/atom+xml", "Content-Length: ".strlen($postText), "Authorization: GoogleLogin auth=".$auth, $postText);
    curl_setopt($ch, CURLOPT_UNRESTRICTED_AUTH, 1);  curl_setopt($ch, CURLOPT_POST, true);  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1; WOW64; Trident/4.0)");
    curl_setopt($ch, CURLOPT_HEADER,0); curl_setopt($ch, CURLOPT_RETURNTRANSFER ,1); curl_setopt($ch, CURLINFO_HEADER_OUT, true); 
    $result = curl_exec($ch); curl_close($ch); if (stripos($result,'tag:blogger.com')!==false) return 'OK'; else { prr($result); return false;}
}}

if (!function_exists("nsFormatMessage")) {//## Format Message
  function nsFormatMessage($msg, $postID){   global $ShownAds; $post = get_post($postID); $msg = stripcslashes($msg); if (isset($ShownAds)) $ShownAdsL = $ShownAds; // $msg = htmlspecialchars(stripcslashes($msg)); 
      if (preg_match('%URL%', $msg)) { $url = get_permalink($postID); $msg = str_ireplace("%URL%", $url, $msg);}                    
      if (preg_match('%SURL%', $msg)) { $url = get_permalink($postID);   $response  = wp_remote_get('http://gd.is/gtq/'.$url); 
        if ((is_array($response) && ($response['response']['code']=='200'))) $url = $response['body'];  $msg = str_ireplace("%SURL%", $url, $msg);
      }                    
      if (preg_match('%IMG%', $msg)) { if (function_exists("get_post_thumbnail_id") ){ $src = wp_get_attachment_image_src(get_post_thumbnail_id($postID), 'large'); $src = $src[0];} 
        if ($src=='') { $options = get_option('NS_SNAutoPoster'); $src = $options['ogImgDef'];  }  $msg = str_ireplace("%IMG%", $src, $msg); 
      }
      if (preg_match('%TITLE%', $msg)) { $title = $post->post_title; $msg = str_ireplace("%TITLE%", $title, $msg); }                    
      if (preg_match('%STITLE%', $msg)) { $title = $post->post_title;  $title = substr($title, 0, 115); $msg = str_ireplace("%STITLE%", $title, $msg); }                    
      if (preg_match('%AUTHORNAME%', $msg)) { $aun = $post->post_author;  $aun = get_the_author_meta('display_name', $aun );  $msg = str_ireplace("%AUTHORNAME%", $aun, $msg);}                    
      if (preg_match('%TEXT%', $msg)) {      
        if ($post->post_excerpt!="") $excerpt = apply_filters('the_content', $post->post_excerpt); else $excerpt= nsTrnc(strip_tags(strip_shortcodes(apply_filters('the_content', $post->post_content))), 300, " ", "...");     
        $msg = str_ireplace("%TEXT%", $excerpt, $msg);
      }     
      if (preg_match('%FULLTEXT%', $msg)) { $postContent = apply_filters('the_content', $post->post_content); $msg = str_ireplace("%FULLTEXT%", $postContent, $msg);}                    
      if (preg_match('%SITENAME%', $msg)) { $siteTitle = htmlspecialchars_decode(get_bloginfo('name'), ENT_QUOTES); $msg = str_ireplace("%SITENAME%", $siteTitle, $msg);}      
      if (isset($ShownAds)) $ShownAds = $ShownAdsL; // FIX for the quick-adsense plugin
      return nsTrnc($msg, 996, " ", "...");
  }
}
if (!function_exists("nsPublishTo")) { //## Main Function to Post 
  function nsPublishTo($postArr, $type='', $aj=false) {  $options = get_option('NS_SNAutoPoster'); 
    if(is_object($postArr)) $postID = $postArr->ID; else $postID = $postArr;  $isPost = isset($_POST["SNAPEdit"]);  
    if($postID==0) {
        if ($type=='GP') doPublishToGP($postID, $options);  if ($type=='FB') doPublishToFB($postID, $options);  if ($type=='TW') doPublishToTW($postID, $options); 
    } else { $post = get_post($postID);  $maxLen = 1000; 
     
    $args=array( 'public'   => true, '_builtin' => false);  $output = 'names';  $operator = 'and';  $post_types=get_post_types($args,$output,$operator);
     
    if ($post->post_type == 'post' || in_array($post->post_type, $post_types) ) { //prr($options);
      //## Check if need to publish it
      if (!$aj && $type!='' && (int)$options['do'.$type]!=1) return; $chCats = isset($options['apCats'])?trim($options['apCats']):''; $continue = true;
      if ($chCats!=''){ $cats = split(",", $options['apCats']);  $continue = false;
        foreach ($cats as $cat) { if (preg_match('/^-\d+/', $cat)) { $cat = preg_replace('/^-/', '', $cat);
            //## if in the exluded category, return.
            if (in_category( (int)$cat, $post )) return; else  $continue = true; 
          } else if (preg_match('/\d+/', $cat)) { if (in_category( (int)$cat, $post )) $continue = true; }
        }
      }
      
      // prr($options); echo $type; prr($_POST); die();
      
      if ($type==''){  
        if ($isPost) $doGP = $_POST['SNAPincludeGP']; else { $t = get_post_meta($postID, 'SNAPincludeGP', true); $doGP = $t!=''?$t:$options['doGP']; }
        if ($isPost) $doFB = $_POST['SNAPincludeFB']; else { $t = get_post_meta($postID, 'SNAPincludeFB', true); $doFB = $t!=''?$t:$options['doFB']; }
        if ($isPost) $doTW = $_POST['SNAPincludeTW']; else { $t = get_post_meta($postID, 'SNAPincludeTW', true); $doTW = $t!=''?$t:$options['doTW']; }    
        if ($isPost) $doTR = $_POST['SNAPincludeTR']; else { $t = get_post_meta($postID, 'SNAPincludeTR', true); $doTR = $t!=''?$t:$options['doTR']; }    
        if ($isPost) $doPN = $_POST['SNAPincludePN']; else { $t = get_post_meta($postID, 'SNAPincludePN', true); $doPN = $t!=''?$t:$options['doPN']; }    
        if ($isPost) $doBG = $_POST['SNAPincludeBG']; else { $t = get_post_meta($postID, 'SNAPincludeBG', true); $doBG = $t!=''?$t:$options['doBG']; }    
      } //var_dump($doBG); var_dump($doGP); var_dump($doFB); var_dump($doTR); echo "===".$type; //die();
      if (!$continue) return; else {
          if ($type=='TW' || ($type=='' && (int)$doTW==1)) doPublishToTW($postID, $options);
          if ($type=='GP' || ($type=='' && (int)$doGP==1)) doPublishToGP($postID, $options); 
          if ($type=='FB' || ($type=='' && (int)$doFB==1)) doPublishToFB($postID, $options);
          if ($type=='TR' || ($type=='' && (int)$doTR==1)) doPublishToTR($postID, $options);
          if ($type=='PN' || ($type=='' && (int)$doPN==1)) doPublishToPN($postID, $options);
          if ($type=='BG' || ($type=='' && (int)$doBG==1)) doPublishToBG($postID, $options);
      }
    } //die();
    }
  }
}
// Add function to pubslih to Google +
if (!function_exists("doPublishToGP")) { //## Second Function to Post to G+
  function doPublishToGP($postID, $options){ if ($postID=='0') echo "Testing ... <br/><br/>";  $isPost = isset($_POST["SNAPEdit"]);
      if ($isPost) $gpMsgFormat = $_POST['SNAP_FormatGP']; else { $t = get_post_meta($postID, 'SNAP_FormatGP', true); $gpMsgFormat = $t!=''?$t:$options['gpMsgFormat']; } 
      if ($isPost) $isAttachGP = $_POST['SNAP_AttachGP'];  else { $t = get_post_meta($postID, 'SNAP_AttachGP', true); $isAttachGP = $t!=''?$t:$options['gpAttch']; }  
      $msg = nsFormatMessage($gpMsgFormat, $postID);// prr($msg); echo $postID;
      if ($isAttachGP=='1' && function_exists("get_post_thumbnail_id") ){ $src = wp_get_attachment_image_src(get_post_thumbnail_id($postID), 'thumbnail'); $src = $src[0];}      
      $email = $options['gpUName'];  $pass = $options['gpPass'];     
      $connectID = getUqID();  $loginError = doConnectToGooglePlus($connectID, $email, $pass);  if ($loginError!==false) {echo $loginError; return "BAD USER/PASS";} 
      $url =  get_permalink($postID);  if ($isAttachGP=='1') $lnk = doGetGoogleUrlInfo($connectID, $url);  if (is_array($lnk) && $src!='') $lnk['img'] = $src;                                    
      if (!empty($options['gpPageID'])) {  $to = $options['gpPageID']; $ret = doPostToGooglePlus($connectID, $msg, $lnk, $to);} else $ret = doPostToGooglePlus($connectID, $msg, $lnk);
      if ($ret!='OK') echo $ret; else if ($postID=='0') echo 'OK - Message Posted, please see your Google+ Page';
  }
}
// Add function to pubslih to FaceBook
if (!function_exists("doPublishToFB")) { //## Second Function to Post to FB
  function doPublishToFB($postID, $options){ global $ShownAds; require_once ('apis/facebook.php'); $page_id = $options['fbPgID'];  $isPost = isset($_POST["SNAPEdit"]); if (isset($ShownAds)) $ShownAdsL = $ShownAds;
    $facebook = new NXS_Facebook(array( 'appId' => $options['fbAppID'], 'secret' => $options['fbAppSec'], 'cookie' => true ));  
    $blogTitle = htmlspecialchars_decode(get_bloginfo('name'), ENT_QUOTES); if ($blogTitle=='') $blogTitle = home_url();
    
    if ($postID=='0') {echo "Testing ... <br/><br/>"; 
    $mssg = array('access_token'  => $options['fbAppPageAuthToken'], 'message' => 'Test Post', 'name' => 'Test Post', 'caption' => 'Test Post', 'link' => home_url(),
       'description' => 'test Post', 'actions' => array(array('name' => $blogTitle, 'link' => home_url())) );  
    } else {$post = get_post($postID); 
      if ($isPost) $fbMsgFormat = $_POST['SNAP_FormatFB']; else { $t = get_post_meta($postID, 'SNAP_FormatFB', true);  $fbMsgFormat = $t!=''?$t:$options['fbMsgFormat'];}
      if ($isPost) $isAttachFB = $_POST['SNAP_AttachFB'];  else { $t = get_post_meta($postID, 'SNAP_AttachFB', true);  $isAttachFB = $t!=''?$t:$options['fbAttch'];}
      $msg = nsFormatMessage($fbMsgFormat, $postID);
      if ($isAttachFB=='1' && function_exists("get_post_thumbnail_id") ){ $src = wp_get_attachment_image_src(get_post_thumbnail_id($postID), 'medium'); $src = $src[0];} 
       $dsc = trim(apply_filters('the_content', $post->post_excerpt)); if ($dsc=='') $dsc = apply_filters('the_content', $post->post_content); $dsc = nsTrnc($dsc, 900, ' ');
      $postSubtitle = home_url(); $dsc = strip_tags($dsc);
      $mssg = array('access_token'  => $options['fbAppPageAuthToken'], 'message' => $msg, 'name' => $post->post_title, 'caption' => $postSubtitle, 'link' => get_permalink($postID),
       'description' => $dsc, 'actions' => array(array('name' => $blogTitle, 'link' => home_url())) );  
      if (trim($src)!='') $mssg['picture'] = $src;
    }  //  prr($mssg);
    if (isset($ShownAds)) $ShownAds = $ShownAdsL; // FIX for the quick-adsense plugin
    try { $ret = $facebook->api("/$page_id/feed","post", $mssg);} catch (NXS_FacebookApiException $e) { echo 'Error:',  $e->getMessage(), "\n";}    
    if ($postID=='0') { prr($ret); echo 'OK - Message Posted, please see your Facebook Page ';}
  }
}
// Add function to pubslih to Twitter
if (!function_exists("doPublishToTW")) { //## Second Function to Post to TW 
  function doPublishToTW($postID, $options){ $blogTitle = htmlspecialchars_decode(get_bloginfo('name'), ENT_QUOTES); if ($blogTitle=='') $blogTitle = home_url();  $isPost = isset($_POST["SNAPEdit"]);
      if ($postID=='0') { echo "Testing ... <br/><br/>"; $msg = 'Test Post from '.$blogTitle." - ".rand(1, 155);}
      else{
        $post = get_post($postID); //prr($post); die();
        if ($isPost) $twMsgFormat = $_POST['SNAP_FormatTW']; else { $t = get_post_meta($postID, 'SNAP_FormatTW', true); $twMsgFormat = $t!=''?$t:$options['twMsgFormat']; }    
        
        $md = get_post_meta_all($postID); prr($md);
         prr($twMsgFormat); echo"####"; prr(get_post_meta($postID, 'SNAP_FormatTW', true)); echo"^^^^^";
         
        $twMsgFormat = str_ireplace("%TITLE%", "%STITLE%", $twMsgFormat); $msg = nsFormatMessage($twMsgFormat, $postID); 
      }
      require_once ('apis/tmhOAuth.php'); require_once ('apis/tmhUtilities.php'); $msg = nsTrnc($msg, 140);  
      $tmhOAuth = new NXS_tmhOAuth(array( 'consumer_key' => $options['twConsKey'], 'consumer_secret' => $options['twConsSec'], 'user_token' => $options['twAccToken'], 'user_secret' => $options['twAccTokenSec']));
      $code = $tmhOAuth->request('POST', $tmhOAuth->url('1/statuses/update'), array('status' =>$msg));
      if ($code == 200) { if ($postID=='0') { echo 'OK - Message Posted, please see your Twitter Page'; NXS_tmhUtilities::pr(json_decode($tmhOAuth->response['response']));}} else { NXS_tmhUtilities::pr($tmhOAuth->response['response']);}      
  }
}
// Add function to pubslih to tumblr.
if (!function_exists("doPublishToTR")) { //## Second Function to Post to TR
  function doPublishToTR($postID, $options){  $blogTitle = htmlspecialchars_decode(get_bloginfo('name'), ENT_QUOTES); if ($blogTitle=='') $blogTitle = home_url(); $isPost = isset($_POST["SNAPEdit"]); 
    if ($postID=='0') { echo "Testing ... <br/><br/>"; $msg = 'Test Post from '.$blogTitle;}
    else{        
      if ($isPost) $trMsgFormat = $_POST['SNAP_FormatTR']; else { $t = get_post_meta($postID, 'SNAP_FormatTR', true); $trMsgFormat = $t!=''?$t:$options['trMsgFormat']; } 
      $msg = nsFormatMessage($trMsgFormat, $postID);        
      if ($isPost) $trMsgTFormat = $_POST['SNAP_FormatTTR']; else { $t = get_post_meta($postID, 'SNAP_FormatTTR', true); $trMsgTFormat = $t!=''?$t:$options['trMsgTFormat']; } 
      $msgT = nsFormatMessage($trMsgTFormat, $postID);  
    } 
    require_once('apis/trOAuth.php'); $consumer_key = $options['trConsKey']; $consumer_secret = $options['trConsSec'];
    $tum_oauth = new TumblrOAuth($consumer_key, $consumer_secret, $options['trAccessTocken']['oauth_token'], $options['trAccessTocken']['oauth_token_secret']); //prr($options);
    $trURL = trim(str_ireplace('http://', '', $options['trURL'])); if (substr($trURL,-1)=='/') $trURL = substr($trURL,0,-1); 
    if ($options['trInclTags']=='1'){$t = wp_get_post_tags($postID); $tggs = array(); foreach ($t as $tagA) {$tggs[] = $tagA->name;} $tags = implode(',',$tggs); }
    $postinfo = $tum_oauth->post("http://api.tumblr.com/v2/blog/".$trURL."/post", array('type'=>'text', 'title'=>$msgT,  'body'=>$msg, 'tags'=>$tags, 'source'=>get_permalink($postID)));
    $code = $postinfo->meta->status; //prr($msg); prr($postinfo); echo $code."VVVV"; die("|====");
    if ($code == 201) { if ($postID=='0') { echo 'OK - Message Posted, please see your Tumblr  Page. <br/> Result:'; prr($postinfo->meta); } } else {  prr($postinfo);  }      
    return $code;
  }
}
if (!function_exists("doPublishToPN")) { //## Second Function to Post to PN
  function doPublishToPN($postID, $options){  $blogTitle = htmlspecialchars_decode(get_bloginfo('name'), ENT_QUOTES); if ($blogTitle=='') $blogTitle = home_url(); $isPost = isset($_POST["SNAPEdit"]); 
    if ($postID=='0') { echo "Testing ... <br/><br/>"; $msg = 'Test Post from '.$blogTitle; $link = home_url(); 
      if ($options['pnDefImg']!='') $imgURL = $options['pnDefImg']; else $imgURL ="http://direct.gtln.us/img/nxs/NextScriptsLogoT.png"; $boardID = $options['pnBoard']; 
    }
    else{        
      if ($isPost) $pnMsgFormat = $_POST['SNAP_FormatPN']; else { $t = get_post_meta($postID, 'SNAP_FormatPN', true); $pnMsgFormat = $t!=''?$t:$options['pnMsgFormat']; } 
      if ($isPost) $boardID = $_POST['apPNBoard']; else { $t = get_post_meta($postID, 'apPNBoard', true); $boardID = $t!=''?$t:$options['pnBoard']; } 
      $msg = nsFormatMessage($pnMsgFormat, $postID); $link = get_permalink($postID);
    } 
    $email = $options['pnUName'];  $pass = $options['pnPass'];// prr($boardID); prr($_POST); die();
    
    if ($imgURL=='') if (function_exists("get_post_thumbnail_id") ){ $imgURL = wp_get_attachment_image_src(get_post_thumbnail_id($postID), 'large'); $imgURL = $imgURL[0];} 
    if ($imgURL=='') {$post = get_post($postID); $imgsFromPost = nsFindImgsInPost($post);  if (is_array($imgsFromPost) && count($imgsFromPost)>0) $imgURL = $imgsFromPost[0]; }
    if ($imgURL=='') $imgURL = $options['pnDefImg']; if ($imgURL=='') $imgURL = $options['ogImgDef']; // prr($msg);
    
    $loginError = doConnectToPinterest($email, $pass);  if ($loginError!==false) {echo $loginError; return "BAD USER/PASS";} 
    $ret = doPostToPinterest($msg, $imgURL, $link, $boardID);
    if ($ret!='OK') echo $ret; else if ($postID=='0') echo 'OK - Message Posted, please see your Pinterest Page';
  }
}
if (!function_exists("doPublishToBG")) { //## Second Function to Post to PN
  function doPublishToBG($postID, $options){  $blogTitle = htmlspecialchars_decode(get_bloginfo('name'), ENT_QUOTES); if ($blogTitle=='') $blogTitle = home_url(); $isPost = isset($_POST["SNAPEdit"]); 
    if ($postID=='0') { echo "Testing ... <br/><br/>"; $msgT = 'Test Post from '.$blogTitle;  $link = home_url(); $msg = 'Test Post from '.$blogTitle. " ".$link; }
    else{        
      if ($isPost) $bgMsgFormat = $_POST['SNAP_FormatBG']; else { $t = get_post_meta($postID, 'SNAP_FormatBG', true); $bgMsgFormat = $t!=''?$t:$options['bgMsgFormat']; } 
      $msg = nsFormatMessage($bgMsgFormat, $postID); $link = get_permalink($postID);      
      if ($isPost) $bgMsgTFormat = $_POST['SNAP_FormatTBG']; else { $t = get_post_meta($postID, 'SNAP_FormatTBG', true); $bgMsgTFormat = $t!=''?$t:$options['bgMsgTFormat']; } 
      $msgT = nsFormatMessage($bgMsgTFormat, $postID);              
    }
    $email = $options['bgUName'];  $pass = $options['bgPass']; $blogID = $options['bgBlogID'];
    //echo "###".$auth."|".$blogID."|".$msgT."|".$msg;
    if ($options['bgInclTags']=='1'){$t = wp_get_post_tags($postID); $tggs = array(); foreach ($t as $tagA) {$tggs[] = $tagA->name;} $tags = implode('","',$tggs);}
    if (function_exists("doConnectToBlogger")) {$auth = doConnectToBlogger($email, $pass);   $ret = doPostToBlogger($blogID, $msgT, $msg, $tags);} 
      else {$auth = nsBloggerGetAuth($email, $pass);  $ret = nsBloggerNewPost($auth, $blogID, $msgT, $msg);}
    if ($ret!='OK') echo $ret; else if ($postID=='0') echo 'OK - Message Posted, please see your Blooger/Blogpost Page';
  }
}

    // add settings link to plugins list
function ns_add_settings_link($links, $file) {
    static $this_plugin;
    if (!$this_plugin) $this_plugin = plugin_basename(__FILE__);
    if ($file == $this_plugin){
        $settings_link = '<a href="options-general.php?page=NextScripts_SNAP.php">'.__("Settings","default").'</a>';
        array_unshift($links, $settings_link);
    }
    return $links;
}

function nsFindImgsInPost($post) { global $ShownAds; if (isset($ShownAds)) $ShownAdsL = $ShownAds; $postCnt = apply_filters('the_content', $post->post_content); $postImgs = array();
  $output = preg_match_all( '/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $postCnt, $matches ); if ($output === false){return false;}
  foreach ($matches[1] as $match) { if (!preg_match('/^https?:\/\//', $match ) ) $match = site_url( '/' ) . ltrim( $match, '/' ); $postImgs[] = $match;} if (isset($ShownAds)) $ShownAds = $ShownAdsL; return $postImgs;
}

function nsAddOGTags() { global $post, $ShownAds;; $options = get_option("NS_SNAutoPoster"); if ((int)$options['nsOpenGraph'] != 1) return ""; $ogimgs = array();     if (isset($ShownAds)) $ShownAdsL = $ShownAds; 
  //## Add og:site_name, og:locale, og:url, og:title, og:description, og:type
  echo '<meta property="og:site_name" content="' . get_bloginfo( 'name' ) . '">' . "\n"; echo '<meta property="og:locale" content="' . esc_attr( get_locale() ) . '">' . "\n";
  if (is_home() || is_front_page()) {$ogurl = get_bloginfo( 'url' ); } else { $ogurl = 'http' . (is_ssl() ? 's' : '') . "://".$_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];}
  echo '<meta property="og:url" content="' . esc_url( apply_filters( 'ns_ogurl', $ogurl ) ) . '">' . "\n";
  if (is_home() || is_front_page()) {$ogtitle = get_bloginfo( 'name' ); } else { $ogtitle = get_the_title();}
  echo '<meta property="og:title" content="' . esc_attr( apply_filters( 'ns_ogtitle', $ogtitle ) ) . '">' . "\n";
  if ( is_singular() ) {
    if ( has_excerpt( $post->ID )) {$ogdesc = strip_tags( get_the_excerpt( $post->ID ) ); } else { $ogdesc = str_replace( "\r\n", ' ' , substr( strip_tags( strip_shortcodes( apply_filters('the_content', $post->post_content) ) ), 0, 160 ) ); }
  } else { $ogdesc = get_bloginfo( 'description' ); } $ogdesc = nsTrnc($ogdesc, 900, ' ');
  echo '<meta property="og:description" content="' . esc_attr( apply_filters( 'ns_ogdesc', $ogdesc ) ) . '">' . "\n";        
  $ogtype = is_single()?'article':'website'; echo '<meta property="og:type" content="' . esc_attr(apply_filters( 'ns_ogtype', $ogtype)).'">'."\n";                
  //## Add og:image
  if (!is_home()) {
    if (function_exists('has_post_thumbnail') && has_post_thumbnail($post->ID)) {
      $thumbnail_src = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'thumbnail' ); $ogimgs[] = $thumbnail_src[0];
    } $imgsFromPost = nsFindImgsInPost($post);           
    if ($imgsFromPost !== false && is_singular())  $ogimgs = array_merge($ogimgs, $imgsFromPost); 
  }        
  //## Add default image to the endof the array
  if (isset($options['ogImgDef']) && $options['ogImgDef']!='') $ogimgs[] = $options['ogImgDef']; 
  //## Output og:image tags
  if (!empty($ogimgs) && is_array($ogimgs)) foreach ($ogimgs as $ogimage)  echo '<meta property="og:image" content="' . esc_url(apply_filters('ns_ogimage', $ogimage)).'">'."\n";       if (isset($ShownAds)) $ShownAds = $ShownAdsL;          
}

//## Actions and filters    
function ns_custom_types_setup(){ $args=array('public'=>true, '_builtin'=>false);  $output = 'names';  $operator = 'and';  $post_types=get_post_types($args, $output, $operator); 
  foreach ($post_types  as $post_type ) {
    add_action('future_to_publish_'.$post_type, 'nsPublishTo');
    add_action('new_to_publish_'.$post_type, 'nsPublishTo');
    add_action('draft_to_publish_'.$post_type, 'nsPublishTo');
    add_action('pending_to_publish_'.$post_type, 'nsPublishTo');
  }
}    
if (isset($plgn_NS_SNAutoPoster)) { //## Actions
    //## Add the admin menu
    add_action('admin_menu', 'NS_SNAutoPoster_ap');
    //## Initialize options on plugin activation
    add_action("activate_NextScripts_GPAutoPoster/NextScripts_SNAP.php",  array(&$plgn_NS_SNAutoPoster, 'init'));    
    
    add_action('edit_form_advanced', array($plgn_NS_SNAutoPoster, 'NS_SNAP_AddPostMetaTags'));
    add_action('edit_page_form', array($plgn_NS_SNAutoPoster, 'NS_SNAP_AddPostMetaTags'));
    
    add_action('edit_post', array($plgn_NS_SNAutoPoster, 'NS_SNAP_SavePostMetaTags'));
    add_action('publish_post', array($plgn_NS_SNAutoPoster, 'NS_SNAP_SavePostMetaTags'));
    add_action('save_post', array($plgn_NS_SNAutoPoster, 'NS_SNAP_SavePostMetaTags'));
    add_action('edit_page_form', array($plgn_NS_SNAutoPoster, 'NS_SNAP_SavePostMetaTags'));    
    //## Whenever you publish a post, post to Google Plus
    add_action('future_to_publish', 'nsPublishTo');
    add_action('new_to_publish', 'nsPublishTo');
    add_action('draft_to_publish', 'nsPublishTo');
    add_action('pending_to_publish', 'nsPublishTo');   
    
    add_action( 'wp_loaded', 'ns_custom_types_setup' );        
    
    add_action('admin_head', 'jsPostToSNAP');    
    add_action('wp_ajax_rePostToGP', 'rePostToGP_ajax');
    add_action('wp_ajax_rePostToFB', 'rePostToFB_ajax');
    add_action('wp_ajax_rePostToTW', 'rePostToTW_ajax');
    add_action('wp_ajax_rePostToTR', 'rePostToTR_ajax');
    add_action('wp_ajax_rePostToPN', 'rePostToPN_ajax');
    add_action('wp_ajax_rePostToBG', 'rePostToBG_ajax');
    add_action('wp_ajax_getBoards' , 'nsGetBoards_ajax');
    //## Custom Post Types and OG tags
    add_filter('plugin_action_links','ns_add_settings_link', 10, 2 );
    add_action('wp_head','nsAddOGTags',50);
}
?>