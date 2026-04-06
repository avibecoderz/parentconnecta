<?php

namespace App\Livewire\School\Admin;

use Illuminate\Contracts\View\View;

abstract class PlaceholderPage extends SchoolAdminPage
{
    public function render(): View
    {
        $school = $this->currentSchool();

        return view('livewire.school.admin.placeholder-page', [
            'school' => $school,
            'title' => $this->title(),
            'eyebrow' => $this->eyebrow(),
            'description' => $this->description(),
            'cards' => $this->cards(),
            'nextSteps' => $this->nextSteps(),
        ])->layout('layouts.school.admin');
    }

    abstract protected function title(): string;

    abstract protected function eyebrow(): string;

    abstract protected function description(): string;

    /**
     * @return array<int, array{label: string, value: string|int, hint: string}>
     */
    abstract protected function cards(): array;

    /**
     * @return list<string>
     */
    abstract protected function nextSteps(): array;
}
