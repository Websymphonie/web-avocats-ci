<?php declare(strict_types=1);

namespace Websymphonie\SharedContext\Presenter\Form\FormExtension;


use Psr\Log\LoggerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Websymphonie\SharedContext\Domain\Service\EventSubscriber\Form\AntispamSubscriber;

/** @extends AbstractType<mixed> */
class AntispamType extends AbstractType
{
    protected const string ANTI_SPAM_FORM_FOR_PHONE_BOT = 'phone';
    protected const string ANTI_SPAM_FORM_FOR_FAXNUMBER_BOT = 'faxNumber';

    public function __construct(private readonly LoggerInterface $antispamLogger, private readonly RequestStack $requestStack)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(self::ANTI_SPAM_FORM_FOR_PHONE_BOT, TextType::class, $this->setDatasConfigurations())
            ->add(self::ANTI_SPAM_FORM_FOR_FAXNUMBER_BOT, TextType::class, $this->setDatasConfigurations())
            ->addEventSubscriber(new AntispamSubscriber($this->antispamLogger, $this->requestStack));
    }

    /** @return array<string, mixed> */
    protected function setDatasConfigurations(): array
    {
        return [
            'row_attr' => [
                'class' => 'antispam_robot hidden',
            ],
            'attr' => [
                'autocomplete' => 'off',
                'tabindex' => '-1',
            ],
            'mapped' => false,
            'required' => false,
        ];
    }
}
