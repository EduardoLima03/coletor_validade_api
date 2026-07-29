@extends("layouts.app")

@section("title", "Notificações")

@section("content")
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="bi bi-bell"></i> Notificações</h4>
    <div class="d-flex gap-2">
        <form action="{{ route('admin.notifications.read-all') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-check-all"></i> Marcar todas como lidas
            </button>
        </form>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Voltar
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        @forelse ($notifications as $notif)
            <div class="d-flex align-items-start gap-3 p-3 border-bottom {{ $notif->read_at ? '' : 'bg-light' }}">
                <div class="fs-4 text-{{ $notif->color }}">
                    <i class="bi {{ $notif->icon }}"></i>
                </div>
                <div class="flex-grow-1">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <strong>{{ $notif->title }}</strong>
                            @if (!$notif->read_at)
                                <span class="badge bg-primary ms-1" style="font-size: 0.6rem;">NOVA</span>
                            @endif
                        </div>
                        <small class="text-muted text-nowrap ms-2">
                            {{ $notif->created_at->format('d/m/Y H:i') }}
                        </small>
                    </div>
                    <p class="mb-0 text-muted small">{{ $notif->message }}</p>
                </div>
                @if (!$notif->read_at)
                    <form action="{{ route('admin.notifications.read', $notif->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-secondary" title="Marcar como lida">
                            <i class="bi bi-check"></i>
                        </button>
                    </form>
                @endif
            </div>
        @empty
            <div class="text-center py-5 text-muted">
                <i class="bi bi-bell-slash fs-1 d-block mb-2"></i>
                Nenhuma notificação.
            </div>
        @endforelse
    </div>
</div>

<div class="mt-3">
    {{ $notifications->links() }}
</div>
@endsection
