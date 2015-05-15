<?php

/*
	Mention Notifier (c) 2011, Pedro Silva

	http://scorch.isgreat.org/

	
	File: qa-plugin/mention-notifier/qa-mention-detect.php
	Version: 1.0
	Date: 2011-04-14 10:14:00 GMT
	Description: This page detects any mentions which are made to an user and adds them do the database.


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


	class qa_mention_detect {
	
		public function process_event($event, $userid, $handle, $cookieid, $params){
			//Define the events to track
			$posting_events = Array("q_post", "a_post", "c_post", "q_edit", "a_edit", "c_edit");
			$deleting_events = Array("q_delete", "a_delete", "c_delete");
			if (in_array($event, $posting_events)){
				
				//Get the mentions
				$return = preg_match_all("/@([a-zA-Z_\-][a-zA-Z_\-]*)/", $params["text"], $text, PREG_SET_ORDER);
				
				$names = Array();
				foreach ($text as $index => $value){
					if (!in_array($value[1], $names)){
						$names[] = str_replace("-"," ",$value[1]);
					}
				}
				
				
				if (substr($event, 0, 1) == "q"){
					$this->mention($names, $userid, explode("_", $event), $params['postid'], $params['postid']);
				} else {
					$this->mention($names, $userid, explode("_", $event), $params['postid'], null);
				}
			} elseif (in_array($event, $deleting_events)){
				$this->unmention($params["postid"]);
			}
		}
		
		public function unmention($post_id){
			$sql = "UPDATE ^mentions SET eliminated = 1 WHERE post_id = #";
			$update_mentions = qa_db_query_sub($sql, $post_id);
		}
		
		public function mention($names, $from, $type, $postid, $questionid = null){
			//Get the user's ID's based on their usernames
			require_once QA_INCLUDE_DIR.'qa-app-users.php';
		//	$users_id = qa_get_userids_from_public($names);
			$parenttype = 'Q';
			$users_id = qa_handles_to_userids($names);
			//Checks if there still are any users to mention
			if (empty($users_id)){
				return(false);
			}
			
			//Checks if the event type is valid
			$post_types = Array("q", "a", "c");
			$event_types = Array("post", "edit");
			
			if (!in_array($type[0], $post_types) or !in_array($type[1], $event_types)){
				return(false);
			}
			
			//Gets the question id
			$answerid = $postid;
			if ($questionid == null){
				if ($type[0] == "q"){
					$questionid = $postid;
				} else{
					if ($type[0] == "c"){
						//Get parent answer id
						$answerid = "SELECT parentid FROM ^posts WHERE postid = # LIMIT 1";
						$answerid = qa_db_query_sub($answerid, $postid);
						$answerid = qa_db_read_one_value($answerid, false);
					}
					$checkparenttype ="SELECT type FROM ^posts WHERE postid = # LIMIT 1";
					$checkparenttype = qa_db_query_sub($checkparenttype, $answerid);
					$checkparenttype = qa_db_read_one_value($checkparenttype, false);
					
					if($checkparenttype == "Q")
					{
						$questionid = $answerid;
					}
					else
					{
						$parenttype = 'A';
						$questionid = "SELECT parentid FROM ^posts WHERE postid = # LIMIT 1";
						$questionid = qa_db_query_sub($questionid, $answerid);
						$questionid = qa_db_read_one_value($questionid, false);
					}
				}
			}
			
			//Checks if we're editing the post. If we are, there may be already notifications on the database
			//and we don't want to insert duplicate entries so we remove them from the array
			if ($type[1] != "post"){
				$mentions = "SELECT * FROM ^mentions WHERE post_id = #";
				$mentions = qa_db_query_sub($mentions, $postid);
				$mentions = qa_db_read_all_assoc($mentions);
				
				foreach ($mentions as $index => $value){
					foreach ($users_id as $name => $id){
						if ($value["to_id"]){
							unset($users_id[$name]);
						}
					}
				}
			}
			
			if (isset($users_id[qa_get_logged_in_handle()])){
				unset($users_id[qa_get_logged_in_handle()]);
			}
			
			//Check if there are not users to mention
			if (empty($users_id)){
				return(false);
			}
			
			$pass = 1;
			
			//That's it. Now it's just nitificate the mentioned users :)
			foreach ($users_id as $index => $value){
				if (isset($value)){
					$mention = "INSERT INTO ^mentions (to_id, from_id, question_id, post_id, post_type, date)
								VALUES (#, #, #, #, $, #)";
					$mention = qa_db_query_sub($mention, $value, $from, $questionid, $postid, strtoupper($type[0]), time());
					
					//Reportes an event for each mentioned user
					//This makes possible to add new ways of notify the user
					qa_report_event("u_mentioned", qa_get_logged_in_userid(), qa_get_logged_in_handle(), qa_cookie_get_create(), array(
								"postid" => $postid,
								"userid" => $value,
								"questionid" => $questionid,
								"parentid" => $questionid,
								"parenttype" => $parenttype,
								"post_type" => strtoupper($type[0]),
								"time" => $time));
					
				}
				$pass++;
			}
			
			return(true);
		}
		
	}


/*
	Omit PHP closing tag to help avoid accidental output
*/
