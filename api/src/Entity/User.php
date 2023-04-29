<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use ApiPlatform\Metadata\ApiResource;
use Symfony\Component\Serializer\Annotation\SerializedName;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use Symfony\Component\Serializer\Annotation\Groups;
use App\State\UserPasswordHasherProcessor;
use ApiPlatform\OpenApi\Model;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    normalizationContext: ['groups' => ['user:read']],
    denormalizationContext: ['groups' => ['user:create']],
    operations: [
        new Post(processor: UserPasswordHasherProcessor::class,
            uriTemplate: '/registration',
            security: "is_granted('ROLE_ADMIN')",
            openapi: new Model\Operation(
                summary: 'Добавление нового пользователя ',
                description: 'Доступно только администратору'
            )
        ),
        new Patch(processor: UserPasswordHasherProcessor::class,
            security: "is_granted('ROLE_ADMIN') or object == user",
            securityMessage: 'Access denied',
            normalizationContext: ['groups' => ['user:passwordRead']],
            denormalizationContext: ['groups' => ['user:update']],
            openapi: new Model\Operation(
                summary: 'Редактирование существующего пользователя ',
                description: 'Доступно только для редактирования своего профиля и администратору'
            )
        ),
        new Delete(
            security: "is_granted('ROLE_ADMIN')",
            openapi: new Model\Operation(
                summary: 'Удаление существующего пользователя',
                description: 'Доступно только администратору'
            )
        ),
    ])
]

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups(['user:create', 'user:passwordRead', 'user:update'])]
    #[ORM\Column(length: 180, unique: true)]
    #[Assert\NotBlank()]
    #[Assert\Email()]
    private ?string $email = null;

    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[Groups(['user:create', 'user:update'])]
    #[ORM\Column(length: 255)]
    #[Assert\NotBlank()]
    #[Assert\Length(min:2)]
    private ?string $firstName = null;

    #[Groups(['user:create', 'user:update'])]
    #[ORM\Column(length: 255)]
    #[Assert\NotBlank()]
    #[Assert\Length(min:2)]
    private ?string $lastName = null;

    #[Groups(['user:create', 'user:update'])]
    #[ORM\Column(length: 12)]
    #[Assert\NotBlank()]
    #[Assert\Length(min:7, minMessage:"Minimum length 7")]
    #[Assert\Regex(pattern:"/^\+\d+$/", message:"number_only") ]
    private ?string $phone = null;

    #[Groups(['user:create', 'user:update'])]
    #[SerializedName('password')]
    #[Assert\NotBlank()]
    #[Assert\Length(min:6)]
    private $plainPassword;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPlainPassword()
    {
        return $this->plainPassword;
    }

    /**
     * @param   mixed  $plainPassword
     */
    public function setPlainPassword($plainPassword): void
    {
        $this->plainPassword = $plainPassword;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        $this->plainPassword = null;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

}
