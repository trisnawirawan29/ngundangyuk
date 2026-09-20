<?php

namespace App\Http\Controllers;

use App\Models\Invitation;
use App\Models\InvitationTemplate;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PublicInvitationController extends Controller
{
    public function show(Request $request, string $slug): View
    {
        $invitation = Invitation::query()->published()->where('slug', $slug)->firstOrFail();
        $recipientName = $request->query('to');
        $recipientName = is_string($recipientName) && trim($recipientName) !== '' ? Str::limit(trim(strip_tags($recipientName)), 80, '') : 'Tamu Undangan';

        return view('invitations.show', ['invitation' => $invitation, 'template' => $this->templateData($invitation->template), 'formattedDate' => Carbon::parse($invitation->content['date'])->translatedFormat('l, d F Y'), 'recipientName' => $recipientName]);
    }

    public function storeRsvp(Request $request, string $slug): RedirectResponse
    {
        $invitation = Invitation::query()->published()->where('slug', $slug)->firstOrFail();
        $data = $request->validate([
            'guest_name' => ['required', 'string', 'max:120'],
            'attendance' => ['required', 'in:hadir,tidak_hadir,ragu_ragu'],
            'guest_count' => ['required', 'integer', 'min:1', 'max:10'],
            'message' => ['nullable', 'string', 'max:500'],
        ], [
            'guest_name.required' => 'Nama wajib diisi.',
            'attendance.required' => 'Pilih status kehadiran Anda.',
            'guest_count.max' => 'Jumlah tamu maksimal 10 orang.',
        ]);

        $invitation->rsvps()->create($data);

        return back()->with('rsvp_success', 'Konfirmasi kehadiran berhasil dikirim. Terima kasih!');
    }

    private function templateData(string $slug): array
    {
        $template = InvitationTemplate::query()->where('slug', $slug)->firstOrFail();

        return ['name' => $template->name, 'description' => $template->description, 'accent' => $template->accent_color, 'paper' => $template->paper_color, 'class' => $template->design_class.' invitation-header-'.$template->header_style.' invitation-hero-'.$template->hero_style.' invitation-footer-'.$template->footer_style.' invitation-opening-'.$template->opening_style, 'image_url' => $template->image_url, 'header_image_url' => $template->header_image_url, 'opening' => $template->opening_style];
    }
}
