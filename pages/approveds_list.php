<?php 

	gatekeeper();
	
	$group_guid = (int) get_input("group_guid", 0);
	$editing = (int) get_input("edit", 0);
  $db_prefix = elgg_get_config('dbprefix');

	if(($group = get_entity($group_guid)) && ($group instanceof ElggGroup) && ($group->canEdit())){
		// set page owner
		elgg_set_page_owner_guid($group->getGUID());
		elgg_set_context("groups");
		
		// set breadcrumb
		elgg_push_breadcrumb(elgg_echo("groups"), "groups/all");
		elgg_push_breadcrumb($group->name, $group->getURL());
		elgg_push_breadcrumb(elgg_echo("oficinas_reports:approved:members"));
    // elgg_push_breadcrumb(elgg_echo("oficinas_reports:group:members"));
		
    //query copied from http://reference.elgg.org/group_8php_source.html
    
    $ia = elgg_set_ignore_access(true);
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
    $ia = elgg_set_ignore_access(false);      
		
		// build page elements
		$title_text = elgg_echo('oficinas_reports:approved:members');
		$title = elgg_view_title($title_text);
		
		
		$__elgg_ts = time();
    $__elgg_token = generate_action_token($__elgg_ts);
    $content = "<form action='/action/oficinas_reports/approve_users' method='post'>";
    $content .= "<input type=hidden name=__elgg_token value='$__elgg_token'>";
    $content .= "<input type=hidden name=__elgg_ts value='$__elgg_ts'>";
    $content .= "<input type=hidden name=group_guid value='$group_guid'>";
    
    
    if(!$editing) {
      $edit = elgg_view('output/confirmlink', array(
      	'confirm' => elgg_echo('oficinas_reports:approved:edit:confirm'),
      	'href' => "groups/approveds_list/$group_guid/?edit=1",
      	'text' => elgg_echo('oficinas_reports:approved:edit')
      ));
    } else {
      $edit = "<a href='groups/approveds_list/$group_guid'>".elgg_echo('oficinas_reports:approved:edit:cancel')."</a>";
    }
    
  
    $content .= "<ul class=\"elgg-menu elgg-menu-general elgg-menu-hz float-alt\"><li>$edit</li></ul>";

		
    $content .= "<table class='elgg-table'>";
  	$content .= "<tr>";
  	$content .= "<th width='30%'>" . elgg_echo("name") . "</th>";
  	$content .= "<th width='20%'>" . elgg_echo("Escola") . "</th>";
  	$content .= "<th width='20%'>" . elgg_echo("Cidade") . "</th>";
  	$content .= "<th width='5%'>" . elgg_echo("Estado") . "</th>";
	  $content .= "<th width='5%'>".elgg_echo('oficinas_reports:approved')."</th>";
  	$content .= "<th width='20%'>" . elgg_echo("oficinas_reports:approved:comment") . "</th>";

		
  	$content .= "</tr>";
  	
  	
  	$custom_profile_types= array();
  	
  	foreach($members as $user){
  	  
  	  //increment the counter for each profile type

			
  	  if(!array_key_exists($user->custom_profile_type,$custom_profile_types)) {
  	    $profile_type = get_entity($user->custom_profile_type); 
  	    if($profile_type!=null) {
      			$profile_type_name = $profile_type->getTitle();
      			$custom_profile_types[$user->custom_profile_type] = $profile_type_name;
      	}
  	  }
      
      if($custom_profile_types[$user->custom_profile_type]!='Professor-Aluno') continue; //skip if profile type isn't professor aluno
      
      $url = 'groups/member_report/'.$group_guid.'/'.$user->guid;

  		$icon = elgg_view('output/img', array(
				'src' => $user->getIconURL('topbar'),
				'class' => 'pas elgg-icon-hover-menu'
			));
			$replace = elgg_view('output/url', array(
				'href' => $url ,
				'text' => $icon . $user->name,
			));

      
  		$content .= "<tr>";
  		$content .= "<td >$replace </td>" ;
  		$content .= "<td >" . $user->escola . "</td>";
  		$content .= "<td> $user->cidade</td>";
	  	$content .= "<td >".$user->estado."</td>";
  		$approved = eval("return \$user->approved_{$group_guid};");
  		$comment = eval("return \$user->approved_{$group_guid}_comment;");

  		if($approved) {
  		  $approved_users++;
  		} else {
  		  $non_approved_users++;
  		}
  		
      if($editing) {
      		$selected_yes = ($approved?"selected":"");
      		$selected_no = (!$approved?"selected":"");
  		  		
      		$content .= "<td ><select name='approved[$user->guid]' style=\"width:60px\">";
      		   $content .= "<option value=1 $selected_yes >Sim</option>";
      		   $content .= "<option value=0 $selected_no>Não</option></select>";  		   
  		
      		$content .= "</td>";
      		$content .= "<td><input type=text name='comment[$user->guid]' value='$comment' style=\"width:120px\"></td>";
      		$content .= "</tr>";
      } else {
        $content .= "<td>".($approved?"Sim":"Não")."</td>";
    		$content .= "<td>$comment</td>";
    		$content .= "</tr>";
      
      }
  	}




  	$content .= "</table>";

    $content .="<input type=submit value='Salvar'>";
  	$content .= "</form>";
  	
  	
  	$header = "<ul>";
  	
  	$header = "<ul>";
    $header.= "<li>Aprovados: $group->students_approved</li>";
    $header.= "<li>Não Aprovados: $non_approved_users</li>";
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
