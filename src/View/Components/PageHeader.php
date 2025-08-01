<?php

namespace FontAwesome\Migrator\View\Components;

use Illuminate\View\Component;

class PageHeader extends Component
{
    public string $icon;

    public string $title;

    public string $subtitle;

    public ?string $counterText;

    public ?string $counterIcon;

    public string $actionsLabel;

    public bool $hasActions;

    public bool $hasCounter;

    public function __construct(
        string $icon,
        string $title,
        string $subtitle = '',
        ?string $counterText = null,
        ?string $counterIcon = null,
        string $actionsLabel = 'Actions',
        bool $hasActions = false
    ) {

        $this->icon = $icon;
        $this->title = $title;
        $this->subtitle = $subtitle;
        $this->counterText = $counterText;
        $this->counterIcon = $counterIcon ?? $icon; // Par défaut, même icône que le header
        $this->actionsLabel = $actionsLabel;
        $this->hasActions = $hasActions;
        $this->hasCounter = ! empty($counterText); // Computed property dans le constructeur
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render()
    {

        return view('fontawesome-migrator::components.page-header');
    }
}
