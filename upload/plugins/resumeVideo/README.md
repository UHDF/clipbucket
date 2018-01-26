#Plugin ClipBucket - Resume video

## Requirement - *Minimum requis*
You need that the common library plugins is installed in order to install this plugin.
This plugin work with Video JS player (http://videojs.com/).

*Vous devez avoir le plugin "common library" installer pour installer ce plugin*
*Ce plugin fonctionne avec le lecteur Video JS (http://videojs.com/)*

## Installation
Go to the plugin administration panel and install the "Resume video" plugin.

*Activer le plugin "Resume video" depuis la rubrique plugin de l'administration.*

## Usage - *Utilisation*
In the front office use the following anchor to display the input :
	`{ANCHOR place="shareVideoOptions"}`

*Dans votre template, ajoutez l'ancre suivante pour afficher les champs :*
	`{ANCHOR place="shareVideoOptions"}`

Play a video, pause the video, reload the page. A confirmation box will appears. You can resume the video or not.

*Lire la vidéo, mettre en pause, recharger la page. Une boite de confirmation apparait. Vous pouvez reprendre la vidéo ou pas.*

## Extra features - *Fonctions bonus*
You can add some parameter in the URL :
- time : 
    Format : string or number. Ex.: 23:45 or 240.
    Description : Position the video at time

- stop :
    Format : string or number. Ex.: 23:45 or 240.
    Description : Stop the video at time

- autoplay :
    Format : boolean. Ex.: true or false (or not present)
    Description : Start playing video

*Vous pouvez ajouter des paramètre dans l'URL :*
*- time :*
    *Format : chaine ou chiffre. Ex.: 23:45 ou 240.*
    *Description : Positionne la video au moment voulu*

*- stop :*
    *Format : chaine ou chiffre. Ex.: 23:45 ou 240.*
    *Description : Arrête la video au moment voulu*

*- autoplay :*
    *Format : booléen. Ex.: true ou false (ou non présent)*
    *Description : Lance la lecture de la vidéo*

## ChangeLog

### [2.0] - 2018-01-18
#### Modified
- Plugin redesign
- Front input added
### [1.0] - 2017-05-19
#### Added
- First version
