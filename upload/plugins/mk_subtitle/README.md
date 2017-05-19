#Plugin ClipBucket - Subtitle maker

## Requirement - *Minimum requis*
You need that the common library plugins is installed in order to install this plugin.

*Vous devez avoir le plugin "common library" installer pour installer ce plugin*

## Installation
In many case, you must modify the "max_input_vars" variable in the php.ini configuration file. This is due to the default limit of input of 1000.

*Dans la plupart des cas vous devez ajuster la variable "max_input_vars* dans le fichier de configuration php.ini. Ceci est dû à la limite par défault de 1000.


Go to the plugin administration panel and install the "Subtitle maker" plugin.

*Activer le plugin "Subtitle maker" depuis la rubrique plugin de l'administration.*

## Usage - *Utilisation*
In the "Actions" menu of the video edit page, an item is add (subtitle maker).

You have to generate the marker file who want to find the sentences asked in the video. To do that click on the "Sound finder" button. You can adjust the threshold and the duration of silences if it's not just.

When the marker file is generated, a list of timestamps and inputs appears. You can write the sentences ask in the input (it will play loop the portion of video corresponding).

You can click on the "Envoyer" button to save your work.

Finally click on the "Generer le fichier de sous titres" to publish the work.

*Une entrée est ajoutée dans la page d'administration d'édition de video dans le bouton "Actions".*

*Vous devez générer le fichier de marqueur, ce fichier essaye de trouver les phrases dites dans la vidéo. Pour faire ça, cliquer sur le bouton "Sound finder". Vous pouvez ajuster le seuil et la durée des silences si ça n'est pas juste.*

*Lorsque le fichier de marqueur est générer, une liste de timestamp et de champs apparaissent. Vous pouvez écrire les phrases dites dans le champ (la portion de vidéo correspondante sera jouée en boucle)."*

*Vous pouvez cliquer sur le bouton "Envoyer" pour sauvegarder votre travail.*

*Finalement, cliquer sur le bouton "Generer le fichier de sous titres" pour publié votre travail.*

## ChangeLog
### [0.2] - 2017-05-18
#### Modified
- Merge sentence less than one seconds.

### [0.1] - 2017-05-09
#### Added
- First version
