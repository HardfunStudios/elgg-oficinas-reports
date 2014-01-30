<?php


// require_once(dirname(__FILE__) . "/lib/array_column.php");

function oficinas_reports_init() {
  

  // add barc code to the footer
  if(elgg_in_context("admin") && elgg_is_admin_logged_in()){
		elgg_register_admin_menu_item('administer', 'bygroups', 'users');	
    // elgg_register_admin_menu_item('administer', 'oficinas'); 
		elgg_register_admin_menu_item('administer', 'approved', 'oficinas');	
		elgg_register_admin_menu_item('administer', 'approved_by_round', 'oficinas');	
		
		elgg_register_admin_menu_item('administer', 'enforce_default_cpt', 'users');	
  }
	
	elgg_register_action("oficinas_reports/enforce_dpf", dirname(__FILE__) . "/actions/enforce_default_cpf.php", "admin");
	elgg_register_action("oficinas_reports/approve_users", dirname(__FILE__) . "/actions/approve_users.php");
	
	
	// extend groups page handler
	elgg_register_plugin_hook_handler("route", "groups", "oficinas_reports_route_groups_handler");
  elgg_register_plugin_hook_handler("permissions_check:metadata","user","oficinas_reports_group_admin_approve_rights");
}

function oficinas_reports_pagesetup(){
  $page_owner = elgg_get_page_owner_entity();

  if(elgg_in_context("groups") && ($page_owner instanceof ElggGroup)){

  		// group mail options
  		if ($page_owner->canEdit()) {
  			elgg_register_menu_item('page', array(
  				'name' => 'phones_list',
  				'text' => elgg_echo("oficinas_reports:group:phones_list"), //elgg_echo('group_tools:menu:mail'),
  				'href' => "groups/phones_list/" . $page_owner->getGUID(),
  			));
  			elgg_register_menu_item('page', array(
  				'name' => 'members_report',
  				'text' => elgg_echo('oficina_reports:registrations_list'),
  				'href' => "groups/members_report/" . $page_owner->getGUID(),
  			));
  			elgg_register_menu_item('page', array(
  				'name' => 'approveds_list',
  				'text' => elgg_echo('oficinas_reports:approveds_list'),
  				'href' => "groups/approveds_list/" . $page_owner->getGUID(),
  			));
  			
  		};
  }
}

function  oficinas_reports_route_groups_handler($hook, $type, $return_value, $params){
  $result = $return_value;
	
	if(!empty($return_value) && is_array($return_value)){
		$page = $return_value['segments'];
		
		switch($page[0]){
			case "members_report":
				$result = false;
				
				set_input("group_guid", $page[1]);
					
				include(dirname(dirname(__FILE__)) . "/oficinas_reports/pages/members_report.php");
				break;
		  case "member_report":
  				$result = false;

  				set_input("group_guid", $page[1]);
  				set_input("user_guid", $page[2]);

  				include(dirname(dirname(__FILE__)) . "/oficinas_reports/pages/member_report.php");
  				break;
		  case "phones_list":
			    $result = false;
			
    			set_input("group_guid", $page[1]);
				
    			include(dirname(dirname(__FILE__)) . "/oficinas_reports/pages/phones_list.php");
    			break; 					
		  case "approveds_list":
			    $result = false;

    			set_input("group_guid", $page[1]);

    			include(dirname(dirname(__FILE__)) . "/oficinas_reports/pages/approveds_list.php");
    			break;
		}
	}
	
	return $result;
}

function group_get_admins($group_guid) {
  
  $group = get_entity($group_guid);
  
	$options = array(
		"relationship" => "group_admin",
		"relationship_guid" => $group->getGUID(),
		"inverse_relationship" => true,
		"type" => "user",
		"limit" => false,
		"list_type" => "gallery",
		"gallery_class" => "elgg-gallery-users",
	);
	
	$users = elgg_get_entities_from_relationship($options);
	
	array_unshift($users, $group->getOwnerEntity());
	
	return $users;
}

function oficinas_reports_group_admin_approve_rights($hook, $type, $return_value, $params) {

  $metadata = $params['metadata'];
  //determines if we are dealing with the right metadata
  $md_name = explode('_',$metadata->name);
  
  if($md_name[0]=='approved') {
    if($params['user']->isAdmin()) return true;
    
    $admins = group_get_admins($md_name[1]);
    if(!empty($admins)) {
      foreach($admins as $admin) {
        if($admin->guid==$params['user']->guid) return true;
      }
    }
  }
  return false;
}


register_elgg_event_handler('init', 'system', 'oficinas_reports_init');
elgg_register_event_handler("pagesetup", "system", "oficinas_reports_pagesetup", 650);