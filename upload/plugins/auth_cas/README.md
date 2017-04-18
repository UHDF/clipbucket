#Plugin ClipBucket - Central Authentication Service (Single sign-on)

## Requirement - *Minimum requis*
__Be careful__, the Common Library plugin must be firstly installed and activated on the ClipBucket platform.

*__Attention__, le plugin Common Library doit d'abord être installé et activé sur la plateforme Clipbucket.*

## Installation
Activate the plugin from the plugin manager section of the administration panel.

*Activer le plugin depuis la rubrique plugin manager de l'administration.*

## Configuration
From administration, go to "General Configurations", then "CAS Configuration" and inquire the required fields (server, port, etc...).

*Depuis l'administration du site aller dans la rubrique "General Configurations" puis "Connexion CAS" et renseigner les informations requises (serveur, port, etc...).*

## Utilisation - Usage
In your layout (styles/<nom_du_template>/layout/signup.html), add the code `{ANCHOR place="is_auth_cas"}`

Users must be firstly created in clipbucket with the same username (login) as in central authentication service.

If you check "create user if he doesn't exist ?", you must have at least the LDAP client plugin installed in order to retrieve users emails.

N.B.: If an email was already used by an other user, the signup process will be aborted.

*Dans votre template (styles/<nom_du_template>/layout/signup.html), ajouter le code suivant : `{ANCHOR place="is_auth_cas"}`*

*Les utilisateurs doivent d'abord être enregistrer dans clipbucket avec le même nom d'utilisateur (login) que dans le service d'authentification central.*

*Si vous cochez "créer l'utilisateur s'il n'existe pas ?", vous devez avoir au moins le plugin LDAP client installé dans le but de récupérer les emails des utilisateurs.*

*N.B.: Si un email est déjà utilisé par un autre utilisateur le compte ne sera pas créé.*