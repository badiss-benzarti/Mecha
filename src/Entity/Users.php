<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'users')]
class Users
{

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $nom = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $prenom = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $email = null;
// src/Entity/Users.php
#[ORM\Id]
#[ORM\GeneratedValue]
#[ORM\Column(type: 'integer')]
private ?int $id = null;

public function setId(int $id): self
{
    $this->id = $id;
    return $this;
}
    // Add other fields as needed

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;
        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): self
    {
        $this->prenom = $prenom;
        return $this;
    }

    public function getFullName(): string
    {
        return $this->prenom . ' ' . $this->nom;
    }
    #[ORM\Column(type: 'string', length: 180, unique: true)]



public function getEmail(): string
{
    return $this->email;
}

public function setEmail(string $email): self
{
    $this->email = $email;
    return $this;
}

}