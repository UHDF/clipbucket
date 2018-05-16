<?php
/* 
 *****************************************************************
 | Copyright (c) 2007-2010 Clip-Bucket.com. All rights reserved.	
 | @ Author : ArslanHassan											
 | @ Software : ClipBucket , © PHPBucket.com						
 ******************************************************************
*/

define("THIS_PAGE",'rss');
require 'includes/config.inc.php';

if(!array_key_exists('cat',$_GET) || !array_key_exists('tag',$_GET) || !is_numeric($_GET['cat'])) {
	header('Location: /404.php');
	exit();
}


header ("Content-type: text/xml; charset=utf-8");
echo '<?xml version=\'1.0\' encoding=\'UTF-8\'?>'."\n";

$limit = 20;
/*
// page code is dead... add it back in query if needed

$page = $_GET['page'];
if($page<1 || !is_numeric($page))
	$page = 1;
if($page)
{
	$from = ($page-1)*$limit;
	$limit = "$from,$limit";
}*/


$cat = mysql_clean($_GET['cat']);
$tag = mysql_clean($_GET['tag']);
/*if($cat<1 && !is_numeric($cat)) {
	$cat=null;
}*/

$mode = $_GET['mode'];
switch($mode)
{
	case 'recent':
	default:
	{
		$videos = get_videos(array('limit'=>$limit,'broadcast'=>'public','order'=>'date_added DESC', 'tags'=>$tag, 'category'=>$cat));
		$title  = "Vidéos récentes";
	}
	break;
	
	case 'featured':
	{
			$videos = get_videos(array('limit'=>$limit,'broadcast'=>'public','featured'=>'yes','order'=>'featured_date DESC', 'tags'=>$tag, 'category'=>$cat));
			$title  = "Vidéos à la une";
	}
	break;
	
	case 'views':
	{
		 $videos = get_videos(array('limit'=>$limit,'broadcast'=>'public','order'=>'views DESC', 'tags'=>$tag, 'category'=>$cat));
		 $title = "Most Viewed Videos";
	}
	break;
	
	case 'rating':
	{
		 $videos = get_videos(array('limit'=>$limit,'broadcast'=>'public','order'=>'rating DESC', 'tags'=>$tag, 'category'=>$cat));
		 $title = "Top Rated Videos";
	}
	break;
	
	case 'watching':
	{
		 $videos = get_videos(array('limit'=>$limit,'broadcast'=>'public','order'=>'last_viewed DESC', 'tags'=>$tag, 'category'=>$cat));
		 $title = "Videos Being Watched";
	}
	break;
	
	case 'user':
	{
		 $user = mysql_clean($_GET['username']);
		 //Get userid from username
		 $uid = $userquery->get_user_field_only($user,'userid');
		 $uid = $uid ? $uid : 'x';
		 $videos = get_videos(array('limit'=>$limit,'user'=>$uid,'order'=>'date_added DESC'));
		 $total_vids = get_videos(array('count_only'=>true,'user'=>$uid));
		 $title = "Videos uploaded by ".$user;
	}
	break;
	case 'single':
	{
		$v = mysql_clean($_GET['v']);
		if (!$v) {
			echo "Request is missing video id";
			exit();
		}
	  global $cbvid;
	  $vid = $cbvid->get_video($v);
		$videos = array( $vid );
		$title = "Informations on a single video";
	}
	
	break;
}

subtitle($title);
?>

<rss version="2.0" xmlns:media="http://search.yahoo.com/mrss/" xmlns:atom="http://www.w3.org/2005/Atom">
<channel>
<title><?=cbtitle()?></title>
<link><?=BASEURL?></link>
<atom:link href="<?=htmlspecialchars('http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . "{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}")?>" rel="self" type="application/rss+xml"/>
    <image> 
        <url><?=website_logo()?></url>
        <link><?=BASEURL?></link>
        <title><?=cbtitle()?></title>
    </image>
    <description><?=$Cbucket->configs['description']?></description>
<?php
   
    foreach($videos as $video)
    {
    ?>
    <item>
        <author><?=$video['username']?></author>
        <title><?=substr($video['title'],0,500)?></title>
        <link><?=video_link($video)?></link>
        <description>
                <![CDATA[   
        <table width="600" border="0" cellspacing="0" cellpadding="2">
        <tr>
        <td width="130" height="90" align="center" valign="middle"><img src="<?=get_thumb($video)?>"  border="0"/></td>
        <td valign="top">
        <?=/*clean_string_tags(*/$video["description"]/*)*/?>
        </td>
        <td width="100" valign="top" align="right">
        <?=$video['views']?> Vues<br />
        <?=SetTime($video['duration'])?>
        </tr>
        </table>
        ]]></description>
        <category><?=strip_tags(categories($video['category'],'video'))?></category>
        <guid isPermaLink="true"><?=video_link($video)?></guid>
        <pubDate><?=date_format(date_create($video['date_added']),DateTime::RSS)?></pubDate>
        <media:player url="<?=video_link($video)?>" />
        <media:thumbnail url="<?=get_thumb($video)?>" width="120" height="90" />
        <![CDATA[<media:title><?=substr($video['title'],0,500)?></media:title> 
        <media:category label="Tags"><?=strip_tags(tags($video['tags'],'video'))?></media:category>]]>
        <media:credit><?=$video['username']?></media:credit>
    </item>
    <?php
    }
    ?>
</channel>
</rss>
