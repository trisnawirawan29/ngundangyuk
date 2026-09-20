<?php

namespace App\Http\Controllers;

use App\Models\Invitation;
use App\Models\InvitationTemplate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminTemplateController extends Controller
{
    public function index(): View
    {
        return view('admin.templates', ['templates' => InvitationTemplate::query()->whereNull('user_id')->latest()->get()]);
    }

    public function create(): View
    {
        return view('admin.template-form', ['template' => new InvitationTemplate(['is_active' => true, 'design_class' => 'invitation-template-classic', 'accent_color' => '#9a6a48', 'paper_color' => '#fffaf5'])]);
    }

    public function store(Request $request): RedirectResponse
    {
        InvitationTemplate::create($this->validatedData($request));

        return redirect()->route('admin.templates.index')->with('success', 'Template berhasil ditambahkan.');
    }

    public function edit(InvitationTemplate $template): View
    {
        return view('admin.template-form', ['template' => $template]);
    }

    public function update(Request $request, InvitationTemplate $template): RedirectResponse
    {
        $template->update($this->validatedData($request, $template));

        return redirect()->route('admin.templates.index')->with('success', 'Template berhasil diperbarui.');
    }

    public function destroy(InvitationTemplate $template): RedirectResponse
    {
        if (Invitation::query()->where('template', $template->slug)->exists()) {
            return back()->with('error', 'Template tidak dapat dihapus karena sudah digunakan oleh undangan. Nonaktifkan template tersebut.');
        }

        $template->delete();

        return redirect()->route('admin.templates.index')->with('success', 'Template berhasil dihapus.');
    }

    public function preview(InvitationTemplate $template): View
    {
        $sample = new Invitation([
            'title' => 'Contoh Undangan',
            'slug' => 'template-preview',
            'template' => $template->slug,
            'content' => ['groom_name' => 'Raka', 'bride_name' => 'Naya', 'date' => now()->addMonths(2)->toDateString(), 'time' => '10:00 WIB', 'venue' => 'Taman Bahagia', 'address' => 'Jl. Harmoni No. 1', 'hero_message' => 'Dengan penuh kebahagiaan, kami mengundang Anda.', 'story' => 'Kisah kami dimulai dari sebuah pertemuan sederhana.', 'rsvp_url' => ''],
            'sections' => ['header', 'hero', 'story', 'couple', 'countdown', 'event', 'gallery', 'rsvp', 'footer'],
        ]);

        return view('invitations.show', ['invitation' => $sample, 'template' => $this->templateData($template), 'formattedDate' => now()->addMonths(2)->translatedFormat('l, d F Y'), 'isPreview' => true, 'templatePreview' => true]);
    }

    private function validatedData(Request $request, ?InvitationTemplate $template = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'slug' => ['required', 'alpha_dash', 'max:80', Rule::unique('invitation_templates', 'slug')->ignore($template)],
            'description' => ['nullable', 'string', 'max:255'],
            'accent_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'paper_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'design_class' => ['required', 'alpha_dash', 'max:80'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        return ['name' => $data['name'], 'slug' => Str::slug($data['slug']), 'description' => $data['description'] ?? null, 'accent_color' => $data['accent_color'], 'paper_color' => $data['paper_color'], 'design_class' => $data['design_class'], 'is_active' => $request->boolean('is_active')];
    }

    private function templateData(InvitationTemplate $template): array
    {
        return ['name' => $template->name, 'description' => $template->description, 'accent' => $template->accent_color, 'paper' => $template->paper_color, 'class' => $template->design_class.' invitation-header-'.$template->header_style.' invitation-hero-'.$template->hero_style.' invitation-footer-'.$template->footer_style.' invitation-opening-'.$template->opening_style, 'image_url' => $template->image_url, 'header_image_url' => $template->header_image_url, 'opening' => $template->opening_style];
    }
}
