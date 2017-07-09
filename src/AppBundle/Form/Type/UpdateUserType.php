<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;

class UpdateUserType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
        $builder->add('secretkey');
		$builder->add('firstname');
		$builder->add('lastname');
        $builder->add('plainPassword'); // Rajout du mot de passe
		$builder->add('email');
		$builder->add('role');
	}

	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefaults([
			'data_class' => 'AppBundle\Entity\User',
			'csrf_protection' => false
		]);
	}
}
