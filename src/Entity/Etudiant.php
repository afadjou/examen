<?php

namespace App\Entity;

use Symfony\Component\HttpFoundation\Request;

class Etudiant extends Utilisateur {
    public function __construct(Request $req) {
        parent::__construct($req);
        $this->target = 'etudiant';
    }
}