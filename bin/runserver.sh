#!/bin/bash

cd $(dirname $0)/..

. bin/config.inc

if test "$PYTHON_ENV_DIR"; then
	. $PYTHON_ENV_DIR"/bin/activate"
fi

export COMPTA_PDF_EXCLUDE_PATH
export COMPTA_PDF_URL
export COMPTA_PDF_BASE_PATH
export COMPTA_PDF_COMPTA_SUBDIR
export ALLOWED_HOSTS

python manage.py runserver $COMPTA_BIND
