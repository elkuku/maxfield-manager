<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;

class MaxfieldZipType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'zipfile',
                FileType::class,
                [
                    'label'       => 'ZIP File',
                    'mapped'      => false,
                    'required'    => true,
                    'constraints' => [
                        new File([
                            'maxSize'          => '1024k',
                            'mimeTypes'        => [
                                'application/zip',
                            ],
                            'mimeTypesMessage' => 'Please upload a valid ZIP file',
                        ]),
                    ],
                ]
            );
    }
}
