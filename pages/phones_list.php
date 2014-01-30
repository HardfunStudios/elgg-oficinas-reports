<?php 

	gatekeeper();
	
	$group_guid = (int) get_input("group_guid", 0);

	if(($group = get_entity($group_guid)) && ($group instanceof ElggGroup) && ($group->canEdit())){
		// set page owner
		elgg_set_page_owner_guid($group->getGUID());
		elgg_set_context("groups");
		
		// set breadcrumb
		elgg_push_breadcrumb(elgg_echo("groups"), "groups/all");
		elgg_push_breadcrumb($group->name, $group->getURL());
		elgg_push_breadcrumb(elgg_echo("oficinas_reports:group:phones_list:title"));
		
		// get members
		$members = $group->getMembers(false);
		
		// build page elements
		$title_text = elgg_echo("oficinas_reports:group:phones_list:title");
		$title = elgg_view_title($title_text);
		

  	$content .= "<ul>";
  	
  	foreach($members as $user){
  	  if(isset($user->celular)) {
    		$content .= "<li>" . preg_replace("/[^0-9,.]/", "", $user->celular) . "</li>";
    	}
  	}
  	$content .= "</ul>";
  	

  	
  	
  	
		
		$body = elgg_view_layout("content", array(
			"entity" => $group,
			"title" => $title_text,
			"content" => $content,
			"filter" => false
		));
		echo elgg_view_page($title_text, $body);
	} else {
		forward(REFERER);
	}
