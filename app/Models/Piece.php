<?php

namespace Models;

use \DB\Cortex;

class Piece extends Cortex
{
    use EmptyArrayFindTrait;

    protected $db = 'DB';
    protected $table = 'piece';

    protected $fieldConf = [
        'id' => ['type' => \DB\SQL\Schema::DT_INT1, 'primary' => true],
        'filename' => ['type' => \DB\SQL\Schema::DT_TEXT],
        'fullpath' => ['type' => \DB\SQL\Schema::DT_TEXT, 'unique' => true],
        'extention' => ['type' => \DB\SQL\Schema::DT_TEXT],
        'size' => ['type' => \DB\SQL\Schema::DT_INT1],
        'mtime' => ['type' => \DB\SQL\Schema::DT_INT1],
        'md5' => ['type' => \DB\SQL\Schema::DT_TEXT],
        'facture_type' => ['type' => \DB\SQL\Schema::DT_TEXT],
        'facture_author' => ['type' => \DB\SQL\Schema::DT_TEXT],
        'facture_client' => ['type' => \DB\SQL\Schema::DT_TEXT],
        'facture_identifier' => ['type' => \DB\SQL\Schema::DT_TEXT],
        'facture_date' => ['type' => \DB\SQL\Schema::DT_DATE],
        'facture_libelle' => ['type' => \DB\SQL\Schema::DT_TEXT],
        'facture_prix_ht' => ['type' => \DB\SQL\Schema::DT_FLOAT],
        'facture_prix_tax' => ['type' => \DB\SQL\Schema::DT_FLOAT],
        'facture_prix_ttc' => ['type' => \DB\SQL\Schema::DT_FLOAT],
        'facture_devise' => ['type' => \DB\SQL\Schema::DT_TEXT],
        'paiement_comment' => ['type' => \DB\SQL\Schema::DT_TEXT],
        'paiement_date' => ['type' => \DB\SQL\Schema::DT_DATE],
        'paiement_proof' => ['type' => \DB\SQL\Schema::DT_TEXT],
        'banque' => ['belongs-to-one' => Banque::class],
        'exercice_comptable' => ['type' => \DB\SQL\Schema::DT_TEXT],
        // Relations
        /*
        'other' => [
            'has-many' => [Turbo::class, 'elements', 'element_turbo',
                'relField' => 'element_id'
            ]
        ],
        */
    ];
}
