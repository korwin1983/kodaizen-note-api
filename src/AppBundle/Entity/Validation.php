<?php
namespace AppBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;

class Validation
{

    /**
     * @var string
     * @Assert\NotBlank()
     */
    protected $login;

    /**
     * @var string
     * @Assert\NotBlank()
     */
    protected $activationkey;

    public function getLogin()
    {
        return $this->login;
    }

    public function setLogin($login)
    {
        $this->login = $login;
    }

    public function getActivationkey()
    {
        return $this->activationkey;
    }

    public function setActivationkey($activationkey)
    {
        $this->activationkey = $activationkey;
    }
}