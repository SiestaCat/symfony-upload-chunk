<?php declare( strict_types = 1 );

namespace Siestacat\UploadChunkBundle\Form\ProcessRequest;

use Siestacat\UploadChunkBundle\Form\Process\Data\ProcessRequestData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProcessRequestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('request_id')
        ;
    }

    public function getBlockPrefix()
    {
        return '';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProcessRequestData::class,
            'method' => 'POST',
            'csrf_protection' => false
        ]);
    }
}
