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
		elgg_push_breadcrumb(elgg_echo("oficinas_reports:group:members"));
		elgg_push_breadcrumb(elgg_echo("oficinas_reports:group:members"));
		
		// get members
		$members = $group->getMembers(false);
		
		// build page elements
		$title_text = elgg_echo("oficinas_reports:group:members");
		$title = elgg_view_title($title_text);
		
    $content = "<table class='elgg-table'>";
  	$content .= "<tr>";
  	$content .= "<th>" . elgg_echo("name") . "</th>";
  	$content .= "<th>" . elgg_echo("celular") . "</th>";
  	$content .= "<th>" . elgg_echo("email") . "</th>";
  	$content .= "<th>" . elgg_echo("usersettings:statistics:label:lastlogin") . "</th>";
  	$content .= "<th>" . elgg_echo("oficinas_reports:group:member:number_interactions") . "</th>";

  	$content .= "</tr>";
  	
  	foreach($members as $user){
      
      $url = 'groups/member_report/'.$group_guid.'/'.$user->guid;

  		$icon = elgg_view('output/img', array(
				'src' => $user->getIconURL('topbar'),
				'class' => 'pas elgg-icon-hover-menu'
			));
			$replace = elgg_view('output/url', array(
				'href' => $url ,
				'text' => $icon . $user->name,
        // 'class' => 'mentions-user-link'
			));

      
  		$content .= "<tr>";
  		$content .= "<td width='40%'>$replace</td>" ;
  		$content .= "<td width='5%'>" . $user->celular . "</td>";
  		$content .= "<td width='5%'> $user->email</td>";


  		$last_action = $user->last_action;
  		if(empty($last_action)){
  			$content .= "<td>" . elgg_echo("profile_manager:admin:users:inactive:never") . "</td>";
  		} else {
  			$content .= "<td>" . elgg_view_friendly_time($last_action) . "</td>";
  		}
  		
      $db_prefix = elgg_get_config('dbprefix');
      $ev= elgg_get_river(array(
       // 'limit' => 1000,
       'pagination' => false,
       'count' => true,
       'joins' => array("JOIN {$db_prefix}entities e1 ON e1.guid = rv.object_guid"),
       'wheres' => array("(e1.container_guid = $group->guid) AND (rv.subject_guid = $user->guid)"),
      ));
      
      $content .= "<td > $ev </td>";
      
  		$content .= "</tr>";
  	}

  	$content .= "</table>";
  	
  	
  	
		
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
