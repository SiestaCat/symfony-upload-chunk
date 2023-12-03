<?php

namespace Siestacat\UploadChunkBundle\Form\Upload;

use Siestacat\UploadChunkBundle\Form\Upload\Data\UploadPartData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;

class UploadPartType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('request_id')
            ->add('file_id')
            ->add('part_index')
            ->add('file', FileType::class)
        ;
    }

    public function getBlockPrefix()
    {
        return '';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => UploadPartData::class,
            'method' => 'POST',
            'csrf_protection' => false
        ]);
    }
}
