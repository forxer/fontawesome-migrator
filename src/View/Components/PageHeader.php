<?php

declare(strict_types=1);

namespace FontAwesome\Migrator\View\Components;

use Illuminate\View\Component;

class PageHeader extends Component
{
    public ?string $counterIcon;

    public bool $hasCounter;

    public function __construct(
        public string $icon,
        public string $title,
        public string $subtitle = '',
        public ?string $counterText = null,
        ?string $counterIcon = null,
        public string $actionsLabel = 'Actions',
        public bool $hasActions = false
    ) {

        $this->counterIcon = $counterIcon ?? $this->icon;
        $this->hasCounter = $this->counterText !== null && $this->counterText !== '' && $this->counterText !== '0'; // Computed property dans le constructeur
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render()
    {

        return view('fontawesome-migrator::components.page-header');
    }
}
