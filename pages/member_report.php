<?php 

	gatekeeper();
	
	$group_guid = (int) get_input("group_guid", 0);
	$user_guid = (int) get_input("user_guid", 0);

	if(($group = get_entity($group_guid)) && ($group instanceof ElggGroup) && ($group->canEdit())){
	  
	  $user = get_entity($user_guid);
    // var_dump($user); die();
    
    
    $header = elgg_view_entity($user, array(
    	'use_hover' => false,
    	'use_link' => false,
    ));
    $header .= "<ul style=\"padding-bottom: 3px; border-bottom: 1px solid #CCCCCC; margin-bottom: 10px;\">";
    $header .= "<li>Cidade: ".$user->cidade;
    $header .= "<li>Estado: ".$user->estado;
    $header .= "<li>Escola: ".$user->escola;
    $header .= "</ul>";
	  
		// set page owner
		elgg_set_page_owner_guid($group->getGUID());
		elgg_set_context("groups");
		
		// set breadcrumb
		elgg_push_breadcrumb(elgg_echo("groups"), "groups/all");
		elgg_push_breadcrumb($group->name, $group->getURL());
		elgg_push_breadcrumb(elgg_echo("oficinas_reports:group:members"));
		elgg_push_breadcrumb(elgg_echo($user->name));
			
		// build page elements
		$title_text = sprintf(elgg_echo("oficinas_reports:group:member:river"),$user->name);
		$title = elgg_view_title($title_text);
		
		
    $db_prefix = elgg_get_config('dbprefix');
    $ev= elgg_list_river(array(
     'limit' => 1000,
     'pagination' => false,
     'joins' => array("JOIN {$db_prefix}entities e1 ON e1.guid = rv.object_guid"),
     'wheres' => array("(e1.container_guid = $group->guid) AND (rv.subject_guid = $user->guid)"),
    ));
    
    if(!empty($ev)) {
      $content .= $ev;
    } else {
      $content = elgg_echo("oficinas_reports:group:member:nointeraction");
    }
		
		$body = elgg_view_layout("content", array(
			"entity" => $group,
			"title" => $title_text,
			"content" => $header.$content,
			"filter" => false
		));
		echo elgg_view_page($title_text, $body);
	} else {
		forward(REFERER);
	}
