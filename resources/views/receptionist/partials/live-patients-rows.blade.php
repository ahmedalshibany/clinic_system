@forelse($livePatients as $appt)
<tr>
    <td class="recep-patient-name">{{ $appt->patient->name ?? __('messages.patient') }}</td>
    <td style="color: var(--text-secondary);">{{ $appt->doctor->name ?? __('messages.doctor') }}</td>
    <td>
        @php
            $fbClass = 'badge-dv-checkedin';
            $fbIcon = 'fa-user-check';
            $fbKey = 'messages.checked_in';
            if ($appt->status === 'waiting') { $fbClass = 'badge-dv-waiting'; $fbIcon = 'fa-chair'; $fbKey = 'messages.waiting'; }
            elseif ($appt->status === 'in_progress') { $fbClass = 'badge-dv-progress'; $fbIcon = 'fa-play-circle'; $fbKey = 'messages.in_progress'; }
        @endphp
        <span class="badge-dv {{ $fbClass }}">
            <i class="fas {{ $fbIcon }}" style="font-size: 0.65rem;"></i>
            {{ __($fbKey) }}
        </span>
    </td>
    <td>
        <span class="dv-since">
            @if($appt->checked_in_at)
                {{ $appt->checked_in_at->diffForHumans() }}
            @elseif($appt->started_at)
                {{ $appt->started_at->diffForHumans() }}
            @else
                {{ $appt->time->format('H:i') }}
            @endif
        </span>
    </td>
</tr>
@empty
<tr class="empty-row">
    <td colspan="4">
        <div class="dv-empty">
            <i class="fas fa-bed dv-empty-icon"></i>
            <span class="dv-empty-text">{{ __('messages.noActivePatients') }}</span>
        </div>
    </td>
</tr>
@endforelse
