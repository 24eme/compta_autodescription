<?php

namespace Models;

use \DB\Cortex;

class Banque extends Cortex
{
    use EmptyArrayFindTrait;

    protected $db = 'DB';
    protected $table = 'pdf_banque';

    protected $fieldConf = [
        'id' => ['type' => \DB\SQL\Schema::DT_INT1, 'primary' => true],
        'date' => ['type' => \DB\SQL\Schema::DT_DATE],
        'mtime' => ['type' => \DB\SQL\Schema::DT_INT1],
        'raw' => ['type' => \DB\SQL\Schema::DT_TEXT],
        'amount' => ['type' => \DB\SQL\Schema::DT_FLOAT],
        'type' => ['type' => \DB\SQL\Schema::DT_TEXT],
        'banque_id' => ['type' => \DB\SQL\Schema::DT_TEXT],
        'rdate' => ['type' => \DB\SQL\Schema::DT_DATE],
        'vdate' => ['type' => \DB\SQL\Schema::DT_DATE],
        'label' => ['type' => \DB\SQL\Schema::DT_TEXT],
        'piece' => ['belongs-to-one' => Piece::class],
        'imported_at' => ['type' => \DB\SQL\Schema::DT_INT1]
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
