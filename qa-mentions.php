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


class qa_mentions {
	public function init_queries($tableslc)
	{
		$queries = array();
		$tablename=qa_db_add_table_prefix('mentions');
		$tablenameq=qa_db_add_table_prefix('userlogs');
		if(!in_array($tablename, $tableslc)) {
			$queries[] = "
				CREATE TABLE IF NOT EXISTS `$tablename` (
						`ID` int(11) NOT NULL AUTO_INCREMENT,
						`to_id` int(11) NOT NULL,
						`from_id` int(11) NOT NULL,
						`question_id` int(11) NOT NULL,
						`post_id` int(11) NOT NULL,
						`post_type` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
						`eliminated` tinyint(1) NOT NULL DEFAULT '0',
						`closed` tinyint(1) NOT NULL DEFAULT '0',
						`date` int(11) NOT NULL,
						PRIMARY KEY (`ID`),
						UNIQUE KEY `ID` (`ID`)
						) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
			";
		}
		$tablename=qa_db_add_table_prefix('userlogs');
		if(!in_array($tablename, $tableslc)) {
			$queries[] = "
				CREATE TABLE IF NOT EXISTS `$tablename` (
						`user_id` int(11) NOT NULL,
						`name` varchar(40) NOT NULL,
						`value` varchar(100) NOT NULL
						) ENGINE=MyISAM DEFAULT CHARSET=latin1;
				";
		}
		return $queries;

	}


	public function match_request($request){
		if (qa_get_logged_in_userid() == null){
			return(false);
		}
		if ($request == "mentions" or substr($request, 0, 13) == "mentions/ref/" or substr($request, 0, 15) == "mentions/close/"){
			return(true);
		}

		return(false);
	}

	public function suggest_requests(){
		return(array(
					"0" => array(
						"title" => "Mentions",
						"request" => "mentions",
						"nav" => "M",
						)));
	}

	public function process_request($request){
		$qa_content=qa_content_prepare();

		if (substr($request, 0, 13) == "mentions/ref/"){
			$url = explode("/", $request);
			if (isset($url[2])){
				$update = "UPDATE ^userlogs SET value = # WHERE user_id = # and name = \"mentions_last_update\" LIMIT 1";
				$update = qa_db_query_sub($update, time(), qa_get_logged_in_userid());
				qa_redirect($url[2]);
			}
		} elseif (substr($request, 0, 15) == "mentions/close/"){
			$url = explode("/", $request);
			if (isset($url[2])){
				$close = "UPDATE ^mentions SET closed = 1 WHERE ID = #";
				$close = qa_db_query_sub($close, $url[2]);
			}
			qa_redirect("mentions");
		}

		$mentions = "SELECT * FROM ^mentions 						INNER JOIN ^posts
			ON ^posts.postid = ^mentions.question_id and ^mentions.to_id = # and ^mentions.closed = 0
			ORDER BY ^mentions.date DESC";
		$mentions = qa_db_query_sub($mentions, qa_get_logged_in_userid());
		$results = qa_db_read_all_assoc($mentions);

		$last_update = "SELECT value FROM ^userlogs WHERE user_id = # and name = \"mentions_last_update\"";
		$last_update = qa_db_query_sub($last_update, qa_get_logged_in_userid());

		if (mysql_num_rows($last_update) == 0){
			$last_update = "INSERT INTO ^userlogs (user_id, name, value) VALUES(#, $, $)";
			$last_update = qa_db_query_sub($last_update, qa_get_logged_in_userid(), "mentions_last_update", time());
			$last_update = time();
		} else {
			$last_update = qa_db_read_one_value($last_update, true);
		}
		$qa_content["title"] = "Mentions to me";

		if (mysql_num_rows($mentions) > 0){
			$users_id = Array();
			//Gets all users id's needed
			foreach ($results as $index => $row){
				if (!in_array($row["from_id"], $users_id)){
					$users_id[] = $row["from_id"];
				}
			}

			//Obtém os pedaços de HTML pertencentes a cada utilizador
			$users_html = qa_userids_handles_html($users_id, true);
			//$users_html = qa_get_users_html($users_id, true, "");
			$new_ones = 0;
			$messages = "";

			//Mostra cada notificação
			foreach ($results as $index => $row){
				if ($row["date"] > $last_update){
					$backcolor = "#EDF6FF";
					$new_ones++;
				} else {
					$backcolor = "#F4F4F4";
				}
				if ($row["eliminated"] == "1"){
					$s = "<s>";
					$s2 = "</s>";
				} else {
					$s = "";
					$s2 = "";
				}
				$messages .= "<div style=\"margin-bottom: -26px;
				-webkit-border-radius: 10px;
				-moz-border-radius: 10px;
				border-radius: 10px;
				padding-top: 11px;
				padding-left: 10px;
height: 26px;
	background-color: ".$backcolor.";\">
		".$s."The user <b>".$users_html[$row["from_id"]]."</b> mentioned you in the question <b><a class=\"qa-nav-user-link\" href=\"".qa_path($row["question_id"])."#".strtolower($row["post_type"])."".$row["post_id"]."\">".utf8_encode($row["title"])."</a></b>".$s2."</div>";
	$messages .= "<div style=\"margin-bottom: 16px; padding-right: 10px; text-align: right;\"><b><a href=\"".qa_path("mentions/close/".$row["ID"])."\">close</a></b></div>";
			}
			$qa_content["custom1"] = "<div style=\"margin-bottom: 5px;
			-webkit-border-radius: 10px;
			-moz-border-radius: 10px;
			border-radius: 10px;
			padding-top: 11px;
			padding-left: 10px;
height: 26px;
	background-color: #FFEDED;
	text-align: center;\">
		You have ".mysql_num_rows($mentions)." mentions, and ".$new_ones." are new ones.
		</div>
		".$messages."";
		} else {
			$qa_content["custom"] = "There aren't any mentions related to you.";
		}

		$update = "UPDATE ^userlogs SET value = # WHERE user_id = # and name = \"mentions_last_update\" LIMIT 1";
		$update = qa_db_query_sub($update, time(), qa_get_logged_in_userid());

		return($qa_content);
	}
}


/*
   Omit PHP closing tag to help avoid accidental output
 */
