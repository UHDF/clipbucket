#Plugin ClipBucket - Subtitle maker

## Requirement - *Minimum requis*
You need that the common library plugins is installed in order to install this plugin.

*Le plugin "common library" doit être installé pour installer ce plugin*

## Installation
Go to the plugin administration panel and install the "Subtitle maker" plugin. Go to the "Users" > "User Levels" menu to give the right to use this plugin.

*Activer le plugin "Subtitle maker" depuis la rubrique plugin de l'administration. Aller dans le menu "Users" > "User Levels" pour attribuer les droits d'utilisation de ce plugin.*

## Usage - *Utilisation*
### Sound finder - *Trouveur de son*
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

### Create an other translation - *Créer une autre langue*
Go to the "Final file" tab. Beside your generated file, you can see a form that let you choose an other language, choose the one you want to create and click on "Add language". This will open a new text area. Make your translation and save your work. The translation is immediately publish.

*Aller dans l'onglet "Fichier final". Sous le fichier qui a été généré, vous pouvez voir un formulaire vous permettant de choisir un autre langage, choisissez celui que vous voulez créer et cliquer sur "Ajouter une langue". Ceci ouvira une nouvelle zone de texte. Effectuez votre traduction et sauvegarder votre travail. La traduction est immédiatement publiée.*

## ChangeLog
### [0.3] - 2018-10-02
#### Added
- Multilingual feature
#### Modified
- Marker meta file format

### [0.2] - 2017-05-18
#### Modified
- Merge sentence less than one seconds.

### [0.1] - 2017-05-09
#### Added
- First version
