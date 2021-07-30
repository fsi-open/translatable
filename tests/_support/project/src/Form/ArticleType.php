<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Tests\FSi\App\Form;

use FSi\Component\Files\Integration\Symfony\Form\WebFileType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Valid;
use Tests\FSi\App\Entity\Article;

final class ArticleType extends AbstractType
{
    /**
     * @param FormBuilderInterface<FormBuilderInterface> $builder
     * @param array<string, mixed> $options
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('title', TextType::class, [
            'label' => 'Title',
            'required' => false
        ]);

        $builder->add('description', TextareaType::class, [
            'label' => 'Description',
            'required' => false
        ]);

        $builder->add('publicationDate', DateType::class, [
            'label' => 'Publication date',
            'constraints' => [new NotBlank()],
            'input' => 'datetime_immutable',
            'widget' => 'single_text',
            'required' => false
        ]);

        $builder->add('photo', WebFileType::class, [
            'label' => 'Photo',
            'required' => false
        ]);

        $builder->add('author', AuthorType::class, [
            'label' => 'Author',
            'required' => false
        ]);

        $builder->add('banner', BannerType::class, [
            'label' => 'Banner',
            'required' => false
        ]);

        $builder->add('comments', CollectionType::class, [
            'label' => 'Comments',
            'constraints' => [new Valid()],
            'entry_type' => CommentType::class,
            'allow_add' => true,
            'allow_delete' => true,
            'by_reference' => false,
            'delete_empty' => true,
            'required' => false
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('data_class', Article::class);
    }
}
