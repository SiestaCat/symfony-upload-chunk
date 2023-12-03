<?php declare( strict_types = 1 );

namespace Siestacat\UploadChunkBundle\Form\Process;

use Siestacat\UploadChunkBundle\Form\Process\Data\ProcessData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProcessType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('request_id')
            ->add('file_id')
        ;
    }

    public function getBlockPrefix()
    {
        return '';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProcessData::class,
            'method' => 'POST',
            'csrf_protection' => false
        ]);
    }
}
