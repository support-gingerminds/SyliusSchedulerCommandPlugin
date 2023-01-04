<?php

declare(strict_types=1);

namespace Synolia\SyliusSchedulerCommandPlugin\Grid\FieldType;

use Sylius\Component\Grid\Definition\Field;
use Sylius\Component\Grid\FieldTypes\FieldTypeInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Synolia\SyliusSchedulerCommandPlugin\Humanizer\HumanizerInterface;
use Twig\Environment;

final class ScheduledCommandHumanReadableExpressionType implements FieldTypeInterface
{
    public function __construct(private Environment $twig, private HumanizerInterface $humanizer)
    {
    }

    /**
     * @inheritdoc
     */
    public function render(Field $field, $scheduleCommand, array $options): string
    {
        return $this->twig->render(
            $options['template'],
            [
                'schedulerCommand' => $scheduleCommand,
                'value' => $this->humanizer->humanize($scheduleCommand->getCronExpression()),
            ],
        );
    }

    /** @inheritdoc */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('template');
    }
}
