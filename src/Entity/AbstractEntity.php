<?php

namespace App\Entity;

use App\Repository\Database;
use PDO;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractEntity
{
    protected int $id;          // Numéro auto-incrémenté.
    protected ?\DateTimeInterface $created;
    protected string $target;

    public function __construct(Request $req) {
        $this->id = $req->get('id') ?? 0;
    }

    /**
     * Crée une ligne d'instance dans la BDD ou applique des modifications sur l'existante.
     *
     * @return void
     */
    public abstract function save(): void;

    /**
     * Supprime de la BDD, la ligne correspondante.
     *
     * @return void
     */
    public abstract function delete(): void;

    /**
     * @return string
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId(int $id): void
    {
        $this->id = $_REQUEST['id'] ?? $id;
    }

    /**
     * @return string
     */
    public function getTarget(): string
    {
        return $this->target;
    }

    /**
     * @param string $target
     */
    public function setTarget(string $target): void
    {
        $this->target = $target;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getCreated(): ?\DateTimeInterface
    {
        return $this->created;
    }

    /**
     * @param \DateTimeInterface|null $created
     */
    public function setCreated(?\DateTimeInterface $created): void
    {
        $this->created = $created;
    }

    /**
     * Assignation de la valeur d'une propriété.
     *
     * @param sring $field
     * @param $value
     * @return void
     */
    public function set(string $field, $value): void
    {
        $this->{$field} = $value;
    }
}