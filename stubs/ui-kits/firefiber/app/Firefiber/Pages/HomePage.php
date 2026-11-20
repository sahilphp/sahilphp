<?php

declare(strict_types=1);

namespace App\Firefiber\Pages;

use Sahil\Firefiber\Attributes\On;
use Sahil\Firefiber\Component;
use Sahil\Firefiber\Page;
use Sahil\Http\Request;

/**
 * HomePage — a first-class Firefiber page.
 *
 * Mount it explicitly in `routes/web.php`:
 *
 *     Link::firefiber('/', HomePage::class)->name('home');
 *
 * The page owns its state, actions and lifecycle — no controller involved.
 * It also demonstrates scoped styles (styles()), an event listener
 * (#[On]) and a browser event dispatch.
 */
final class HomePage extends Page
{
    protected array $state = ['visits' => 0];

    protected array $actions = ['increment'];

    public function path(): string
    {
        return '/';
    }

    public function title(): string
    {
        return 'Home';
    }

    protected function mount(array $params = [], ?Request $request = null): void
    {
    }

    public function increment(): void
    {
        $visits = (int) ($this->state['visits'] ?? 0) + 1;
        $this->updateState(['visits' => $visits]);
        $this->dispatchBrowserEvent('visit-counted', ['visits' => $visits]);
    }

    /** Components in this page's tree can dispatch('reset-visits') here. */
    #[On('reset-visits')]
    public function resetVisits(): void
    {
        $this->updateState(['visits' => 0]);
    }

    /**
     * Scoped under this page's [data-fx] root at render time — these
     * selectors cannot leak into other components or the layout.
     */
    protected function styles(): string
    {
        return '.home-title { letter-spacing: .02em; }'
            .'.visit-count { font-weight: 700; }';
    }

    /**
     * @return list<Component>
     */
    public function components(): array
    {
        return [];
    }

    public function view(): ?string
    {
        return 'firefiber.pages.home';
    }
}
