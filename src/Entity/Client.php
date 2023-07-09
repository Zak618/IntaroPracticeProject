<?php

namespace App\Entity;

use App\Repository\ClientRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use PhpParser\Node\Expr\FuncCall;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use RetailCrm\Api\Factory\SimpleClientFactory;
use RetailCrm\Api\Model\Filter\Customers\CustomerFilter;
use RetailCrm\Api\Model\Request\Customers\CustomersRequest;

#[ORM\Entity(repositoryClass: ClientRepository::class)]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class Client implements UserInterface, PasswordAuthenticatedUserInterface
{ 
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    public $firstname;
    public $lastname;
    public $patronymic;
    public $phone;
    public $birthday;
    public $address;
    public $sex;
    public $isCrmLoad = false;

    #[ORM\OneToOne(mappedBy: 'id_client', cascade: ['persist', 'remove'])]
    private ?Basket $basket = null;

    #[ORM\Column(type: Types::GUID)]
    private ?string $uuid = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
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

    public function setRoles(array $roles): static
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

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getBasket(): ?Basket
    {
        return $this->basket;
    }

    public function setBasket(?Basket $basket): static
    {
        // unset the owning side of the relation if necessary
        if ($basket === null && $this->basket !== null) {
            $this->basket->setIdClient(null);
        }

        // set the owning side of the relation if necessary
        if ($basket !== null && $basket->getIdClient() !== $this) {
            $basket->setIdClient($this);
        }

        $this->basket = $basket;

        return $this;
    }

    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    public function setUuid(string $uuid): static
    {
        $this->uuid = $uuid;

        return $this;
    }

    public function crmLoad()
    {
        if(!$this->isCrmLoad && $this->uuid)
        {
            $client = SimpleClientFactory::createClient($_ENV['RETAIL_CRM_URL'], $_ENV['API_KEY']);

            $customersRequest = new CustomersRequest();
            $customersRequest->filter = new CustomerFilter();
            $customersRequest->filter->externalIds = [$this->uuid];

            try {
                $customersResponse = $client->customers->list($customersRequest);
                if (0 === count($customersResponse->customers)) return false;
                
                $resultClient = $customersResponse->customers[0];
                $this->firstname = $resultClient->firstName;
                $this->lastname = $resultClient->lastName;
                $this->patronymic = $resultClient->patronymic;
                $this->phone = $resultClient->phones;
                $this->birthday = $resultClient->birthday;
                $this->address = $resultClient->address;
                $this->sex = $resultClient->sex;

                $this->isCrmLoad = true;

            } catch (Exception $exception) {
                dd($exception);
                exit(-1);
            }

            return true;
        } else {
            return false;
        }
    }
}
