#Plugin ClipBucket - Define Chapters

This plugin is used to add chapters to a video.      

# Install
To activate this plugin, go to the plugin manager and add click on the "install button" on the "Define Chapters" plugin. It also add a new table called "chapters" in CB database.

## Requirements
This plugin is based on the following plugins :

- **Common Library** (Required) : Used in this plugin for localisation, and admin access permissions. 
- **Extended Search** (Required) : Used in this plugin for searching videos with words stored into their chapters. 
- **Expand video Manager** (Required) : Used to add a new "chapters" tab into the edit video page. 

# Uninstall
Uninstalling the plugin in the plugin manager will remove the database table and clean up the config table.
	
# Use
The plugin has two parts one into the admin interface and one in user interface. 

## Use in the admin interface :
You have to activate the plugin for the user level you wnat it. Then go to the video manager page, select an edit a video. go to the "chapter" tab. Play the video and click on the "add chapter" button. set a title and click save chapter before endind chaptering.

## Use in the user interface :
You need a version of videojs that support vtt files. Add the following anchor to the "video" html tag to add chapters to the current video :
    {ANCHOR place="getVTTFile"}

