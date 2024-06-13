<?php

namespace App\Entity;

use App\Repository\Database;
use PDO;

class Matiere extends AbstractEntity
{
    protected string $title;
    protected int $coef;

    public function save(): void
    {
        // On vérifie si la ligne est déjà présente en BDD.
        $search = Database::create()->prepare("SELECT * FROM matiere WHERE(id = :id)");
        $search->setFetchMode(PDO::FETCH_CLASS, get_class($this));
        $search->execute([
            'id' => $this->id
        ]);

        $entities = $search->fetchAll(PDO::FETCH_OBJ);

        if (!empty($entities)) {
            // Application des modification

            $update = Database::create()->prepare("UPDATE matiere SET title = :title, coef = :coef WHERE(id = :id)");
            $update->execute([
                'title' => $this->title,
                'coef' => $this->coef,
                'id' => $this->id
            ]);

        } else {
            // Ajout d'une nouvelle entrée

            $insert = Database::create()->prepare("INSERT INTO matiere (title,created, coef) VALUES(:title,:created, :coef)");
            $insert->execute([
                'title' => $this->title,
                'created' => $this->created->format('Y-m-d'),
                'coef' => $this->coef
            ]);
        }
    }

    public function delete(): void
    {
        Database::create()->prepare("DELETE FROM matiere WHERE(id = :id)")->execute([
            'id' => $this->id
        ]);
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getCoef(): int
    {
        return $this->coef;
    }

    public function setCoef(int $coef): void
    {
        $this->coef = $coef;
    }

}