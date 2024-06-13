<?php

namespace App\Entity;

use App\Repository\Database;
use PDO;
use stdClass;
use Symfony\Component\HttpFoundation\Request;

class Examen extends AbstractEntity
{
    protected ?\DateTimeInterface $created;
    protected string $title;

    public function __construct(Request $req) {
        parent::__construct($req);

        $this->target = 'examen';
    }

    /**
     * @inheritDoc
     */
    public function save(): void
    {
        // On vérifie si la ligne est déjà présente en BDD.
        $search = Database::create()->prepare("SELECT * FROM examen WHERE(id = :id)");
        $search->setFetchMode(PDO::FETCH_CLASS, get_class($this));
        $search->execute([
            'id' => $this->id
        ]);

        $entities = $search->fetchAll(PDO::FETCH_OBJ);

        if (!empty($entities)) {
            // Application des modification

            $update = Database::create()->prepare("UPDATE examen SET title = :title WHERE(id = :id)");
            $update->execute([
                'title' => $this->title,
                'id' => $this->id
            ]);

        } else {
            // Ajout d'une nouvelle entrée

            $insert = Database::create()->prepare("INSERT INTO examen (title,created) VALUES(:title,:created)");
            $insert->execute([
                'title' => $this->title,
                'created' => $this->created->format('Y-m-d')
            ]);
        }
    }

    /**
     * @inheritDoc
     */
    public function delete(): void
    {
        Database::create()->prepare("DELETE FROM examen WHERE(id = :id)")->execute([
            'id' => $this->id
        ]);
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

}