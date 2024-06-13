<?php

namespace App\Entity;

use App\Repository\Database;
use PDO;
use Symfony\Component\HttpFoundation\Request;

class Releve extends AbstractEntity
{
    protected int $year;
    protected string $serial;
    protected Utilisateur $utilisateur;
    protected Examen $examen;

    public function __construct(Request $req) {
        $this->id = $req->get('releve_id') ?? 0;
        $this->target = 'releve';
    }

    public function save(): void
    {
        // On vérifie si la ligne est déjà présente en BDD.
        $search = Database::create()->prepare("SELECT * FROM releve WHERE(id = :id)");
        $search->setFetchMode(PDO::FETCH_CLASS, get_class($this));
        $search->execute([
            'id' => $this->id
        ]);

        $entities = $search->fetchAll(PDO::FETCH_OBJ);

        if (!empty($entities)) {
            // Application des modification

            $update = Database::create()->prepare("UPDATE releve SET year = :year, serial = :serial WHERE(id = :id)");
            $update->execute([
                'year' => $this->year,
                'serial' => $this->serial,
                'id' => $this->id
            ]);

        } else {
            // Ajout d'une nouvelle entrée

            $insert = Database::create()->prepare("INSERT INTO releve (created, id_examen, id_utilisateur, year, serial)" .
                " VALUES(:created,:id_examen, :id_utilisateur, :year, :serial)");
            $insert->execute([
                'created' => $this->created->format('Y-m-d'),
                'id_examen' => $this->examen->id,
                'id_utilisateur' => $this->utilisateur->id,
                'year' => $this->year,
                'serial' => $this->serial
            ]);
        }
    }

    public function delete(): void
    {
        $st = Database::create()->prepare("SELECT * FROM note WHERE(id_releve = :id_releve)");
        $st->execute(['id_releve' => $this->id]);
        if (!empty($results = $st->fetchAll(PDO::FETCH_ASSOC))) {
            foreach ($results as $result) {
                $note = new Note(new Request());
                $note->setId($result['id']);
                $note->delete();
            }

        }
        Database::create()->prepare("DELETE FROM releve WHERE(id = :id)")->execute([
            'id' => $this->id
        ]);
    }

    /**
     * @return int
     */
    public function getYear(): int
    {
        return $this->year;
    }

    /**
     * @param int $year
     */
    public function setYear(int $year): void
    {
        $this->year = $year;
    }

    /**
     * @return string
     */
    public function getSerial(): string
    {
        return $this->serial;
    }

    /**
     * @param string $serial
     */
    public function setSerial(string $serial): void
    {
        $this->serial = $serial;
    }

    /**
     * @return int
     */
    public function getUtilisateur(): Utilisateur
    {
        return $this->utilisateur;
    }

    /**
     * @param int $id_utilisateur
     */
    public function setUtilisateur(Utilisateur $utilisateur): void
    {
        $this->utilisateur = $utilisateur;
    }

    /**
     * @return int
     */
    public function getExamen(): Examen
    {
        return $this->examen;
    }

    /**
     * @param int $id_examen
     */
    public function setExamen(Examen $examen): void
    {
        $this->examen = $examen;
    }

}