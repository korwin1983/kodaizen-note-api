<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\Common\Collections\ArrayCollection;
use AppBundle\Entity\Note;


/**
 * @ORM\Entity()
 * @ORM\Table(name="su_projects")
 */

class Project
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @ORM\Column(type="string")
     */
    protected $name;

	/**
	 * @ORM\OneToMany(targetEntity="Note", mappedBy="project")
	 * @var Note[]
	 */
    protected $notes;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="projects")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
     * @var user
     */
    protected $user;


    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

	public function __construct()
	{
		$this->notes = new ArrayCollection();
	}

	public function getNotes()
	{
		return $this->notes;
	}

    public function setUser($user)
    {
        return $this->user = $user;
    }


}