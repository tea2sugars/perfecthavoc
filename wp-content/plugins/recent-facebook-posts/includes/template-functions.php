<?php

function recent_facebook_posts($args = array())
{
	$rfbp = rfbp_get_class();
	echo $rfbp->output($args);
}