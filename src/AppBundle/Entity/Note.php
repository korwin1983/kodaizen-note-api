<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity()
 * @ORM\Table(name="su_notes")
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
	 * @ORM\Column(type="string", nullable=false, name="no_name")
	 * @Assert\NotBlank()
	 * @Assert\NotNull()
	 * @Assert\Type("string")
	 */
	protected $name;

	/**
	 * @ORM\Column(type="string", nullable=true, name="no_content")
	 * @Assert\Type("string")
	 */
	protected $content;

	/**
	 * @ORM\ManyToOne(targetEntity="Project", inversedBy="notes")
     * @ORM\JoinColumn(name="project_id", referencedColumnName="id", onDelete="CASCADE")
	 * @var project
	 */
	protected $project;

    /**
     * @ORM\Column(type="datetime", name="no_createdat")
     * @var \DateTime
     */
    protected $createdAt;

    /**
     * @ORM\Column(type="datetime", name="no_updatedat", nullable=true)
     * @var \DateTime
     */
    protected $updatedAt;

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

    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;
    }

    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTime $updatedAt)
    {
        $this->updatedAt = $updatedAt;
    }


}