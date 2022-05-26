<?php

namespace Filament\Forms\Components;

use Filament\Forms\Components\Wizard\Step;
use Filament\Support\Concerns\HasExtraAlpineAttributes;
use Illuminate\Contracts\Support\Htmlable;
use Livewire\Component as LivewireComponent;

class Wizard extends Component
{
    use HasExtraAlpineAttributes;

    protected string | Htmlable | null $cancelAction = null;

    protected string | Htmlable | null $submitAction = null;

    protected string $view = 'forms::components.wizard';

    final public function __construct(array $steps = [])
    {
        $this->steps($steps);
    }

    public static function make(array $steps = []): static
    {
        $static = app(static::class, ['steps' => $steps]);
        $static->setUp();

        return $static;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->registerListeners([
            'wizard::nextStep' => [
                function (Wizard $component, string $statePath, string $currentStep): void {
                    if ($statePath !== $component->getStatePath()) {
                        return;
                    }

                    $component->getChildComponentContainer()->getComponents()[$currentStep]->getChildComponentContainer()->validate();

                    /** @var LivewireComponent $livewire */
                    $livewire = $component->getLivewire();
                    $livewire->dispatchBrowserEvent('next-wizard-step', [
                        'statePath' => $statePath,
                    ]);
                },
            ],
        ]);
    }

    public function steps(array $steps): static
    {
        $this->childComponents($steps);

        return $this;
    }

    public function cancelAction(string | Htmlable | null $action): static
    {
        $this->cancelAction = $action;

        return $this;
    }

    public function submitAction(string | Htmlable | null $action): static
    {
        $this->submitAction = $action;

        return $this;
    }

    public function getConfig(): array
    {
        return collect($this->getChildComponentContainer()->getComponents())
            ->filter(static fn (Step $step): bool => ! $step->isHidden())
            ->mapWithKeys(static fn (Step $step): array => [$step->getId() => $step->getLabel()])
            ->toArray();
    }

    public function getCancelAction(): string | Htmlable | null
    {
        return $this->cancelAction;
    }

    public function getSubmitAction(): string | Htmlable | null
    {
        return $this->submitAction;
    }
}