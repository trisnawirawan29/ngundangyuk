<?php

namespace App\Http\Controllers;

use App\Models\Invitation;
use App\Models\InvitationTemplate;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class InvitationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('invitations.index', ['invitations' => Auth::user()->invitations()->withCount('rsvps')->latest()->get(), 'templates' => $this->templates()]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('invitations.create', ['templates' => $this->templates(), 'sectionLabels' => config('invitations.sections'), 'invitation' => new Invitation(['template' => 'wedding-premium', 'sections' => array_keys(config('invitations.sections'))])]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $invitation = Auth::user()->invitations()->create($this->validatedData($request));

        return redirect()->route('invitations.edit', $invitation)->with('success', 'Undangan berhasil dibuat. Silakan lengkapi isinya.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): RedirectResponse
    {
        return redirect()->route('invitations.edit', $id);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Invitation $invitation): View
    {
        abort_unless($invitation->user_id === Auth::id(), 404);

        return view('invitations.create', ['templates' => $this->templates($invitation), 'sectionLabels' => config('invitations.sections'), 'invitation' => $invitation]);
    }

    public function preview(Invitation $invitation): View
    {
        abort_unless($invitation->user_id === Auth::id(), 404);

        return view('invitations.show', [
            'invitation' => $invitation,
            'template' => $this->templateData($invitation->template),
            'formattedDate' => Carbon::parse($invitation->content['date'])->translatedFormat('l, d F Y'),
            'isPreview' => true,
        ]);
    }

    public function templatePreview(string $template): View
    {
        $invitationTemplate = InvitationTemplate::query()->where('slug', $template)->where('is_active', true)->where(function ($query): void {
            $query->whereNull('user_id')->orWhere('user_id', Auth::id());
        })->firstOrFail();
        $sample = new Invitation(['title' => 'Contoh Undangan · '.$invitationTemplate->name, 'slug' => 'template-preview', 'template' => $invitationTemplate->slug, 'content' => ['groom_name' => 'Raka', 'bride_name' => 'Naya', 'date' => now()->addMonths(2)->toDateString(), 'time' => '10:00 WIB', 'venue' => 'Taman Bahagia', 'address' => 'Jl. Harmoni No. 1', 'hero_message' => 'Dengan penuh kebahagiaan, kami mengundang Anda.', 'story' => 'Kisah kami dimulai dari sebuah pertemuan sederhana.', 'rsvp_url' => ''], 'sections' => array_keys(config('invitations.sections'))]);

        return view('invitations.show', ['invitation' => $sample, 'template' => $this->templateDataFromModel($invitationTemplate), 'formattedDate' => now()->addMonths(2)->translatedFormat('l, d F Y'), 'isPreview' => true, 'templatePreview' => true]);
    }

    public function rsvps(Invitation $invitation): View
    {
        abort_unless($invitation->user_id === Auth::id(), 404);

        return view('invitations.rsvps', ['invitation' => $invitation, 'rsvps' => $invitation->rsvps()->latest()->get()]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Invitation $invitation): RedirectResponse
    {
        abort_unless($invitation->user_id === Auth::id(), 404);
        $invitation->update($this->validatedData($request, $invitation));

        return redirect()->route('invitations.edit', $invitation)->with('success', 'Perubahan undangan berhasil disimpan.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Invitation $invitation): RedirectResponse
    {
        abort_unless($invitation->user_id === Auth::id(), 404);
        $invitation->delete();

        return redirect()->route('invitations.index')->with('success', 'Undangan berhasil dihapus.');
    }

    public function togglePublish(Invitation $invitation): RedirectResponse
    {
        abort_unless($invitation->user_id === Auth::id(), 404);
        $invitation->update(['is_published' => ! $invitation->is_published]);

        return back()->with('success', $invitation->is_published ? 'Undangan sudah dipublikasikan.' : 'Undangan disembunyikan dari publik.');
    }

    private function validatedData(Request $request, ?Invitation $invitation = null): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'slug' => ['required', 'alpha_dash', 'min:3', 'max:80', Rule::unique('invitations', 'slug')->ignore($invitation)],
            'template' => ['required', Rule::exists('invitation_templates', 'slug')->where('is_active', true)],
            'sections' => ['nullable', 'array'], 'sections.*' => [Rule::in(array_keys(config('invitations.sections')))],
            'content.groom_name' => ['required', 'string', 'max:80'], 'content.bride_name' => ['required', 'string', 'max:80'],
            'content.date' => ['required', 'date'], 'content.time' => ['required', 'string', 'max:80'],
            'content.venue' => ['required', 'string', 'max:160'], 'content.address' => ['required', 'string', 'max:255'],
            'content.hero_message' => ['nullable', 'string', 'max:500'], 'content.story' => ['nullable', 'string', 'max:2000'],
            'content.rsvp_url' => ['nullable', 'url', 'max:500'],
        ]);

        return ['title' => $data['title'], 'slug' => Str::slug($data['slug']), 'template' => $data['template'], 'content' => Arr::only($data['content'], ['groom_name', 'bride_name', 'date', 'time', 'venue', 'address', 'hero_message', 'story', 'rsvp_url']), 'sections' => array_values($data['sections'] ?? []), 'is_published' => $invitation?->is_published ?? false];
    }

    private function templates(?Invitation $invitation = null): array
    {
        $templates = InvitationTemplate::query()->whereNull('user_id')->where('is_active', true)->get();

        return $templates->mapWithKeys(fn (InvitationTemplate $template): array => [$template->slug => $this->templateDataFromModel($template) + ['preview_url' => route('invitations.templates.preview', $template->slug)]])->all();
    }

    private function templateData(string $slug): array
    {
        $template = InvitationTemplate::query()->where('slug', $slug)->firstOrFail();

        return $this->templateDataFromModel($template);
    }

    private function templateDataFromModel(InvitationTemplate $template): array
    {
        return ['name' => $template->name, 'description' => $template->description, 'accent' => $template->accent_color, 'paper' => $template->paper_color, 'class' => $template->design_class.' invitation-header-'.$template->header_style.' invitation-hero-'.$template->hero_style.' invitation-footer-'.$template->footer_style.' invitation-opening-'.$template->opening_style, 'image_url' => $template->image_url, 'header_image_url' => $template->header_image_url, 'opening' => $template->opening_style];
    }
}
