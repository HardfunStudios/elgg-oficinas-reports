<?php 

	gatekeeper();
	
	$group_guid = (int) get_input("group_guid", 0);
    $db_prefix = elgg_get_config('dbprefix');

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
    // $members = $group->getMembers(false);
    //query copied from http://reference.elgg.org/group_8php_source.html
    
    $members = elgg_get_entities_from_relationship(array(
         'relationship' => 'member',
         'relationship_guid' => $group_guid,
         'inverse_relationship' => TRUE,
         'type' => 'user',
         'site_guid' => ELGG_ENTITIES_ANY_VALUE,
         'joins' => array("LEFT JOIN {$db_prefix}users_entity u ON (e.guid = u.guid)"),
         'order_by' => "u.name",
         'limit' => 0,
         'offset' => 0,
          ));
          
          
		
		// build page elements
		$title_text = elgg_echo("oficinas_reports:group:members");
		$title = elgg_view_title($title_text);
		
    $content = "<table class='elgg-table'>";
  	$content .= "<tr>";
  	$content .= "<th>" . elgg_echo("name") . "</th>";
  	$content .= "<th>" . elgg_echo("celular") . "</th>";
  	$content .= "<th>" . elgg_echo("email") . "</th>";
  	$content .= "<th>" . elgg_echo("usersettings:statistics:label:lastlogin") . " ao AVA</th>";
  	$content .= "<th>" . elgg_echo("oficinas_reports:group:member:number_interactions") . "</th>";

  	$content .= "</tr>";
  	
  	
  	$custom_profile_types_users = array();
  	
  	foreach($members as $user){
  	  
  	  //increment the counter for each profile type
  	  $custom_profile_types_users[$user->custom_profile_type]++;
      
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
  	
  	
  	$header = "<ul>";
  	
  	$header = "<h3>".elgg_echo('oficinas_reports:group:members:by_profile_type')."</h3><ul>";
  	foreach($custom_profile_types_users as $cpt=>$value) {
  	  if(empty($cpt)) {
  	    $profile_type_name = elgg_echo("custom_profile_type:none");
  	  } else {
    	  $profile_type = get_entity($cpt); 
  		  $profile_type_name = $profile_type->getTitle();	
  		}
	  if($profile_type_name=='Professor-Aluno')  create_metadata($group_guid, 'students_registered', $value);
  	  $header .= "<li>$profile_type_name: $value </li>";
  	}
  	$header .= "</ul><br/>";
  	
		
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
