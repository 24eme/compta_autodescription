<?php

namespace Models;

use \DB\Cortex;

class File extends Cortex
{
    use EmptyArrayFindTrait;

    protected $db = 'DB';
    protected $table = 'file';

    protected $fieldConf = [
        'id' => ['type' => \DB\SQL\Schema::DT_INT1,  'primary' => true],
        'filename' => ['type' => \DB\SQL\Schema::DT_TEXT],
        'fullpath' => ['type' => \DB\SQL\Schema::DT_TEXT, 'unique' => true],
        'extention' => ['type' => \DB\SQL\Schema::DT_TEXT],
        'size' => ['type' => \DB\SQL\Schema::DT_INT1],
        'ctime' => ['type' => \DB\SQL\Schema::DT_INT1],
        'mtime' => ['type' => \DB\SQL\Schema::DT_INT1],
        'md5' => ['type' => \DB\SQL\Schema::DT_TEXT],
        'piece' => ['belongs-to-one' => Piece::class],
    ];
}
