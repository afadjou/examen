<?php

namespace App\Entity;

use App\Controller\ReleveController;
use App\Repository\Database;
use PDO;
use Symfony\Component\HttpFoundation\Request;

class Utilisateur extends AbstractEntity
{
    protected int $nin;         // Numéro d'Identification National
    protected string $name;
    protected string $firstName;
    protected ?\DateTimeInterface $birthDayDate;
    protected string $birthPlace;
    protected string $email;
    protected string $password;
    protected string $role;

    public function __construct(Request $req) {
        $this->id = $req->get('user_id') ?? 0;
        $this->target = 'utilisateur';
    }

    public function save(): void
    {
        // On vérifie si la ligne est déjà présente en BDD.
        $search = Database::create()->prepare("SELECT * FROM utilisateur WHERE(id = :id)");
        $search->setFetchMode(PDO::FETCH_CLASS, get_class($this));
        $search->execute([
            'id' => $this->id
        ]);

        $entities = $search->fetchAll(PDO::FETCH_OBJ);

        if (!empty($entities)) {
            // Application des modification

            $update = Database::create()->prepare("UPDATE utilisateur SET nin = :nin, " .
                "name = :name, firstName = :firstName, birthDayDate = :birthDayDate, " .
                "birthPlace = :birthPlace, email = :email, role = :role WHERE(id = :id)");

            $update->execute([
                'id' => $this->id,
                'nin' => $this->nin,
                'name' => $this->name,
                'firstName' => $this->firstName,
                'birthDayDate' => $this->birthDayDate->format('Y-m-d'),
                'birthPlace' => $this->birthPlace,
                'email' => $this->email,
                'role' => $this->role
            ]);

        } else {
            // Ajout d'une nouvelle entrée

            $insert = Database::create()->prepare("INSERT INTO utilisateur (created, nin, name, firstName, birthDayDate, birthPlace, email, role, password) " .
                "VALUES(:created, :nin, :name, :firstName, :birthDayDate, :birthPlace, :email, :role, :password)");

            $insert->execute([
                'created' => $this->created->format('Y-m-d'),
                'nin' => $this->nin,
                'name' => $this->name,
                'firstName' => $this->firstName,
                'birthDayDate' => $this->birthDayDate->format('Y-m-d'),
                'birthPlace' => $this->birthPlace,
                'email' => $this->email,
                'password' => password_hash($this->password, PASSWORD_DEFAULT),
                'role' => $this->role
            ]);
        }
    }

    public function delete(): void
    {
        $st = Database::create()->prepare("SELECT * FROM releve WHERE(id_utilisateur = :id_utilisateur)");
        $st->execute(['id_utilisateur' => $this->id]);
        if (!empty($results = $st->fetchAll(PDO::FETCH_ASSOC))) {
            foreach ($results as $result) {
                $releve = new Releve(new Request());
                $releve->setId($result['id']);
                $releve->delete();
            }

        }
        Database::create()->prepare("DELETE FROM utilisateur WHERE(id = :id)")->execute([
            'id' => $this->id
        ]);
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function setRole(string $role): void
    {
        $this->role = $role;
    }

    /**
     * @return int
     */
    public function getNin(): int
    {
        return $this->nin;
    }

    /**
     * @param int $nie
     */
    public function setNin(int $nin): void
    {
        $this->nin = $nin;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getFirstName(): string
    {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     */
    public function setFirstName(string $firstName): void
    {
        $this->firstName = $firstName;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getBirthDayDate(): ?\DateTimeInterface
    {
        return $this->birthDayDate;
    }

    /**
     * @param \DateTimeInterface|null $birthDayDate
     */
    public function setBirthDayDate(?\DateTimeInterface $birthDayDate): void
    {
        $this->birthDayDate = $birthDayDate;
    }

    /**
     * @return string
     */
    public function getBirthPlace(): string
    {
        return $this->birthPlace;
    }

    /**
     * @param string $birthPlace
     */
    public function setBirthPlace(string $birthPlace): void
    {
        $this->birthPlace = $birthPlace;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }


}