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


	class qa_mentioned_me {
	
		public function allow_template($template){
			return(true);
		}
		
		public function allow_region($region){
			if ($region == "side"){
				return(true);
			} else {
				return(false);
			}
		}
		
		public function output_widget($region, $place, $themeobject, $template, $request, $qa_content){
			if (qa_get_logged_in_userid() == null){
				return(false);
			}
			
			//Obtém a última vizualização das menções
			$last_update = "SELECT value FROM ^userlogs WHERE user_id = # and name = \"mentions_last_update\"";
			$last_update = qa_db_query_sub($last_update, qa_get_logged_in_userid());
			$last_update = qa_db_read_one_value($last_update, true);
			
			if ($last_update == null){
				$last_update = 0;
			}
			
			$results = "SELECT * FROM ^mentions WHERE to_id = # and date > #";
			$results = qa_db_query_sub($results, qa_get_logged_in_userid(), $last_update);
			$results = mysql_num_rows($results);
			
			if ($results > 0){
				echo "<div class=\"span3\"> <a href=\"".qa_path("mentions")."\">You have ".$results." new mentions.</a></div>";
			} else {
				echo "<div class=\"span3\"> <a href=\"".qa_path("mentions")."\">There are no mentions related to you.</a></div>";
			}
		}
		
	}


/*
	Omit PHP closing tag to help avoid accidental output
*/
