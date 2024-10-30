<?php
/*
Plugin Name: CrunchBase API Widget
Version: 1.0
Plugin URI: http://www.iteamweb.com/
Description: Show cruchbase companies with basic details using API
Author: Suresh Baskaran
*/


function cbapiShortCodeHandler($atts=NULL, $content_company){
	cbapiInit(true, $atts,$content_company);
}

function cbapiWidgetHandler(){
	
	function cbapiWidgetInit($args){
		extract($args);
		$options = get_option('cbapi-widget');
		
		echo $before_widget . $before_title . $options['title'] . $after_title;
		
		cbapiInit(false, $args,$options['title']);
		
		echo $after_widget;
		
	}
	
	function cbapiWidgetOptions(){
		
		$errors;
		
		if ( $_POST['cbapi-options-submit'] ) {
			$options_pre = array();
			$options_pre['title'] = strip_tags(stripslashes($_POST["cbapi-title"]));
			$options_pre['cbapikey'] = strip_tags(stripslashes($_POST["cbapi-cbapikey"]));						
			$errors = cbapiValidateOptions($options_pre);

			if(empty($errors)){
				update_option('cbapi-widget', $options_pre);
			}
		}
		
		$options = get_option('cbapi-widget');
		
		?>
		<?php
		$styleError = "";
		if ($errors){
			while(!empty($errors)){
				echo "<div id='errorMessage' class='error fade'><p>".array_pop($errors)."</p></div>";
			}
			$styleError = "background-color: rgb(255, 235, 232); border-color: rgb(204, 0, 0); border-style: solid; border-width: 1px;";
			
		}
		?>
		<div id="error" style="<?php echo $styleError?>">
		<h3>API settings</h3>
		<p>
			<label for="cbapi-title"><?php _e('Title:'); ?> <input class="widefat" id="cbapi-title" name="cbapi-title" type="text" value="<?php echo $options['title'];?>" /></label>
			<small></small>
		</p>
		<p>
			<label for="cbapi-cbapikey"><?php _e('cbapikey:'); ?> <input class="widefat" id="cbapi-cbapikey" name="cbapi-cbapikey" type="text" value="<?php echo $options['cbapikey'];?>" /></label>
			<small>Please enter the key alone</small>
		</p>		
		<input type="hidden" id="cbapi-options-submit" name="cbapi-options-submit" value="true" />
		</div>

	<?php
	}
	
	wp_register_sidebar_widget('cbapi','Crunchbase Widget', 'cbapiWidgetInit');
	wp_register_widget_control('cbapi','Crunchbase Widget', 'cbapiWidgetOptions' );
}

function cbapiAdminMenuHandler(){
	function cbapiOptionsHandler(){
		if (!current_user_can('manage_options'))  {
			wp_die( __('You do not have sufficient permissions to access this page.') );
		}		
		
		
		$errors;
		
		if($_POST['cbapi-options-submit']){
			$options_pre = array();
			$options_pre['cbapikey'] = strip_tags(stripslashes($_POST['cbapikey']));
			$options_pre['show_category'] = $_POST['show_category'];
			$options_pre['show_email'] = $_POST['show_email'];
			$options_pre['show_link'] = $_POST['show_link'];
			$errors = cbapiValidateOptions($options_pre);

			if(empty($errors)){
				update_option('cbapi-options', $options_pre);
			}
		}
		
		$options = get_option('cbapi-options');
	
		?>
		
		    <div class="wrap">
		      <h2>Crunchbase API settings</h2>
			<form method="post">
				<h3>API settings</h3>

				<table class="form-table">					
					<tr valign="top">
						<th scope="row"><label for="args">Crunch base API Key</label></th>
						<td>
							<input name="cbapikey" value="<?php echo $options['cbapikey'];?>" type="text" />
							<span class="description">Please enter the key alone</span>
						</td>						
					</tr>				 
				</table>

				<h3>Display Settings of the company</h3>
				<table class="form-table">					
					<tr valign="top">
						<th scope="row"><label for="args1">Check on the items to be displayed</label></th>
						<td>
							<input name="show_category" value="1" type="checkbox" <?php if($options['show_category']) echo "checked";?> />
							<span class="description">Category</span>
							<br>
							<input name="show_email" value="1" type="checkbox" <?php if($options['show_email']) echo "checked";?> />
							<span class="description">EMail</span>
							<br>
							<input name="show_link" value="1" type="checkbox" <?php if($options['show_link']) echo "checked";?> />
							<span class="description">Show Crunch base link</span>
						</td>						
					</tr>				 
				</table>

				<br/>
				<input type="submit" name="Submit" class="button-primary" value="<?php echo _e('Save Changes')?>"/>
				<input type="hidden" value="true" name="cbapi-options-submit">
			</form>
		    </div>
		   
<?php
	}

	add_menu_page('CrunchBase settings', 'CrunchBase', 'manage_options', __FILE__, 'cbapiOptionsHandler');
}



function cbapiInit( $shortcode=false, $args, $content_company){	
	?>
	<div class="cb_shortcode">
		
		<?php 
$content_company=str_replace(" ", "+", $content_company);
		if($shortcode)
		$options=get_option('cbapi-options'); 
		else
		$options=get_option('cbapi-widget'); 	
		?>

		<?php
		$url="http://api.crunchbase.com/v/1/company/".$content_company.".js?api_key=".$options['cbapikey'];
        $jsonText = @file_get_contents($url);

        $jsonObject = json_decode($jsonText);
        
        if (!empty($jsonObject)) {
	        
if($options[show_link]) echo "<a href=".$jsonObject->{"crunchbase_url"}." target='_blank'>".$jsonObject->{"name"}."</a>";	
else echo $jsonObject->{"name"};	        
$foundedOn = $jsonObject->{"founded_day"}."/".$jsonObject->{"founded_month"}."/".$jsonObject->{"founded_year"};
echo "<br>Founded on ".$foundedOn;
if($options[show_email]) echo "<br>Email Id: ".$jsonObject->{"email_address"};
if($options[show_category]) echo "<br>Category: ".$jsonObject->{"category_code"};


    	}
		?>

	</div>
	<?php	
}

function cbapiInstall(){
	$options['cbapikey'] = '500';
	add_option('cbapi-options', $options);
	$options['cbapikey'] = '160';	
	add_option('cbapi-widget', $options);
}

function cbapiUninstall(){
	delete_option('cbapi-options');
	delete_option('cbapi-widget');
}

function cbapiValidateOptions($inputs){
	$fontColorErrorMessage = "The font color must be a correct hex value (i.e. #000000)";
	$backgroundColorErrorMessage = "The background color must be a correct hex value (i.e. #000000)";
	
	$errors = array();
	
	if (!empty($inputs['fontcolor'])){
		if (!preg_match('/^#[\da-fA-F]{6}$/', $inputs['fontcolor'])){
			array_push($errors, $fontColorErrorMessage);
		}
	}
	
	if (!empty($inputs['backgroundcolor'])){
		if (!preg_match('/^#[\da-fA-F]{6}$/', $inputs['backgroundcolor'])){
			array_push($errors, $backgroundColorErrorMessage);
		}
	}
	
	return $errors;
}

// create initial values and delete them on deactivation
register_activation_hook( __FILE__, 'cbapiInstall' );
register_deactivation_hook( __FILE__, 'cbapiUninstall' );



// check if shortcode is used
add_shortcode('cb', 'cbapiShortCodeHandler');

// register widget
add_action('widgets_init', 'cbapiWidgetHandler');

// register menu
add_action('admin_menu', 'cbapiAdminMenuHandler');
?>