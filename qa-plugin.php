<?php

/*
	Plugin Name: Mention Notifier
	Plugin URI: http://www.portugal-a-programar.org/
	Plugin Description: Notifies a user when he is mentioned in a post.
	Plugin Version: 1.0
	Plugin Date: 2011-03-30
	Plugin Author: Pedro Silva (a.k.a Scorch)
	Plugin Author URI: http://scorch.isgreat.org/
	Plugin License: GPLv2
	Plugin Minimum Question2Answer Version: 1.4
*/

	if (!defined('QA_VERSION')) { // don't allow this page to be requested directly from browser
		header('Location: ../../');
		exit;
	}
	
	qa_register_plugin_module('event', 'qa-mention-detect.php', 'qa_mention_detect', 'Mention Detecter');
	qa_register_plugin_module('widget', 'qa-mentioned-me.php', 'qa_mentioned_me', 'Mentioned Me');
	qa_register_plugin_module('widget', 'qa-mentions-in-question.php', 'qa_mentions_in_question', 'Mentions In This Question');
	qa_register_plugin_module('page', 'qa-mentions.php', 'qa_mentions', 'Mentions To Me');

/*
	Omit PHP closing tag to help avoid accidental output
*/