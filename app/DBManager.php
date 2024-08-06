<?php

class DBManager
{
    public static function init($dsn)
    {
        $db = null;

        try {
            $db = new \DB\SQL($dsn);
            \Base::instance()->set('DB', $db);
        } catch (PDOException $e) {
            die("Fatal error while creating PDO connexion: ".$e->getMessage());
        }

        if (! $db->schema('object') && \Base::instance()->get('db.create') === true) {
            \Models\Banque::setup();
        }

        return $db;
    }
}
