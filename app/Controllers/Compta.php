<?php

namespace Controllers;

use Base;
use Models\Banque;
use Models\Piece;
use Models\File;

class Compta extends Controller
{
    public function home(Base $f3)
    {
        return $f3->reroute('/banque');
    }

    public function banque(Base $f3)
    {
        $banque = new Banque();
        $banques = $banque->find(array(), array('order'=>'date DESC'));
        $f3->set('content', 'banque.html.php');
        echo \View::instance()->render('layout.html.php', 'text/html', compact('banques'));
    }

    public function pieces(Base $f3)
    {
        $piece = new Piece();
        $pieces = $piece->find(null, array('order' => 'facture_date DESC'));
        $f3->set('content', 'pieces.html.php');
        echo \View::instance()->render('layout.html.php', 'text/html', compact('pieces'));
    }

    public function piece(Base $f3)
    {
        $piece = new Piece();
        $id = $f3->get('PARAMS.piece_id');
        $piece = $piece->findone(array('id = ?', $id));
        if (!$piece) {
            die('piece '.$id.' not found');
        }
        $f3->set('content', 'piece.html.php');
        echo \View::instance()->render('layout.html.php', 'text/html', compact('piece'));
    }


    public function files(Base $f3)
    {
        $file = new File();
        $files = $file->find(null, array('order' => 'ctime DESC'));
        $f3->set('content', 'files.html.php');
        echo \View::instance()->render('layout.html.php', 'text/html', compact('files'));
    }

    public static function distancesort($a, $b) {
        return ($a['distance'] - $b['distance']) > 0 ? 1 : -1;
    }

    public static function piece_compare($a, $b) {
        $a = strtoupper($a);
        $b = strtoupper($b);
        $as = [];
        $bs = [];
        for ($i = 0 ; $i < strlen($a) - 4 ; $i++) {
            $as[] = substr($a, $i, 4);
        }
        for ($i = 0 ; $i < strlen($b) - 4 ; $i++) {
            $bs[] = substr($b, $i, 4);
        }
        return 1 - count(array_intersect($as, $bs)) / count($as);
    }

    public function associate(Base $f3) {
        $b = new Banque();
        $banque_line = $b->findone(array('id = ?', $f3->get("GET.banque_id")));
        $pieces = [];
        $p = new Piece();
        foreach($p->find(array('facture_prix_ttc = ? AND paiement_date = ?', $banque_line->amount, null)) as $p) {
            $distance = 0;
            $nb = 1;
            $distance += $this->piece_compare($banque_line->raw, $p->fullpath);
            $nb++;
            $distance += $this->piece_compare($banque_line->raw, $p->filename);
            $nb++;
            if (isset($p->facture_author)) {
                $distance += $this->piece_compare($banque_line->raw, $p->facture_author);
                $nb++;
            }
            if (isset($p->facture_client)) {
                $distance += $this->piece_compare($banque_line->raw, $p->facture_client);
                $nb++;
            }
            if (isset($p->facture_libelle)) {
                $distance += $this->piece_compare($banque_line->raw, $p->facture_libelle);
                $nb++;
            }
            if ($p->paiement_date) {
                $distance += 1;
                $nb++;
            }
            $pieces[$p->fullpath] = array('distance' => $distance / $nb, 'piece' => $p);
        }
        $files = [];
        if (!count($pieces)) {
            $f = new File();
            foreach($f->find(array('piece_id = ?', null)) as $p) {
                $distance = 0;
                $nb = 0;
                $distance += $this->piece_compare($banque_line->raw, $p->fullpath);
                $nb++;
                $distance += $this->piece_compare($banque_line->raw, $p->filename);
                $nb++;
                $distance += abs(strtotime($banque_line->date) - $p->ctime) / (60*60*24*30);
                $nb++;

                $files[$p->fullpath] = array('distance' => $distance / $nb, 'file' => $p);
            }
        }
        uasort($pieces, 'Controllers\Compta::distancesort');
        $f3->set('content', 'associate.html.php');
        echo \View::instance()->render('layout.html.php', 'text/html', compact('pieces', 'banque_line', 'files'));

    }
}
