<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity()
 * @ORM\Table(name="su_notes")
 * @UniqueEntity("name")
 */
class Note
{

	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer", name="no_id")
	 * @ORM\GeneratedValue
	 */
	protected $id;

	/**
	 * @ORM\Column(type="string", nullable=false, unique=true, name="no_name")
	 * @Assert\NotBlank()
	 * @Assert\NotNull()
	 * @Assert\Type("string")
	 */
	protected $name;

	/**
	 * @ORM\Column(type="string", nullable=true)
	 * @Assert\Type("string")
	 */
	protected $content;

	/**
	 * @ORM\ManyToOne(targetEntity="Project", inversedBy="notes")
	 * @var project
	 */
	protected $project;

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

	public function setProject($project)
	{
		return $this->project = $project;
	}

	public function getProject()
	{
		return $this->project;
	}

}