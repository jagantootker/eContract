<?php

namespace App\Notifications;

use App\Models\Kontrak;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ContractExpiryNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Kontrak $kontrak) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $tamat    = $kontrak->tamat_tarikh?->format('d/m/Y');
        $daysLeft = now()->diffInDays($this->kontrak->tamat_tarikh, false);

        return (new MailMessage)
            ->subject("[eKontrak] Amaran Tamat Kontrak — {$this->kontrak->no_kontrak}")
            ->greeting("Salam, {$notifiable->name}")
            ->line("Kontrak berikut akan tamat dalam **{$daysLeft} hari** ({$tamat}):")
            ->line("**No. Kontrak:** {$this->kontrak->no_kontrak}")
            ->line("**Tajuk:** {$this->kontrak->tajuk_kontrak}")
            ->line("**Syarikat:** {$this->kontrak->syarikat?->nama_syarikat}")
            ->line("**Nilai Kontrak:** RM " . number_format($this->kontrak->nilai_kontrak, 2))
            ->action('Lihat Kontrak', url("/kontrak/{$this->kontrak->id}"))
            ->line('Sila ambil tindakan yang sewajarnya sebelum tarikh tamat kontrak.')
            ->salutation('Sekian, Sistem eKontrak KPKT/JBPM');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'kontrak_id' => $this->kontrak->id,
            'no_kontrak' => $this->kontrak->no_kontrak,
            'tamat_tarikh' => $this->kontrak->tamat_tarikh,
        ];
    }
}
