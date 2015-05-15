<?php

/*
	Mention Notifier (c) 2011, Pedro Silva

	http://scorch.isgreat.org/

	
	File: qa-plugin/mention-notifier/qa-mentioned-me.php
	Version: 1.0
	Date: 2011-04-14 16:01:00 GMT
	Description: A widget which shows any mentions to the current logged-in user


	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	as published by the Free Software Foundation; either version 2
	of the License, or (at your option) any later version.
	
	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	More about this license: http://www.question2answer.org/license.php
*/


	class qa_mentions_in_question {
	
		public function allow_template($template){
			if ($template == "question"){
				return(true);
			}
			return(false);
		}
		
		public function allow_region($region){
			if ($region == "side"){
				return(true);
			}
			return(false);
		}
		
		public function output_widget($region, $place, $themeobject, $template, $request, $qa_content){
			if (qa_get_logged_in_userid() == null){
				return(false);
			}
			
			echo "<b class=\"qa-nav-cat-list qa-nav-cat-link\">&nbsp;&nbsp; Mentions in This Question</b>";
			
			$sql = "SELECT * FROM ^mentions WHERE question_id = # and eliminated = 0";
			$sql = qa_db_query_sub($sql, $request);
			$results = qa_db_read_all_assoc($sql);
			
			
			if (mysql_num_rows($sql) > 0){
				
				$user_ids = Array();
				foreach ($results as $index => $row){
					if (!in_array($row["to_id"], $user_ids)){
						$user_ids[] = $row["to_id"];
					}
					
					if (!in_array($row["from_id"], $user_ids)){
						$user_ids[] = $row["from_id"];
					}
				}
			$user_html = qa_userids_handles_html($user_ids, true);	
				//$user_html = qa_get_users_html($user_ids, true, "");
				
				echo "<ul>";
				foreach ($results as $index => $row){
					echo "<li>";
					echo "@".$user_html[$row["to_id"]]." by ".$user_html[$row["from_id"]].": <b><a href=\"#".strtolower($row["post_type"]).$row["post_id"]."\">#".$row["post_type"].$row["post_id"]."</a></b>";
					echo "</li>";
				}
				echo "</ul> ";
			} else {
				echo "<div class=\"span3\"> <p>There are no mentions to any one in this question.</p> </div>";
			}
		}
		
	}


/*
	Omit PHP closing tag to help avoid accidental output
*/
