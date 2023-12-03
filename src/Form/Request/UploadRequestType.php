<?php declare( strict_types = 1 );

namespace Siestacat\UploadChunkBundle\Form\Request;

use Siestacat\UploadChunkBundle\Validator\Request\Files\Limit;
use Siestacat\UploadChunkBundle\Validator\Request\Files\Size;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class UploadRequestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('files', CollectionType::class, [
                'entry_type' => UploadRequestFileType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'constraints' => [
                    new Limit,
                    new Size
                ]
            ])
        ;
    }

    public function getBlockPrefix()
    {
        return 'upload_chunk';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'method' => 'POST',
            'csrf_protection' => false
        ]);
    }
}
