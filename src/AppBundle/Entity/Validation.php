<?php
namespace AppBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;

class Validation
{


    /**
     * @var string
     * @Assert\NotBlank()
     */
    protected $secretkey;

    public function getSecretKey()
    {
        return $this->secretkey;
    }

    public function setSecretKey($secretkey)
    {
        $this->secretkey = $secretkey;
    }
}