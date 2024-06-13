<?php

namespace App\Entity;

use App\Repository\Database;
use PDO;
use Symfony\Component\HttpFoundation\Request;

class Note extends AbstractEntity
{
    protected float $note;
    protected Releve $releve;
    protected Matiere $matiere;

    public function __construct(Request $req) {
        $this->id = $req->get('note_id') ?? 0;
        $this->target = 'note';
    }

    public function save(): void
    {
        // On vérifie si la ligne est déjà présente en BDD.
        $search = Database::create()->prepare("SELECT * FROM note WHERE(id = :id)");
        $search->setFetchMode(PDO::FETCH_CLASS, get_class($this));
        $search->execute([
            'id' => $this->id
        ]);

        $entities = $search->fetchAll(PDO::FETCH_OBJ);

        if (!empty($entities)) {
            // Application des modification

            $update = Database::create()->prepare("UPDATE note SET note = :note WHERE(id = :id)");
            $update->execute([
                'note' => $this->note,
                'id' => $this->id
            ]);

        } else {
            // Ajout d'une nouvelle entrée

            $insert = Database::create()->prepare("INSERT INTO note (created, note, id_releve, id_matiere) " .
                "VALUES(:created,:note, :id_releve, :id_matiere)");
            $insert->execute([
                'created' => $this->created->format('Y-m-d'),
                'note' => $this->note,
                'id_releve' => $this->releve->id,
                'id_matiere' => $this->matiere->id
            ]);
        }
    }

    public function delete(): void
    {
        Database::create()->prepare("DELETE FROM note WHERE(id = :id)")->execute([
            'id' => $this->id
        ]);
    }

    /**
     * @return float
     */
    public function getNote(): float
    {
        return $this->note;
    }

    /**
     * @param float $note
     */
    public function setNote(float $note): void
    {
        $this->note = $note;
    }

    /**
     * @return Releve
     */
    public function getReleve(): Releve
    {
        return $this->releve;
    }

    /**
     * @param Releve $releve
     */
    public function setReleve(Releve $releve): void
    {
        $this->releve = $releve;
    }

    /**
     * @return Matiere
     */
    public function getMatiere(): Matiere
    {
        return $this->matiere;
    }

    /**
     * @param Matiere $matiere
     */
    public function setMatiere(Matiere $matiere): void
    {
        $this->matiere = $matiere;
    }

}