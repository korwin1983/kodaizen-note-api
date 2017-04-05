<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity()
 * @ORM\Table(name="notes")
 * @UniqueEntity("name")
 */

class Note
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank()
     * @Assert\Type("string")
     */
    protected $name;

    /**
     * @ORM\Column(type="string")
     * @Assert\Length(min=10)
     * @Assert\Type("string")
     */
    protected $content;

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getContent()
    {
        return $this->content;
    }

//    public function setId()
//    {
//        return $this->id;
//    }

    public function setName($name)
    {
        $this->name = $name;
        return $this->name;
    }

    public function setContent($content)
    {
        $this->content = $content;
        return $this->content;
    }


}