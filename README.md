# Compta centrée sur les pièces comptables

## Installation

Installation des dépendances :
```
sudo apt-get install python
virtualenv env
. env/bin/activate
pip install -r requirements.txt
```

Lancer le projet, via la console :

```
. env/bin/activate
COMPTA_PDF_URL="http://localhost:8888/metadata#local:" COMPTA_PDF_BASE_PATH="/path/to/compta" python manage.py runserver
```

## License

Logiciel libre sous license AGPL V3
